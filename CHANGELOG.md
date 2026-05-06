# Changelog

Toutes les modifications notables apportées à ce projet seront documentées dans ce fichier.

Le format est basé sur [Keep a Changelog](https://keepachangelog.com/fr/1.0.0/).

## [1.2.0] - 2026-05-06

### Ajouté
- Module `Hide Components` : Permet de masquer SKU, Catégories et Produits Apparentés sur les thèmes classiques (Legacy).
- Module `Bulk Price Manager` : Intégration native dans les "Actions Groupées" de WooCommerce pour modifier les prix massivement (Fixe/Pourcentage, Augmentation/Diminution, Arrondi à .99).
- Refonte du module `Direct Checkout` : Ajout d'un bouton secondaire "Acheter maintenant" (Legacy & FSE) plutôt qu'une redirection forcée, supportant la validation native des quantités et des variations.
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
