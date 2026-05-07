#!/bin/bash

# Configuration
PLUGIN_FILE="woo-tweaks-sdf30.php"
README_FILE="readme.txt"
PLUGIN_NAME="woo-tweaks-sdf30"

echo "🚀 Préparation d'une nouvelle release pour ${PLUGIN_NAME}..."

# 1. Récupérer la version actuelle
CURRENT_VERSION=$(grep -E "^ \* Version:" "$PLUGIN_FILE" | awk '{print $3}')
echo "📍 Version actuelle : ${CURRENT_VERSION}"

# 2. Calculer la version suggérée (Patch +1)
IFS='.' read -r major minor patch <<< "$CURRENT_VERSION"
SUGGESTED_VERSION="$major.$minor.$((patch + 1))"

# 3. Demander la nouvelle version
read -p "📝 Entrez la nouvelle version [${SUGGESTED_VERSION}] : " NEW_VERSION
NEW_VERSION=${NEW_VERSION:-$SUGGESTED_VERSION}

echo "⭐ Nouvelle version choisie : ${NEW_VERSION}"

# 4. Mettre à jour les fichiers
echo "📝 Mise à jour des fichiers..."
sed -i '' "s/Version:           ${CURRENT_VERSION}/Version:           ${NEW_VERSION}/g" "$PLUGIN_FILE"
sed -i '' "s/Stable tag: ${CURRENT_VERSION}/Stable tag: ${NEW_VERSION}/g" "$README_FILE"

# 5. Git Commit & Tag
echo "💾 Git commit et tag..."
git add "$PLUGIN_FILE" "$README_FILE"
git commit -m "UPDATE: Bump version to ${NEW_VERSION}"
git tag -a "v${NEW_VERSION}" -m "Release v${NEW_VERSION}"

# 6. Build ZIP
echo "📦 Génération du ZIP..."
./build-zip.sh

# 7. Push & GitHub Release
echo "📤 Envoi vers GitLab (origin) et GitHub..."
git push origin main && git push origin --tags
git push github main && git push github --tags

echo "🎈 Création de la Release GitHub..."
gh release create "v${NEW_VERSION}" "${PLUGIN_NAME}.zip" --title "Release v${NEW_VERSION}" --notes "Mise à jour vers la version ${NEW_VERSION}"

echo "✅ Release ${NEW_VERSION} terminée avec succès !"
