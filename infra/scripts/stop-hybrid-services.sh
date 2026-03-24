#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
MODE="${1:-demo}"

case "${MODE}" in
  demo)
    docker compose \
      --env-file "${ROOT_DIR}/demo/.env.demo-services" \
      -f "${ROOT_DIR}/demo/compose.demo-services.yml" \
      down
    ;;
  internal)
    docker compose \
      --env-file "${ROOT_DIR}/.env.internal-services" \
      -f "${ROOT_DIR}/compose.internal-services.yml" \
      down
    ;;
  *)
    cat <<'EOF'
Usage:
  infra/scripts/stop-hybrid-services.sh [demo|internal]

Modes:
  demo      Stops the demo stack with MailHog and Samba.
  internal  Stops the internal stack.
EOF
    exit 1
    ;;
esac
