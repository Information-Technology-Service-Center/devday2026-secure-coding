#!/usr/bin/env bash
set -u

TARGET="${1:-}"
LABEL="${2:-}"
if [[ -z "$TARGET" ]]; then
  echo "Usage: $0 <target-dir> [label]" >&2
  exit 2
fi

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

"$SCRIPT_DIR/preflight.sh" || exit 1

set +e
"$SCRIPT_DIR/run-semgrep.sh" "$TARGET" "$LABEL"
SEM_STATUS=$?
"$SCRIPT_DIR/run-syft.sh" "$TARGET" "$LABEL"
SYFT_STATUS=$?
"$SCRIPT_DIR/run-gitleaks.sh" "$TARGET" "$LABEL"
GITLEAKS_STATUS=$?
set -e

echo
echo "[summary] semgrep:  $SEM_STATUS"
echo "[summary] syft:     $SYFT_STATUS"
echo "[summary] gitleaks: $GITLEAKS_STATUS"

if [[ $SEM_STATUS -ne 0 || $SYFT_STATUS -ne 0 || $GITLEAKS_STATUS -ne 0 ]]; then
  exit 1
fi
