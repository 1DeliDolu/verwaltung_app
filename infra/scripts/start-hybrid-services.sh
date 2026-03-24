#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
MODE="${1:-demo}"

case "${MODE}" in
  demo)
    "${ROOT_DIR}/scripts/start-demo-services.sh"
    ;;
  internal)
    "${ROOT_DIR}/scripts/start-internal-services.sh"
    ;;
  *)
    cat <<'EOF'
Usage:
  infra/scripts/start-hybrid-services.sh [demo|internal]

Modes:
  demo      Starts the demo stack with MailHog and Samba on demo ports.
  internal  Starts the internal stack with the full compose file.
EOF
    exit 1
    ;;
esac
