#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
EXAMPLES_DIR="${ROOT_DIR}/infra/examples"
OUTPUT_DIR="${1:-}"
APP_USER="${2:-${APP_USER:-$(id -un)}}"
APP_GROUP="${3:-${APP_GROUP:-$(id -gn)}}"
ADMIN_EMAIL="${4:-${ADMIN_EMAIL:-admin@verwaltung.local}}"
ON_CALENDAR="${5:-${ON_CALENDAR:-Mon *-*-* 07:00:00}}"
SERVICE_NAME="verwaltung-weekly-audit-report"

usage() {
  cat <<'EOF'
Usage:
  infra/scripts/render-weekly-audit-report-systemd.sh OUTPUT_DIR [APP_USER] [APP_GROUP] [ADMIN_EMAIL] [ON_CALENDAR]

Example:
  infra/scripts/render-weekly-audit-report-systemd.sh /tmp/systemd www-data www-data admin@verwaltung.local "Mon *-*-* 07:00:00"
EOF
}

render_template() {
  local template_path="$1"
  local destination_path="$2"

  sed \
    -e "s#__APP_ROOT__#${ROOT_DIR//\\/\\\\}#g" \
    -e "s#__APP_USER__#${APP_USER//\\/\\\\}#g" \
    -e "s#__APP_GROUP__#${APP_GROUP//\\/\\\\}#g" \
    -e "s#__ADMIN_EMAIL__#${ADMIN_EMAIL//\\/\\\\}#g" \
    -e "s#__ON_CALENDAR__#${ON_CALENDAR//\\/\\\\}#g" \
    "${template_path}" > "${destination_path}"

  if rg -n "__[A-Z_]+__" "${destination_path}" >/dev/null 2>&1; then
    echo "Unresolved placeholder found in ${destination_path}" >&2
    exit 1
  fi
}

if [[ -z "${OUTPUT_DIR}" ]]; then
  usage >&2
  exit 1
fi

mkdir -p "${OUTPUT_DIR}"

render_template \
  "${EXAMPLES_DIR}/weekly-audit-report.service.example" \
  "${OUTPUT_DIR}/${SERVICE_NAME}.service"
render_template \
  "${EXAMPLES_DIR}/weekly-audit-report.timer.example" \
  "${OUTPUT_DIR}/${SERVICE_NAME}.timer"

echo "Rendered ${SERVICE_NAME}.service and ${SERVICE_NAME}.timer into ${OUTPUT_DIR}"
