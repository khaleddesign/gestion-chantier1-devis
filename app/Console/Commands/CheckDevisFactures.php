<?php
// app/Console/Commands/CheckDevisFactures.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use App\Models\Devis;
use App\Models\Facture;
use App\Models\Ligne;
use App\Models\Paiement;

class CheckDevisFactures extends Command
{
    protected $signature = 'devis:check {--fix : Tenter de corriger les problèmes détectés}';
    protected $description = 'Vérifier l\'installation et la configuration du système de devis/factures';

    public function handle()
    {
        $this->info('🔍 Vérification du système de devis et factures');
        $this->newLine();

        $checks = [
            'Base de données' => 'checkDatabase',
            'Modèles Eloquent' => 'checkModels',
            'Relations' => 'checkRelations',
            'Policies' => 'checkPolicies',
            'Routes' => 'checkRoutes',
            'Vues' => 'checkViews',
            'Stockage' => 'checkStorage',
            'Configuration' => 'checkConfiguration',
        ];

        $results = [];
        
        foreach ($checks as $description => $method) {
            $this->info("📋 Vérification: {$description}");
            $result = $this->$method();
            $results[$description] = $result;
            
            if ($result['status'] === 'success') {
                $this->line("  ✅ {$result['message']}");
            } elseif ($result['status'] === 'warning') {
                $this->warn("  ⚠️ {$result['message']}");
            } else {
                $this->error("  ❌ {$result['message']}");
            }
            
            if (isset($result['details'])) {
                foreach ($result['details'] as $detail) {
                    $this->line("     • {$detail}");
                }
            }
            
            $this->newLine();
        }

        $this->displaySummary($results);
        
        return $this->hasErrors($results) ? 1 : 0;
    }

