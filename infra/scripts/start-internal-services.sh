#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

"${ROOT_DIR}/scripts/preflight-internal-services.sh"
"${ROOT_DIR}/scripts/bootstrap-file-shares.sh"

docker compose \
  --env-file "${ROOT_DIR}/.env.internal-services" \
  -f "${ROOT_DIR}/compose.internal-services.yml" \
  up -d

echo "Internal services stack started."
