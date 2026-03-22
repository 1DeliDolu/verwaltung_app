#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

MAIL_DOMAIN="${MAIL_DOMAIN:-verwaltung.demo}"
MAIL_HOST="${MAIL_HOST:-mail}"
MAIL_FQDN="${MAIL_FQDN:-mail.${MAIL_DOMAIN}}"

MAIL_DOMAIN="${MAIL_DOMAIN}" MAIL_HOST="${MAIL_HOST}" MAIL_FQDN="${MAIL_FQDN}" \
  "${ROOT_DIR}/scripts/generate-demo-env.sh"

MAIL_DOMAIN="${MAIL_DOMAIN}" MAIL_HOST="${MAIL_HOST}" MAIL_FQDN="${MAIL_FQDN}" \
  "${ROOT_DIR}/scripts/generate-internal-secrets.sh"

"${ROOT_DIR}/scripts/generate-demo-certs.sh" "${MAIL_FQDN}"
"${ROOT_DIR}/scripts/bootstrap-file-shares.sh"

docker compose \
  --env-file "${ROOT_DIR}/demo/.env.demo-services" \
  -f "${ROOT_DIR}/demo/compose.demo-services.yml" \
  up -d

echo "Demo services stack started."
