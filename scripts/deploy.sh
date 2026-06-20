#!/usr/bin/env bash
#
# NiceShoply 生产部署脚本
# -----------------------------------------------------------------------------
# 标准部署顺序（对应《NiceShoply改进任务清单》§2.2 / §4.3）：
#   composer install → npm run vendor → npm run build → migrate → seed → 缓存
#
# 用法：
#   ./scripts/deploy.sh            # 完整部署（含前端构建与 seed）
#   ./scripts/deploy.sh --no-seed  # 跳过 db:seed（已有数据的存量环境）
#   ./scripts/deploy.sh --no-build # 跳过前端构建（仅后端发布）
#
# 前置条件：
#   - 在 niceshoply/ 项目根目录执行（与 artisan 同级）
#   - .env 已按 .env.production.example 配置完毕
# -----------------------------------------------------------------------------
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

RUN_SEED=true
RUN_BUILD=true
for arg in "$@"; do
  case "$arg" in
    --no-seed)  RUN_SEED=false ;;
    --no-build) RUN_BUILD=false ;;
    -h|--help)
      sed -n '3,18p' "$0" | sed 's/^# \{0,1\}//'
      exit 0
      ;;
    *) echo "未知参数: $arg" >&2; exit 1 ;;
  esac
done

log() { printf '\033[1;34m▶\033[0m %s\n' "$*"; }
ok()  { printf '\033[1;32m✓\033[0m %s\n' "$*"; }
die() { printf '\033[1;31m✗\033[0m %s\n' "$*" >&2; exit 1; }

[[ -f artisan ]] || die "请在 niceshoply 项目根目录执行（找不到 artisan）"
[[ -f .env ]]   || die "缺少 .env，请先 cp .env.production.example .env 并完成配置"

log "进入维护模式"
php artisan down --render="errors::503" || true
trap 'php artisan up || true' EXIT

log "安装 PHP 依赖（生产模式）"
composer install --no-dev --prefer-dist --no-interaction --no-progress --optimize-autoloader

if [[ "$RUN_BUILD" == "true" ]]; then
  command -v npm >/dev/null 2>&1 || die "未找到 npm，无法构建前端资源"
  log "安装前端依赖"
  npm ci || npm install
  log "拷贝 vendor 静态资源（npm run vendor）"
  npm run vendor
  log "构建前端资源（npm run build）"
  npm run build
  ok "前端资源构建完成"
fi

log "执行数据库迁移（migrate --force）"
php artisan migrate --force

if [[ "$RUN_SEED" == "true" ]]; then
  log "执行数据填充（db:seed --force）"
  php artisan db:seed --force
fi

log "重建配置/路由/视图缓存"
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache || true

log "重启队列与 Horizon（如在运行）"
php artisan queue:restart || true
php artisan horizon:terminate || true

ok "部署完成。退出维护模式…"
