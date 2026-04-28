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
require_cmd syft

OUT_DIR="$ROOT_DIR/reports"
mkdir -p "$OUT_DIR"

SLUG="$(slug_from_target "$TARGET")"
RUN_ID="$(run_id_from_label_or_time "$LABEL")"
OUT_JSON="$OUT_DIR/${SLUG}.${RUN_ID}.sbom.cyclonedx.json"
OUT_LATEST="$OUT_DIR/${SLUG}.latest.sbom.cyclonedx.json"
TMP_JSON="$OUT_JSON.tmp"

echo "[syft] generating SBOM: $TARGET"
syft "$TARGET" -o cyclonedx-json > "$TMP_JSON"
mv -f "$TMP_JSON" "$OUT_JSON"
cp -f "$OUT_JSON" "$OUT_LATEST"
echo "[syft] wrote: $OUT_JSON"
echo "[syft] latest: $OUT_LATEST"
