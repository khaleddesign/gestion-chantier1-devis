# 📋 Système de Devis et Factures

Module complet de gestion des devis et factures pour l'application de gestion de chantiers.

## 🚀 Installation rapide

### 1. Prérequis
```bash
# Laravel 11+ avec les modèles de base (User, Chantier)
# PHP 8.2+
# Base de données (MySQL/SQLite)
```

### 2. Installation automatique
```bash
# Installation complète en une commande
php artisan devis:install

# Ou installation manuelle étape par étape
composer require barryvdh/laravel-dompdf
php artisan migrate
php artisan storage:link
```

### 3. Configuration
```bash
# Copier le fichier de configuration
cp config/devis.php.example config/devis.php

# Configurer les informations de l'entreprise dans .env
COMPANY_NAME="Votre Entreprise"
COMPANY_ADDRESS="123 rue de la Construction, 75001 Paris"
COMPANY_PHONE="+33 1 23 45 67 89"
COMPANY_EMAIL="contact@entreprise.com"
```

### 4. Vérification
```bash
# Vérifier que tout fonctionne
php artisan devis:check
```

## 📊 Fonctionnalités

### ✅ Gestion des Devis
- **Création intuitive** avec interface de lignes dynamiques
- **Numérotation automatique** configurable (DEV-2024-001)
- **Workflow complet** : Brouillon → Envoyé → Accepté/Refusé
- **Signature électronique** avec horodatage et IP
- **Génération PDF** automatique avec template personnalisable
- **Validation publique** via lien sécurisé sans connexion
- **Expiration automatique** avec notifications
- **Conversion en facture** en un clic

### ✅ Gestion des Factures
- **Conversion automatique** depuis les devis acceptés
- **Suivi des paiements** partiels et complets
- **Gestion des échéances** avec alertes de retard
- **Relances automatiques** configurables (3 niveaux)
- **Modes de paiement multiples** (virement, chèque, CB, etc.)
- **Rapprochement bancaire** manuel
- **États détaillés** : Brouillon → Envoyée → Payée/En retard

### ✅ Système de Lignes
- **Interface intuitive** de saisie des lignes
- **Calculs automatiques** HT/TVA/TTC en temps réel
- **Gestion des remises** par ligne
- **Catégorisation** des produits/services
- **Suggestions intelligentes** basées sur l'historique
- **Unités multiples** (m², heure, forfait, etc.)
- **TVA configurable** par ligne

### ✅ Génération PDF
- **Templates professionnels** conformes
- **Informations entreprise** automatiques
- **Logos et personnalisation** 
- **Conditions générales** intégrées
- **Signatures électroniques** visibles
- **Optimisation pour impression**

### ✅ Notifications & Workflow
- **Notifications in-app** temps réel
- **Emails automatiques** (envoi, acceptation, relances)
- **Intégration avec le système existant** de notifications
- **Workflow par rôle** (Admin/Commercial/Client)
- **Permissions granulaires**

### ✅ Rapports & Statistiques
- **Chiffre d'affaires** par période/commercial
- **Taux de conversion** des devis
- **Factures impayées** avec ancienneté
- **Performance commerciale** individuelle
- **Exports Excel/CSV/PDF**

## 🏗️ Architecture

### Structure des données
```
Chantier (existant)
├── Devis (1→n)
│   ├── Lignes (1→n, polymorphe)
│   └── Facture (1→1, optionnel)
├── Factures (1→n)
│   ├── Lignes (1→n, polymorphe)
│   └── Paiements (1→n)
└── Notifications (1→n, existant)
```

### Modèles principaux
- **`Devis`** : Gestion complète des devis avec workflow
- **`Facture`** : Facturation avec suivi des paiements
- **`Ligne`** : Relation polymorphe pour devis ET factures
- **`Paiement`** : Encaissements avec validation

### Services
- **`PdfService`** : Génération de tous les PDF
- **`NotificationService`** : Gestion des alertes (existant)
- **`CalculService`** : Calculs financiers complexes

## 🔐 Sécurité & Permissions

