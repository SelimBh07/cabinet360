# ⚖️ Cabinet360 - Système de Gestion pour Cabinet d'Avocat

![Cabinet360](https://img.shields.io/badge/Version-1.0-gold?style=for-the-badge)
![PHP](https://img.shields.io/badge/PHP-8.0+-blue?style=for-the-badge&logo=php)
![MySQL](https://img.shields.io/badge/MySQL-8.0+-orange?style=for-the-badge&logo=mysql)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-purple?style=for-the-badge&logo=bootstrap)

**Cabinet360** est un système de gestion professionnel conçu spécialement pour les cabinets d'avocats au Maroc. Il permet de gérer les clients, les dossiers juridiques, les rendez-vous et les paiements de manière efficace et sécurisée.

---

## ✨ Fonctionnalités

### 🔐 Authentification
- Système de connexion sécurisé avec sessions PHP
- Hachage des mots de passe avec bcrypt
- Timeout automatique après 30 minutes d'inactivité

### 📊 Tableau de Bord
- Vue d'ensemble avec statistiques en temps réel
- Graphiques des revenus mensuels
- Liste des clients récents
- Rendez-vous à venir
- Dossiers actifs

### 👥 Gestion des Clients
- Ajouter, modifier, supprimer des clients
- Informations complètes (nom, CIN, téléphone, email, adresse)
- Recherche avancée par nom, CIN, téléphone ou email
- Pagination et filtrage
- Vue détaillée avec historique des dossiers

### 📁 Gestion des Dossiers
- Création et suivi des dossiers juridiques
- Types de dossiers : civil, pénal, commercial, administratif, familial
- Statuts : ouvert, en cours, clos, suspendu
- Upload de documents (PDF, DOCX) jusqu'à 5 MB
- Assignation d'avocat
- Filtrage par type et statut

### 📅 Gestion des Rendez-vous
- Calendrier interactif avec FullCalendar
- Vue calendrier et vue liste
- Création rapide de rendez-vous
- Notifications visuelles par statut
- Historique complet

### 💰 Gestion des Paiements
- Enregistrement des paiements
- Méthodes : espèces, chèque, virement, carte bancaire
- Statuts : payé, impayé, partiel
- Statistiques des revenus
- **Génération automatique de reçus PDF**
- Filtrage et recherche

### 🔍 Recherche Globale
- Recherche instantanée dans toute l'application
- Raccourci clavier (Ctrl+K)
- Résultats groupés par catégorie

---

## 🛠️ Technologies Utilisées

| Technologie | Version | Utilisation |
|-------------|---------|-------------|
| **PHP** | 7.4+ | Backend / Logique métier |
| **MySQL** | 8.0+ | Base de données |
| **Bootstrap 5** | 5.3.0 | Interface utilisateur responsive |
| **jQuery** | 3.7.0 | Interactions AJAX |
| **Font Awesome** | 6.4.0 | Icônes |
| **Chart.js** | 4.3.0 | Graphiques statistiques |
| **FullCalendar** | 6.1.8 | Calendrier des rendez-vous |

---

## 📋 Prérequis

- **XAMPP** ou **Laragon** (recommandé pour Windows)
- **PHP 7.4** ou supérieur
- **MySQL 8.0** ou supérieur
- **Apache** avec mod_rewrite activé
- Navigateur web moderne (Chrome, Firefox, Edge)

---

## 🚀 Installation

### Production Deployment

#### Prerequisites
- GitHub account
- Render.com account
- PlanetScale account

#### 1. Database Setup (PlanetScale)
1. Create a PlanetScale account at https://planetscale.com
2. Create a new database:
   ```bash
   pscale database create cabinet360_saas
   ```
3. Import the schema:
   ```bash
   pscale shell cabinet360_saas main < database.sql
   ```
4. Get your database credentials from PlanetScale dashboard

#### 2. Render Deployment
1. Fork/clone this repository
2. Connect your GitHub repo to Render
3. Create a new Web Service
4. Select "Docker" environment
5. Configure environment variables:
   - Copy variables from `.env.example`
   - Set PlanetScale database credentials
   - Set `APP_ENV=production`
6. Deploy the service

#### 3. Post-Deployment
1. Access your application at the Render URL
2. Update `APP_URL` in Render dashboard with the actual URL
3. Test login functionality
4. Verify file uploads
5. Check database connectivity

### Local Development

### Étape 1 : Télécharger et Extraire

```bash
# Si vous avez téléchargé le projet
cd C:\xampp\htdocs\
# Ou pour Laragon
cd C:\laragon\www\
```

Placez le dossier **Cabinet360** dans le répertoire approprié.

### Étape 2 : Créer la Base de Données

1. Démarrez **XAMPP** ou **Laragon**
2. Ouvrez **phpMyAdmin** : `http://localhost/phpmyadmin`
3. Créez une nouvelle base de données nommée : `lexmanage`
4. Importez le fichier SQL :
   - Cliquez sur la base `lexmanage`
   - Allez dans l'onglet **Importer**
   - Sélectionnez le fichier `database.sql`
   - Cliquez sur **Exécuter**

**Alternative (via ligne de commande) :**

```bash
mysql -u root -p
CREATE DATABASE lexmanage CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE lexmanage;
SOURCE C:/xampp/htdocs/Cabinet360/database.sql;
```

### Étape 3 : Configuration

Ouvrez le fichier `config/config.php` et vérifiez les paramètres :

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');  // Modifiez si vous avez un mot de passe
define('DB_NAME', 'lexmanage');
```

### Étape 4 : Créer le Dossier Uploads

Créez un dossier `uploads` à la racine du projet pour stocker les documents :

```bash
mkdir uploads
chmod 755 uploads  # Sur Linux/Mac
```

Sous Windows, assurez-vous que le dossier a les permissions d'écriture.

### Étape 5 : Accéder à l'Application

Ouvrez votre navigateur et accédez à :

```
http://localhost/Cabinet360/login.php
```

### Étape 6 : Connexion

Utilisez les identifiants par défaut :

| Champ | Valeur |
|-------|--------|
| **Nom d'utilisateur** | `admin` |
| **Mot de passe** | `admin123` |

---

## 📁 Structure du Projet

```
Cabinet360/
├── config/
│   ├── config.php          # Configuration de la base de données
│   └── auth.php            # Contrôle d'authentification
├── includes/
│   ├── header.php          # En-tête commun
│   ├── sidebar.php         # Menu latéral
│   └── footer.php          # Pied de page
├── pages/
│   ├── clients.php         # Gestion des clients
│   ├── cases.php           # Gestion des dossiers
│   ├── appointments.php    # Gestion des rendez-vous
│   └── payments.php        # Gestion des paiements
├── actions/
│   ├── client_actions.php  # CRUD clients
│   ├── case_actions.php    # CRUD dossiers
│   ├── appointment_actions.php  # CRUD rendez-vous
│   ├── payment_actions.php # CRUD paiements
│   ├── generate_receipt.php # Génération de reçus PDF
│   └── global_search.php   # Recherche globale
├── assets/
│   ├── css/
│   │   └── style.css       # Styles personnalisés
│   └── js/
│       ├── script.js       # JavaScript principal
│       ├── clients.js      # JS pour clients
│       ├── cases.js        # JS pour dossiers
│       ├── appointments.js # JS pour rendez-vous
│       └── payments.js     # JS pour paiements
├── uploads/                # Documents uploadés
├── database.sql            # Script de création de la base
├── login.php               # Page de connexion
├── logout.php              # Déconnexion
├── index.php               # Tableau de bord
└── README.md               # Ce fichier
```

---

## 🎨 Thème et Design

Cabinet360 utilise un thème professionnel avec les couleurs suivantes :

| Couleur | Code | Usage |
|---------|------|-------|
| **Noir primaire** | `#111` | Fond principal |
| **Noir secondaire** | `#1a1a1a` | Cartes et composants |
| **Or** | `#D4AF37` | Accents, titres, boutons |
| **Blanc** | `#ffffff` | Texte principal |
| **Gris** | `#999` | Texte secondaire |

---

## 🔒 Sécurité

Cabinet360 implémente plusieurs mesures de sécurité :

- ✅ **Hachage bcrypt** pour les mots de passe
- ✅ **Protection contre les injections SQL** avec PDO et requêtes préparées
- ✅ **Protection XSS** avec htmlspecialchars()
- ✅ **Sessions sécurisées** avec httponly et timeout
- ✅ **Validation des uploads** (type et taille de fichier)
- ✅ **Sanitization** de toutes les entrées utilisateur

---

## 📱 Responsive Design

L'application est entièrement responsive et fonctionne sur :

- 💻 Desktop (1920x1080 et plus)
- 💻 Laptop (1366x768)
- 📱 Tablette (768x1024)
- 📱 Mobile (375x667 et plus)

---

## 🔧 Configuration Avancée

### Changer l'URL de l'Application

Si vous installez l'application dans un autre dossier, modifiez `config/config.php` :

```php
define('APP_URL', 'http://localhost/VotreDossier');
```

### Augmenter la Taille Maximale des Fichiers

Dans `config/config.php` :

```php
define('MAX_FILE_SIZE', 10485760); // 10 MB
```

N'oubliez pas de modifier également `php.ini` :

```ini
upload_max_filesize = 10M
post_max_size = 10M
```

### Modifier le Timeout de Session

Dans `config/config.php` :

```php
define('SESSION_TIMEOUT', 3600); // 1 heure
```

---

## 📊 Fonctionnalités de la Base de Données

### Tables Principales

| Table | Description |
|-------|-------------|
| `users` | Comptes utilisateurs |
| `clients` | Informations des clients |
| `cases` | Dossiers juridiques |
| `appointments` | Rendez-vous |
| `payments` | Paiements et factures |

### Relations

- `cases.client_id` → `clients.id` (ON DELETE CASCADE)
- `appointments.client_id` → `clients.id` (ON DELETE CASCADE)
- `payments.client_id` → `clients.id` (ON DELETE CASCADE)
- `payments.case_id` → `cases.id` (ON DELETE SET NULL)

---

## 🐛 Dépannage

### Erreur de connexion à la base de données

**Solution :** Vérifiez les identifiants dans `config/config.php`

### Les fichiers ne s'uploadent pas

**Solution :** Vérifiez les permissions du dossier `uploads/` et les paramètres PHP

### Erreur 404 sur les pages

**Solution :** Vérifiez que `APP_URL` dans `config.php` correspond à votre configuration

### Session expire trop rapidement

**Solution :** Augmentez `SESSION_TIMEOUT` dans `config/config.php`

---

## 📈 Améliorations Futures (Roadmap)

- [ ] 🔔 Notifications par email
- [ ] 📄 Export PDF des dossiers complets
- [ ] 📊 Rapports statistiques avancés
- [ ] 🔐 Authentification à deux facteurs
- [ ] 👥 Gestion multi-utilisateurs avec rôles
- [ ] 📅 Synchronisation avec Google Calendar
- [ ] 💬 Système de messagerie interne
- [ ] 📱 Application mobile (React Native)

---

## 👨‍💻 Auteur

**Cabinet360** a été développé pour répondre aux besoins spécifiques des cabinets d'avocats marocains.

---

## 📄 Licence

© 2025 Cabinet360 - Tous droits réservés

---

## 🤝 Support

Pour toute question ou problème :

1. Consultez la section **Dépannage** ci-dessus
2. Vérifiez la configuration de votre environnement
3. Assurez-vous que toutes les dépendances sont installées

---

## 🎯 Démo

Pour tester rapidement l'application après installation :

1. Connectez-vous avec `admin` / `admin123`
2. Explorez le tableau de bord
3. Ajoutez quelques clients de test
4. Créez des dossiers associés
5. Planifiez des rendez-vous
6. Enregistrez des paiements et générez des reçus

---

## ⚡ Performances

Cabinet360 est optimisé pour :

- Temps de chargement < 2 secondes
- Requêtes SQL indexées
- Pagination automatique
- Chargement AJAX pour les interactions
- Cache navigateur optimisé

---

## 🌐 Navigateurs Supportés

| Navigateur | Version Minimale |
|------------|------------------|
| Chrome | 90+ |
| Firefox | 88+ |
| Safari | 14+ |
| Edge | 90+ |

---

## 📞 Contact

Pour toute question commerciale ou technique, veuillez nous contacter.

---

**Merci d'utiliser Cabinet360 ! ⚖️**

*Rendez votre cabinet plus efficace et professionnel.*

