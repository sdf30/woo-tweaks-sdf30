# Wiki Développeur - Woo Tweaks Tools

Ce document est destiné aux développeurs souhaitant comprendre l'architecture du plugin ou ajouter de nouveaux modules.

## Architecture Globale

Le plugin suit une architecture Orientée Objet (OOP) et modulaire :
- **Point d'entrée** : `woo-tweaks-sdf30.php` (Définition des constantes, chargement de l'autoloader et initialisation du plugin).
- **Core** : Dossier `includes/Core/`. Contient l'infrastructure centrale.
  - `ModuleManager.php` : Registre central qui charge et active les modules.
  - `SettingsPage.php` : Gère l'affichage natif HTML/CSS en 2 colonnes (Grid) de la page de configuration, l'intégration de CodeMirror et la sauvegarde des options.
  - `AbstractModule.php` : Classe parente que **tous** les modules doivent étendre. Elle impose les méthodes `is_active()` et `init()`.

---

## Création d'un Nouveau Module

Pour ajouter une fonctionnalité, suivez ces étapes :

1. **Créer le dossier** : Créez un dossier dans `includes/Modules/[NomDuModule]/`.
2. **Créer la classe** : Créez un fichier `[NomDuModule]Module.php` qui étend `AbstractModule`.
3. **Implémenter la logique** :
   - `is_active()` : Retourne `true` ou une condition (ex: `get_option(...) === 'yes'`).
   - `init()` : Enregistrez vos hooks WordPress/WooCommerce ici.
   - **Hooks cibles** : Utilisez les filtres de WooCommerce/WP (ex: `woocommerce_product_bulk_edit_start`) ou interceptez les actions groupées via `bulk_actions-{screen}`.
   - **UI Administrative** : Pour les interactions complexes dans le backend, privilégiez l'injection d'un modal en `admin_footer` pour ne pas casser le layout natif de WordPress.
4. **Enregistrement** : Ajoutez votre classe dans le tableau `$module_classes` du fichier `includes/Core/ModuleManager.php`.

---

## Conventions de Code

- **Namespacing** : Utilisez `WooTweaksTools\Modules\[NomDuModule]`.
- **Typage** : Utilisez le typage strict (`declare(strict_types=1);`) et le typage des propriétés/méthodes.
- **Sécurité** : 
  - Toujours utiliser `defined('ABSPATH') || exit;`.
  - Échappez les sorties avec `esc_html`, `esc_attr`, etc.
  - Utilisez les nonces pour toute action soumise par formulaire.

## Gestion FSE (Full Site Editing)

Le plugin est conçu pour être compatible avec les thèmes classiques et les thèmes de blocs (FSE).
- Si un module modifie le rendu visuel (ex: masquer des éléments), utilisez `wp_is_block_theme()` pour désactiver le module ou adapter son comportement.
- Pour ajouter des éléments dans FSE, privilégiez la création de Blocs Gutenberg (ex: `includes/Modules/ReadMoreButton/ReadMoreBlock.php`).

---

*Document de référence pour le maintien des standards de qualité OPEN-SDF.*
