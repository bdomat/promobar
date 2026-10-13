# Historique

## 1.0.4 — 2026-10-13

- Correction du double décodage HTML côté navigateur ; mini-markup parsé une seule fois.
- Contrôle des permissions, requêtes POST et jeton CSRF dédié pour la configuration.
- Revalidation des URL, couleurs et polices lors du rendu, y compris pour les anciens réglages.
- Syntaxe compatible avec les anciens PHP, fermeture cURL adaptée à PHP 8.5, HTTPS avec validation TLS et sans redirection.
- Cookies SameSite=Lax et Secure sur HTTPS ; correctif du compteur expiré.
- Script de mise à jour non destructif et tests automatisés.

Base : PromoBar 1.0.3, dépôt public bdomat/promobar, commit 14180b9. Les adaptations syntaxiques de la variante 1.0.3-ps1731 sont reprises sans conserver ce suffixe non standard.
