#!/usr/bin/env bash

# Exit on error
set -e

if [ -z "$1" ]; then
    echo "Usage: ./release.sh <version>"
    echo "Example: ./release.sh 1.3.3"
    exit 1
fi

NEW_VERSION=$1
PLUGIN_FILE="tweak-tools-sdf30.php"
README_FILE="readme.txt"

echo "🚀 Preparing release for version $NEW_VERSION..."

# 1. Update version in main plugin file
echo "📝 Updating version in $PLUGIN_FILE..."
# Update Plugin header (Version: ...)
sed -i '' "s/Version:           .*/Version:           $NEW_VERSION/" $PLUGIN_FILE
# Update Plugin class constant
sed -i '' "s/public const VERSION = '.*';/public const VERSION = '$NEW_VERSION';/" $PLUGIN_FILE

# 2. Update version in readme.txt
echo "📝 Updating version in $README_FILE..."
sed -i '' "s/Stable tag: .*/Stable tag: $NEW_VERSION/" $README_FILE

# 3. Commit the version bump
echo "📦 Committing changes to Git..."
git add $PLUGIN_FILE $README_FILE
# Commit only if there are changes (prevents crash if version was already up-to-date)
git diff --cached --quiet || git commit -m "🔖 Release v$NEW_VERSION"

# 4. Create Git Tag
echo "🏷️ Tagging as v$NEW_VERSION..."
# Force recreate tag if it exists, or create new
git tag -f -a "v$NEW_VERSION" -m "Release v$NEW_VERSION"

# 5. Build the zip artifact using our new build-zip.sh
echo "🗜️ Generating release archive..."
if [ -f "./build-zip.sh" ]; then
    bash ./build-zip.sh
else
    echo "⚠️ build-zip.sh not found. Skipping zip generation."
fi

echo ""
echo "✅ Release v$NEW_VERSION is fully ready!"
echo "--------------------------------------------------"
echo "L'archive zip est dispo dans le dossier parent."
echo "Pense à pousser tes changements sur GitHub avec :"
echo "👉 git push origin main && git push origin --tags"
