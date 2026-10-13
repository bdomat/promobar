# PromoBar 1.0.4

## Compatibilité et validation

Cible : PrestaShop **1.7.0 à 9.2.x**, avec la version de PHP autorisée par chaque version de PrestaShop. Le module ne rend pas PrestaShop compatible avec une version de PHP non prise en charge par le cœur.

| PrestaShop | PHP du cœur |
| --- | --- |
| 1.7.x | Dépend de la sous-version ; 1.7.8 : jusqu’à PHP 7.4 |
| 8.0 à 8.2 | PHP 7.2 à 8.1 |
| 9.0 | PHP 8.1 à 8.4 |
| 9.1 à 9.2 | PHP 8.1 à 8.5 |

Le code du module conserve la syntaxe PHP 5.6 à 8.5 pour les installations historiques. **Ce maintien de compatibilité ne constitue pas une recommandation de sécurité** : au 13 octobre 2026, PHP 8.2 à 8.5 restent maintenus par PHP. Pour un nouveau déploiement, privilégier PrestaShop 9.2 avec PHP 8.4 ou 8.5 et les derniers correctifs.

- Tests locaux : analyse syntaxique PHP 8.3.6, tests de sécurité et de non-régression isolés.
- GitHub Actions : matrice PHP 5.6, 7.0–7.4 et 8.0–8.5 ; intégration sur PrestaShop 1.7.8.11 / PHP 7.4, 8.2.8 / PHP 8.1 et 9.2.0 / PHP 8.5. Vérifier le résultat du workflow associé au commit avant diffusion.
- Les tests isolés couvrent notamment le filtrage HTML et les contrôles des formulaires. Les intégrations utilisent des boutiques jetables. Ils ne remplacent pas la recette du thème et des modules tiers d’une boutique cliente.
- Références : [prérequis PrestaShop](https://devdocs.prestashop-project.org/9/basics/installation/system-requirements/), [versions PHP maintenues](https://www.php.net/supported-versions.php).

## Mise à jour sans perte des réglages

Sauvegarder la boutique, importer le ZIP installable dans le gestionnaire de modules, puis exécuter la mise à jour proposée. **Ne pas désinstaller ni réinitialiser le module** : la désinstallation supprime volontairement sa configuration. Sélectionner une boutique précise pour la configuration en multiboutique.

Les scripts `upgrade/` enregistrent les hooks nécessaires sans écraser les réglages. Après la mise à jour, recharger la page de configuration avant d’enregistrer : les formulaires possèdent désormais un jeton CSRF dédié.

## Développement

```sh
php -n tests/regression.php
python3 tools/build.py
```

Le ZIP est construit depuis le commit Git courant, avec un dossier racine portant le nom du module. Tests, outils de développement et workflows sont exclus de l’archive installable. Le ZIP « Source code » généré automatiquement par GitHub n’est pas l’archive d’installation à utiliser.

## Fonctionnalités

Bannière multilingue, dates de début et de fin, compte à rebours, bouton optionnel, couleurs, polices, animations et fermeture mémorisée par cookie. Les deux emplacements proposés sont `displayTop` et `displayAfterBodyOpeningTag` ; le thème doit appeler le hook sélectionné.

Le texte accepte `**gras**`, `[libellé](https://exemple.fr)` et les liens HTTP(S). Le HTML brut est affiché comme texte pour éviter les injections.

## Licence et assistance

Licence du dépôt : **GPL-3.0-or-later**, voir `LICENSE`.
Auteur : [BeDOM – Solutions Web](https://bedom.fr/) · [Assistance](https://bedom.fr/support).
