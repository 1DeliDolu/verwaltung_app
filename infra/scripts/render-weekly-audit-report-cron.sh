#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
EXAMPLES_DIR="${ROOT_DIR}/infra/examples"
source "${ROOT_DIR}/infra/scripts/lib/template-helpers.sh"
OUTPUT_PATH="${1:-}"
APP_USER="${2:-${APP_USER:-$(id -un)}}"
ADMIN_EMAIL="${3:-${ADMIN_EMAIL:-admin@verwaltung.local}}"
CRON_SCHEDULE="${4:-${CRON_SCHEDULE:-0 7 * * 1}}"
LOG_PATH="${5:-${LOG_PATH:-/var/log/verwaltung-weekly-audit-report.log}}"
PHP_BIN="${6:-${PHP_BIN:-php}}"

usage() {
  cat <<'EOF'
Usage:
  infra/scripts/render-weekly-audit-report-cron.sh OUTPUT_PATH [APP_USER] [ADMIN_EMAIL] [CRON_SCHEDULE] [LOG_PATH] [PHP_BIN]

Example:
  infra/scripts/render-weekly-audit-report-cron.sh /tmp/verwaltung-weekly-audit-report root admin@verwaltung.local "0 7 * * 1" /var/log/verwaltung-weekly-audit-report.log /usr/bin/php8.2
EOF
}

if [[ -z "${OUTPUT_PATH}" ]]; then
  usage >&2
  exit 1
fi

mkdir -p "$(dirname "${OUTPUT_PATH}")"

sed \
  -e "s#__APP_ROOT__#$(template_escape_sed_replacement "${ROOT_DIR}")#g" \
  -e "s#__APP_USER__#$(template_escape_sed_replacement "${APP_USER}")#g" \
  -e "s#__ADMIN_EMAIL__#$(template_escape_sed_replacement "${ADMIN_EMAIL}")#g" \
  -e "s#__CRON_SCHEDULE__#$(template_escape_sed_replacement "${CRON_SCHEDULE}")#g" \
  -e "s#__LOG_PATH__#$(template_escape_sed_replacement "${LOG_PATH}")#g" \
  -e "s#__PHP_BIN__#$(template_escape_sed_replacement "${PHP_BIN}")#g" \
  "${EXAMPLES_DIR}/weekly-audit-report.cron.example" > "${OUTPUT_PATH}"

template_assert_no_unresolved_placeholders "${OUTPUT_PATH}"

echo "Rendered weekly audit report cron file into ${OUTPUT_PATH}"
