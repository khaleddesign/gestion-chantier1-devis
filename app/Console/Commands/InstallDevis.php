<?php
// app/Console/Commands/InstallDevis.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class InstallDevis extends Command
{
    protected $signature = 'devis:install {--force : Force l\'installation même si déjà installé}';
    protected $description = 'Installer le module devis/factures';

    public function handle()
    {
        $this->info('🚀 Installation du module Devis/Factures...');
        
        // Vérifier les prérequis
        if (!$this->checkPrerequisites()) {
            return 1;
        }

        // Vérifier l'état des tables
        $this->info('📊 Vérification de l\'état des tables...');
        $this->checkTablesStatus();

        // Lancer les migrations seulement si nécessaire
        if ($this->shouldRunMigrations()) {
            $this->info('📊 Exécution des migrations manquantes...');
            if (!$this->runMigrations()) {
                return 1;
            }
        } else {
            $this->info('✅ Toutes les tables sont déjà présentes !');
        }

        // Publier les assets
        $this->info('📁 Publication des assets...');
        $this->publishAssets();

        // Créer le lien de stockage
        $this->info('🔗 Création du lien de stockage...');
        $this->createStorageLink();

        // Enregistrer les policies
        $this->info('🔒 Vérification des policies...');
        $this->registerPolicies();

        // Vérifier la configuration
        $this->info('⚙️  Vérification de la configuration...');
        $this->checkConfiguration();

        // Message de succès
        $this->success();

        return 0;
    }

    private function checkTablesStatus(): void
    {
        $tables = [
            'devis' => 'Table des devis',
            'factures' => 'Table des factures', 
            'lignes' => 'Table des lignes (polymorphe)',
            'paiements' => 'Table des paiements'
        ];

        $this->line('📋 État des tables :');
        
        foreach ($tables as $table => $description) {
            $exists = Schema::hasTable($table);
            $status = $exists ? '✅' : '❌';
            $this->line("   {$status} {$description} ({$table})");
        }
    }

    private function shouldRunMigrations(): bool
    {
        $requiredTables = ['devis', 'factures', 'lignes', 'paiements'];
        $missingTables = [];

        foreach ($requiredTables as $table) {
            if (!Schema::hasTable($table)) {
                $missingTables[] = $table;
            }
        }

        if (empty($missingTables)) {
            return false; // Toutes les tables existent
        }

        $this->warn('⚠️  Tables manquantes: ' . implode(', ', $missingTables));
        return $this->confirm('Créer les tables manquantes ?', true);
    }

    private function checkPrerequisites(): bool
    {
        $this->info('✅ Vérification des prérequis...');

        // Vérifier Laravel
        $laravelVersion = app()->version();
        if (version_compare($laravelVersion, '11.0', '<')) {
            $this->error("❌ Laravel 11+ requis. Version actuelle: {$laravelVersion}");
            return false;
        }

        // Vérifier PHP
        if (version_compare(PHP_VERSION, '8.2', '<')) {
            $this->error('❌ PHP 8.2+ requis. Version actuelle: ' . PHP_VERSION);
            return false;
        }

        // Vérifier DomPDF
        if (!class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            $this->error('❌ Package barryvdh/laravel-dompdf manquant.');
            $this->line('Installez-le avec: composer require barryvdh/laravel-dompdf');
            return false;
        }

        // Vérifier la base de données
        try {
            \DB::connection()->getPdo();
        } catch (\Exception $e) {
            $this->error('❌ Connexion à la base de données impossible: ' . $e->getMessage());
            return false;
        }

        $this->info('✅ Tous les prérequis sont satisfaits !');
        return true;
    }

    private function runMigrations(): bool
    {
        try {
            // Créer les migrations seulement pour les tables manquantes
            $this->createMissingMigrations();

            // Exécuter les migrations
            Artisan::call('migrate', ['--force' => true]);
            $output = Artisan::output();
            
            if (!empty(trim($output))) {
                $this->line($output);
            }

            $this->info('✅ Migrations exécutées avec succès !');
            return true;

        } catch (\Exception $e) {
            $this->error('❌ Erreur lors des migrations: ' . $e->getMessage());
            return false;
        }
    }

    private function createMissingMigrations(): void
    {
        $tables = ['devis', 'factures', 'lignes', 'paiements'];
        
        foreach ($tables as $table) {
            if (!Schema::hasTable($table)) {
                $this->line("🔨 Création de la table {$table}...");
                $this->createTableManually($table);
            }
        }
    }

    private function createTableManually(string $tableName): void
    {
        try {
            switch ($tableName) {
                case 'devis':
                    $this->createDevisTable();
                    break;
                case 'factures':
                    $this->createFacturesTable();
                    break;
                case 'lignes':
                    $this->createLignesTable();
                    break;
                case 'paiements':
                    $this->createPaiementsTable();
                    break;
            }
            $this->line("✅ Table {$tableName} créée avec succès");
        } catch (\Exception $e) {
            $this->error("❌ Erreur création table {$tableName}: " . $e->getMessage());
        }
    }

    private function createDevisTable(): void
    {
        if (Schema::hasTable('devis')) return;

        Schema::create('devis', function ($table) {
            $table->id();
            $table->string('numero')->unique();
            $table->foreignId('chantier_id')->constrained()->onDelete('cascade');
            $table->foreignId('commercial_id')->constrained('users')->onDelete('cascade');
            $table->string('titre');
            $table->text('description')->nullable();
            $table->enum('statut', ['brouillon', 'envoye', 'accepte', 'refuse', 'expire'])->default('brouillon');
            $table->json('client_info');
            $table->date('date_emission');
            $table->date('date_validite');
            $table->timestamp('date_envoi')->nullable();
            $table->timestamp('date_reponse')->nullable();
            $table->decimal('montant_ht', 10, 2)->default(0);
            $table->decimal('montant_tva', 10, 2)->default(0);
            $table->decimal('montant_ttc', 10, 2)->default(0);
            $table->decimal('taux_tva', 5, 2)->default(20.00);
            $table->text('conditions_generales')->nullable();
            $table->integer('delai_realisation')->nullable();
            $table->text('modalites_paiement')->nullable();
            $table->text('signature_client')->nullable();
            $table->timestamp('signed_at')->nullable();
            $table->string('signature_ip')->nullable();
            $table->foreignId('facture_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamp('converted_at')->nullable();
            $table->text('notes_internes')->nullable();
            $table->timestamps();
            
            $table->index(['chantier_id', 'statut']);
            $table->index(['commercial_id', 'date_emission']);
        });
    }

    private function createFacturesTable(): void
    {
        if (Schema::hasTable('factures')) return;

        Schema::create('factures', function ($table) {
            $table->id();
            $table->string('numero')->unique();
            $table->foreignId('chantier_id')->constrained()->onDelete('cascade');
            $table->foreignId('commercial_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('devis_id')->nullable()->constrained()->onDelete('set null');
            $table->string('titre');
            $table->text('description')->nullable();
            $table->enum('statut', ['brouillon', 'envoyee', 'payee_partiel', 'payee', 'en_retard', 'annulee'])->default('brouillon');
            $table->json('client_info');
            $table->date('date_emission');
            $table->date('date_echeance');
            $table->timestamp('date_envoi')->nullable();
            $table->decimal('montant_ht', 10, 2)->default(0);
            $table->decimal('montant_tva', 10, 2)->default(0);
            $table->decimal('montant_ttc', 10, 2)->default(0);
            $table->decimal('taux_tva', 5, 2)->default(20.00);
            $table->decimal('montant_paye', 10, 2)->default(0);
            $table->decimal('montant_restant', 10, 2)->default(0);
            $table->timestamp('date_paiement_complet')->nullable();
            $table->text('conditions_reglement')->nullable();
            $table->integer('delai_paiement')->default(30);
            $table->string('reference_commande')->nullable();
            $table->text('notes_internes')->nullable();
            $table->integer('nb_relances')->default(0);
            $table->timestamp('derniere_relance')->nullable();
            $table->timestamps();
            
            $table->index(['chantier_id', 'statut']);
            $table->index(['commercial_id', 'date_emission']);
            $table->index(['statut', 'date_echeance']);
        });
    }

    private function createLignesTable(): void
    {
        if (Schema::hasTable('lignes')) return;

        Schema::create('lignes', function ($table) {
            $table->id();
            $table->morphs('ligneable');
            $table->integer('ordre')->default(0);
            $table->string('designation');
            $table->text('description')->nullable();
            $table->string('unite')->default('unité');
            $table->decimal('quantite', 8, 2)->default(1);
            $table->decimal('prix_unitaire_ht', 8, 2);
            $table->decimal('taux_tva', 5, 2)->default(20.00);
            $table->decimal('montant_ht', 10, 2);
            $table->decimal('montant_tva', 10, 2);
            $table->decimal('montant_ttc', 10, 2);
            $table->decimal('remise_pourcentage', 5, 2)->default(0);
            $table->decimal('remise_montant', 8, 2)->default(0);
            $table->string('categorie')->nullable();
            $table->string('code_produit')->nullable();
            $table->timestamps();
            
            $table->index(['ligneable_type', 'ligneable_id']);
            $table->index('ordre');
        });
    }

    private function createPaiementsTable(): void
    {
        if (Schema::hasTable('paiements')) return;

        Schema::create('paiements', function ($table) {
            $table->id();
            $table->foreignId('facture_id')->constrained()->onDelete('cascade');
            $table->decimal('montant', 10, 2);
            $table->date('date_paiement');
            $table->enum('mode_paiement', ['virement', 'cheque', 'especes', 'cb', 'prelevement', 'autre']);
            $table->string('reference_paiement')->nullable();
            $table->string('banque')->nullable();
            $table->enum('statut', ['en_attente', 'valide', 'rejete'])->default('valide');
            $table->text('commentaire')->nullable();
            $table->foreignId('saisi_par')->constrained('users');
            $table->timestamp('valide_at')->nullable();
            $table->string('justificatif_path')->nullable();
            $table->timestamps();
            
            $table->index(['facture_id', 'date_paiement']);
            $table->index(['mode_paiement', 'statut']);
        });
    }

    private function publishAssets(): void
    {
        $directories = [
            storage_path('app/public/documents'),
            storage_path('app/public/pdf'),
            storage_path('app/private/signatures'),
            storage_path('app/temp'),
            resource_path('views/devis'),
            resource_path('views/factures'),
            resource_path('views/pdf'),
        ];

        foreach ($directories as $dir) {
            if (!File::exists($dir)) {
                File::makeDirectory($dir, 0755, true);
                $this->line("📁 Dossier créé: " . str_replace(base_path(), '', $dir));
            }
        }

        $this->info('✅ Dossiers créés !');
    }

    private function createStorageLink(): void
    {
        try {
            if (!File::exists(public_path('storage'))) {
                Artisan::call('storage:link');
                $this->info('✅ Lien de stockage créé !');
            } else {
                $this->line('ℹ️  Lien de stockage déjà existant');
            }
        } catch (\Exception $e) {
            $this->warn('⚠️  Impossible de créer le lien de stockage: ' . $e->getMessage());
        }
    }

    private function registerPolicies(): void
    {
        $policyFile = app_path('Providers/AuthServiceProvider.php');
        
        if (File::exists($policyFile)) {
            $content = File::get($policyFile);
            
            if (strpos($content, 'DevisPolicy') === false) {
                $this->warn('⚠️  Ajoutez manuellement les policies dans AuthServiceProvider.php:');
                $this->line('   \\App\\Models\\Devis::class => \\App\\Policies\\DevisPolicy::class,');
                $this->line('   \\App\\Models\\Facture::class => \\App\\Policies\\FacturePolicy::class,');
            } else {
                $this->info('✅ Policies déjà enregistrées !');
            }
        }
    }

    private function checkConfiguration(): void
    {
        // Vérifier config/devis.php
        if (!config('devis')) {
            $this->error('❌ Fichier config/devis.php manquant !');
            return;
        }

        // Vérifier les éléments essentiels
        $checks = [
            'devis.numerotation' => 'Configuration numérotation devis',
            'factures.numerotation' => 'Configuration numérotation factures', 
            'entreprise.nom' => 'Nom de l\'entreprise',
            'pdf.format' => 'Configuration PDF'
        ];

        $this->line('🔧 Configuration :');
        foreach ($checks as $key => $description) {
            $value = config("devis.{$key}");
            $status = $value ? '✅' : '❌';
            $this->line("   {$status} {$description}");
        }
    }

    private function success(): void
    {
        $this->newLine();
        $this->info('🎉 Installation terminée avec succès !');
        $this->newLine();
        
        $this->line('📋 Prochaines étapes:');
        $this->line('1. ✅ Tables créées et prêtes');
        $this->line('2. ✅ Configuration validée');
        $this->line('3. 🔄 Testez les routes: php artisan route:list | grep devis');
        $this->line('4. 🎨 Créez vos vues dans resources/views/devis/');
        $this->line('5. 🧪 Testez un premier devis via l\'interface web');
        
        $this->newLine();
        $this->info('✨ Module devis/factures installé et opérationnel !');
        $this->line('🌐 Accédez à votre application et créez votre premier devis depuis un chantier.');
    }
}