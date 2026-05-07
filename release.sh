#!/usr/bin/env bash

# Exit on error
set -e

# Get the latest git tag
LATEST_TAG=$(git describe --tags --abbrev=0 2>/dev/null || echo "v0.0.0")
CURRENT_VERSION=${LATEST_TAG#v}

# Parse current version into array
IFS='.' read -r -a parts <<< "$CURRENT_VERSION"
X=${parts[0]:-0}
Y=${parts[1]:-0}
Z=${parts[2]:-0}

# Calculate next version
Z=$((Z + 1))
if [ $Z -gt 3 ]; then
    Z=0
    Y=$((Y + 1))
    if [ $Y -gt 3 ]; then
        Y=0
        X=$((X + 1))
    fi
fi

SUGGESTED_VERSION="$X.$Y.$Z"

if [ -n "$1" ]; then
    NEW_VERSION=$1
else
    echo "💡 Dernière version trouvée : $CURRENT_VERSION"
    read -p "👉 Appuyez sur Entrée pour utiliser la version suggérée [$SUGGESTED_VERSION], ou tapez une version manuellement : " input_version
    if [ -z "$input_version" ]; then
        NEW_VERSION=$SUGGESTED_VERSION
    else
        NEW_VERSION=$input_version
    fi
fi
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

read -p "🚀 Voulez-vous pousser la release sur GitLab (origin) et GitHub (github) ? (y/n) " push_confirm
if [[ "$push_confirm" =~ ^[YyOo]$ ]]; then
    echo "📡 Déploiement sur GitLab (origin)..."
    git push origin main && git push origin --tags
    
    echo "📡 Déploiement sur GitHub (github)..."
    git push github main && git push github --tags
    
    echo "🎈 Création de la Release GitHub..."
    if command -v gh >/dev/null 2>&1; then
        gh release create "v$NEW_VERSION" "../tweak-tools-sdf30-release.zip" --title "Release v$NEW_VERSION" --notes "Mise à jour vers la version $NEW_VERSION"
    else
        echo "⚠️ CLI GitHub (gh) non trouvée. Release manuelle nécessaire."
    fi

    echo "🎉 Push terminé avec succès !"
else
    echo "✋ Push ignoré. Clôture de la tâche."
fi
