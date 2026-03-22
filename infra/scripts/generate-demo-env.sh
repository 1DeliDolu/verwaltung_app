#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
ENV_TARGET="${ROOT_DIR}/demo/.env.demo-services"

MAIL_DOMAIN="${MAIL_DOMAIN:-verwaltung.demo}"
MAIL_HOST="${MAIL_HOST:-mail}"
MAIL_FQDN="${MAIL_FQDN:-mail.${MAIL_DOMAIN}}"

cat > "${ENV_TARGET}" <<EOF
MAIL_DOMAIN=${MAIL_DOMAIN}
MAIL_HOSTNAME=${MAIL_HOST}
MAIL_FQDN=${MAIL_FQDN}
OVERRIDE_HOSTNAME=${MAIL_FQDN}
DMS_DEBUG=0
ENABLE_IMAP=1
ENABLE_POP3=0
ENABLE_CLAMAV=0
ENABLE_FAIL2BAN=0
SSL_TYPE=manual
SSL_CERT_PATH=/etc/letsencrypt/live/${MAIL_FQDN}/fullchain.pem
SSL_KEY_PATH=/etc/letsencrypt/live/${MAIL_FQDN}/privkey.pem
POSTMASTER_ADDRESS=postmaster@${MAIL_DOMAIN}
TZ=Europe/Berlin
SAMBA_LOG_LEVEL=0
WSDD2_ENABLE=0
EOF

echo "Generated demo env at ${ENV_TARGET}"
