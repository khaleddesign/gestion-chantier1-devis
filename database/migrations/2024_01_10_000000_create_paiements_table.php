<?php
// database/migrations/2024_01_10_000000_create_paiements_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('paiements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facture_id')->constrained()->onDelete('cascade');
            
            // Informations du paiement
            $table->decimal('montant', 10, 2);
            $table->date('date_paiement');
            $table->enum('mode_paiement', [
                'virement', 'cheque', 'especes', 'cb', 'prelevement', 'autre'
            ]);
            
            // Détails selon le mode
            $table->string('reference_paiement')->nullable(); // N° chèque, ref virement
            $table->string('banque')->nullable();
            
            // Statut et validation
            $table->enum('statut', ['en_attente', 'valide', 'rejete'])->default('valide');
            $table->text('commentaire')->nullable();
            
            // Traçabilité
            $table->foreignId('saisi_par')->constrained('users'); // Qui a saisi le paiement
            $table->timestamp('valide_at')->nullable();
            
            // Documents justificatifs
            $table->string('justificatif_path')->nullable(); // Chemin vers scan chèque, etc.
            
            $table->timestamps();
            
            // Index
            $table->index(['facture_id', 'date_paiement']);
            $table->index(['mode_paiement', 'statut']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('paiements');
    }
};