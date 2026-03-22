#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

MAIL_DOMAIN="${MAIL_DOMAIN:-verwaltung.demo}"
MAIL_HOST="${MAIL_HOST:-mailhog}"
MAIL_PORT="${MAIL_PORT:-1025}"
MAIL_WEB_PORT="${MAIL_WEB_PORT:-8025}"

MAIL_DOMAIN="${MAIL_DOMAIN}" MAIL_HOST="${MAIL_HOST}" MAIL_PORT="${MAIL_PORT}" MAIL_WEB_PORT="${MAIL_WEB_PORT}" \
  "${ROOT_DIR}/scripts/generate-demo-env.sh"

MAIL_DOMAIN="${MAIL_DOMAIN}" MAIL_HOST="${MAIL_HOST}" MAIL_FQDN="mail.${MAIL_DOMAIN}" \
  "${ROOT_DIR}/scripts/generate-internal-secrets.sh"

"${ROOT_DIR}/scripts/bootstrap-file-shares.sh"

docker compose \
  --env-file "${ROOT_DIR}/demo/.env.demo-services" \
  -f "${ROOT_DIR}/demo/compose.demo-services.yml" \
  up -d

echo "Demo services stack started. MailHog UI: http://127.0.0.1:${MAIL_WEB_PORT}"
