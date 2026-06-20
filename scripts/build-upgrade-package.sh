#!/usr/bin/env bash
#
# NiceShoply 在线升级包构建脚本
# -----------------------------------------------------------------------------
# 生成符合 UpgradeService 规范的 overlay zip（含 upgrade-manifest.json）。
# 包内路径与商家站点 base_path()（artisan 所在目录）一致。
#
# 用法示例：
#   # 增量包（推荐）：自 tag v1.6.8 以来变更的文件
#   ./scripts/build-upgrade-package.sh --version 1.7.0 --build 20260614 \
#       --min-version 1.6.0 --incremental v1.6.8
#
#   # 全量核心源码（不含 vendor / 前端构建产物）
#   ./scripts/build-upgrade-package.sh --version 1.7.0 --full
#
#   # 全量 + vendor + public/build（依赖或前端有变更时）
#   ./scripts/build-upgrade-package.sh --version 1.7.0 --full --with-vendor --with-assets
#
#   # 指定待删除文件（写入 manifest.delete）
#   ./scripts/build-upgrade-package.sh --version 1.7.0 --incremental v1.6.8 \
#       --delete "niceshoply/common/src/Services/OldService.php"
#
# 输出：dist/upgrades/niceshoply-{version}.zip
# 构建完成后会打印 size / sha256，便于在 marketplace Admin → Upgrades 填写或核对。
# -----------------------------------------------------------------------------
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

GIT_ROOT="$(git rev-parse --show-toplevel 2>/dev/null || echo "$ROOT_DIR")"
# git 仓库内 app 目录相对 git 根的路径前缀（如 monorepo 中为 niceshoply/）
APP_GIT_PREFIX=""
if [[ "$GIT_ROOT" != "$ROOT_DIR" ]]; then
  APP_GIT_PREFIX="$(python3 -c "import os; print(os.path.relpath('$ROOT_DIR', '$GIT_ROOT').rstrip('/') + '/')" 2>/dev/null || true)"
fi

VERSION=""
BUILD=""
MIN_VERSION=""
EDITION="community"
PHP_REQUIRE=">=8.2"
OUTPUT_DIR="$ROOT_DIR/dist/upgrades"
MODE="incremental"
INCREMENTAL_REF="HEAD~1"
WITH_VENDOR=false
WITH_ASSETS=false
MIGRATE=true
FORCE=false
DELETE_PATHS=()
CHANGELOG_LINES=()

log() { printf '\033[1;34m▶\033[0m %s\n' "$*"; }
ok()  { printf '\033[1;32m✓\033[0m %s\n' "$*"; }
die() { printf '\033[1;31m✗\033[0m %s\n' "$*" >&2; exit 1; }

usage() {
  sed -n '3,28p' "$0" | sed 's/^# \{0,1\}//'
  exit "${1:-0}"
}

while [[ $# -gt 0 ]]; do
  case "$1" in
    --version)      VERSION="$2"; shift 2 ;;
    --build)        BUILD="$2"; shift 2 ;;
    --min-version)  MIN_VERSION="$2"; shift 2 ;;
    --edition)      EDITION="$2"; shift 2 ;;
    --php)          PHP_REQUIRE="$2"; shift 2 ;;
    --output)       OUTPUT_DIR="$2"; shift 2 ;;
    --full)         MODE="full"; shift ;;
    --incremental)  MODE="incremental"; INCREMENTAL_REF="$2"; shift 2 ;;
    --with-vendor)  WITH_VENDOR=true; shift ;;
    --with-assets)  WITH_ASSETS=true; shift ;;
    --no-migrate)   MIGRATE=false; shift ;;
    --force)        FORCE=true; shift ;;
    --delete)       DELETE_PATHS+=("$2"); shift 2 ;;
    --changelog)    CHANGELOG_LINES+=("$2"); shift 2 ;;
    -h|--help)      usage 0 ;;
    *) die "未知参数: $1（使用 --help 查看说明）" ;;
  esac
done

