# Wiki Développeur - Woo Tweaks Tools

Ce document est destiné aux développeurs souhaitant comprendre l'architecture du plugin ou ajouter de nouveaux modules.

## Architecture Globale

Le plugin suit une architecture Orientée Objet (OOP) et modulaire :
- **Point d'entrée** : `woo-tweaks-sdf30.php` (Définition des constantes, chargement de l'autoloader et initialisation du plugin).
- **Core** : Dossier `includes/Core/`. Contient l'infrastructure centrale.
  - `ModuleManager.php` : Registre central qui charge et active les modules.
  - `SettingsPage.php` : Gère l'affichage natif HTML/CSS en 2 colonnes (Grid) de la page de configuration, l'intégration de CodeMirror et la sauvegarde des options. Agit comme un conteneur pour les réglages décentralisés.
  - `AbstractModule.php` : Classe parente que **tous** les modules doivent étendre. Elle impose les méthodes `register_settings()`, `is_active()` et `init()`.

---

## Création d'un Nouveau Module

Pour ajouter une fonctionnalité, suivez ces étapes :

1. **Créer le dossier** : Créez un dossier dans `includes/Modules/[NomDuModule]/`.
2. **Créer la classe** : Créez un fichier `[NomDuModule]Module.php` qui étend `AbstractModule`.
3. **Implémenter la logique** :
   - `register_settings()` : Déclarer vos réglages via un `add_filter('woo_tweaks_core_settings', [$this, 'add_settings'])` pour les injecter dans l'interface de SettingsPage.
   - `is_active()` : Retourne `true` ou une condition (ex: `get_option(...) === 'yes'`).
   - `init()` : Enregistrez vos hooks WordPress/WooCommerce ici.
   - **Hooks cibles** : Utilisez les filtres de WooCommerce/WP (ex: `woocommerce_product_bulk_edit_start`) ou interceptez les actions groupées via `bulk_actions-{screen}`.
   - **UI Administrative** : Pour les interactions complexes dans le backend, privilégiez l'injection d'un modal en `admin_footer` pour ne pas casser le layout natif de WordPress.
4. **Enregistrement** : Ajoutez votre classe dans le tableau `$module_classes` du fichier `includes/Core/ModuleManager.php`.

---

## Étude de Cas : Bulk Price Manager

Le module `BulkPriceModule` sert d'exemple pour les manipulations complexes de données :
- **Interception JS** : Le formulaire `posts-filter` est intercepté en JavaScript pour afficher le modal avant la soumission réelle.
- **Passage de Paramètres** : Les réglages du modal sont injectés comme `hidden inputs` dans le formulaire natif juste avant l'envoi.
- **Traitement Serveur** : Utilise le hook `handle_bulk_actions-edit-product`. Il boucle sur les IDs, charge les objets `WC_Product`, calcule les nouveaux prix (incluant la logique d'arrondi) et persiste via `$product->save()`.
- **Feedback** : Utilise `add_query_arg` dans l'URL de redirection pour passer le nombre de modifications à `admin_notices`.

---

## Étude de Cas : Direct Checkout (Hybride)

Le module `DirectCheckoutModule` démontre une approche hybride pour assurer une compatibilité maximale :
- **Thèmes Classiques** : Injection via le hook `woocommerce_after_add_to_cart_button`. Le bouton est un `<button type="submit">` natif, ce qui permet de soumettre le formulaire existant sans JavaScript additionnel.
- **Thèmes FSE** : Utilisation d'un Bloc Gutenberg dynamique.
  - **Asset Management** : Le script frontend `direct-checkout.js` est déclaré via `viewScript` dans `block.json`. Il n'est chargé que si le bloc est présent.
  - **Logique JS** : Le script intercepte le clic, cherche le formulaire `form.cart` le plus proche pour injecter un flag `woo_tweaks_direct_checkout`. Si aucun formulaire n'est trouvé (Archives), il manipule l'URL pour ajouter `?add-to-cart=ID&woo_tweaks_direct_checkout=1`.
- **Redirection** : Le plugin intercepte le hook `woocommerce_add_to_cart_redirect`. Si le flag `woo_tweaks_direct_checkout` est présent dans la requête, il redirige vers `wc_get_checkout_url()`.

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

## Étude de Cas : Stock Status Shortcuts (AJAX)
Ce module utilise une approche réactive pour le backend :
- **Hooks** : Utilise `manage_product_posts_columns` pour s'injecter et `manage_product_posts_custom_column` pour remplacer l'affichage natif du stock.
- **AJAX** : Enregistre une action `wp_ajax_woo_tweaks_toggle_stock`. La sécurité est assurée par un contrôle de capacité `edit_products` et une vérification de nonce.
- **Frontend Admin** : Un script léger `stock-status.js` gère le clic, affiche un état de chargement visuel (opacité) et met à jour la couleur/texte de la pastille en fonction du retour JSON.

## Étude de Cas : Smart Stock Messaging (Filtre dynamique)
Démontre l'utilisation des filtres de données WooCommerce :
- **Filtre** : `woocommerce_get_availability`. Ce filtre est idéal car il est appelé partout où WooCommerce affiche le stock (Fiche produit, archives, widgets).
- **Logique** : Vérifie si le produit gère le stock et si la quantité est inférieure ou égale au seuil défini. Si oui, il injecte le template HTML dans la clé `availability` du tableau de retour.
- **Styling** : Utilise des variables CSS pour permettre une personnalisation facile via le module `Custom CSS`.

## Étude de Cas : Enhanced Account UX (DOM Manipulation)
Approche axée sur le "Clean UI" via JavaScript :
- **Account Details** : Utilise `woocommerce_edit_account_form` pour injecter le champ "Date de naissance" et les boutons de toggle.
- **JS Toggle** : Le script `account-ux.js` enveloppe les sections Email et Mot de passe dynamiquement au chargement de la page pour éviter de casser la structure HTML native générée par WooCommerce ou d'autres plugins.
- **Persistance** : La date de naissance est traitée lors du hook `woocommerce_save_account_details` et stockée comme `user_meta`.

## Étude de Cas : Promo Urgency Badges (Dynamic Injection & FSE)
Optimisation des conversions par l'urgence :
- **Calcul de %** : Gère les produits simples et variables (prend le prix min pour les variables).
- **Formatage Date** : Utilise `date_i18n` pour respecter la locale du site.
- **Injection HTML** : Utilise `woocommerce_get_price_html` pour insérer le badge de réduction proprement.
- **Support FSE** : Le module enregistre un bloc Gutenberg dynamique (`block.json` + `render.php`). La méthode `get_urgency_message_html` est partagée entre le hook classique et le rendu du bloc pour une maintenance simplifiée.

---

*Document de référence pour le maintien des standards de qualité OPEN-SDF.*
