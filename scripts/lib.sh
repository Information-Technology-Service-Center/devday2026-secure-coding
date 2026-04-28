#!/usr/bin/env bash
set -euo pipefail

require_cmd() {
  local cmd="$1"
  if ! command -v "$cmd" >/dev/null 2>&1; then
    echo "[error] missing command: $cmd" >&2
    return 1
  fi
}

require_dir() {
  local dir="$1"
  if [[ ! -d "$dir" ]]; then
    echo "[error] directory not found: $dir" >&2
    return 1
  fi
}

slug_from_target() {
  local target="$1"
  local base
  base="$(basename "$target")"
  if [[ "$base" == "app" ]]; then
    base="$(basename "$(dirname "$target")")"
  fi
  echo "$base" | tr '[:upper:]' '[:lower:]' | tr -cs 'a-z0-9._-' '-'
}

run_id_from_label_or_time() {
  local label="${1:-}"
  if [[ -n "$label" ]]; then
    echo "$label" | tr '[:upper:]' '[:lower:]' | tr -cs 'a-z0-9._-' '-'
    return 0
  fi
  date +"%Y%m%d-%H%M%S"
}

