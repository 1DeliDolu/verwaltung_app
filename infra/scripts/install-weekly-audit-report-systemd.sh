#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
INSTALL_DIR="${1:-}"
APP_USER="${2:-${APP_USER:-$(id -un)}}"
APP_GROUP="${3:-${APP_GROUP:-$(id -gn)}}"
ADMIN_EMAIL="${4:-${ADMIN_EMAIL:-admin@verwaltung.local}}"
ON_CALENDAR="${5:-${ON_CALENDAR:-Mon *-*-* 07:00:00}}"
SERVICE_NAME="verwaltung-weekly-audit-report"
TEMP_DIR=""

usage() {
  cat <<'EOF'
Usage:
  infra/scripts/install-weekly-audit-report-systemd.sh INSTALL_DIR [APP_USER] [APP_GROUP] [ADMIN_EMAIL] [ON_CALENDAR]

Example:
  sudo infra/scripts/install-weekly-audit-report-systemd.sh /etc/systemd/system www-data www-data admin@verwaltung.local "Mon *-*-* 07:00:00"
EOF
}

cleanup() {
  if [[ -n "${TEMP_DIR}" && -d "${TEMP_DIR}" ]]; then
    rm -rf "${TEMP_DIR}"
  fi
}

if [[ -z "${INSTALL_DIR}" ]]; then
  usage >&2
  exit 1
fi

trap cleanup EXIT

TEMP_DIR="$(mktemp -d)"

"${ROOT_DIR}/infra/scripts/render-weekly-audit-report-systemd.sh" \
  "${TEMP_DIR}" \
  "${APP_USER}" \
  "${APP_GROUP}" \
  "${ADMIN_EMAIL}" \
  "${ON_CALENDAR}" >/dev/null

mkdir -p "${INSTALL_DIR}"
cp "${TEMP_DIR}/${SERVICE_NAME}.service" "${INSTALL_DIR}/${SERVICE_NAME}.service"
cp "${TEMP_DIR}/${SERVICE_NAME}.timer" "${INSTALL_DIR}/${SERVICE_NAME}.timer"
chmod 0644 "${INSTALL_DIR}/${SERVICE_NAME}.service" "${INSTALL_DIR}/${SERVICE_NAME}.timer"

echo "Installed ${SERVICE_NAME}.service and ${SERVICE_NAME}.timer into ${INSTALL_DIR}"
echo "Next step: systemctl daemon-reload && systemctl enable --now ${SERVICE_NAME}.timer"
