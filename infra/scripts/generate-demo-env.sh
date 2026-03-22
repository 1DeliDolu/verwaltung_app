#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
ENV_TARGET="${ROOT_DIR}/demo/.env.demo-services"

MAIL_DOMAIN="${MAIL_DOMAIN:-verwaltung.demo}"
MAIL_HOST="${MAIL_HOST:-mailhog}"
MAIL_FQDN="${MAIL_FQDN:-mail.${MAIL_DOMAIN}}"
MAIL_PORT="${MAIL_PORT:-1025}"
MAIL_WEB_PORT="${MAIL_WEB_PORT:-8025}"

cat > "${ENV_TARGET}" <<EOF
MAIL_DOMAIN=${MAIL_DOMAIN}
MAIL_HOSTNAME=${MAIL_HOST}
MAIL_FQDN=${MAIL_FQDN}
OVERRIDE_HOSTNAME=${MAIL_FQDN}
MAIL_PORT=${MAIL_PORT}
MAIL_WEB_PORT=${MAIL_WEB_PORT}
EOF

echo "Generated demo env at ${ENV_TARGET}"
