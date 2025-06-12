<?php
// database/migrations/2024_01_07_000000_create_devis_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('devis', function (Blueprint $table) {
            $table->id();
            $table->string('numero')->unique(); // Numérotation automatique
            $table->foreignId('chantier_id')->constrained()->onDelete('cascade');
            $table->foreignId('commercial_id')->constrained('users')->onDelete('cascade');
            $table->string('titre');
            $table->text('description')->nullable();
            
            // Statuts du devis
            $table->enum('statut', ['brouillon', 'envoye', 'accepte', 'refuse', 'expire'])->default('brouillon');
            
            // Informations client (copie pour historique)
            $table->json('client_info'); // {nom, email, adresse, etc.}
            
            // Dates importantes
            $table->date('date_emission');
            $table->date('date_validite'); // Date limite d'acceptation
            $table->timestamp('date_envoi')->nullable();
            $table->timestamp('date_reponse')->nullable(); // Date acceptation/refus
            
            // Montants
            $table->decimal('montant_ht', 10, 2)->default(0);
            $table->decimal('montant_tva', 10, 2)->default(0);
            $table->decimal('montant_ttc', 10, 2)->default(0);
            $table->decimal('taux_tva', 5, 2)->default(20.00); // % TVA
            
            // Conditions commerciales
            $table->text('conditions_generales')->nullable();
            $table->integer('delai_realisation')->nullable(); // en jours
            $table->text('modalites_paiement')->nullable();
            
            // Signature électronique
            $table->text('signature_client')->nullable(); // Base64 de l'image
            $table->timestamp('signed_at')->nullable();
            $table->string('signature_ip')->nullable();
            
            // Conversion en facture
            $table->foreignId('facture_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamp('converted_at')->nullable();
            
            // Notes internes (non visible client)
            $table->text('notes_internes')->nullable();
            
            $table->timestamps();
            
            // Index pour les recherches
            $table->index(['chantier_id', 'statut']);
            $table->index(['commercial_id', 'date_emission']);
            $table->index('numero');
        });
    }

    public function down()
    {
        Schema::dropIfExists('devis');
    }
};