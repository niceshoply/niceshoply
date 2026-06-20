#!/usr/bin/env bash
#
# NiceShoply GeoLite2 离线 IP 库部署 / 更新脚本
#
# 用途：
#   将 GeoLite2-City.mmdb 落地到 storage/app/geolite2/GeoLite2-City.mmdb，
#   供 GeoLite2Service / GeoLocationService 读取（IP 归属地识别）。
#
# 两种来源：
#   1) MaxMind 官方（推荐，数据最新；需免费账号的 license_key）：
#        MAXMIND_LICENSE_KEY=xxxx ./scripts/geoip-update.sh maxmind
#      官方为 tar.gz 打包，脚本自动解压取出 .mmdb。
#   2) 直链 .mmdb（内置镜像或自建镜像）：
#        ./scripts/geoip-update.sh url https://res.innoshop.net/GeoLite2-City.mmdb
#      也可直接调用 artisan：php artisan geoip:update
#
# 用法：
#   ./scripts/geoip-update.sh maxmind                 # 用 MAXMIND_LICENSE_KEY 从官方拉取
#   ./scripts/geoip-update.sh url <下载直链>          # 从指定 .mmdb 直链拉取
#   ./scripts/geoip-update.sh artisan                 # 走 php artisan geoip:update（默认源）
#
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

TARGET_DIR="storage/app/geolite2"
TARGET_FILE="${TARGET_DIR}/GeoLite2-City.mmdb"

log()  { printf '\033[1;34m▶\033[0m %s\n' "$*"; }
ok()   { printf '\033[1;32m✓\033[0m %s\n' "$*"; }
warn() { printf '\033[1;33m!\033[0m %s\n' "$*"; }
die()  { printf '\033[1;31m✗\033[0m %s\n' "$*" >&2; exit 1; }

usage() { sed -n '3,30p' "$0" | sed 's/^# \{0,1\}//'; exit 0; }

CMD="${1:-artisan}"

mkdir -p "$TARGET_DIR"

verify_mmdb() {
  # 用 artisan 命令读取库信息做基本校验（若 artisan 可用）
  if [[ -f artisan ]] && command -v php >/dev/null 2>&1; then
    php artisan tinker --execute="echo (new \\NiceShoply\\Common\\Services\\GeoLite2Service)->isAvailable() ? 'OK' : 'MISSING';" 2>/dev/null || true
  fi
}

case "$CMD" in
  -h|--help|help) usage ;;

  maxmind)
    : "${MAXMIND_LICENSE_KEY:?请先设置 MAXMIND_LICENSE_KEY 环境变量（MaxMind 免费账号获取）}"
    command -v curl >/dev/null 2>&1 || die "未找到 curl"
    command -v tar  >/dev/null 2>&1 || die "未找到 tar"

    DL_URL="https://download.maxmind.com/app/geoip_download?edition_id=GeoLite2-City&license_key=${MAXMIND_LICENSE_KEY}&suffix=tar.gz"
    TMP_DIR="$(mktemp -d)"
    trap 'rm -rf "$TMP_DIR"' EXIT

    log "从 MaxMind 官方下载 GeoLite2-City.tar.gz …"
    curl -fSL "$DL_URL" -o "${TMP_DIR}/geolite2.tar.gz" || die "下载失败，请检查 license_key / 网络"

    log "解压并提取 .mmdb …"
    tar -xzf "${TMP_DIR}/geolite2.tar.gz" -C "$TMP_DIR"
    MMDB_PATH="$(find "$TMP_DIR" -name 'GeoLite2-City.mmdb' | head -1)"
    [[ -n "$MMDB_PATH" ]] || die "压缩包内未找到 GeoLite2-City.mmdb"

    cp "$MMDB_PATH" "$TARGET_FILE"
    ok "已更新 ${TARGET_FILE} ($(wc -c < "$TARGET_FILE" | tr -d ' ') bytes)"
    ;;

  url)
    SRC_URL="${2:-}"
    [[ -n "$SRC_URL" ]] || die "请提供 .mmdb 下载直链：./scripts/geoip-update.sh url <URL>"
    command -v curl >/dev/null 2>&1 || die "未找到 curl"

    log "从直链下载：${SRC_URL} …"
    curl -fSL "$SRC_URL" -o "$TARGET_FILE" || die "下载失败"
    ok "已更新 ${TARGET_FILE} ($(wc -c < "$TARGET_FILE" | tr -d ' ') bytes)"
    ;;

  artisan)
    [[ -f artisan ]] || die "未找到 artisan，请在项目根目录执行"
    command -v php >/dev/null 2>&1 || die "未找到 php"
    log "执行 php artisan geoip:update --force …"
    php artisan geoip:update --force
    ;;

  *)
    die "未知命令: $CMD（可用: maxmind | url <URL> | artisan）"
    ;;
esac

log "校验数据库可用性 …"
RESULT="$(verify_mmdb)"
if [[ "$RESULT" == *OK* ]]; then
  ok "GeoLite2 库可用"
else
  warn "未能通过 artisan 校验（可能 DB/缓存未就绪），请确认 ${TARGET_FILE} 存在且非空"
fi

ok "完成"
