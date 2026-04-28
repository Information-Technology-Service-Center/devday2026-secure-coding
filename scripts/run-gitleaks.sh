#!/usr/bin/env bash
set -euo pipefail

TARGET="${1:-}"
LABEL="${2:-}"
if [[ -z "$TARGET" ]]; then
  echo "Usage: $0 <target-dir> [label]" >&2
  exit 2
fi

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
# shellcheck source=lib.sh
source "$SCRIPT_DIR/lib.sh"

require_dir "$TARGET"
require_cmd gitleaks

OUT_DIR="$ROOT_DIR/reports"
mkdir -p "$OUT_DIR"

SLUG="$(slug_from_target "$TARGET")"
RUN_ID="$(run_id_from_label_or_time "$LABEL")"
OUT_JSON="$OUT_DIR/${SLUG}.${RUN_ID}.gitleaks.json"
OUT_LATEST="$OUT_DIR/${SLUG}.latest.gitleaks.json"

echo "[gitleaks] scanning: $TARGET"
set +e
gitleaks detect \
  --source "$TARGET" \
  --no-git \
  --config "$ROOT_DIR/configs/.gitleaks.toml" \
  --report-format json \
  --report-path "$OUT_JSON"
STATUS=$?
set -e

if [[ -f "$OUT_JSON" ]]; then
  cp -f "$OUT_JSON" "$OUT_LATEST"
  echo "[gitleaks] wrote: $OUT_JSON"
  echo "[gitleaks] latest: $OUT_LATEST"
else
  echo "[gitleaks] report missing: $OUT_JSON" >&2
fi

# gitleaks returns non-zero when leaks are found; propagate that status so the
# lab can treat findings as a "failed scan" until fixed.
exit "$STATUS"
