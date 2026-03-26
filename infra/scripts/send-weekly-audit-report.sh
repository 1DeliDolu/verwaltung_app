#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
PHP_BIN="${PHP_BIN:-php}"

if ! command -v "${PHP_BIN}" >/dev/null 2>&1; then
  echo "Missing required command: ${PHP_BIN}" >&2
  exit 1
fi

exec "${PHP_BIN}" "${ROOT_DIR}/bin/send-weekly-audit-report.php" "$@"
