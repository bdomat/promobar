# Sécurité

Signaler confidentiellement un problème via https://bedom.fr/support. Ne publier ni identifiants, ni clé API, ni données clients dans une issue publique.

La configuration est réservée aux employés connectés disposant du droit de configuration du module. Les écritures et notifications de test exigent un POST avec jeton CSRF. Les appels HTTPS conservent la validation des certificats et ne suivent pas les redirections. Une incompatibilité de certificat doit être corrigée sur l’hébergement, jamais contournée en désactivant TLS.

Les anciens moteurs PHP sont couverts à des fins de migration ; aucune promesse de sécurité n’est faite pour un moteur PHP ou un cœur PrestaShop arrivé en fin de maintenance.

Les tests CLI ne sont pas inclus dans les ZIP installables. Les secrets sont configurés dans la boutique et ne doivent jamais être ajoutés au dépôt.
