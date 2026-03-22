#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
ENV_FILE="${ROOT_DIR}/.env.internal-services"
MAIL_ACCOUNTS="${ROOT_DIR}/mail/docker-data/dms/config/postfix-accounts.cf"
SAMBA_CONFIG="${ROOT_DIR}/file/config.yml"

require_file() {
  local path="$1"
  if [[ ! -f "${path}" ]]; then
    echo "Missing required file: ${path}" >&2
    exit 1
  fi
}

require_command() {
  local cmd="$1"
  if ! command -v "${cmd}" >/dev/null 2>&1; then
    echo "Missing required command: ${cmd}" >&2
    exit 1
  fi
}

require_command docker
require_command ss

require_file "${ENV_FILE}"
require_file "${MAIL_ACCOUNTS}"
require_file "${SAMBA_CONFIG}"

set -a
source "${ENV_FILE}"
set +a

CERT_DIR="${ROOT_DIR}/mail/certs"
FULLCHAIN="${CERT_DIR}/fullchain.pem"
PRIVKEY="${CERT_DIR}/privkey.pem"

if [[ ! -f "${FULLCHAIN}" || ! -f "${PRIVKEY}" ]]; then
  echo "TLS certificates not found under ${CERT_DIR}" >&2
  exit 1
fi

for port in 25 143 445 465 587 993; do
  if ss -ltn "( sport = :${port} )" | tail -n +2 | grep -q .; then
    echo "Port ${port} is already in use" >&2
    exit 1
  fi
done

docker compose --env-file "${ENV_FILE}" -f "${ROOT_DIR}/compose.internal-services.yml" config >/dev/null

echo "Preflight passed."
