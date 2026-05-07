# Leçons Apprises (Woo Tweaks SDF30)

Ce document répertorie de manière exhaustive l'ensemble des connaissances, des problématiques techniques et des solutions implémentées tout au long du cycle de développement du plugin `woo-tweaks-sdf30`. Il sert de bible technique pour les futures itérations et les nouveaux contributeurs.

## 1. Architecture et Philosophie Code (PHP / OOP)

### Modularité ("Decentralized Pattern")
- **Problème initial** : La page de réglages centralisée ("God Object") devenait lourde et difficile à maintenir. Chaque ajout de fonctionnalité nécessitait la modification du cœur de la page de réglages.
- **Solution** : Passage à une architecture orientée objet (OOP) avec une classe `AbstractModule` et un `ModuleManager`. Chaque module (ex: `DirectCheckoutModule`, `PromoUrgencyModule`) gère désormais sa propre logique, ses propres réglages et ses propres hooks.
- **Bénéfice** : Scalabilité infinie. Pour ajouter une fonctionnalité, il suffit de créer une classe qui hérite de l'abstraction, sans toucher au noyau dur.

### Sécurité et Standards WordPress (WP.org)
- **Validation et Échappement** : Les standards de WordPress.org sont intraitables. Chaque donnée utilisateur *doit* être désinfectée avant traitement (`sanitize_text_field()`) et chaque sortie *doit* être échappée (`esc_html()`, `esc_attr()`, `wp_kses_post()`). L'utilisation des superglobales (`$_POST`, `$_GET`) a été systématiquement protégée avec `wp_unslash()`.
- **Analyse Statique** : La mise en place de PHP_CodeSniffer (avec les règles `WordPress-Core`) et de PHPStan a permis d'identifier et de nettoyer proactivement le code mort, les variables non initialisées et les failles de sécurité avant la soumission au repo.

## 2. Intégration avancée avec WooCommerce

### Manipulation de l'interface d'administration
- L'ajout d'onglets personnalisés dans la metabox "Product Data" (ex: bouton "Read More") nécessite de se greffer spécifiquement sur `woocommerce_product_options_general_product_data` et de sauvegarder via `woocommerce_process_product_meta`.
- **AJAX et Bulk Actions** : Le développement du "Bulk Price Manager" a démontré la complexité d'ajouter des actions groupées personnalisées avec interface modale native de WP, nécessitant l'envoi asynchrone pour traiter de larges volumes de produits sans faire planter le serveur.

### Les Hooks Front-end
- Nous avons navigué dans les subtilités des hooks front-end de WooCommerce (`woocommerce_after_shop_loop_item` pour les archives vs `woocommerce_single_product_summary` pour la vue produit). L'ordre des priorités (le 3e paramètre de `add_action`) est crucial pour le rendu visuel.

## 3. Transition vers l'Éditeur de Site (FSE) & Gutenberg

### Hybridation (Legacy vs FSE)
- **Contexte** : Avec l'arrivée du Full Site Editing (Themes à base de Blocs), les hooks WooCommerce classiques sont souvent ignorés ou remplacés par des composants React.
- **Solution** : Nous avons dû implémenter une logique hybride. Si un thème classique est utilisé, nous injectons le code via les hooks. Si un thème FSE est utilisé, la fonctionnalité est encapsulée dans un bloc natif Gutenberg.

### Les blocs natifs (`block.json` & ServerSideRender)
- La transformation de fonctions PHP en blocs FSE s'est faite via `register_block_type` utilisant un rendu dynamique (`render_callback`). 

### 🚨 Le Piège du Cycle de Vie (L'erreur "Doing it Wrong")
- **L'anomalie** : L'outil `QueryMonitor` a remonté une alerte fatale depuis WP 6.7.0 : `Function _load_textdomain_just_in_time was called incorrectly`.
- **L'explication** : Lors du refactoring modulaire, la méthode `register_fse_block()` était appelée directement dans la méthode `init()` du module, elle-même déclenchée lors de l'évènement `plugins_loaded`. Or, la déclaration via `block.json` déclenche en cascade la lecture du fichier de traduction (i18n schema). Faire cela sur `plugins_loaded` est trop précoce pour WordPress.
- **La correction** : L'enregistrement d'un bloc `register_block_type()` **doit impérativement** être rattaché au hook `init` de WordPress (`add_action('init', [$this, 'register_fse_block'])`).

## 4. Internationalisation (i18n) et Chaînes Linguistiques

### L'Anglais comme Source de Vérité
- **L'erreur du développeur natif** : Initialement, les chaînes de caractères étaient codées en dur en Français. Cela crée une lourde dette technique. WordPress.org exige que la langue par défaut du code (`__('String', 'text-domain')`) soit l'**Anglais (US)**.
- **Refonte** : Remplacement massif de l'intégralité du code et des commentaires pour faire de l'anglais l'unique langue source.

### La complexité de `block.json`
- Les métadonnées des blocs FSE (`title`, `description`, `keywords` dans le `block.json`) sont également lues et traduites par l'écosystème WP. Elles doivent donc elles aussi être rédigées en anglais. Le WP CLI (`wp i18n make-pot`) se charge automatiquement d'extraire ces chaînes JSON vers le fichier POT.

### Piège d'échappement lors de la refonte
- Les apostrophes échappées (ex: `Cache le champ d\'adresse`) ont causé des échecs silencieux lors de nos scripts de recherche et de remplacement automatique. **Leçon** : Toujours utiliser des outils comme Loco Translate ou des regex stricts pour auditer le code afin d'identifier les reliquats linguistiques.

## 5. DevOps, Build & Distribution (L'écosystème Git/Bash)

### Automatisation du Packaging
- Soumettre à WordPress.org (ou toute autre plateforme pro) requiert une archive `.zip` propre. Les dossiers comme `.git`, `tests`, `temp_`, `.vscode` ou `.DS_Store` ne doivent pas figurer dans la release.
- **Solution** : Création d'un script Bash dédié (`build-zip.sh`) qui compile uniquement les fichiers nécessaires en se basant sur une approche ciblée, couplé à un script de release (`release.sh`) pour automatiser le versioning (bump de la version PHP, tags Git, etc).

### Double Synchronisation Git
- La gestion d'un environnement de dev hybride requiert la maîtrise d'un dépôt local interconnecté avec des *remotes* distants garantissant que la branche `main` soit toujours la version finale, propre et vérifiée de l'extension avant la soumission.
