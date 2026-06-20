#!/usr/bin/env bash
#
# NiceShoply 升级包自动发布脚本
# -----------------------------------------------------------------------------
# 一键完成：依赖安装 → 前端构建 → 生产升级包打包 → 元数据输出
#
# 用法：
#   ./scripts/release-upgrade.sh                    # 从 config 读版本，增量自上一 tag
#   ./scripts/release-upgrade.sh --version 1.7.0    # 指定版本
#   ./scripts/release-upgrade.sh --full             # 全量包（含 vendor + public/build）
#   ./scripts/release-upgrade.sh --skip-build       # 跳过 composer/npm（已构建过）
#   ./scripts/release-upgrade.sh --upload           # 打包后调用 marketplace 导入（需配置 .env）
#
# 环境变量（可选，用于 --upload）：
#   MARKETPLACE_URL          默认 https://marketplace.niceshoply.com
#   MARKETPLACE_DEPLOY_TOKEN  与 marketplace UPGRADE_DEPLOY_TOKEN 一致
# -----------------------------------------------------------------------------
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

VERSION=""
BUILD=""
MODE="incremental"
SKIP_BUILD=false
DO_UPLOAD=false
EDITION="community"
EXTRA_BUILD_ARGS=()

log() { printf '\033[1;34m▶\033[0m %s\n' "$*"; }
ok()  { printf '\033[1;32m✓\033[0m %s\n' "$*"; }
die() { printf '\033[1;31m✗\033[0m %s\n' "$*" >&2; exit 1; }

usage() {
  sed -n '3,18p' "$0" | sed 's/^# \{0,1\}//'
  exit 0
}

while [[ $# -gt 0 ]]; do
  case "$1" in
    --version)     VERSION="$2"; shift 2 ;;
    --build)       BUILD="$2"; shift 2 ;;
    --edition)     EDITION="$2"; shift 2 ;;
    --full)        MODE="full"; shift ;;
    --incremental) MODE="incremental"; shift ;;
    --skip-build)  SKIP_BUILD=true; shift ;;
    --upload)      DO_UPLOAD=true; shift ;;
    -h|--help)     usage ;;
    *) die "未知参数: $1" ;;
  esac
done

[[ -f artisan ]] || die "请在 niceshoply 项目根目录执行"

CONFIG_FILE="niceshoply/common/config/niceshoply.php"
read_config() {
  local key="$1"
  grep -E "'${key}'" "$CONFIG_FILE" | head -1 | sed -E "s/.*'${key}'[^']*'([^']+)'.*/\1/"
}

if [[ -z "$VERSION" ]]; then
  VERSION="$(read_config version)"
fi
[[ -n "$VERSION" ]] || die "无法读取版本号，请使用 --version 指定"
if [[ -z "$BUILD" ]]; then
  BUILD="$(read_config build)"
  BUILD="${BUILD:-$(date +%Y%m%d)}"
fi

