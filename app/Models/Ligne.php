<?php
// app/Models/Ligne.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Ligne extends Model
{
    use HasFactory;

    protected $fillable = [
        'ligneable_type', 'ligneable_id', 'ordre', 'designation',
        'description', 'unite', 'quantite', 'prix_unitaire_ht',
        'taux_tva', 'montant_ht', 'montant_tva', 'montant_ttc',
        'remise_pourcentage', 'remise_montant', 'categorie', 'code_produit'
    ];

    protected $casts = [
        'quantite' => 'decimal:2',
        'prix_unitaire_ht' => 'decimal:2',
        'taux_tva' => 'decimal:2',
        'montant_ht' => 'decimal:2',
        'montant_tva' => 'decimal:2',
        'montant_ttc' => 'decimal:2',
        'remise_pourcentage' => 'decimal:2',
        'remise_montant' => 'decimal:2',
        'ordre' => 'integer',
    ];

    // Relations
    public function ligneable()
    {
        return $this->morphTo();
    }

    // Méthodes métier
    public function calculerMontants(): void
    {
        // Calcul du montant HT avec remise
        $montantHtBrut = $this->quantite * $this->prix_unitaire_ht;
        
        // Application de la remise
        if ($this->remise_pourcentage > 0) {
            $this->remise_montant = $montantHtBrut * ($this->remise_pourcentage / 100);
        }
        
        $montantHt = $montantHtBrut - $this->remise_montant;
        $montantTva = $montantHt * ($this->taux_tva / 100);
        $montantTtc = $montantHt + $montantTva;
    
        // Mise à jour directe sans déclencher d'événements
        DB::table('lignes')
            ->where('id', $this->id)
            ->update([
                'montant_ht' => $montantHt,
                'montant_tva' => $montantTva,
                'montant_ttc' => $montantTtc,
                'updated_at' => now(),
            ]);
    
        // Mettre à jour l'instance
        $this->montant_ht = $montantHt;
        $this->montant_tva = $montantTva;
        $this->montant_ttc = $montantTtc;
    }

    public function dupliquer()
    {
        return $this->replicate([
            'id', 'created_at', 'updated_at'
        ]);
    }

    // Accesseurs
    public function getPrixUnitaireTtcAttribute(): float
    {
        return $this->prix_unitaire_ht * (1 + $this->taux_tva / 100);
    }

    public function getMontantRemiseAttribute(): float
    {
        return $this->remise_montant > 0 ? $this->remise_montant : 
               ($this->quantite * $this->prix_unitaire_ht * $this->remise_pourcentage / 100);
    }

    public function getDesignationCompleteAttribute(): string
    {
        $designation = $this->designation;
        if ($this->description) {
            $designation .= ' - ' . $this->description;
        }
        return $designation;
    }

    // Scopes
    public function scopeOrdonnes($query)
    {
        return $query->orderBy('ordre');
    }

    public function scopeParCategorie($query, string $categorie)
    {
        return $query->where('categorie', $categorie);
    }

    // Événements du modèle
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($ligne) {
            // Définir l'ordre automatiquement
            if (!$ligne->ordre) {
                $dernierOrdre = static::where('ligneable_type', $ligne->ligneable_type)
                    ->where('ligneable_id', $ligne->ligneable_id)
                    ->max('ordre');
                $ligne->ordre = ($dernierOrdre ?? 0) + 1;
            }

            // Valeurs par défaut
            if (!$ligne->unite) {
                $ligne->unite = 'unité';
            }
            if (!$ligne->taux_tva) {
                $ligne->taux_tva = 20.00;
            }
            if (!$ligne->quantite) {
                $ligne->quantite = 1;
            }
        });

        // ÉVÉNEMENT SAVED COMMENTÉ POUR ÉVITER LA BOUCLE INFINIE
        /*
        static::saved(function ($ligne) {
            // Recalculer les montants après modification
            if ($ligne->isDirty(['quantite', 'prix_unitaire_ht', 'taux_tva', 'remise_pourcentage', 'remise_montant'])) {
                $ligne->calculerMontants();
                
                // Mettre à jour les totaux du parent (devis ou facture)
                if ($ligne->ligneable) {
                    $ligne->ligneable->calculerMontants();
                }
            }
        });
        */

        static::deleted(function ($ligne) {
            // Mettre à jour les totaux du parent après suppression
            if ($ligne->ligneable) {
                $ligne->ligneable->calculerMontants();
            }
        });
    }
}