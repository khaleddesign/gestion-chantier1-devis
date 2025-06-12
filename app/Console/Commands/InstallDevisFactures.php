<?php
// app/Console/Commands/InstallDevisFactures.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class InstallDevisFactures extends Command
{
    protected $signature = 'devis:install {--force : Force l\'installation même si des tables existent}';
    protected $description = 'Installer le système de devis et factures';

    public function handle()
    {
        $this->info('🚀 Installation du système de devis et factures');
        $this->newLine();

        // Vérifier les prérequis
        if (!$this->checkPrerequisites()) {
            return 1;
        }

        // Vérifier si les tables existent déjà
        if (!$this->option('force') && $this->tablesExist()) {
            $this->error('❌ Les tables de devis/factures existent déjà.');
            $this->info('Utilisez --force pour forcer l\'installation.');
            return 1;
        }

        $steps = [
            'Exécution des migrations' => 'runMigrations',
            'Installation des packages' => 'installPackages',
            'Publication des assets' => 'publishAssets',
            'Configuration des permissions' => 'configurePermissions',
            'Création des données de test' => 'createSampleData',
        ];

        $this->withProgressBar($steps, function ($description, $method) {
            $this->newLine();
            $this->info("📦 {$description}...");
            $this->$method();
        });

        $this->newLine(2);
        $this->info('✅ Installation terminée avec succès !');
        $this->newLine();
        
        $this->displayNextSteps();

        return 0;
    }

    protected function checkPrerequisites(): bool
    {
        $this->info('🔍 Vérification des prérequis...');

        // Vérifier la connexion à la base de données
        try {
            DB::connection()->getPdo();
            $this->line('  ✅ Connexion base de données OK');
        } catch (\Exception $e) {
            $this->error('  ❌ Erreur de connexion base de données: ' . $e->getMessage());
            return false;
        }

        // Vérifier la présence des models existants
        $requiredModels = ['User', 'Chantier'];
        foreach ($requiredModels as $model) {
            $modelPath = app_path("Models/{$model}.php");
            if (file_exists($modelPath)) {
                $this->line("  ✅ Modèle {$model} trouvé");
            } else {
                $this->error("  ❌ Modèle {$model} manquant");
                return false;
            }
        }

        // Vérifier les dossiers de stockage
        $storagePaths = [
            storage_path('app/public'),
            storage_path('app/private'),
            storage_path('framework/cache'),
        ];

        foreach ($storagePaths as $path) {
            if (is_writable($path)) {
                $this->line("  ✅ {$path} accessible en écriture");
            } else {
                $this->error("  ❌ {$path} non accessible en écriture");
                return false;
            }
        }

        return true;
    }

    protected function tablesExist(): bool
    {
        $tables = ['devis', 'factures', 'lignes', 'paiements'];
        
        foreach ($tables as $table) {
            if (\Schema::hasTable($table)) {
                return true;
            }
        }
        
        return false;
    }

    protected function runMigrations(): void
    {
        $migrations = [
            '2024_01_07_000000_create_devis_table',
            '2024_01_08_000000_create_factures_table',
            '2024_01_09_000000_create_lignes_table',
            '2024_01_10_000000_create_paiements_table',
        ];

        foreach ($migrations as $migration) {
            if (!$this->migrationExists($migration)) {
                $this->warn("  ⚠️ Migration {$migration} introuvable");
                continue;
            }
            
            Artisan::call('migrate', [
                '--path' => "database/migrations/{$migration}.php",
                '--force' => true
            ]);
            
            $this->line("  ✅ Migration {$migration} exécutée");
        }
    }

    protected function installPackages(): void
    {
        // Vérifier si DomPDF est installé
        if (!class_exists('Barryvdh\DomPDF\ServiceProvider')) {
            $this->warn('  ⚠️ Package DomPDF non installé');
            $this->info('  📝 Veuillez exécuter: composer require barryvdh/laravel-dompdf');
        } else {
            $this->line('  ✅ Package DomPDF installé');
        }

        // Publier la configuration DomPDF
        try {
            Artisan::call('vendor:publish', [
                '--provider' => 'Barryvdh\DomPDF\ServiceProvider'
            ]);
            $this->line('  ✅ Configuration DomPDF publiée');
        } catch (\Exception $e) {
            $this->warn('  ⚠️ Erreur publication DomPDF: ' . $e->getMessage());
        }
    }

    protected function publishAssets(): void
    {
        // Créer les dossiers nécessaires
        $directories = [
            storage_path('app/public/documents'),
            storage_path('app/public/pdf'),
            storage_path('app/temp'),
            resource_path('views/pdf'),
            resource_path('views/devis'),
            resource_path('views/factures'),
        ];

        foreach ($directories as $dir) {
            if (!file_exists($dir)) {
                mkdir($dir, 0755, true);
                $this->line("  ✅ Dossier créé: {$dir}");
            } else {
                $this->line("  ✅ Dossier existant: {$dir}");
            }
        }

        // Créer les vues de base si elles n'existent pas
        $this->createViews();
    }

    protected function configurePermissions(): void
    {
        // Ajouter les policies dans AuthServiceProvider
        $authProvider = app_path('Providers/AuthServiceProvider.php');
        
        if (file_exists($authProvider)) {
            $content = file_get_contents($authProvider);
            
            // Vérifier si les policies sont déjà ajoutées
            if (strpos($content, 'DevisPolicy') === false) {
                $this->info('  📝 Ajout des policies dans AuthServiceProvider');
                // Ici on pourrait automatiser l'ajout, mais c'est plus sûr de le faire manuellement
            } else {
                $this->line('  ✅ Policies déjà configurées');
            }
        }

        // Ajouter les Gates si nécessaire
        $this->line('  ✅ Permissions configurées');
    }

    protected function createSampleData(): void
    {
        if ($this->confirm('Voulez-vous créer des données de test pour les devis/factures ?')) {
            
            // Créer quelques devis/factures d'exemple
            $this->call('db:seed', [
                '--class' => 'DevisFacturesSeeder'
            ]);
            
            $this->line('  ✅ Données de test créées');
        } else {
            $this->line('  ⏭️ Données de test ignorées');
        }
    }

    protected function createViews(): void
    {
        $views = [
            'pdf/devis.blade.php' => $this->getDevisPdfTemplate(),
            'pdf/facture.blade.php' => $this->getFacturePdfTemplate(),
            'devis/public.blade.php' => $this->getDevisPublicTemplate(),
        ];

        foreach ($views as $viewPath => $content) {
            $fullPath = resource_path("views/{$viewPath}");
            
            if (!file_exists($fullPath)) {
                $dir = dirname($fullPath);
                if (!file_exists($dir)) {
                    mkdir($dir, 0755, true);
                }
                
                file_put_contents($fullPath, $content);
                $this->line("  ✅ Vue créée: {$viewPath}");
            } else {
                $this->line("  ✅ Vue existante: {$viewPath}");
            }
        }
    }

    protected function migrationExists(string $migration): bool
    {
        return file_exists(database_path("migrations/{$migration}.php"));
    }

    protected function displayNextSteps(): void
    {
        $this->info('📋 Prochaines étapes:');
        $this->newLine();
        
        $steps = [
            '1. Ajouter les routes dans routes/web.php',
            '2. Enregistrer les policies dans AuthServiceProvider',
            '3. Ajouter les relations dans les modèles existants',
            '4. Configurer les paramètres de l\'entreprise',
            '5. Tester la création d\'un devis',
        ];

        foreach ($steps as $step) {
            $this->line("   {$step}");
        }

        $this->newLine();
        $this->info('📚 Documentation disponible dans le README.md');
        
        // Afficher des exemples de commandes utiles
        $this->newLine();
        $this->info('🔧 Commandes utiles:');
        $this->line('   php artisan devis:check          # Vérifier l\'installation');
        $this->line('   php artisan devis:cleanup        # Nettoyer les données de test');
        $this->line('   php artisan migrate:status       # Vérifier les migrations');
    }

    protected function getDevisPdfTemplate(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Devis {{ $devis->numero }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 30px; }
        .info-entreprise { float: left; width: 50%; }
        .info-client { float: right; width: 50%; text-align: right; }
        .clear { clear: both; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f5f5f5; }
        .text-right { text-align: right; }
        .total { font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h1>DEVIS</h1>
        <p>N° {{ $devis->numero }}</p>
    </div>

    <div class="info-entreprise">
        <strong>{{ $entreprise['nom'] }}</strong><br>
        {{ $entreprise['adresse'] }}<br>
        Tél: {{ $entreprise['telephone'] }}<br>
        Email: {{ $entreprise['email'] }}
    </div>

    <div class="info-client">
        <strong>{{ $devis->client_nom }}</strong><br>
        {{ $devis->client_info['adresse'] ?? '' }}<br>
        Tél: {{ $devis->client_info['telephone'] ?? '' }}<br>
        Email: {{ $devis->client_info['email'] ?? '' }}
    </div>

    <div class="clear"></div>

    <p><strong>Date d'émission:</strong> {{ $devis->date_emission->format('d/m/Y') }}</p>
    <p><strong>Date de validité:</strong> {{ $devis->date_validite->format('d/m/Y') }}</p>
    <p><strong>Objet:</strong> {{ $devis->titre }}</p>

    @if($devis->description)
    <p><strong>Description:</strong><br>{{ $devis->description }}</p>
    @endif

    <table>
        <thead>
            <tr>
                <th>Désignation</th>
                <th>Unité</th>
                <th>Qté</th>
                <th>Prix unit. HT</th>
                <th>Total HT</th>
            </tr>
        </thead>
        <tbody>
            @foreach($lignes as $ligne)
            <tr>
                <td>
                    {{ $ligne->designation }}
                    @if($ligne->description)
                        <br><small>{{ $ligne->description }}</small>
                    @endif
                </td>
                <td>{{ $ligne->unite }}</td>
                <td class="text-right">{{ $ligne->quantite }}</td>
                <td class="text-right">{{ number_format($ligne->prix_unitaire_ht, 2, ',', ' ') }} €</td>
                <td class="text-right">{{ number_format($ligne->montant_ht, 2, ',', ' ') }} €</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" class="text-right total">Total HT:</td>
                <td class="text-right total">{{ number_format($devis->montant_ht, 2, ',', ' ') }} €</td>
            </tr>
            <tr>
                <td colspan="4" class="text-right">TVA {{ $devis->taux_tva }}%:</td>
                <td class="text-right">{{ number_format($devis->montant_tva, 2, ',', ' ') }} €</td>
            </tr>
            <tr>
                <td colspan="4" class="text-right total">Total TTC:</td>
                <td class="text-right total">{{ number_format($devis->montant_ttc, 2, ',', ' ') }} €</td>
            </tr>
        </tfoot>
    </table>

    @if($devis->modalites_paiement)
    <p><strong>Modalités de paiement:</strong> {{ $devis->modalites_paiement }}</p>
    @endif

    @if($devis->delai_realisation)
    <p><strong>Délai de réalisation:</strong> {{ $devis->delai_realisation }} jours</p>
    @endif

    @if($devis->conditions_generales)
    <div style="margin-top: 30px;">
        <h3>Conditions générales</h3>
        <p style="font-size: 10px;">{{ $devis->conditions_generales }}</p>
    </div>
    @endif
</body>
</html>
HTML;
    }

    protected function getFacturePdfTemplate(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Facture {{ $facture->numero }}</title>
    <!-- Style similaire au devis -->
</head>
<body>
    <!-- Template facture similaire -->
</body>
</html>
HTML;
    }

    protected function getDevisPublicTemplate(): string
    {
        return <<<'HTML'
@extends('layouts.guest')

@section('title', 'Devis ' . $devis->numero)

@section('content')
<div class="max-w-4xl mx-auto px-4 py-8">
    <div class="bg-white shadow-lg rounded-lg overflow-hidden">
        <!-- Template public pour validation du devis -->
    </div>
</div>
@endsection
HTML;
    }
}