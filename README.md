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
- Les tests isolés simulent l’API Pushover et n’envoient aucune notification réelle. Les intégrations utilisent des boutiques jetables. Ils ne remplacent pas la recette du thème et des modules tiers d’une boutique cliente.
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

---

<!-- BANNER -->

![PromoBar banner](https://bedom.fr/img/promobar-banner.png)

# 🏷️ PromoBar — Announcement Banner for PrestaShop

### 🇬🇧 English | 🇫🇷 Français

[![Version](https://img.shields.io/badge/version-1.0.0-blue.svg)](#)
[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)
[![Made by BeDOM](https://img.shields.io/badge/Made%20by-BeDOM-ff69b4.svg)](https://bedom.fr/)

---

## 🇬🇧 English

### 🚀 Overview

**PromoBar** is a lightweight, open-source PrestaShop module that lets you display a **fully customizable announcement banner** on your shop.  
Perfect for promoting sales, special events, or important messages — without touching a single line of code.

---

### ✨ Features

- 🌍 **Multilingual support** — display messages in multiple languages
- 🕐 **Scheduled display** — set start and end dates automatically
- ⏳ **Countdown timer** — ideal for flash sales or limited offers
- 🎨 **Full customization** — colors, fonts, alignment, animations
- 🧱 **No external dependencies** — clean, secure, and self-contained
- ⚡ **Optimized for performance** — lightweight and theme-friendly
- 🧠 **Simple back-office interface** — configure everything visually

---

### ⚙️ Installation

1. Download `promobar.zip`
2. In your PrestaShop back-office:
   - Go to **Modules → Module Manager**
   - Click **Upload a module**
   - Import `promobar.zip`
3. Install and configure it under **Modules → PromoBar**

> 💡 No code changes required — just plug, configure, and enjoy.

---

### 🧰 Configuration

In the module settings:

- Write your **promo message**
- Add an optional **button link**
- Customize **colors, fonts, and animations**
- Define **start/end dates**
- Enable the **countdown timer** if needed

---

### 🧩 Compatibility

- **PrestaShop 1.7.x → 9.x**
- ✅ **Compatible with Creative Elements (Elementor for PrestaShop)**
- Works with all modern themes
- Tested on PHP 7.4 → 8.3

---

### 🗓️ Changelog

#### v1.0.0 — Initial release 🎉

- First stable version of PromoBar
- Multilingual support
- Built-in countdown timer
- Customizable colors & animations
- 100% back-office configuration
- Full compatibility with Creative Elements

---

### 👨‍💻 Author

**BeDOM – Solutions Web**  
💼 [Website](https://bedom.fr/)  
📞 [Support](https://bedom.fr/support)  
🔗 [LinkedIn](https://www.linkedin.com/company/bedom-web)  
📘 [Facebook](https://www.facebook.com/agencebedom)  
📸 [Instagram](https://www.instagram.com/bedom_web/)

---

### ⚖️ License

Released under the **MIT License** — free to use, modify, and share.  
Please keep author credits 🙌

---

### 💫 Vision

PromoBar aims to **empower merchants** to communicate easily and effectively.  
No dependencies, no complexity — just a **clean, fast, and efficient banner**.  
Upcoming features include:

- 🧩 Multi-banner management
- 📈 Click analytics
- ⚙️ Import/export settings
- 💬 Marketing integrations

---

## 🇫🇷 Français

### 🚀 Présentation

**PromoBar** est un module PrestaShop open-source et léger permettant d’afficher une **bannière d’annonce personnalisable** sur votre boutique.  
Idéal pour vos promotions, événements ou messages clés — sans aucune ligne de code.

---

### ✨ Fonctionnalités

- 🌍 **Multilingue** — messages et boutons traduisibles
- 🕐 **Programmation par dates** — affichage automatique selon période
- ⏳ **Compte à rebours intégré** — parfait pour les ventes flash
- 🎨 **Personnalisation complète** — couleurs, polices, alignements, animations
- 🧱 **Sans dépendances externes** — propre, sécurisé et autonome
- ⚡ **Optimisé pour les performances** — léger et compatible thème
- 🧠 **Interface intuitive** — tout se gère depuis le back-office

---

### ⚙️ Installation

1. Téléchargez le fichier `promobar.zip`
2. Dans votre back-office PrestaShop :
   - Allez dans **Modules → Gestionnaire de modules**
   - Cliquez sur **Téléverser un module**
   - Importez `promobar.zip`
3. Installez et configurez le module dans **Modules → PromoBar**

> 💡 Aucune modification de thème nécessaire — tout fonctionne directement.

---

### 🧰 Configuration

Depuis l’interface d’administration :

- Créez votre **message promotionnel**
- Ajoutez un **bouton cliquable**
- Personnalisez **les couleurs, polices et animations**
- Définissez les **dates d’affichage**
- Activez le **compte à rebours** si souhaité

---

### 🧩 Compatibilité

- **PrestaShop 1.7.x → 9.x**
- ✅ **Compatible avec Creative Elements (Elementor pour PrestaShop)**
- Compatible avec tous les thèmes modernes
- Testé sur PHP 7.4 à 8.3

---

### 🗓️ Journal des versions

#### v1.0.0 — Première version stable 🥳

- Version complète et fonctionnelle
- Support multilingue
- Compte à rebours dynamique
- Personnalisation avancée
- Configuration simple depuis le back-office
- Compatibilité complète avec Creative Elements

---

### 👨‍💻 Auteur

**BeDOM – Solutions Web**  
💼 [Site web](https://bedom.fr/)  
📞 [Support](https://bedom.fr/support)  
🔗 [LinkedIn](https://www.linkedin.com/company/bedom-web)  
📘 [Facebook](https://www.facebook.com/agencebedom)  
📸 [Instagram](https://www.instagram.com/bedom_web/)

---

### ⚖️ Licence

Distribué sous **licence MIT** — libre de modifier, partager et contribuer.  
Merci de conserver la mention d’auteur 🙌

---

### 💫 Vision

PromoBar est conçu pour **simplifier la communication des e-commerçants**.  
Pas de code, pas de dépendances, juste une **barre d’annonce propre et efficace**.  
La roadmap inclut :

- 🧩 Gestion multi-bannières
- 📈 Statistiques de clics
- ⚙️ Import/export de configuration
- 💬 Intégrations marketing avancées

---

> 🖤 _Created with passion by [BeDOM – Solutions Web](https://bedom.fr/). Built for merchants who want performance and impact._


## BD Central compatibility
This module sends a lightweight inventory ping (module name, versions, domain/base URL, instance id) to BeDOM BD Central at https://bedom.fr/module/bdcentral/ping (rate-limited).
