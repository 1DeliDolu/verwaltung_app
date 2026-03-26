#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
EXAMPLES_DIR="${ROOT_DIR}/infra/examples"
OUTPUT_PATH="${1:-}"
APP_USER="${2:-${APP_USER:-$(id -un)}}"
ADMIN_EMAIL="${3:-${ADMIN_EMAIL:-admin@verwaltung.local}}"
CRON_SCHEDULE="${4:-${CRON_SCHEDULE:-0 7 * * 1}}"
LOG_PATH="${5:-${LOG_PATH:-/var/log/verwaltung-weekly-audit-report.log}}"

usage() {
  cat <<'EOF'
Usage:
  infra/scripts/render-weekly-audit-report-cron.sh OUTPUT_PATH [APP_USER] [ADMIN_EMAIL] [CRON_SCHEDULE] [LOG_PATH]

Example:
  infra/scripts/render-weekly-audit-report-cron.sh /tmp/verwaltung-weekly-audit-report root admin@verwaltung.local "0 7 * * 1" /var/log/verwaltung-weekly-audit-report.log
EOF
}

if [[ -z "${OUTPUT_PATH}" ]]; then
  usage >&2
  exit 1
fi

mkdir -p "$(dirname "${OUTPUT_PATH}")"

sed \
  -e "s#__APP_ROOT__#${ROOT_DIR//\\/\\\\}#g" \
  -e "s#__APP_USER__#${APP_USER//\\/\\\\}#g" \
  -e "s#__ADMIN_EMAIL__#${ADMIN_EMAIL//\\/\\\\}#g" \
  -e "s#__CRON_SCHEDULE__#${CRON_SCHEDULE//\\/\\\\}#g" \
  -e "s#__LOG_PATH__#${LOG_PATH//\\/\\\\}#g" \
  "${EXAMPLES_DIR}/weekly-audit-report.cron.example" > "${OUTPUT_PATH}"

if rg -n "__[A-Z_]+__" "${OUTPUT_PATH}" >/dev/null 2>&1; then
  echo "Unresolved placeholder found in ${OUTPUT_PATH}" >&2
  exit 1
fi

echo "Rendered weekly audit report cron file into ${OUTPUT_PATH}"
