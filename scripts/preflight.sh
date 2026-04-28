#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
# shellcheck source=lib.sh
source "$SCRIPT_DIR/lib.sh"

require_cmd semgrep
require_cmd syft
require_cmd gitleaks

echo "[preflight] semgrep:  $(semgrep --version | head -n 1)"
echo "[preflight] syft:     $(syft version | head -n 1)"
echo "[preflight] gitleaks: $(gitleaks version | head -n 1)"

