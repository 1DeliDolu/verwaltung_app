#!/usr/bin/env bash
set -euo pipefail

BASE_DIR="${1:-$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)/file/shares}"

mkdir -p "${BASE_DIR}/it/mail-server"
mkdir -p "${BASE_DIR}/it/file-server"
mkdir -p "${BASE_DIR}/hr/policies"
mkdir -p "${BASE_DIR}/operations/runbooks"

cat > "${BASE_DIR}/it/mail-server/README.txt" <<'EOF'
Mailserver-Dokumente:
- Architektur
- Benutzer- und Aliasverwaltung
- Zertifikate
EOF

cat > "${BASE_DIR}/it/file-server/README.txt" <<'EOF'
Dateiserver-Dokumente:
- Freigaben
- Zugriffsregeln
- Backup-Plan
EOF

cat > "${BASE_DIR}/hr/policies/README.txt" <<'EOF'
HR-Richtlinien und interne Formulare.
EOF

cat > "${BASE_DIR}/operations/runbooks/README.txt" <<'EOF'
Operations-Runbooks und Betriebsanweisungen.
EOF

printf 'Share folders initialized under %s\n' "${BASE_DIR}"
