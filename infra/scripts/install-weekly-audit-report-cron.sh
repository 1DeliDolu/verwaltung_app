#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
INSTALL_PATH="${1:-}"
APP_USER="${2:-${APP_USER:-$(id -un)}}"
ADMIN_EMAIL="${3:-${ADMIN_EMAIL:-admin@verwaltung.local}}"
CRON_SCHEDULE="${4:-${CRON_SCHEDULE:-0 7 * * 1}}"
LOG_PATH="${5:-${LOG_PATH:-/var/log/verwaltung-weekly-audit-report.log}}"
PHP_BIN="${6:-${PHP_BIN:-php}}"
TEMP_PATH=""

usage() {
  cat <<'EOF'
Usage:
  infra/scripts/install-weekly-audit-report-cron.sh INSTALL_PATH [APP_USER] [ADMIN_EMAIL] [CRON_SCHEDULE] [LOG_PATH] [PHP_BIN]

Example:
  sudo infra/scripts/install-weekly-audit-report-cron.sh /etc/cron.d/verwaltung-weekly-audit-report root admin@verwaltung.local "0 7 * * 1" /var/log/verwaltung-weekly-audit-report.log /usr/bin/php8.2
EOF
}

cleanup() {
  if [[ -n "${TEMP_PATH}" && -f "${TEMP_PATH}" ]]; then
    rm -f "${TEMP_PATH}"
  fi
}

if [[ -z "${INSTALL_PATH}" ]]; then
  usage >&2
  exit 1
fi

trap cleanup EXIT

TEMP_PATH="$(mktemp)"

"${ROOT_DIR}/infra/scripts/render-weekly-audit-report-cron.sh" \
  "${TEMP_PATH}" \
  "${APP_USER}" \
  "${ADMIN_EMAIL}" \
  "${CRON_SCHEDULE}" \
  "${LOG_PATH}" \
  "${PHP_BIN}" >/dev/null

mkdir -p "$(dirname "${INSTALL_PATH}")"
cp "${TEMP_PATH}" "${INSTALL_PATH}"
chmod 0644 "${INSTALL_PATH}"

echo "Installed weekly audit report cron file into ${INSTALL_PATH}"
echo "Next step: verify the host cron daemon loads ${INSTALL_PATH}"
