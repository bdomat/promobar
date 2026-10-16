# Publier une version stable

Une fusion dans `main` lance les tests et publie automatiquement la version stable déclarée du module si elle n'a pas encore de release. Un tag `vMAJOR.MINOR.PATCH` ou un lancement manuel sur `main` peut aussi déclencher le workflow.

1. Préparer le code, les manifests de version et `CHANGELOG.md`, puis fusionner la version revue dans `main`.
2. Le workflow relance toute la matrice PHP et les intégrations PrestaShop existantes. Il refuse tout paquet si les tests échouent, si la version est candidate, si le tag diffère, ou si le commit n'est plus le HEAD exact de `main`.
3. Le paquet est construit avec `tools/build.py` et les exclusions de `.gitattributes`. Le job final crée le tag sans jamais déplacer un tag existant et crée une release brouillon avec le ZIP installable au nom stable du module et son SHA-256.
4. Les tailles et empreintes SHA-256 des assets GitHub sont vérifiées avant de publier cette release comme Latest. Le SHA du tag et de `main` est contrôlé de nouveau immédiatement avant publication.

Les publications sont sérialisées. Une release stable déjà publiée est laissée intacte ; il faut augmenter la version pour publier de nouveaux fichiers. Un brouillon existant ou un tag incohérent provoque un arrêt pour revue plutôt qu'un écrasement. En cas d'échec, inspecter le workflow et le brouillon avant de relancer.

Le jeton éphémère natif `GITHUB_TOKEN` est en lecture pour les tests et la construction. Seul le job final de publication reçoit `contents: write`, sans checkout ni exécution d'un script provenant du dépôt. Aucun PAT ni secret supplémentaire n'est nécessaire.

Pour un dépôt privé, les assets restent privés : le site de vente doit utiliser son accès GitHub côté serveur et vérifier les droits de téléchargement du client avant de livrer le ZIP. Aucun jeton GitHub ne doit être envoyé au navigateur du client.