[[ -f artisan ]] || die "请在 niceshoply 项目根目录执行（找不到 artisan）"
[[ -n "$VERSION" ]] || die "必须指定 --version，例如 --version 1.7.0"

if [[ -z "$BUILD" ]]; then
  BUILD="$(date +%Y%m%d)"
fi
if [[ -z "$MIN_VERSION" ]]; then
  CONFIG_FILE="niceshoply/common/config/niceshoply.php"
  if [[ -f "$CONFIG_FILE" ]]; then
    MIN_VERSION="$(grep -E "'version'" "$CONFIG_FILE" | head -1 | sed -E "s/.*'version'[^']*'([^']+)'.*/\1/")"
  fi
  MIN_VERSION="${MIN_VERSION:-1.0.0}"
fi

# 与 config/niceshoply.php upgrade.protected_paths 保持一致，打包时排除
is_protected() {
  local rel="${1//\\//}"
  rel="${rel#/}"
  case "$rel" in
    .env|.env.example|.env.production.example) return 0 ;;
    storage|storage/*) return 0 ;;
    bootstrap/cache|bootstrap/cache/*) return 0 ;;
    database/database.sqlite) return 0 ;;
    public/storage|public/storage/*) return 0 ;;
    public/uploads|public/uploads/*) return 0 ;;
    public/themes|public/themes/*) return 0 ;;
  esac
  return 1
}

STAGING="$(mktemp -d "${TMPDIR:-/tmp}/niceshoply-upgrade.XXXXXX")"
trap 'rm -rf "$STAGING"' EXIT

copy_path() {
  local rel="${1//\\//}"
  rel="${rel#/}"
  [[ -e "$rel" ]] || return 0
  if is_protected "$rel"; then
    return 0
  fi
  mkdir -p "$STAGING/$(dirname "$rel")"
  if [[ -d "$rel" ]]; then
    rsync -a --exclude='.DS_Store' "$rel/" "$STAGING/$rel/"
  else
    cp -f "$rel" "$STAGING/$rel"
  fi
}

collect_incremental_files() {
  git rev-parse --is-inside-work-tree >/dev/null 2>&1 || die "增量模式需要在 git 仓库内执行"

  local ref="$INCREMENTAL_REF"
  git -C "$GIT_ROOT" rev-parse "$ref" >/dev/null 2>&1 || die "无效的 git 引用: $ref"

  local copied=0
  local f rel
  while IFS= read -r f; do
    [[ -z "$f" ]] && continue

    # monorepo：只打包本应用目录下的变更
    if [[ -n "$APP_GIT_PREFIX" ]]; then
      [[ "$f" == "$APP_GIT_PREFIX"* ]] || continue
      rel="${f#"$APP_GIT_PREFIX"}"
    else
      rel="$f"
    fi

    if is_protected "$rel"; then
      continue
    fi
    if [[ -e "$rel" ]]; then
      copy_path "$rel"
      copied=$((copied + 1))
    fi
  done < <(git -C "$GIT_ROOT" diff --name-only --diff-filter=ACMRT "$ref" HEAD)

  if [[ $copied -eq 0 ]]; then
    if ! git -C "$GIT_ROOT" diff --name-only --diff-filter=ACMRT "$ref" HEAD | grep -q .; then
      die "自 $ref 以来没有检测到变更文件"
    fi
    die "变更文件均在受保护路径内或不在应用目录（${APP_GIT_PREFIX:-./}），无可打包内容"
  fi

  ok "增量模式：自 $ref 复制 $copied 个文件/目录"
}

collect_full_files() {
  local paths=(
    artisan
    niceshoply
    bootstrap
    config
    lang
    routes
    app
    database/migrations
    composer.json
    composer.lock
  )

  for p in "${paths[@]}"; do
    copy_path "$p"
  done

  # bootstrap/cache 目录结构保留，但不复制缓存文件
  mkdir -p "$STAGING/bootstrap/cache"
  touch "$STAGING/bootstrap/cache/.gitkeep"

  if [[ "$WITH_ASSETS" == true ]]; then
    copy_path public/build
  fi

  if [[ "$WITH_VENDOR" == true ]]; then
    [[ -d vendor ]] || die "vendor 目录不存在，请先 composer install"
    copy_path vendor
  fi

  ok "全量模式：已复制核心目录${WITH_VENDOR:+, vendor}${WITH_ASSETS:+, public/build}"
}

write_manifest() {
  local manifest="$STAGING/upgrade-manifest.json"
  local delete_json="[]"
  if [[ ${#DELETE_PATHS[@]} -gt 0 ]]; then
    delete_json="$(printf '%s\n' "${DELETE_PATHS[@]}" | php -r '
      $lines = array_filter(array_map("trim", file("php://stdin")));
      echo json_encode(array_values($lines), JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    ')"
  fi

  MIGRATE_JSON=$([ "$MIGRATE" = true ] && echo 'true' || echo 'false')
  FORCE_JSON=$([ "$FORCE" = true ] && echo 'true' || echo 'false')

  cat > "$manifest" <<EOF
{
  "version": "$VERSION",
  "build": "$BUILD",
  "edition": "$EDITION",
  "min_version": "$MIN_VERSION",
  "requirements": {
    "php": "$PHP_REQUIRE"
  },
  "migrate": $MIGRATE_JSON,
  "force": $FORCE_JSON,
  "delete": $delete_json,
  "post_commands": [
    "config:clear",
    "route:clear",
    "view:clear",
    "queue:restart"
  ]
}
EOF
  ok "已生成 upgrade-manifest.json"
}

create_zip() {
  mkdir -p "$OUTPUT_DIR"
  local zip_name="niceshoply-${VERSION}.zip"
  local zip_path="$OUTPUT_DIR/$zip_name"

  rm -f "$zip_path"

  if command -v zip >/dev/null 2>&1; then
    (cd "$STAGING" && zip -r -q "$zip_path" .)
  else
    php -r "
      \$zip = new ZipArchive();
      if (\$zip->open('$zip_path', ZipArchive::CREATE) !== true) {
        fwrite(STDERR, '无法创建 zip\n');
        exit(1);
      }
      \$root = '$STAGING';
      \$it = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(\$root, FilesystemIterator::SKIP_DOTS)
      );
      foreach (\$it as \$file) {
        \$path = \$file->getPathname();
        \$rel = ltrim(str_replace(\$root, '', \$path), '/');
        if (\$rel === '') continue;
        \$zip->addFile(\$path, \$rel);
      }
      \$zip->close();
    "
  fi

  [[ -f "$zip_path" ]] || die "zip 创建失败"
  ok "输出: $zip_path"

  local size sha256
  size=$(wc -c < "$zip_path" | tr -d ' ')
  if command -v shasum >/dev/null 2>&1; then
    sha256="sha256:$(shasum -a 256 "$zip_path" | awk '{print $1}')"
  else
    sha256="sha256:$(sha256sum "$zip_path" | awk '{print $1}')"
  fi

  printf '\n%s\n' '--- 上传至 marketplace Admin → Upgrades 时可核对 ---'
  printf 'version : %s\n' "$VERSION"
  printf 'build   : %s\n' "$BUILD"
  printf 'size    : %s bytes (%.2f MB)\n' "$size" "$(echo "scale=2; $size/1048576" | bc 2>/dev/null || echo "?")"
  printf 'sha256  : %s\n' "$sha256"
  if [[ ${#CHANGELOG_LINES[@]} -gt 0 ]]; then
    printf 'changelog (每行一条):\n'
    printf '  - %s\n' "${CHANGELOG_LINES[@]}"
  fi
  printf '%s\n' '---------------------------------------------------'
}

log "版本 $VERSION (build $BUILD, min $MIN_VERSION)"
case "$MODE" in
  incremental) collect_incremental_files ;;
  full)        collect_full_files ;;
  *) die "未知模式: $MODE" ;;
esac

write_manifest
create_zip
