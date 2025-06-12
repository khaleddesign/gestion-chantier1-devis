<?php
// database/migrations/2024_01_09_000000_create_lignes_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('lignes', function (Blueprint $table) {
            $table->id();
            
            // Relation polymorphe pour devis ET factures
            $table->morphs('ligneable'); // ligneable_type, ligneable_id
            
            $table->integer('ordre')->default(0); // Ordre d'affichage
            
            // Informations produit/service
            $table->string('designation');
            $table->text('description')->nullable();
            $table->string('unite')->default('unité'); // unité, m², heure, forfait, etc.
            
            // Quantités et prix
            $table->decimal('quantite', 8, 2)->default(1);
            $table->decimal('prix_unitaire_ht', 8, 2);
            $table->decimal('taux_tva', 5, 2)->default(20.00);
            
            // Montants calculés automatiquement
            $table->decimal('montant_ht', 10, 2); // quantite * prix_unitaire_ht
            $table->decimal('montant_tva', 10, 2); // montant_ht * taux_tva / 100
            $table->decimal('montant_ttc', 10, 2); // montant_ht + montant_tva
            
            // Remise optionnelle
            $table->decimal('remise_pourcentage', 5, 2)->default(0);
            $table->decimal('remise_montant', 8, 2)->default(0);
            
            // Catégorisation (optionnel)
            $table->string('categorie')->nullable(); // matériaux, main-d'œuvre, transport, etc.
            $table->string('code_produit')->nullable(); // Référence interne
            
            $table->timestamps();
            
            // Index
            $table->index(['ligneable_type', 'ligneable_id']);
            $table->index('ordre');
        });
    }

    public function down()
    {
        Schema::dropIfExists('lignes');
    }
};