### Système de rôles
```php
// Admin : Accès complet
Gate::define('admin-devis', fn($user) => $user->isAdmin());

// Commercial : Ses chantiers uniquement
Gate::define('manage-devis', fn($user, $chantier) => 
    $user->isCommercial() && $chantier->commercial_id === $user->id
);

// Client : Visualisation et validation uniquement
Gate::define('view-devis', fn($user, $chantier) => 
    $user->isClient() && $chantier->client_id === $user->id
);
```

### Policies détaillées
- **`DevisPolicy`** : 9 méthodes (view, create, update, delete, envoyer, accepter, etc.)
- **`FacturePolicy`** : 8 méthodes avec gestion des états
- **Validation des montants** et intégrité des données
- **Tokens sécurisés** pour les liens publics
- **Audit trail** des actions importantes

## 🎨 Interface Utilisateur

### Design System
- **Tailwind CSS** avec classes personnalisées
- **Composants réutilisables** (badges, boutons, cartes)
- **Interface responsive** adaptée mobile/tablet
- **Dark mode** supporté
- **Animations fluides** avec Alpine.js

### Pages principales
- **`/chantiers/{id}/devis`** : Liste des devis du chantier
- **`/chantiers/{id}/devis/create`** : Création avec interface dynamique
- **`/chantiers/{id}/devis/{id}`** : Détail et actions
- **`/chantiers/{id}/factures`** : Gestion des factures
- **`/admin/devis`** : Vue globale administrateur

### Composants JavaScript
- **Gestionnaire de lignes** avec calculs temps réel
- **Signature pad** pour acceptation électronique
- **Upload de fichiers** drag & drop
- **Recherche intelligente** avec autocomplétion

## 📱 API & Intégrations

### Endpoints API
```php
// Calculs en temps réel
POST /api/lignes/calculer
GET  /api/chantiers/{id}/financier

// Recherche et suggestions
GET  /api/produits/search?q={query}
GET  /api/commerciaux/{id}/stats

// Actions métier
POST /api/devis/{id}/envoyer
POST /api/factures/{id}/paiement
```

### Webhooks (optionnel)
```php
// Configuration dans config/devis.php
'webhooks' => [
    'devis_accepte' => 'https://crm.exemple.com/webhook/devis',
    'facture_payee' => 'https://compta.exemple.com/webhook/facture',
]
```

## 🔧 Commandes Artisan

### Installation et maintenance
```bash
# Installation initiale
php artisan devis:install [--force]

# Vérification du système
php artisan devis:check [--fix]

# Nettoyage et maintenance
php artisan devis:cleanup [--dry-run] [--days=90]

# Migration des données
php artisan devis:migrate-from-old-system

# Génération de rapports
php artisan devis:rapport --type=ca --periode=mois
```

### Utilitaires
```bash
# Recalculer tous les totaux
php artisan devis:recalcul-totaux

# Marquer les devis expirés
php artisan devis:expirer-anciens

# Envoyer les relances
php artisan factures:envoyer-relances

# Export massif
php artisan devis:export --format=excel --annee=2024
```

## 📊 Configuration avancée

### Personnalisation des PDF
```php
// config/devis.php
'pdf' => [
    'template' => 'custom.devis-template',
    'logo_path' => 'images/logo-entreprise.png',
    'couleurs' => [
        'primaire' => '#1E3A8A',
        'secondaire' => '#6B7280',
    ],
    'mentions_legales' => 'Votre texte légal...',
]
```

### Workflow personnalisé
```php
'workflow' => [
    'validation_manager' => true,  // Approbation manager > 10k€
    'signature_obligatoire' => false,
    'conversion_auto_facture' => true,
    'relances_auto' => [8, 15, 30], // jours après échéance
]
```

### Intégration comptabilité
```php
'comptabilite' => [
    'export_auto' => true,
    'format' => 'csv', // ou 'xml', 'api'
    'mapping_comptes' => [
        'ventes_services' => '706000',
        'ventes_marchandises' => '707000',
        'tva_collectee' => '445571',
    ]
]
```

## 🧪 Tests

