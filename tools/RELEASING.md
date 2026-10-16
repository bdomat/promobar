# Publier une version stable

Ce workflow ne publie rien lors de la fusion sur `main`. La publication est déclenchée uniquement par un tag stable `vMAJOR.MINOR.PATCH` présent sur un commit inclus dans `main`. Un lancement manuel doit également viser ce tag.

1. Préparer le code, les manifests de version et `CHANGELOG.md`, puis fusionner la version validée dans `main`.
2. Créer et pousser le tag correspondant exactement à la version déclarée du module.
3. Le workflow relance la matrice PHP et les intégrations PrestaShop existantes. Il refuse tout paquet si ces contrôles échouent, si le tag est candidat/prerelease, si la version diffère ou si le commit n'appartient pas à `main`.
4. Le paquet est construit avec `tools/build.py` et les exclusions de `.gitattributes`. Un ZIP installable au nom stable du module et son SHA-256 sont joints à la nouvelle release marquée Latest. Une release existante n'est ni écrasée ni modifiée.

Le jeton éphémère natif `GITHUB_TOKEN` est en lecture pour les tests et la construction. Seul le job final de publication reçoit `contents: write`, sans exécuter le code du dépôt. Aucun PAT ni secret supplémentaire n'est nécessaire. Cette permission de publication devra être acceptée lors de l'adoption du workflow.

Pour un dépôt privé, les assets restent privés : le site de vente doit utiliser son accès GitHub côté serveur et vérifier les droits de téléchargement du client avant de livrer le ZIP. Aucun jeton GitHub ne doit être envoyé au navigateur du client.
