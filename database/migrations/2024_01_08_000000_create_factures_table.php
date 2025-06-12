<?php
// database/migrations/2024_01_08_000000_create_factures_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('factures', function (Blueprint $table) {
            $table->id();
            $table->string('numero')->unique(); // F-2024-001, etc.
            $table->foreignId('chantier_id')->constrained()->onDelete('cascade');
            $table->foreignId('commercial_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('devis_id')->nullable()->constrained()->onDelete('set null');
            
            $table->string('titre');
            $table->text('description')->nullable();
            
            // Statut de la facture
            $table->enum('statut', ['brouillon', 'envoyee', 'payee_partiel', 'payee', 'en_retard', 'annulee'])->default('brouillon');
            
            // Informations client (snapshot au moment de la facture)
            $table->json('client_info');
            
            // Dates
            $table->date('date_emission');
            $table->date('date_echeance'); // Date limite de paiement
            $table->timestamp('date_envoi')->nullable();
            
            // Montants
            $table->decimal('montant_ht', 10, 2)->default(0);
            $table->decimal('montant_tva', 10, 2)->default(0);
            $table->decimal('montant_ttc', 10, 2)->default(0);
            $table->decimal('taux_tva', 5, 2)->default(20.00);
            
            // Suivi des paiements
            $table->decimal('montant_paye', 10, 2)->default(0);
            $table->decimal('montant_restant', 10, 2)->default(0);
            $table->timestamp('date_paiement_complet')->nullable();
            
            // Conditions
            $table->text('conditions_reglement')->nullable();
            $table->integer('delai_paiement')->default(30); // jours
            
            // Notes et références
            $table->string('reference_commande')->nullable();
            $table->text('notes_internes')->nullable();
            
            // Relances automatiques
            $table->integer('nb_relances')->default(0);
            $table->timestamp('derniere_relance')->nullable();
            
            $table->timestamps();
            
            // Index
            $table->index(['chantier_id', 'statut']);
            $table->index(['commercial_id', 'date_emission']);
            $table->index(['statut', 'date_echeance']); // Pour les relances
            $table->index('numero');
        });
    }

    public function down()
    {
        Schema::dropIfExists('factures');
    }
};