    protected function checkDatabase(): array
    {
        $tables = ['devis', 'factures', 'lignes', 'paiements'];
        $missingTables = [];
        $details = [];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                $count = \DB::table($table)->count();
                $details[] = "Table '{$table}': {$count} enregistrements";
            } else {
                $missingTables[] = $table;
            }
        }

        if (!empty($missingTables)) {
            return [
                'status' => 'error',
                'message' => 'Tables manquantes: ' . implode(', ', $missingTables),
                'details' => $details
            ];
        }

        return [
            'status' => 'success',
            'message' => 'Toutes les tables sont présentes',
            'details' => $details
        ];
    }

    protected function checkModels(): array
    {
        $models = [
            'Devis' => \App\Models\Devis::class,
            'Facture' => \App\Models\Facture::class,
            'Ligne' => \App\Models\Ligne::class,
            'Paiement' => \App\Models\Paiement::class,
        ];

        $details = [];
        $errors = [];

        foreach ($models as $name => $class) {
            if (class_exists($class)) {
                try {
                    $instance = new $class();
                    $fillable = count($instance->getFillable());
                    $details[] = "Modèle {$name}: {$fillable} champs fillable";
                } catch (\Exception $e) {
                    $errors[] = "Erreur instanciation {$name}: " . $e->getMessage();
                }
            } else {
                $errors[] = "Classe {$class} non trouvée";
            }
        }

        if (!empty($errors)) {
            return [
                'status' => 'error',
                'message' => 'Problèmes avec les modèles',
                'details' => array_merge($details, $errors)
            ];
        }

        return [
            'status' => 'success',
            'message' => 'Tous les modèles sont opérationnels',
            'details' => $details
        ];
    }

    protected function checkRelations(): array
    {
        $details = [];
        $warnings = [];

        try {
            // Tester les relations principales
            if (Schema::hasTable('devis') && Schema::hasTable('chantiers')) {
                $devisCount = Devis::with('chantier')->count();
                $details[] = "Relations Devis-Chantier: {$devisCount} devis testés";
            }

            if (Schema::hasTable('lignes')) {
                $lignesCount = Ligne::with('ligneable')->count();
                $details[] = "Relations polymorphes Lignes: {$lignesCount} lignes testées";
            }

            // Vérifier les modèles existants pour les nouvelles relations
            $userModel = app_path('Models/User.php');
            if (file_exists($userModel)) {
                $content = file_get_contents($userModel);
                if (strpos($content, 'devisCommercial') === false) {
                    $warnings[] = "Relations devis manquantes dans le modèle User";
                }
            }

            $chantierModel = app_path('Models/Chantier.php');
            if (file_exists($chantierModel)) {
                $content = file_get_contents($chantierModel);
                if (strpos($content, 'devis()') === false) {
                    $warnings[] = "Relations devis manquantes dans le modèle Chantier";
                }
            }

        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Erreur test relations: ' . $e->getMessage(),
                'details' => $details
            ];
        }

        $status = empty($warnings) ? 'success' : 'warning';
        $message = empty($warnings) ? 'Toutes les relations fonctionnent' : 'Relations partiellement configurées';

        return [
            'status' => $status,
            'message' => $message,
            'details' => array_merge($details, $warnings)
        ];
    }

    protected function checkPolicies(): array
    {
        $policies = [
            'DevisPolicy' => app_path('Policies/DevisPolicy.php'),
            'FacturePolicy' => app_path('Policies/FacturePolicy.php'),
        ];

        $details = [];
        $missing = [];

        foreach ($policies as $name => $path) {
            if (file_exists($path)) {
                $details[] = "Policy {$name}: présente";
                
                // Vérifier les méthodes principales
                $content = file_get_contents($path);
                $methods = ['view', 'create', 'update', 'delete'];
                foreach ($methods as $method) {
                    if (strpos($content, "function {$method}(") === false) {
                        $missing[] = "Méthode {$method} manquante dans {$name}";
                    }
                }
            } else {
                $missing[] = "Policy {$name} non trouvée";
            }
        }

        // Vérifier l'enregistrement dans AuthServiceProvider
        $authProvider = app_path('Providers/AuthServiceProvider.php');
        if (file_exists($authProvider)) {
            $content = file_get_contents($authProvider);
            if (strpos($content, 'DevisPolicy') === false) {
                $missing[] = "Policies non enregistrées dans AuthServiceProvider";
            }
        }

        if (!empty($missing)) {
            return [
                'status' => 'warning',
                'message' => 'Policies incomplètes',
                'details' => array_merge($details, $missing)
            ];
        }

        return [
            'status' => 'success',
            'message' => 'Policies configurées correctement',
            'details' => $details
        ];
    }

    protected function checkRoutes(): array
    {
        $routesFile = base_path('routes/web.php');
        $details = [];
        $missing = [];

        if (file_exists($routesFile)) {
            $content = file_get_contents($routesFile);
            
            $requiredRoutes = [
                'chantiers.devis' => 'resource(.*chantiers\.devis.*DevisController',
                'chantiers.factures' => 'resource(.*chantiers\.factures.*FactureController',
                'devis.envoyer' => 'devis.*envoyer',
                'factures.paiements' => 'factures.*paiements',
            ];

            foreach ($requiredRoutes as $name => $pattern) {
                if (preg_match("/{$pattern}/", $content)) {
                    $details[] = "Route {$name}: configurée";
                } else {
                    $missing[] = "Route {$name}: manquante";
                }
            }
        } else {
            return [
                'status' => 'error',
                'message' => 'Fichier routes/web.php non trouvé',
                'details' => []
            ];
        }

        if (!empty($missing)) {
            return [
                'status' => 'warning',
                'message' => 'Routes incomplètes',
                'details' => array_merge($details, $missing)
            ];
        }

        return [
            'status' => 'success',
            'message' => 'Routes correctement configurées',
            'details' => $details
        ];
    }

    protected function checkViews(): array
    {
        $views = [
            'devis/index.blade.php',
            'devis/form.blade.php',
            'devis/show.blade.php',
            'pdf/devis.blade.php',
            'pdf/facture.blade.php',
        ];

        $details = [];
        $missing = [];

        foreach ($views as $view) {
            $path = resource_path("views/{$view}");
            if (file_exists($path)) {
                $size = filesize($path);
                $details[] = "Vue {$view}: présente ({$size} bytes)";
            } else {
                $missing[] = "Vue {$view}: manquante";
            }
        }

        if (!empty($missing)) {
            return [
                'status' => 'warning',
                'message' => 'Vues incomplètes',
                'details' => array_merge($details, $missing)
            ];
        }

        return [
            'status' => 'success',
            'message' => 'Toutes les vues sont présentes',
            'details' => $details
        ];
    }

    protected function checkStorage(): array
    {
        $directories = [
            storage_path('app/public/documents'),
            storage_path('app/public/pdf'),
            storage_path('app/temp'),
        ];

        $details = [];
        $issues = [];

        foreach ($directories as $dir) {
            if (file_exists($dir)) {
                if (is_writable($dir)) {
                    $files = count(glob($dir . '/*'));
                    $details[] = "Dossier {$dir}: OK ({$files} fichiers)";
                } else {
                    $issues[] = "Dossier {$dir}: non accessible en écriture";
                }
            } else {
                $issues[] = "Dossier {$dir}: manquant";
                
                if ($this->option('fix')) {
                    if (mkdir($dir, 0755, true)) {
                        $details[] = "Dossier {$dir}: créé automatiquement";
                    } else {
                        $issues[] = "Impossible de créer {$dir}";
                    }
                }
            }
        }

        // Vérifier le lien symbolique storage
        $storageLink = public_path('storage');
        if (!file_exists($storageLink)) {
            $issues[] = "Lien symbolique public/storage manquant";
            
            if ($this->option('fix')) {
                $this->call('storage:link');
                $details[] = "Lien symbolique créé automatiquement";
            }
        } else {
            $details[] = "Lien symbolique public/storage: OK";
        }

        if (!empty($issues)) {
            return [
                'status' => 'warning',
                'message' => 'Problèmes de stockage détectés',
                'details' => array_merge($details, $issues)
            ];
        }

        return [
            'status' => 'success',
            'message' => 'Stockage correctement configuré',
            'details' => $details
        ];
    }

    protected function checkConfiguration(): array
    {
        $details = [];
        $warnings = [];

        // Vérifier la configuration DomPDF
        if (class_exists('Barryvdh\DomPDF\ServiceProvider')) {
            $details[] = "Package DomPDF: installé";
            
            $configPath = config_path('dompdf.php');
            if (file_exists($configPath)) {
                $details[] = "Configuration DomPDF: présente";
            } else {
                $warnings[] = "Configuration DomPDF: manquante";
            }
        } else {
            $warnings[] = "Package DomPDF: non installé";
        }

        // Vérifier la configuration de l'entreprise
        $companyConfig = config('chantiers.company');
        if (!empty($companyConfig['name'])) {
            $details[] = "Configuration entreprise: " . $companyConfig['name'];
        } else {
            $warnings[] = "Configuration entreprise incomplète";
        }

        // Vérifier les variables d'environnement
        $envVars = [
            'APP_NAME' => env('APP_NAME'),
            'APP_URL' => env('APP_URL'),
            'MAIL_FROM_ADDRESS' => env('MAIL_FROM_ADDRESS'),
        ];

        foreach ($envVars as $key => $value) {
            if ($value) {
                $details[] = "Variable {$key}: configurée";
            } else {
                $warnings[] = "Variable {$key}: non configurée";
            }
        }

        if (!empty($warnings)) {
            return [
                'status' => 'warning',
                'message' => 'Configuration incomplète',
                'details' => array_merge($details, $warnings)
            ];
        }

        return [
            'status' => 'success',
            'message' => 'Configuration complète',
            'details' => $details
        ];
    }

    protected function displaySummary(array $results): void
    {
        $this->newLine();
        $this->info('📊 Résumé de la vérification');
        $this->newLine();

        $successCount = 0;
        $warningCount = 0;
        $errorCount = 0;

        foreach ($results as $check => $result) {
            switch ($result['status']) {
                case 'success':
                    $successCount++;
                    break;
                case 'warning':
                    $warningCount++;
                    break;
                case 'error':
                    $errorCount++;
                    break;
            }
        }

        $this->line("✅ Succès: {$successCount}");
        $this->line("⚠️ Avertissements: {$warningCount}");
        $this->line("❌ Erreurs: {$errorCount}");

        $this->newLine();

        if ($errorCount > 0) {
            $this->error('🚨 Des erreurs critiques ont été détectées. Le système peut ne pas fonctionner correctement.');
            $this->info('💡 Exécutez: php artisan devis:install --force pour réinstaller');
        } elseif ($warningCount > 0) {
            $this->warn('⚠️ Des avertissements ont été détectés. Consultez le détail ci-dessus.');
            $this->info('💡 Exécutez: php artisan devis:check --fix pour corriger automatiquement');
        } else {
            $this->info('🎉 Excellent ! Le système est correctement configuré et opérationnel.');
        }

        $this->newLine();
        $this->displayRecommendations($results);
    }

    protected function displayRecommendations(array $results): void
    {
        $recommendations = [];

        // Analyser les résultats pour générer des recommandations
        if (isset($results['Base de données']['status']) && $results['Base de données']['status'] === 'error') {
            $recommendations[] = 'Exécutez les migrations: php artisan migrate';
        }

        if (isset($results['Vues']['status']) && $results['Vues']['status'] === 'warning') {
            $recommendations[] = 'Créez les vues manquantes ou exécutez: php artisan devis:install';
        }

        if (isset($results['Stockage']['status']) && $results['Stockage']['status'] === 'warning') {
            $recommendations[] = 'Créez le lien symbolique: php artisan storage:link';
        }

        if (isset($results['Configuration']['status']) && $results['Configuration']['status'] === 'warning') {
            $recommendations[] = 'Installez DomPDF: composer require barryvdh/laravel-dompdf';
            $recommendations[] = 'Configurez les informations de l\'entreprise dans config/chantiers.php';
        }

        if (!empty($recommendations)) {
            $this->info('💡 Recommandations:');
            foreach ($recommendations as $i => $recommendation) {
                $this->line('   ' . ($i + 1) . '. ' . $recommendation);
            }
        }
    }

    protected function hasErrors(array $results): bool
    {
        foreach ($results as $result) {
            if ($result['status'] === 'error') {
                return true;
            }
        }
        return false;
    }
}