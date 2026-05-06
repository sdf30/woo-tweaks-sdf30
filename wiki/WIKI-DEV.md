# Wiki Développeur - Woo Tweaks Tools

Ce document est destiné aux développeurs contribuant au projet. Il définit l'architecture, les conventions et la méthodologie d'ajout de nouvelles fonctionnalités.

## Architecture Globale

Le plugin suit une architecture Orientée Objet (OOP) et modulaire :
- **Point d'entrée** : `woo-tweaks-sdf30.php` (Définition des constantes, chargement de l'autoloader et initialisation du plugin).
- **Core** : Dossier `includes/Core/`. Contient l'infrastructure centrale.
  - `ModuleManager.php` : Registre central qui charge et active les modules.
  - `SettingsPage.php` : Gère l'affichage natif HTML/CSS en 2 colonnes (Grid) de la page de configuration, l'intégration de CodeMirror et la sauvegarde des options.
- **Modules** : Dossier `includes/Modules/`. Chaque fonctionnalité est isolée.
  - `AbstractModule.php` : Classe parente que **tous** les modules doivent étendre. Elle impose une méthode `init()`.

## Conventions de Code

- **Espace de noms (Namespace)** : `WooTweaksTools\...`
- **Typage strict** : Utilisation du typage fort PHP 8.1+ (ex: `public function foo(string $bar): void`).
- **Analyse Statique** : Validé par PHPCS (Standard WordPress-Core) et PHPStan (Level 5+).
- **Échappement** : Sécurité absolue sur les outputs (`esc_html()`, `esc_url()`, `esc_attr()`).
- **Layout Admin** : Aucune manipulation DOM en JavaScript pour la structure. Le layout doit être écrit de manière native via surcharge des méthodes de `WC_Settings_Page` (ex: `output()`).

## Comment créer un nouveau module ?

1. Créez un dossier dans `includes/Modules/` (ex: `MonNouveauModule`).
2. Créez la classe PHP `MonNouveauModule.php`.
3. Étendez `AbstractModule` et implémentez la méthode `init()`.
   ```php
   namespace WooTweaksTools\Modules\MonNouveauModule;
   use WooTweaksTools\Core\AbstractModule;

   class MonNouveauModule extends AbstractModule {
       public function init(): void {
           // Ajoutez vos hooks (actions/filtres) ici
       }
   }
   ```
4. Enregistrez le module dans `includes/Core/ModuleManager.php` (dans la méthode `init_modules()`).
5. (Optionnel) Si le module nécessite une interface utilisateur, ajoutez les champs dans `SettingsPage.php`.

## FSE (Full Site Editing) & React Blocks
Certains modules (ex: Read More Button) exposent des blocs Gutenberg pour les thèmes FSE.
Ces blocs utilisent le SSR (Server Side Rendering) via la directive `"render": "file:./block.php"` dans le `block.json`, garantissant qu'ils restent parfaitement intégrés entre le front-end et le Site Editor.

## Qualité et IDE (Intelephense)
- Les alertes IDE liées à `Undefined function '__'` ou `add_action` sont normales si l'IDE n'indexe pas le `wp-includes`. 
- Un fichier `.vscode/settings.json` est fourni à la racine pour assurer le bon chargement des *stubs* Intelephense inclus.