### Tests fonctionnels
```bash
# Tests complets
php artisan test --testsuite=DevisFactures

# Tests spécifiques
php artisan test tests/Feature/DevisWorkflowTest.php
php artisan test tests/Feature/FacturePaiementTest.php
php artisan test tests/Feature/PdfGenerationTest.php
```

### Tests d'intégration
```bash
# Test du workflow complet
php artisan test tests/Integration/DevisToFactureTest.php

# Test des permissions
php artisan test tests/Security/DevisPolicyTest.php
```

## 🚀 Déploiement

### Checklist de déploiement
- [ ] Sauvegarder la base de données existante
- [ ] Exécuter `php artisan migrate` 
- [ ] Vérifier `php artisan devis:check`
- [ ] Configurer les variables d'environnement
- [ ] Tester la génération PDF
- [ ] Configurer les tâches cron
- [ ] Former les utilisateurs

### Tâches cron recommandées
```bash
# Nettoyage quotidien
0 2 * * * cd /path/to/app && php artisan devis:cleanup --force

# Relances hebdomadaires  
0 9 * * 1 cd /path/to/app && php artisan factures:envoyer-relances

# Sauvegarde mensuelle
0 1 1 * * cd /path/to/app && php artisan backup:run
```

## 🐛 Dépannage

### Problèmes courants

**PDF ne se génère pas**
```bash
# Vérifier DomPDF
composer show barryvdh/laravel-dompdf

# Vérifier les permissions
chmod -R 755 storage/app/public/pdf
```

**Calculs incorrects**
```bash
# Recalculer tous les totaux
php artisan devis:recalcul-totaux --force
```

**Problèmes de permissions**
```bash
# Vérifier les policies
php artisan route:list --name=devis
php artisan devis:check --fix
```

**Notifications non envoyées**
```bash
# Vérifier la configuration mail
php artisan config:cache
php artisan queue:work --queue=notifications
```

### Debug avancé
```php
// Activer les logs détaillés dans config/devis.php
'debug' => [
    'log_queries' => true,
    'log_pdf_generation' => true,
    'cache_pdf_debug' => true,
]
```

## 📈 Optimisations

### Performance
- **Cache Redis** pour les calculs complexes
- **Index base de données** sur les colonnes fréquemment utilisées
- **Lazy loading** des relations
- **Pagination** intelligente des grandes listes

### Monitoring
```bash
# Surveiller les performances
php artisan devis:stats --performance
php artisan devis:stats --usage --periode=mois
```

## 🤝 Contribution

### Structure du code
```
app/
├── Models/
│   ├── Devis.php
│   ├── Facture.php
│   ├── Ligne.php
│   └── Paiement.php
├── Http/Controllers/
│   ├── DevisController.php
│   └── FactureController.php
├── Policies/
├── Services/
│   └── PdfService.php
└── Console/Commands/
```

### Standards de développement
- **PSR-12** pour le style de code
- **Tests unitaires** obligatoires pour les nouvelles fonctionnalités
- **Documentation** des méthodes publiques
- **Validation** stricte des données entrantes

## 📞 Support

### Ressources
- **Documentation** : Ce fichier README
- **Issues** : GitHub Issues pour les bugs
- **Discussions** : GitHub Discussions pour les questions

### Contact
- **Email** : support@exemple.com
- **Documentation** : https://docs.exemple.com/devis-factures

---

## 🎯 Roadmap

### Version 2.0 (Q3 2024)
- [ ] **Multi-devise** avec taux de change automatiques
- [ ] **Workflow d'approbation** pour gros montants
- [ ] **Templates PDF** multiples selon le type de client
- [ ] **API REST** complète pour intégrations tierces
- [ ] **Tableau de bord** analytics avancé

### Version 2.1 (Q4 2024)
- [ ] **Module de facturation récurrente** (abonnements)
- [ ] **Gestion des avoirs** et remboursements
- [ ] **Intégration bancaire** automatique (API)
- [ ] **Mobile app** pour validation devis en déplacement
- [ ] **Signature biométrique** sur tablet

### Améliorations continues
- Performance et optimisations
- Nouveaux formats d'export
- Intégrations avec d'autres modules
- Retours utilisateurs et UX

---

*Dernière mise à jour : Juin 2024 - Version 1.0*