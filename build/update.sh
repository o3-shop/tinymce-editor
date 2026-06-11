#!/usr/bin/env bash
#
# Fetch a pinned TinyMCE release from npm and stage it into out/tinymce/.
# The bundle is committed to the repo; re-run this script to bump versions.
#
# Usage: ./build/update.sh [version]   (default below)
#
set -euo pipefail

VERSION="${1:-7.9.3}"
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
MODULE_DIR="$(dirname "$SCRIPT_DIR")"
OUT="$MODULE_DIR/out/tinymce"
TMP="$(mktemp -d)"
trap 'rm -rf "$TMP"' EXIT

echo "Downloading tinymce@$VERSION ..."
( cd "$TMP" && npm pack "tinymce@$VERSION" >/dev/null )
tar -xzf "$TMP"/tinymce-*.tgz -C "$TMP"
SRC="$TMP/package"

# Replace bundle assets. Preserve the existing langs/ packs — the npm package
# does not ship language files, and ours are forward-compatible with 7.x.
for item in tinymce.js tinymce.min.js tinymce.d.ts package.json bower.json \
            composer.json CHANGELOG.md README.md license.md license.txt \
            plugins skins themes models icons; do
    rm -rf "${OUT:?}/$item"
    if [ -e "$SRC/$item" ]; then
        cp -R "$SRC/$item" "$OUT/$item"
    fi
done

echo "TinyMCE staged into $OUT"
grep -o '"version"[^,]*' "$OUT/package.json" | head -1