GIT_ROOT="$(git rev-parse --show-toplevel 2>/dev/null || echo "$ROOT_DIR")"
PREV_TAG=""
if [[ "$MODE" == "incremental" ]]; then
  # 取当前 tag 的上一版本（bash 3 兼容，不用 mapfile）
  _TAGS=()
  while IFS= read -r _t; do
    [[ -n "$_t" ]] && _TAGS+=("$_t")
  done < <(git -C "$GIT_ROOT" tag -l 'v*' --sort=-v:refname 2>/dev/null || true)
  if [[ ${#_TAGS[@]} -ge 2 ]]; then
    PREV_TAG="${_TAGS[1]}"
  elif [[ ${#_TAGS[@]} -eq 1 ]]; then
    PREV_TAG="${_TAGS[0]}"
  fi
  if [[ -z "$PREV_TAG" ]]; then
    log "未找到历史 tag，切换为全量打包模式"
    MODE="full"
  else
    ok "增量基准 tag: $PREV_TAG"
  fi
fi

if [[ "$SKIP_BUILD" != true ]]; then
  log "安装 PHP 依赖（生产模式）"
  composer install --no-dev --prefer-dist --no-interaction --no-progress --optimize-autoloader

  if command -v npm >/dev/null 2>&1; then
    log "构建前端资源"
    npm ci 2>/dev/null || npm install
    npm run build
    ok "前端构建完成"
  else
    log "未找到 npm，跳过前端构建（升级包将不含 public/build，除非已存在）"
  fi
else
  log "跳过 composer / npm 构建（--skip-build）"
fi

BUILD_SCRIPT="$ROOT_DIR/scripts/build-upgrade-package.sh"
[[ -x "$BUILD_SCRIPT" ]] || chmod +x "$BUILD_SCRIPT"

BUILD_ARGS=(--version "$VERSION" --build "$BUILD" --edition "$EDITION")

if [[ "$MODE" == "full" ]]; then
  BUILD_ARGS+=(--full --with-vendor --with-assets)
else
  BUILD_ARGS+=(--incremental "$PREV_TAG")
  # 从 git 提交生成 changelog
  while IFS= read -r line; do
    [[ -n "$line" ]] && BUILD_ARGS+=(--changelog "$line")
  done < <(git -C "$GIT_ROOT" log --pretty=format:'%s' "${PREV_TAG}..HEAD" 2>/dev/null | head -20)
fi

log "开始打包升级包 v${VERSION}（${MODE}）"
"$BUILD_SCRIPT" "${BUILD_ARGS[@]}"

ZIP_PATH="$ROOT_DIR/dist/upgrades/niceshoply-${VERSION}.zip"
[[ -f "$ZIP_PATH" ]] || die "未找到输出包: $ZIP_PATH"

META_PATH="$ROOT_DIR/dist/upgrades/niceshoply-${VERSION}.meta.json"
SIZE=$(wc -c < "$ZIP_PATH" | tr -d ' ')
if command -v shasum >/dev/null 2>&1; then
  SHA256="sha256:$(shasum -a 256 "$ZIP_PATH" | awk '{print $1}')"
else
  SHA256="sha256:$(sha256sum "$ZIP_PATH" | awk '{print $1}')"
fi

MIN_VERSION="$(read_config version)"

php -r "
echo json_encode([
  'version' => '$VERSION',
  'build' => '$BUILD',
  'edition' => '$EDITION',
  'min_version' => '$MIN_VERSION',
  'size' => (int)'$SIZE',
  'checksum' => '$SHA256',
  'file' => 'upgrades/niceshoply-${VERSION}.zip',
  'released_at' => date('Y-m-d H:i:s'),
  'mode' => '$MODE',
  'previous_tag' => '$PREV_TAG',
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
" > "$META_PATH"

ok "元数据: $META_PATH"

if [[ "$DO_UPLOAD" == true ]]; then
  MARKETPLACE_DIR="$ROOT_DIR/../niceshoply_marketplace"
  if [[ -f "$MARKETPLACE_DIR/artisan" ]]; then
    log "导入至本地 marketplace"
    php "$MARKETPLACE_DIR/artisan" upgrade:import "$ZIP_PATH" \
      --version="$VERSION" \
      --build="$BUILD" \
      --edition="$EDITION" \
      --min-version="$MIN_VERSION" \
      --mark-latest
    ok "已导入 marketplace 数据库"
  else
    MARKETPLACE_URL="${MARKETPLACE_URL:-https://marketplace.niceshoply.com}"
    TOKEN="${MARKETPLACE_DEPLOY_TOKEN:-}"
    if [[ -z "$TOKEN" ]]; then
      die "--upload 需要本地 niceshoply_marketplace 或设置 MARKETPLACE_DEPLOY_TOKEN"
    fi
    log "远程上传至 $MARKETPLACE_URL"
    curl -sf -X POST "${MARKETPLACE_URL}/api/upgrade/deploy" \
      -H "X-Deploy-Token: ${TOKEN}" \
      -F "version=${VERSION}" \
      -F "build=${BUILD}" \
      -F "edition=${EDITION}" \
      -F "min_version=${MIN_VERSION}" \
      -F "mark_latest=1" \
      -F "package=@${ZIP_PATH}" \
      | php -r 'echo json_encode(json_decode(file_get_contents("php://stdin"), true), JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE)."\n";'
    ok "远程上传完成"
  fi
fi

printf '\n%s\n' "=== 发布完成 ==="
printf 'zip  : %s\n' "$ZIP_PATH"
printf 'meta : %s\n' "$META_PATH"
printf '下一步: 打 tag → git tag v%s && git push origin v%s\n' "$VERSION" "$VERSION"
printf '         或 Admin 上传 / CI Release 自动分发\n'
