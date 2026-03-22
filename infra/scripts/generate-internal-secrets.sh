#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
ENV_TARGET="${ROOT_DIR}/.env.internal-services"
MAIL_ACCOUNTS_TARGET="${ROOT_DIR}/mail/docker-data/dms/config/postfix-accounts.cf"
SAMBA_CONFIG_TARGET="${ROOT_DIR}/file/config.yml"

MAIL_DOMAIN="${MAIL_DOMAIN:-verwaltung.local}"
MAIL_HOST="${MAIL_HOST:-mail}"
MAIL_FQDN="${MAIL_FQDN:-mail.${MAIL_DOMAIN}}"

generate_password() {
  python3 - <<'PY'
import secrets
import string

alphabet = string.ascii_letters + string.digits + "!@#%^&*()_+=-"
print("".join(secrets.choice(alphabet) for _ in range(24)))
PY
}

ADMIN_PASSWORD="$(generate_password)"
TEAMLEAD_PASSWORD="$(generate_password)"
EMPLOYEE_PASSWORD="$(generate_password)"

cat > "${ENV_TARGET}" <<EOF
MAIL_DOMAIN=${MAIL_DOMAIN}
MAIL_HOSTNAME=${MAIL_HOST}
MAIL_FQDN=${MAIL_FQDN}
OVERRIDE_HOSTNAME=${MAIL_FQDN}
DMS_DEBUG=0
ENABLE_IMAP=1
ENABLE_POP3=0
ENABLE_CLAMAV=0
ENABLE_FAIL2BAN=1
SSL_TYPE=manual
SSL_CERT_PATH=/etc/letsencrypt/live/${MAIL_FQDN}/fullchain.pem
SSL_KEY_PATH=/etc/letsencrypt/live/${MAIL_FQDN}/privkey.pem
POSTMASTER_ADDRESS=postmaster@${MAIL_DOMAIN}
TZ=Europe/Berlin
SAMBA_LOG_LEVEL=0
WSDD2_ENABLE=0
EOF

cat > "${MAIL_ACCOUNTS_TARGET}" <<EOF
admin@${MAIL_DOMAIN}|${ADMIN_PASSWORD}
leiter.it@${MAIL_DOMAIN}|${TEAMLEAD_PASSWORD}
mitarbeiter.it@${MAIL_DOMAIN}|${EMPLOYEE_PASSWORD}
EOF

cat > "${SAMBA_CONFIG_TARGET}" <<EOF
auth:
  - user: admin
    group: management
    uid: 1000
    gid: 1000
    password: ${ADMIN_PASSWORD}
  - user: teamlead-it
    group: it
    uid: 1001
    gid: 1001
    password: ${TEAMLEAD_PASSWORD}
  - user: employee-it
    group: it
    uid: 1002
    gid: 1002
    password: ${EMPLOYEE_PASSWORD}

global:
  - "map to guest = never"
  - "server string = Verwaltung File Server"

share:
  - name: IT
    path: /samba/it
    browsable: yes
    readonly: yes
    guestok: no
    validusers: admin teamlead-it employee-it
    writelist: admin teamlead-it
  - name: HR
    path: /samba/hr
    browsable: yes
    readonly: yes
    guestok: no
    validusers: admin
    writelist: admin
  - name: Operations
    path: /samba/operations
    browsable: yes
    readonly: yes
    guestok: no
    validusers: admin
    writelist: admin
EOF

cat <<EOF
Generated:
- ${ENV_TARGET}
- ${MAIL_ACCOUNTS_TARGET}
- ${SAMBA_CONFIG_TARGET}

Mail accounts:
- admin@${MAIL_DOMAIN}
- leiter.it@${MAIL_DOMAIN}
- mitarbeiter.it@${MAIL_DOMAIN}

Passwords were generated automatically. Store them in a password manager now.
EOF
