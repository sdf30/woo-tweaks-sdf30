#!/bin/bash

# Nom du plugin
PLUGIN_NAME="woo-tweaks-sdf30"
ZIP_FILE="${PLUGIN_NAME}.zip"

echo "📦 Préparation du build pour ${PLUGIN_NAME}..."

# Supprimer l'ancien zip s'il existe
if [ -f "$ZIP_FILE" ]; then
    rm "$ZIP_FILE"
fi

# Créer un dossier temporaire pour le build
BUILD_DIR="dist_temp"
mkdir -p "$BUILD_DIR/$PLUGIN_NAME"

echo "📂 Copie des fichiers..."

# Copier les fichiers nécessaires (on exclut les fichiers de dev)
rsync -rc --exclude-from=.gitignore --exclude=".git" --exclude=".github" --exclude=".env" --exclude="*.sh" --exclude="composer.json" --exclude="composer.lock" --exclude="phpcs.xml" --exclude="phpstan.neon" --exclude="wiki" --exclude="GEMINI.md" --exclude=".wordpress-org" --exclude="dist_temp" ./ "$BUILD_DIR/$PLUGIN_NAME/"

echo "🤐 Compression..."

# Créer le zip
cd "$BUILD_DIR"
zip -r "../$ZIP_FILE" "$PLUGIN_NAME" > /dev/null
cd ..

# Nettoyer
rm -rf "$BUILD_DIR"

echo "✅ Build terminé : ${ZIP_FILE}"
echo "🚀 Tu peux maintenant soumettre ce fichier sur : https://wordpress.org/plugins/add/"
