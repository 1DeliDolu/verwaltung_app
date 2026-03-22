#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

docker compose \
  --env-file "${ROOT_DIR}/.env.internal-services" \
  -f "${ROOT_DIR}/compose.internal-services.yml" \
  ps
