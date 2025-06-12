<?php
// app/Console/Commands/FixDevis.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class FixDevis extends Command
{
    protected $signature = 'devis:fix {--reset : Supprimer et recréer toutes les tables}';
    protected $description = 'Réparer les tables du module devis/factures';

    public function handle()
    {
        $this->info('🔧 Réparation du module Devis/Factures...');
        
        if ($this->option('reset')) {
            return $this->resetAndInstall();
        }

        return $this->smartFix();
    }

    private function resetAndInstall(): int
    {
        $this->warn('⚠️  ATTENTION: Cette opération va supprimer TOUTES les données des devis/factures !');
        
        if (!$this->confirm('Êtes-vous sûr de vouloir continuer ?')) {
            $this->info('Opération annulée.');
            return 0;
        }

        $this->info('🗑️  Suppression des tables existantes...');
        $this->dropTablesInOrder();

        $this->info('🔨 Création des tables...');
        $this->createAllTables();

        $this->info('✅ Réinitialisation terminée !');
        return 0;
    }

    private function smartFix(): int
    {
        $this->info('🔍 Analyse des tables existantes...');
        
        $tables = [
            'paiements' => ['table' => 'paiements', 'depends' => ['factures']],
            'lignes' => ['table' => 'lignes', 'depends' => []],
            'factures' => ['table' => 'factures', 'depends' => ['chantiers', 'users', 'devis']],
            'devis' => ['table' => 'devis', 'depends' => ['chantiers', 'users']],
        ];

        foreach ($tables as $name => $config) {
            $this->fixTable($name, $config);
        }

        $this->info('✅ Réparation terminée !');
        $this->checkFinalState();
        return 0;
    }

    private function fixTable(string $name, array $config): void
    {
        $tableName = $config['table'];
        
        if (!Schema::hasTable($tableName)) {
            $this->info("🔨 Création de la table {$tableName}...");
            $this->createTable($tableName);
            return;
        }

        // Vérifier la structure
        $this->info("🔍 Vérification de la table {$tableName}...");
        
        if (!$this->checkTableStructure($tableName)) {
            $this->warn("⚠️  Structure de la table {$tableName} incorrecte");
            
            if ($this->confirm("Recréer la table {$tableName} ?")) {
                $this->info("🔄 Recréation de la table {$tableName}...");
                
                // Sauvegarder les données si possible
                $backup = $this->backupTableData($tableName);
                
                // Supprimer et recréer
                Schema::dropIfExists($tableName);
                $this->createTable($tableName);
                
                // Restaurer les données
                if (!empty($backup)) {
                    $this->restoreTableData($tableName, $backup);
                }
            }
        } else {
            $this->line("✅ Table {$tableName} OK");
        }
    }

    private function checkTableStructure(string $tableName): bool
    {
        try {
            $columns = Schema::getColumnListing($tableName);
            
            $requiredColumns = match($tableName) {
                'devis' => ['id', 'numero', 'chantier_id', 'commercial_id', 'titre', 'statut'],
                'factures' => ['id', 'numero', 'chantier_id', 'commercial_id', 'titre', 'statut'],
                'lignes' => ['id', 'ligneable_type', 'ligneable_id', 'designation', 'quantite'],
                'paiements' => ['id', 'facture_id', 'montant', 'date_paiement', 'mode_paiement'],
                default => []
            };

            foreach ($requiredColumns as $column) {
                if (!in_array($column, $columns)) {
                    return false;
                }
            }

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function backupTableData(string $tableName): array
    {
        try {
            return DB::table($tableName)->get()->toArray();
        } catch (\Exception $e) {
            $this->warn("⚠️  Impossible de sauvegarder {$tableName}: " . $e->getMessage());
            return [];
        }
    }

    private function restoreTableData(string $tableName, array $data): void
    {
        try {
            if (!empty($data)) {
                DB::table($tableName)->insert($data);
                $this->info("✅ Données restaurées pour {$tableName}");
            }
        } catch (\Exception $e) {
            $this->warn("⚠️  Impossible de restaurer {$tableName}: " . $e->getMessage());
        }
    }

    private function dropTablesInOrder(): void
    {
        $tables = ['paiements', 'lignes', 'factures', 'devis'];
        
        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                $this->line("🗑️  Suppression de {$table}...");
                Schema::dropIfExists($table);
            }
        }
    }

    private function createAllTables(): void
    {
        $this->createTable('devis');
        $this->createTable('factures');
        $this->createTable('lignes');
        $this->createTable('paiements');
    }

    private function createTable(string $tableName): void
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
            $this->line("✅ Table {$tableName} créée");
        } catch (\Exception $e) {
            $this->error("❌ Erreur création {$tableName}: " . $e->getMessage());
        }
    }

    private function createDevisTable(): void
    {
        if (Schema::hasTable('devis')) {
            Schema::dropIfExists('devis');
        }

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
            $table->unsignedBigInteger('facture_id')->nullable();
            $table->timestamp('converted_at')->nullable();
            $table->text('notes_internes')->nullable();
            $table->timestamps();
            
            // Index sans contraintes FK pour éviter les problèmes
            $table->index(['chantier_id', 'statut']);
            $table->index(['commercial_id', 'date_emission']);
            $table->index('numero');
        });
    }

    private function createFacturesTable(): void
    {
        if (Schema::hasTable('factures')) {
            Schema::dropIfExists('factures');
        }

        Schema::create('factures', function ($table) {
            $table->id();
            $table->string('numero')->unique();
            $table->foreignId('chantier_id')->constrained()->onDelete('cascade');
            $table->foreignId('commercial_id')->constrained('users')->onDelete('cascade');
            $table->unsignedBigInteger('devis_id')->nullable();
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
            $table->index('numero');
        });
    }

    private function createLignesTable(): void
    {
        if (Schema::hasTable('lignes')) {
            Schema::dropIfExists('lignes');
        }

        Schema::create('lignes', function ($table) {
            $table->id();
            $table->string('ligneable_type');
            $table->unsignedBigInteger('ligneable_id');
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
        if (Schema::hasTable('paiements')) {
            Schema::dropIfExists('paiements');
        }

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

    private function checkFinalState(): void
    {
        $this->info('📊 État final des tables :');
        
        $tables = ['devis', 'factures', 'lignes', 'paiements'];
        $allOk = true;
        
        foreach ($tables as $table) {
            $exists = Schema::hasTable($table);
            $status = $exists ? '✅' : '❌';
            $this->line("   {$status} {$table}");
            
            if (!$exists) {
                $allOk = false;
            }
        }

        if ($allOk) {
            $this->info('🎉 Toutes les tables sont prêtes !');
            $this->line('🚀 Vous pouvez maintenant utiliser le module devis/factures');
        } else {
            $this->error('❌ Certaines tables manquent encore');
        }
    }
}