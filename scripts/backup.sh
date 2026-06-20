#!/usr/bin/env bash
#
# NiceShoply 备份脚本（数据库 + 上传文件）
#
# 用途：
#   1) 用 mysqldump 导出数据库为 .sql.gz
#   2) 打包上传文件目录为 .tar.gz：
#        public/static/uploads  public/static/media  public/catalog  storage/app/public
#   3) 按保留天数清理过期备份
#
# 用法：
#   ./scripts/backup.sh                 # 数据库 + 文件，输出到 storage/backups
#   ./scripts/backup.sh db              # 仅数据库
#   ./scripts/backup.sh files           # 仅上传文件
#   BACKUP_DIR=/data/backups RETENTION_DAYS=14 ./scripts/backup.sh
#
# 定时（crontab，每日 03:00）：
#   0 3 * * * cd /path-to-project && ./scripts/backup.sh >> storage/logs/backup.log 2>&1
#
# 前置：在项目根目录执行；.env 已配置 DB_*；db 备份需 mysqldump（mysql-client）。
#
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

CMD="${1:-all}"
BACKUP_DIR="${BACKUP_DIR:-storage/backups}"
RETENTION_DAYS="${RETENTION_DAYS:-7}"
STAMP="$(date +%Y%m%d-%H%M%S)"

log()  { printf '\033[1;34m▶\033[0m %s\n' "$*"; }
ok()   { printf '\033[1;32m✓\033[0m %s\n' "$*"; }
warn() { printf '\033[1;33m!\033[0m %s\n' "$*"; }
die()  { printf '\033[1;31m✗\033[0m %s\n' "$*" >&2; exit 1; }

usage() { sed -n '3,28p' "$0" | sed 's/^# \{0,1\}//'; exit 0; }

[[ -f artisan ]] || die "请在 niceshoply 项目根目录执行（找不到 artisan）"
mkdir -p "$BACKUP_DIR"

# 从 .env 读取键值（去除引号）
env_value() {
  local key="$1"
  [[ -f .env ]] || return 0
  grep -E "^${key}=" .env 2>/dev/null | tail -1 | cut -d= -f2- | sed 's/^["'\'']//;s/["'\'']$//' || true
}

backup_db() {
  command -v mysqldump >/dev/null 2>&1 || die "未找到 mysqldump，请安装 mysql-client"
  command -v gzip      >/dev/null 2>&1 || die "未找到 gzip"

  local conn host port db user pass
  conn="$(env_value DB_CONNECTION)"; conn="${conn:-mysql}"
  [[ "$conn" == "mysql" || "$conn" == "mariadb" ]] || die "DB_CONNECTION=${conn} 非 mysql/mariadb，无法 mysqldump"

  host="$(env_value DB_HOST)"; host="${host:-127.0.0.1}"
  port="$(env_value DB_PORT)"; port="${port:-3306}"
  db="$(env_value DB_DATABASE)";   [[ -n "$db" ]]   || die "未配置 DB_DATABASE"
  user="$(env_value DB_USERNAME)"; [[ -n "$user" ]] || die "未配置 DB_USERNAME"
  pass="$(env_value DB_PASSWORD)"

  local out="${BACKUP_DIR}/db-${db}-${STAMP}.sql.gz"
  log "导出数据库 ${db}@${host}:${port} → ${out}"

  # 用 MYSQL_PWD 传密码，避免命令行明文泄露到进程列表
  MYSQL_PWD="$pass" mysqldump \
    --host="$host" --port="$port" --user="$user" \
    --single-transaction --quick --no-tablespaces \
    --default-character-set=utf8mb4 \
    "$db" | gzip -9 > "$out"

  [[ -s "$out" ]] || die "数据库备份为空，导出失败"
  ok "数据库已备份：${out} ($(wc -c < "$out" | tr -d ' ') bytes)"
}

backup_files() {
  command -v tar >/dev/null 2>&1 || die "未找到 tar"

  local out="${BACKUP_DIR}/files-${STAMP}.tar.gz"
  local -a targets=()
  for d in "public/static/uploads" "public/static/media" "public/catalog" "storage/app/public"; do
    [[ -d "$d" ]] && targets+=("$d")
  done

  if [[ ${#targets[@]} -eq 0 ]]; then
    warn "未发现可备份的上传目录，跳过文件备份"
    return 0
  fi

  log "打包上传文件 → ${out}"
  log "包含目录：${targets[*]}"
  # 备份目录本身在 storage 下时排除，避免自包含
  tar --exclude="${BACKUP_DIR#./}" -czf "$out" "${targets[@]}"
  ok "上传文件已备份：${out} ($(wc -c < "$out" | tr -d ' ') bytes)"
}

cleanup() {
  log "清理 ${RETENTION_DAYS} 天前的旧备份 …"
  find "$BACKUP_DIR" -maxdepth 1 -type f \( -name 'db-*.sql.gz' -o -name 'files-*.tar.gz' \) \
    -mtime +"$RETENTION_DAYS" -print -delete || true
}

case "$CMD" in
  -h|--help|help) usage ;;
  db)    backup_db ;;
  files) backup_files ;;
  all)   backup_db; backup_files ;;
  *)     die "未知命令: $CMD（可用: all | db | files）" ;;
esac

cleanup
ok "备份完成（输出目录：${BACKUP_DIR}）"
