<?php
// app/Models/Paiement.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Paiement extends Model
{
    use HasFactory;

    protected $fillable = [
        'facture_id', 'montant', 'date_paiement', 'mode_paiement',
        'reference_paiement', 'banque', 'statut', 'commentaire',
        'saisi_par', 'valide_at', 'justificatif_path'
    ];

    protected $casts = [
        'montant' => 'decimal:2',
        'date_paiement' => 'date',
        'valide_at' => 'datetime',
        'saisi_par' => 'integer',
    ];

    // Relations
    public function facture()
    {
        return $this->belongsTo(Facture::class);
    }

    public function saisieParUser()
    {
        return $this->belongsTo(User::class, 'saisi_par');
    }

    // Méthodes métier
    public function valider(): void
    {
        $this->update([
            'statut' => 'valide',
            'valide_at' => now(),
        ]);

        // Mettre à jour la facture
        $this->facture->mettreAJourPaiements();
    }

    public function rejeter(string $raison = null): void
    {
        $this->update([
            'statut' => 'rejete',
            'commentaire' => $raison,
        ]);

        // Mettre à jour la facture
        $this->facture->mettreAJourPaiements();
    }

    public function estValide(): bool
    {
        return $this->statut === 'valide';
    }

    public function peutEtreModifie(): bool
    {
        return $this->statut === 'en_attente';
    }

    // Accesseurs
    public function getModePaiementTexteAttribute(): string
    {
        return match ($this->mode_paiement) {
            'virement' => 'Virement bancaire',
            'cheque' => 'Chèque',
            'especes' => 'Espèces',
            'cb' => 'Carte bancaire',
            'prelevement' => 'Prélèvement',
            'autre' => 'Autre',
            default => 'Inconnu',
        };
    }

    public function getStatutBadgeClassAttribute(): string
    {
        return match ($this->statut) {
            'en_attente' => 'bg-yellow-100 text-yellow-800',
            'valide' => 'bg-green-100 text-green-800',
            'rejete' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getStatutTexteAttribute(): string
    {
        return match ($this->statut) {
            'en_attente' => 'En attente',
            'valide' => 'Validé',
            'rejete' => 'Rejeté',
            default => 'Inconnu',
        };
    }

    // Scopes
    public function scopeValides($query)
    {
        return $query->where('statut', 'valide');
    }

    public function scopeEnAttente($query)
    {
        return $query->where('statut', 'en_attente');
    }

    public function scopeParMode($query, string $mode)
    {
        return $query->where('mode_paiement', $mode);
    }

    // Événements du modèle
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($paiement) {
            if (!$paiement->date_paiement) {
                $paiement->date_paiement = now()->toDateString();
            }
            if (!$paiement->saisi_par) {
                $paiement->saisi_par = auth()->id();
            }
        });

        static::saved(function ($paiement) {
            // Mettre à jour la facture automatiquement
            $paiement->facture->mettreAJourPaiements();
        });

        static::deleted(function ($paiement) {
            // Mettre à jour la facture après suppression
            $paiement->facture->mettreAJourPaiements();
        });
    }
}