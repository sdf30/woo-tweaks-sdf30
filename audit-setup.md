# Rapport de Mise en Place Qualité & Pont MCP

## 1. Pont IDE (MCP)

### État de l'installation
L'installation automatisée de l'extension VS Code via le terminal a rencontré des limitations techniques. Pour assurer un pont temps-réel avec la vue "Problems", veuillez suivre ces étapes manuelles :

1. **Installation de l'extension :**
   - Recherchez et installez l'extension **"Diagnostics MCP Server"** (par Maaz Tajammul) dans le Marketplace VS Code.
   - Identifiant suggéré : `maaztajammul.diagnostics-mcp-server`.

2. **Configuration du client :**
   - L'extension expose par défaut un serveur MCP sur `http://127.0.0.1:3846/mcp`.
   - Ajoutez-le à votre configuration Claude Desktop ou Cursor.

---

## 2. Setup Qualité PHP

Les outils suivants ont été installés via Composer dans le répertoire du plugin :

- **PHP_CodeSniffer (phpcs)** : Avec le standard `WordPress-Core`.
- **PHPStan** : Avec l'extension `phpstan-wordpress` (Niveau 5).

### Commandes disponibles
Depuis la racine du plugin :

| Action | Commande |
| :--- | :--- |
| **Linting (Scan)** | `./vendor/bin/phpcs .` |
| **Auto-fix (PHPCBF)** | `./vendor/bin/phpcbf .` |
| **Analyse Statique** | `./vendor/bin/phpstan analyse --memory-limit=1G` |

---

## 3. Résultats du premier scan (Audit)

### PHP_CodeSniffer (Standards WordPress)
- **Fichiers scannés** : 7
- **Erreurs détectées** : 750
- **Avertissements** : 8
- **Fixables automatiquement** : 718 (Utilisez `phpcbf` pour les corriger instantanément).

### PHPStan (Analyse de niveau 5)
- **Statut** : Configuré avec `php-stubs/woocommerce-stubs` pour une analyse précise des fonctions WooCommerce.
- **Optimisation** : Le scan initial peut être lent lors de la première indexation des stubs.

---

## 4. Recommandations immédiates

1. **Appliquer les correctifs automatiques** :
   ```bash
   ./vendor/bin/phpcbf .
   ```
2. **Activer le serveur MCP** : Une fois l'extension installée dans VS Code, je pourrai lire vos erreurs en temps réel via l'endpoint `http://127.0.0.1:3846/mcp`.

---
*Rapport généré par Antigravity le 2026-05-06.*
