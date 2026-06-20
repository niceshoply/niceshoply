#!/usr/bin/env bash
#
# NiceShoply 生产环境补全脚本
#
# 用途（二选一或全部）：
#   1) 生成 MySQL schema 压缩基线：database/schema/mysql-schema.sql
#   2) 同步商品到 Meilisearch：scout:import + 索引设置
#
# 用法：
#   ./scripts/prod-post-deploy.sh schema-dump          # 仅 schema dump（需 mysqldump + mysql）
#   ./scripts/prod-post-deploy.sh scout-import         # 仅 Scout 同步（需 Meilisearch 可达）
#   ./scripts/prod-post-deploy.sh all                  # 两项都执行（默认）
#   ./scripts/prod-post-deploy.sh scout-import --flush # 先清空 products 索引再全量导入
#
# 前置条件：
#   - 在 niceshoply/ 项目根目录执行（与 artisan 同级）
#   - .env 已配置生产数据库 / Meilisearch
#   - schema-dump：DB_CONNECTION=mysql，且已 migrate 到最新
#   - scout-import：SCOUT_DRIVER=meilisearch、SCOUT_ENABLED=true，MEILISEARCH_HOST 可访问
#
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

PRODUCT_MODEL='NiceShoply\Common\Models\Product'
SCOUT_FLUSH=false
CMD="${1:-all}"

if [[ "${2:-}" == "--flush" ]] || [[ "${3:-}" == "--flush" ]]; then
  SCOUT_FLUSH=true
fi

log()  { printf '\033[1;34m▶\033[0m %s\n' "$*"; }
ok()   { printf '\033[1;32m✓\033[0m %s\n' "$*"; }
warn() { printf '\033[1;33m!\033[0m %s\n' "$*"; }
die()  { printf '\033[1;31m✗\033[0m %s\n' "$*" >&2; exit 1; }

usage() {
  sed -n '3,18p' "$0" | sed 's/^# \{0,1\}//'
  exit 0
}

require_artisan() {
  [[ -f artisan ]] || die "请在 niceshoply 项目根目录执行（找不到 artisan）"
  command -v php >/dev/null 2>&1 || die "未找到 php"
}

env_value() {
  local key="$1"
  if [[ -f .env ]]; then
  grep -E "^${key}=" .env 2>/dev/null | tail -1 | cut -d= -f2- | sed 's/^["'\'']//;s/["'\'']$//' || true
  fi
}

require_artisan

case "$CMD" in
  -h|--help|help) usage ;;
  schema-dump|scout-import|all) ;;
  *) die "未知命令: $CMD（可用: schema-dump | scout-import | all）" ;;
esac

# ---------------------------------------------------------------------------
# 1) MySQL schema:dump
# ---------------------------------------------------------------------------
run_schema_dump() {
  log "检查 schema:dump 前置条件…"

  command -v mysqldump >/dev/null 2>&1 || die "未找到 mysqldump，请安装 mysql-client 后重试"

  local db_conn
  db_conn="$(env_value DB_CONNECTION)"
  [[ -n "$db_conn" ]] || db_conn="mysql"

  if [[ "$db_conn" != "mysql" && "$db_conn" != "mariadb" ]]; then
    die "DB_CONNECTION=${db_conn}，schema:dump 需要 mysql/mariadb（当前 .env 不是生产库）"
  fi

  local out_file="database/schema/${db_conn}-schema.sql"
  log "确认迁移已应用到最新（migrate --force）…"
  php artisan migrate --force

  log "执行 php artisan schema:dump --database=${db_conn} …"
  php artisan schema:dump --database="${db_conn}"

  [[ -f "$out_file" ]] || die "未生成 ${out_file}"
  ok "已生成 ${out_file} ($(wc -c < "$out_file" | tr -d ' ') bytes)"
  warn "请将 ${out_file} 提交到版本库，供新环境/CI 快速建库（不删除既有迁移文件，存量库不受影响）"
}

# ---------------------------------------------------------------------------
# 2) Meilisearch scout:import
# ---------------------------------------------------------------------------
run_scout_import() {
  log "检查 Scout / Meilisearch 前置条件…"

  local driver enabled host key
  driver="$(env_value SCOUT_DRIVER)"
  enabled="$(env_value SCOUT_ENABLED)"
  host="$(env_value MEILISEARCH_HOST)"
  key="$(env_value MEILISEARCH_KEY)"

  [[ "$driver" == "meilisearch" ]] || die "SCOUT_DRIVER=${driver:-<empty>}，请设为 meilisearch"
  [[ "$enabled" == "true" || "$enabled" == "1" ]] || warn "SCOUT_ENABLED 未开启，导入后搜索仍可能走 DB LIKE，建议在 .env 设 SCOUT_ENABLED=true"

  [[ -n "$host" ]] || die "未配置 MEILISEARCH_HOST"
  host="${host%/}"

  log "探测 Meilisearch 健康检查 ${host}/health …"
  local health_code
  if [[ -n "$key" ]]; then
    health_code="$(curl -s -o /dev/null -w '%{http_code}' -H "Authorization: Bearer ${key}" "${host}/health" || echo 000)"
  else
    health_code="$(curl -s -o /dev/null -w '%{http_code}' "${host}/health" || echo 000)"
  fi
  [[ "$health_code" == "200" ]] || die "Meilisearch 不可达（HTTP ${health_code}），请确认服务已启动且 MEILISEARCH_HOST/KEY 正确"

  ok "Meilisearch 可达"

  if php artisan list --raw 2>/dev/null | grep -q '^scout:sync-index-settings'; then
    log "同步索引设置（searchable/filterable/sortable）…"
    php artisan scout:sync-index-settings
    ok "索引设置已同步"
  else
    warn "当前 Scout 版本无 scout:sync-index-settings，跳过（索引设置见 config/scout.php）"
  fi

  if [[ "$SCOUT_FLUSH" == "true" ]]; then
    warn "即将清空 products 搜索索引（scout:flush）"
    php artisan scout:flush "${PRODUCT_MODEL}"
    ok "索引已清空"
  fi

  log "全量导入商品到 Meilisearch（scout:import）…"
  php artisan scout:import "${PRODUCT_MODEL}"
  ok "商品索引导入完成"

  warn "若 SCOUT_QUEUE=true，请确保 Horizon/queue worker 在运行，否则异步索引任务会堆积"
}

# ---------------------------------------------------------------------------
# Main
# ---------------------------------------------------------------------------
case "$CMD" in
  schema-dump)
    run_schema_dump
    ;;
  scout-import)
    run_scout_import
    ;;
  all)
    run_schema_dump
    echo
    run_scout_import
    ;;
esac

ok "全部完成"
