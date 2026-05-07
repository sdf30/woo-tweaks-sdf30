#!/usr/bin/env bash

# Exit on error
set -e

PLUGIN_SLUG="tweak-tools-sdf30"
RELEASE_DIR="../"
ZIP_NAME="${PLUGIN_SLUG}-release.zip"
TARGET_ZIP="${RELEASE_DIR}${ZIP_NAME}"

echo "🚀 Building release zip for $PLUGIN_SLUG..."

# Remove old zip if exists
rm -f "$TARGET_ZIP"

# Create zip excluding development files
zip -r "$TARGET_ZIP" . \
    -x "*.git*" \
    -x "*.DS_Store" \
    -x "phpcs.xml" \
    -x "phpstan.neon" \
    -x "wiki/*" \
    -x "composer.json" \
    -x "composer.lock" \
    -x ".wordpress-org/*" \
    -x "README.md" \
    -x "CHANGELOG.md" \
    -x "vendor/*" \
    -x ".vscode/*" \
    -x "temp_/*" \
    -x "scripts/*" \
    -x "build-zip.sh" \
    -x "release.sh"

echo "✅ Build complete: $TARGET_ZIP"
