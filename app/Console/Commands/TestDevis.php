<?php
// app/Console/Commands/TestDevis.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

class TestDevis extends Command
{
    protected $signature = 'devis:test';
    protected $description = 'Tester l\'installation du module devis/factures';

    public function handle()
    {
        $this->info('🧪 Test du module Devis/Factures...');
        
        $this->checkTables();
        $this->checkRoutes();
        $this->checkControllers();
        $this->checkConfiguration();
        
        $this->info('✅ Tests terminés !');
        return 0;
    }

    private function checkTables(): void
    {
        $this->info('📊 Vérification des tables...');
        
        $tables = ['devis', 'factures', 'lignes', 'paiements'];
        $allOk = true;
        
        foreach ($tables as $table) {
            $exists = Schema::hasTable($table);
            $status = $exists ? '✅' : '❌';
            $this->line("   {$status} Table {$table}");
            
            if (!$exists) {
                $allOk = false;
            }
        }

        if (!$allOk) {
            $this->warn('⚠️  Certaines tables manquent. Lancez: php artisan devis:fix');
        }
    }

    private function checkRoutes(): void
    {
        $this->info('🛣️  Vérification des routes...');
        
        $routeNames = [
            'chantiers.devis.index',
            'chantiers.devis.create', 
            'chantiers.factures.index',
            'devis.envoyer',
            'factures.pdf'
        ];

        foreach ($routeNames as $routeName) {
            $exists = Route::has($routeName);
            $status = $exists ? '✅' : '❌';
            $this->line("   {$status} Route {$routeName}");
        }
    }

    private function checkControllers(): void
    {
        $this->info('🎮 Vérification des contrôleurs...');
        
        $controllers = [
            'App\\Http\\Controllers\\DevisController',
            'App\\Http\\Controllers\\FactureController', 
            'App\\Http\\Controllers\\PaiementController'
        ];

        foreach ($controllers as $controller) {
            $exists = class_exists($controller);
            $status = $exists ? '✅' : '❌';
            $name = class_basename($controller);
            $this->line("   {$status} {$name}");
        }
    }

    private function checkConfiguration(): void
    {
        $this->info('⚙️  Vérification de la configuration...');
        
        $configs = [
            'devis.entreprise.nom' => 'Nom entreprise',
            'devis.devis.numerotation' => 'Numérotation devis',
            'devis.factures.numerotation' => 'Numérotation factures',
            'devis.pdf.format' => 'Format PDF'
        ];

        foreach ($configs as $key => $description) {
            $value = config($key);
            $status = $value ? '✅' : '❌';
            $this->line("   {$status} {$description}");
        }
    }
}