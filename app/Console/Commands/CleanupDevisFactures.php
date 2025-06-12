<?php
// app/Console/Commands/CleanupDevisFactures.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Devis;
use App\Models\Facture;
use App\Models\Notification;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class CleanupDevisFactures extends Command
{
    protected $signature = 'devis:cleanup 
                            {--dry-run : Simuler le nettoyage sans effectuer les suppressions}
                            {--force : Forcer le nettoyage sans confirmation}
                            {--days=90 : Nombre de jours pour les données à nettoyer}';
    
    protected $description = 'Nettoyer et maintenir le système de devis/factures';

    public function handle()
    {
        $this->info('🧹 Nettoyage et maintenance du système de devis/factures');
        $this->newLine();

        $dryRun = $this->option('dry-run');
        $force = $this->option('force');
        $days = (int) $this->option('days');

        if ($dryRun) {
            $this->warn('🔍 Mode simulation activé - aucune suppression ne sera effectuée');
            $this->newLine();
        }

        $tasks = [
            'Devis expirés' => 'cleanupDevisExpires',
            'Factures brouillon anciennes' => 'cleanupFacturesBrouillon',
            'Notifications anciennes' => 'cleanupNotifications',
            'Fichiers PDF temporaires' => 'cleanupTempFiles',
            'Fichiers orphelins' => 'cleanupOrphanedFiles',
            'Optimisation base de données' => 'optimizeDatabase',
        ];

        $results = [];

        foreach ($tasks as $description => $method) {
            $this->info("🔧 {$description}...");
            
            if (!$force && !$dryRun && !$this->confirm("Exécuter: {$description} ?")) {
                $this->line("  ⏭️ Ignoré");
                continue;
            }

            $result = $this->$method($days, $dryRun);
            $results[$description] = $result;

            if ($result['count'] > 0) {
                $action = $dryRun ? 'à nettoyer' : 'nettoyés';
                $this->line("  ✅ {$result['count']} éléments {$action}");
                
                if (isset($result['details'])) {
                    foreach ($result['details'] as $detail) {
                        $this->line("     • {$detail}");
                    }
                }
            } else {
                $this->line("  ✅ Aucun élément à nettoyer");
            }

            if (isset($result['freed_space'])) {
                $this->line("  💾 Espace libéré: {$result['freed_space']}");
            }

            $this->newLine();
        }

        $this->displayCleanupSummary($results, $dryRun);

        return 0;
    }

    protected function cleanupDevisExpires(int $days, bool $dryRun): array
    {
        $cutoffDate = Carbon::now()->subDays($days);
        
        $query = Devis::where('statut', 'envoye')
            ->where('date_validite', '<', $cutoffDate)
            ->whereNull('facture_id'); // Ne pas toucher aux devis convertis

        $count = $query->count();
        $details = [];

        if ($count > 0 && !$dryRun) {
            $devis = $query->get();
            
            foreach ($devis as $devisItem) {
                // Marquer comme expiré au lieu de supprimer
                $devisItem->update(['statut' => 'expire']);
                $details[] = "Devis {$devisItem->numero} marqué comme expiré";
            }
        } elseif ($count > 0) {
            $devis = $query->take(5)->get();
            foreach ($devis as $devisItem) {
                $details[] = "Devis {$devisItem->numero} (expiré depuis " . 
                           $devisItem->date_validite->diffForHumans() . ")";
            }
            if ($count > 5) {
                $details[] = "... et " . ($count - 5) . " autres";
            }
        }

        return [
            'count' => $count,
            'details' => $details
        ];
    }

    protected function cleanupFacturesBrouillon(int $days, bool $dryRun): array
    {
        $cutoffDate = Carbon::now()->subDays($days);
        
        $query = Facture::where('statut', 'brouillon')
            ->where('created_at', '<', $cutoffDate)
            ->whereDoesntHave('paiements'); // S'assurer qu'il n'y a pas de paiements

        $count = $query->count();
        $details = [];

        if ($count > 0 && !$dryRun) {
            $factures = $query->get();
            
            foreach ($factures as $facture) {
                // Supprimer les lignes associées puis la facture
                $facture->lignes()->delete();
                $facture->delete();
                $details[] = "Facture brouillon {$facture->numero} supprimée";
            }
        } elseif ($count > 0) {
            $factures = $query->take(5)->get();
            foreach ($factures as $facture) {
                $details[] = "Facture {$facture->numero} (créée " . 
                           $facture->created_at->diffForHumans() . ")";
            }
            if ($count > 5) {
                $details[] = "... et " . ($count - 5) . " autres";
            }
        }

        return [
            'count' => $count,
            'details' => $details
        ];
    }

    protected function cleanupNotifications(int $days, bool $dryRun): array
    {
        $cutoffDate = Carbon::now()->subDays($days);
        
        $query = Notification::where('lu', true)
            ->where('lu_at', '<', $cutoffDate);

        $count = $query->count();
        $details = [];

        if ($count > 0 && !$dryRun) {
            $deleted = $query->delete();
            $details[] = "{$deleted} notifications anciennes supprimées";
        } elseif ($count > 0) {
            $notificationTypes = $query->groupBy('type')
                ->selectRaw('type, count(*) as count')
                ->pluck('count', 'type');
            
            foreach ($notificationTypes as $type => $typeCount) {
                $details[] = "{$type}: {$typeCount} notifications";
            }
        }

        return [
            'count' => $count,
            'details' => $details
        ];
    }

    protected function cleanupTempFiles(int $days, bool $dryRun): array
    {
        $tempPath = storage_path('app/temp');
        $count = 0;
        $freedSpace = 0;
        $details = [];

        if (!file_exists($tempPath)) {
            return ['count' => 0, 'details' => []];
        }

        $cutoffTime = time() - ($days * 24 * 60 * 60);
        $files = glob($tempPath . '/*');

        foreach ($files as $file) {
            if (is_file($file) && filemtime($file) < $cutoffTime) {
                $size = filesize($file);
                $freedSpace += $size;
                $count++;

                if (!$dryRun) {
                    unlink($file);
                    $details[] = "Fichier supprimé: " . basename($file);
                } else {
                    $details[] = "À supprimer: " . basename($file) . " (" . $this->formatFileSize($size) . ")";
                }

                // Limiter les détails pour éviter une sortie trop longue
                if (count($details) >= 10) {
                    break;
                }
            }
        }

        if ($count > count($details)) {
            $details[] = "... et " . ($count - count($details)) . " autres fichiers";
        }

        return [
            'count' => $count,
            'details' => $details,
            'freed_space' => $this->formatFileSize($freedSpace)
        ];
    }

    protected function cleanupOrphanedFiles(int $days, bool $dryRun): array
    {
        $count = 0;
        $freedSpace = 0;
        $details = [];

        // Nettoyer les documents orphelins
        $documentsPath = storage_path('app/public/documents');
        
        if (file_exists($documentsPath)) {
            $directories = glob($documentsPath . '/*', GLOB_ONLYDIR);
            
            foreach ($directories as $dir) {
                $chantierId = basename($dir);
                
                // Vérifier si le chantier existe encore
                if (!is_numeric($chantierId) || !\App\Models\Chantier::find($chantierId)) {
                    $files = glob($dir . '/*');
                    $dirSize = 0;
                    
                    foreach ($files as $file) {
                        if (is_file($file)) {
                            $dirSize += filesize($file);
                        }
                    }
                    
                    $count += count($files);
                    $freedSpace += $dirSize;
                    
                    if (!$dryRun) {
                        // Supprimer tous les fichiers puis le dossier
                        array_map('unlink', $files);
                        rmdir($dir);
                        $details[] = "Dossier orphelin supprimé: chantier_{$chantierId}";
                    } else {
                        $details[] = "À supprimer: dossier chantier_{$chantierId} (" . 
                                   count($files) . " fichiers, " . $this->formatFileSize($dirSize) . ")";
                    }
                }
            }
        }

        return [
            'count' => $count,
            'details' => $details,
            'freed_space' => $this->formatFileSize($freedSpace)
        ];
    }

    protected function optimizeDatabase(int $days, bool $dryRun): array
    {
        $details = [];
        $optimizations = 0;

        if (!$dryRun) {
            // Optimiser les tables
            $tables = ['devis', 'factures', 'lignes', 'paiements'];
            
            foreach ($tables as $table) {
                try {
                    \DB::statement("OPTIMIZE TABLE {$table}");
                    $details[] = "Table {$table} optimisée";
                    $optimizations++;
                } catch (\Exception $e) {
                    $details[] = "Erreur optimisation {$table}: " . $e->getMessage();
                }
            }

            // Mettre à jour les statistiques
            try {
                \DB::statement("ANALYZE TABLE devis, factures, lignes, paiements");
                $details[] = "Statistiques mises à jour";
                $optimizations++;
            } catch (\Exception $e) {
                $details[] = "Erreur mise à jour statistiques: " . $e->getMessage();
            }
        } else {
            $details[] = "Optimisation des tables devis, factures, lignes, paiements";
            $details[] = "Mise à jour des statistiques de base de données";
            $optimizations = 2;
        }

        return [
            'count' => $optimizations,
            'details' => $details
        ];
    }

    protected function formatFileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, 2) . ' ' . $units[$pow];
    }

    protected function displayCleanupSummary(array $results, bool $dryRun): void
    {
        $this->newLine();
        $this->info('📊 Résumé du nettoyage');
        $this->newLine();

        $totalItems = 0;
        $totalSpace = 0;

        foreach ($results as $task => $result) {
            $totalItems += $result['count'];
            
            if (isset($result['freed_space'])) {
                // Conversion approximative pour le total
                $space = $result['freed_space'];
                if (strpos($space, 'MB') !== false) {
                    $totalSpace += (float) $space * 1024 * 1024;
                } elseif (strpos($space, 'KB') !== false) {
                    $totalSpace += (float) $space * 1024;
                } else {
                    $totalSpace += (float) $space;
                }
            }

            $status = $result['count'] > 0 ? '✅' : '➖';
            $this->line("{$status} {$task}: {$result['count']} éléments");
        }

        $this->newLine();
        $action = $dryRun ? 'seraient nettoyés' : 'ont été nettoyés';
        $this->info("📈 Total: {$totalItems} éléments {$action}");
        
        if ($totalSpace > 0) {
            $this->info("💾 Espace total libéré: " . $this->formatFileSize($totalSpace));
        }

        if ($dryRun) {
            $this->newLine();
            $this->warn('💡 Pour effectuer le nettoyage réel, exécutez sans --dry-run');
        } else {
            $this->newLine();
            $this->info('🎉 Nettoyage terminé avec succès !');
        }

        $this->newLine();
        $this->displayMaintenanceRecommendations();
    }

    protected function displayMaintenanceRecommendations(): void
    {
        $this->info('🔧 Recommandations de maintenance:');
        $this->newLine();

        $recommendations = [
            'Exécutez ce nettoyage régulièrement (recommandé: mensuel)',
            'Surveillez l\'espace disque utilisé par les documents',
            'Configurez une sauvegarde automatique des données importantes',
            'Vérifiez périodiquement l\'intégrité des données avec: php artisan devis:check',
            'Optimisez la base de données en cas de performances dégradées',
        ];

        foreach ($recommendations as $i => $recommendation) {
            $this->line('   ' . ($i + 1) . '. ' . $recommendation);
        }

        $this->newLine();
        $this->info('📅 Pour automatiser, ajoutez à votre crontab:');
        $this->line('   0 2 * * 0 cd /path/to/project && php artisan devis:cleanup --force');
    }
}