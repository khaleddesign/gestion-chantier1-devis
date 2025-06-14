<?php
// database/migrations/xxxx_xx_xx_create_entreprise_settings_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('entreprise_settings', function (Blueprint $table) {
            $table->id();
            
            // Informations générales
            $table->string('nom')->nullable();
            $table->string('forme_juridique', 100)->nullable();
            $table->text('adresse')->nullable();
            $table->string('code_postal', 10)->nullable();
            $table->string('ville', 100)->nullable();
            
            // Contact
            $table->string('telephone', 20)->nullable();
            $table->string('telephone_mobile', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('site_web')->nullable();
            
            // Informations légales
            $table->string('siret', 14)->nullable()->unique();
            $table->string('tva_intracommunautaire', 15)->nullable();
            $table->decimal('capital', 15, 2)->nullable();
            $table->string('code_ape', 6)->nullable();
            
            // Coordonnées bancaires
            $table->string('banque')->nullable();
            $table->string('iban', 34)->nullable();
            $table->string('bic', 11)->nullable();
            
            // Branding
            $table->string('logo')->nullable();
            $table->string('couleur_principale', 7)->default('#2563eb');
            
            // Paramètres par défaut pour devis/factures
            $table->decimal('taux_tva_defaut', 5, 2)->default(20.00);
            $table->integer('delai_paiement_defaut')->default(30);
            $table->text('conditions_generales_defaut')->nullable();
            $table->string('modalites_paiement_defaut')->default('Paiement à 30 jours fin de mois');
            
            $table->timestamps();
            
            // Index pour optimiser les recherches
            $table->index(['nom']);
            $table->index(['siret']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entreprise_settings');
    }
};