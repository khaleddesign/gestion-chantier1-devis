<?php
// app/Providers/EntrepriseServiceProvider.php

namespace App\Providers;

use App\Models\EntrepriseSettings;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

class EntrepriseServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Enregistrer le service EntrepriseSettings comme singleton
        $this->app->singleton('entreprise.settings', function ($app) {
            return $this->getEntrepriseSettings();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Éviter les erreurs lors des migrations ou installations
        if (!$this->app->runningInConsole() || $this->app->runningUnitTests()) {
            $this->shareEntrepriseSettings();
        }

        // Enregistrer des macros utilitaires
        $this->registerMacros();
    }

    /**
     * Partager les paramètres entreprise avec toutes les vues
     */
    private function shareEntrepriseSettings(): void
    {
        View::composer('*', function ($view) {
            // Éviter les erreurs lors des migrations
            if (!Schema::hasTable('entreprise_settings')) {
                $view->with('entreprise_config', config('entreprise.defaut', []));
                return;
            }

            try {
                $entreprise = $this->getEntrepriseSettings();
                $view->with('entreprise_config', $entreprise);
            } catch (\Exception $e) {
                // En cas d'erreur, utiliser les valeurs par défaut
                Log::debug('Erreur chargement paramètres entreprise: ' . $e->getMessage());
                $view->with('entreprise_config', config('entreprise.defaut', []));
            }
        });
    }

    /**
     * Récupérer les paramètres de l'entreprise avec cache
     */
    private function getEntrepriseSettings(): array
    {
        $cacheKey = config('entreprise.cache.key', 'entreprise_settings');
        $cacheTtl = config('entreprise.cache.ttl', 3600);
        $cacheEnabled = config('entreprise.cache.enabled', true);

        if (!$cacheEnabled) {
            return $this->loadEntrepriseSettings();
        }

        return Cache::remember($cacheKey, $cacheTtl, function () {
            return $this->loadEntrepriseSettings();
        });
    }

    /**
     * Charger les paramètres depuis la base de données
     */
    private function loadEntrepriseSettings(): array
    {
        try {
            // Vérifier que la table existe
            if (!Schema::hasTable('entreprise_settings')) {
                return config('entreprise.defaut', []);
            }

            $settings = EntrepriseSettings::first();
            
            if (!$settings) {
                return config('entreprise.defaut', []);
            }

            // Fusionner avec les valeurs par défaut
            $defaultSettings = config('entreprise.defaut', []);
            $databaseSettings = $settings->toArray();

            // Ajouter des informations calculées
            $databaseSettings['configured'] = EntrepriseSettings::isConfigured();
            $databaseSettings['logo_url'] = $settings->logo_url;
            $databaseSettings['logo_path'] = $settings->logo_path;

            // Fusionner en gardant les valeurs non nulles de la DB
            return array_merge($defaultSettings, array_filter($databaseSettings, function ($value) {
                return $value !== null && $value !== '';
            }));

        } catch (\Exception $e) {
            // Log l'erreur et retourner les valeurs par défaut
            Log::warning('Erreur lors du chargement des paramètres entreprise: ' . $e->getMessage());
            return config('entreprise.defaut', []);
        }
    }

    /**
     * Enregistrer des macros utilitaires
     */
    private function registerMacros(): void
    {
        // Macro pour formater un montant en euros
        if (!collect()->hasMacro('formatEuro')) {
            collect()->macro('formatEuro', function (float $montant = null) {
                if ($montant === null) {
                    $montant = $this->first();
                }
                return number_format($montant, 2, ',', ' ') . ' €';
            });
        }

        // Macro pour formater un pourcentage
        if (!collect()->hasMacro('formatPourcentage')) {
            collect()->macro('formatPourcentage', function (float $pourcentage = null, int $decimales = 1) {
                if ($pourcentage === null) {
                    $pourcentage = $this->first();
                }
                return number_format($pourcentage, $decimales, ',', ' ') . '%';
            });
        }

        // Macro pour formater un numéro de téléphone
        if (!collect()->hasMacro('formatTelephone')) {
            collect()->macro('formatTelephone', function (string $telephone = null) {
                if ($telephone === null) {
                    $telephone = $this->first();
                }
                $numero = preg_replace('/[^\d]/', '', $telephone);
                
                if (strlen($numero) === 10) {
                    return substr($numero, 0, 2) . ' ' . 
                           substr($numero, 2, 2) . ' ' . 
                           substr($numero, 4, 2) . ' ' . 
                           substr($numero, 6, 2) . ' ' . 
                           substr($numero, 8, 2);
                }
                
                return $telephone;
            });
        }
    }

    /**
     * Vider le cache des paramètres entreprise
     */
    public static function clearCache(): void
    {
        $cacheKey = config('entreprise.cache.key', 'entreprise_settings');
        Cache::forget($cacheKey);
        
        // Vider aussi les caches avec tags si supporté
        if (config('entreprise.cache.tags')) {
            try {
                Cache::tags(config('entreprise.cache.tags'))->flush();
            } catch (\Exception $e) {
                // Ignorer les erreurs de tags si le driver ne les supporte pas
                Log::debug('Cache tags non supporté: ' . $e->getMessage());
            }
        }
    }

    /**
     * Recharger les paramètres entreprise
     */
    public static function reload(): array
    {
        static::clearCache();
        return app('entreprise.settings');
    }

    /**
     * Vérifier si les paramètres sont configurés
     */
    public static function isConfigured(): bool
    {
        try {
            if (!Schema::hasTable('entreprise_settings')) {
                return false;
            }
            
            return EntrepriseSettings::isConfigured();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Obtenir les paramètres pour les vues PDF
     */
    public static function getForPdf(): array
    {
        try {
            $settings = app('entreprise.settings');
            
            // Ajouter le chemin absolu du logo pour les PDF
            if (isset($settings['logo']) && $settings['logo']) {
                $settings['logo_path'] = storage_path('app/public/' . $settings['logo']);
                
                // Vérifier que le fichier existe réellement
                if (!file_exists($settings['logo_path'])) {
                    $settings['logo_path'] = null;
                    $settings['logo'] = null;
                }
            }
            
            return $settings;
        } catch (\Exception $e) {
            Log::warning('Erreur récupération paramètres pour PDF: ' . $e->getMessage());
            return config('entreprise.defaut', []);
        }
    }

    /**
     * Mettre à jour les paramètres et vider le cache
     */
    public static function updateSettings(array $data): void
    {
        try {
            EntrepriseSettings::updateSettings($data);
            static::clearCache();
        } catch (\Exception $e) {
            Log::error('Erreur mise à jour paramètres entreprise: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtenir les valeurs par défaut
     */
    public static function getDefaults(): array
    {
        return config('entreprise.defaut', []);
    }

    /**
     * Vérifier si un champ obligatoire est manquant
     */
    public static function getMissingRequiredFields(): array
    {
        $required = ['nom', 'adresse', 'telephone', 'email', 'siret'];
        $settings = app('entreprise.settings');
        $missing = [];

        foreach ($required as $field) {
            if (empty($settings[$field])) {
                $missing[] = $field;
            }
        }

        return $missing;
    }

    /**
     * Obtenir des statistiques sur la configuration
     */
    public static function getConfigurationStats(): array
    {
        try {
            $settings = app('entreprise.settings');
            $required = ['nom', 'adresse', 'telephone', 'email', 'siret'];
            
            $completedFields = 0;
            $totalFields = count($required);
            
            foreach ($required as $field) {
                if (!empty($settings[$field])) {
                    $completedFields++;
                }
            }

            $pourcentage = $totalFields > 0 ? round(($completedFields / $totalFields) * 100) : 0;

            return [
                'completed_fields' => $completedFields,
                'total_fields' => $totalFields,
                'percentage' => $pourcentage,
                'is_configured' => $pourcentage >= 100,
                'missing_fields' => static::getMissingRequiredFields(),
                'has_logo' => !empty($settings['logo']),
                'cache_enabled' => config('entreprise.cache.enabled', true),
            ];
        } catch (\Exception $e) {
            return [
                'completed_fields' => 0,
                'total_fields' => 5,
                'percentage' => 0,
                'is_configured' => false,
                'missing_fields' => ['nom', 'adresse', 'telephone', 'email', 'siret'],
                'has_logo' => false,
                'cache_enabled' => false,
            ];
        }
    }
}