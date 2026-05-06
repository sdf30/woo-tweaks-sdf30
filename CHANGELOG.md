# Changelog

Toutes les modifications notables apportées à ce projet seront documentées dans ce fichier.

Le format est basé sur [Keep a Changelog](https://keepachangelog.com/fr/1.0.0/).

## [1.3.0] - 2026-05-06

### Ajouté
- **Smart Stock Messaging** : Système d'alertes d'urgence sur les fiches produits (ex: "🔥 Plus que X articles !") avec seuil et template personnalisables.
- **Enhanced Account UX** : 
    - Champs Email et Mot de passe rétractables par défaut pour une interface "Mon Compte" plus épurée.
    - Ajout d'un champ natif "Date de naissance" sauvegardé dans le profil utilisateur.
- **Améliorations UX** : Nouvelle section dédiée dans les réglages pour les optimisations frontend.

### Modifié
- Optimisation des assets (JS/CSS) chargés conditionnellement selon la page (Produit seul ou Compte).
- Amélioration de la clarté des labels de toggle dans l'espace client.

## [1.2.1] - 2026-05-06
### Ajouté
- **Stock Status Shortcuts** : Ajout d'actions rapides (AJAX) dans la liste des produits pour basculer l'état du stock sans rechargement.
- **Gestion Administrative** : Nouvelle section dans les réglages pour les outils de productivité backend.

## [1.2.0] - 2026-05-06

### Ajouté
- Module `Hide Components` : Permet de masquer SKU, Catégories et Produits Apparentés sur les thèmes classiques (Legacy).
- Module `Bulk Price Manager` : Intégration native dans les "Actions Groupées" de WooCommerce pour modifier les prix massivement (Fixe/Pourcentage, Augmentation/Diminution, Arrondi à .99).
- Refonte du module `Direct Checkout` : 
    - Ajout d'un bouton secondaire "Acheter maintenant" (Legacy & FSE) plutôt qu'une redirection forcée.
    - **Nouveau** : Label du bouton personnalisable via les réglages.
    - **Nouveau** : Support complet des archives produits (Boutique/Catégories) sur thèmes FSE via détection dynamique de l'ID produit.
    - Gestion native de la validation WooCommerce (quantités et variations).
- Amélioration `Read More Button` :
    - Option pour ouvrir les liens dans un nouvel onglet (`target="_blank"`).
    - Option pour afficher le bouton sur les pages de produit seul (Single Product).

### Modifié
- Harmonisation de l'interface d'administration avec des toggles clairs.
- Stabilisation du cycle de vie des modules (`AbstractModule` require `is_active`).

## [1.1.0] - 2026-05-05

### Ajouté
- Architecture modulaire via `ModuleManager`.
- Module `Custom Labels` (Personnalisation du texte "Ajouter au panier").
- Module `Read More Button` (Version initiale pour les archives).
- Module `Empty Cart Redirect` (Redirection du panier vide).
- Module `Checkout Fields Cleaner` (Suppression des champs inutiles).
- Moteur de `Custom CSS` avec éditeur `CodeMirror`.
- Liens rapides d'accès aux réglages.
- Documentation continue : `README.md`, `WIKI-USER.md` et `WIKI-DEV.md`.

## [1.0.0] - 2026-05-04
- Version initiale du projet (PoC).
