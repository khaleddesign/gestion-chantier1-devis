<?php
// app/Models/Facture.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Facture extends Model
{
    use HasFactory;

    protected $fillable = [
        'numero', 'chantier_id', 'commercial_id', 'devis_id', 'titre',
        'description', 'statut', 'client_info', 'date_emission',
        'date_echeance', 'date_envoi', 'montant_ht', 'montant_tva',
        'montant_ttc', 'taux_tva', 'montant_paye', 'montant_restant',
        'date_paiement_complet', 'conditions_reglement', 'delai_paiement',
        'reference_commande', 'notes_internes', 'nb_relances',
        'derniere_relance'
    ];

    protected $casts = [
        'client_info' => 'array',
        'date_emission' => 'date',
        'date_echeance' => 'date',
        'date_envoi' => 'datetime',
        'date_paiement_complet' => 'datetime',
        'derniere_relance' => 'datetime',
        'montant_ht' => 'decimal:2',
        'montant_tva' => 'decimal:2',
        'montant_ttc' => 'decimal:2',
        'taux_tva' => 'decimal:2',
        'montant_paye' => 'decimal:2',
        'montant_restant' => 'decimal:2',
        'delai_paiement' => 'integer',
        'nb_relances' => 'integer',
    ];

    // Relations
    public function chantier()
    {
        return $this->belongsTo(Chantier::class);
    }

    public function commercial()
    {
        return $this->belongsTo(User::class, 'commercial_id');
    }

    public function devis()
    {
        return $this->belongsTo(Devis::class);
    }

    public function lignes()
    {
        return $this->morphMany(Ligne::class, 'ligneable')->orderBy('ordre');
    }

    public function paiements()
    {
        return $this->hasMany(Paiement::class)->orderBy('date_paiement', 'desc');
    }

    // Méthodes métier
    public static function genererNumero(): string
    {
        $annee = date('Y');
        $dernierNumero = static::where('numero', 'like', "F-{$annee}-%")
            ->orderBy('numero', 'desc')
            ->value('numero');

        if ($dernierNumero) {
            $numero = (int) substr($dernierNumero, -3);
            $numero++;
        } else {
            $numero = 1;
        }

        return sprintf('F-%s-%03d', $annee, $numero);
    }

    public function calculerMontants(): void
    {
        $montantHT = $this->lignes->sum('montant_ht');
        $montantTVA = $this->lignes->sum('montant_tva');
        $montantTTC = $montantHT + $montantTVA;

        $this->update([
            'montant_ht' => $montantHT,
            'montant_tva' => $montantTVA,
            'montant_ttc' => $montantTTC,
            'montant_restant' => $montantTTC - $this->montant_paye,
        ]);
    }

    public function mettreAJourPaiements(): void
    {
        $montantPaye = $this->paiements()->where('statut', 'valide')->sum('montant');
        $montantRestant = $this->montant_ttc - $montantPaye;

        $ancienStatut = $this->statut;
        $nouveauStatut = $this->determinerStatut($montantPaye, $montantRestant);

        $this->update([
            'montant_paye' => $montantPaye,
            'montant_restant' => $montantRestant,
            'statut' => $nouveauStatut,
            'date_paiement_complet' => $montantRestant <= 0 ? now() : null,
        ]);

        // Notification si changement de statut
        if ($ancienStatut !== $nouveauStatut) {
            // TODO: Envoyer notification
        }
    }

    private function determinerStatut(float $montantPaye, float $montantRestant): string
    {
        if ($montantRestant <= 0) {
            return 'payee';
        } elseif ($montantPaye > 0) {
            return 'payee_partiel';
        } elseif ($this->date_echeance->isPast()) {
            return 'en_retard';
        } else {
            return $this->statut;
        }
    }

    public function ajouterPaiement(float $montant, string $mode, array $details = []): Paiement
    {
        $paiement = $this->paiements()->create([
            'montant' => $montant,
            'date_paiement' => $details['date_paiement'] ?? now()->toDateString(),
            'mode_paiement' => $mode,
            'reference_paiement' => $details['reference'] ?? null,
            'banque' => $details['banque'] ?? null,
            'commentaire' => $details['commentaire'] ?? null,
            'saisi_par' => auth()->id(),
            'valide_at' => now(),
        ]);

        $this->mettreAJourPaiements();

        return $paiement;
    }

    public function estEnRetard(): bool
    {
        return $this->date_echeance->isPast() && 
               !in_array($this->statut, ['payee', 'annulee']);
    }

    public function estPayee(): bool
    {
        return $this->statut === 'payee';
    }

    public function peutEtreAnnulee(): bool
    {
        return !in_array($this->statut, ['payee', 'annulee']);
    }

    public function marquerEnvoyee(): void
    {
        $this->update([
            'statut' => 'envoyee',
            'date_envoi' => now(),
        ]);
    }

    public function envoyerRelance(): void
    {
        $this->increment('nb_relances');
        $this->update(['derniere_relance' => now()]);
        
        // TODO: Envoyer email de relance
    }

    // Accesseurs
    public function getStatutBadgeClassAttribute(): string
    {
        return match ($this->statut) {
            'brouillon' => 'bg-gray-100 text-gray-800',
            'envoyee' => 'bg-blue-100 text-blue-800',
            'payee_partiel' => 'bg-yellow-100 text-yellow-800',
            'payee' => 'bg-green-100 text-green-800',
            'en_retard' => 'bg-red-100 text-red-800',
            'annulee' => 'bg-gray-100 text-gray-600',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getStatutTexteAttribute(): string
    {
        return match ($this->statut) {
            'brouillon' => 'Brouillon',
            'envoyee' => 'Envoyée',
            'payee_partiel' => 'Payée partiellement',
            'payee' => 'Payée',
            'en_retard' => 'En retard',
            'annulee' => 'Annulée',
            default => 'Inconnu',
        };
    }

    public function getClientNomAttribute(): string
    {
        return $this->client_info['nom'] ?? $this->chantier->client->name ?? 'Client inconnu';
    }

    public function getPourcentagePaiementAttribute(): float
    {
        return $this->montant_ttc > 0 ? 
            round(($this->montant_paye / $this->montant_ttc) * 100, 1) : 0;
    }

    // Scopes
    public function scopeEnRetard($query)
    {
        return $query->where('date_echeance', '<', now())
            ->whereNotIn('statut', ['payee', 'annulee']);
    }

    public function scopePayees($query)
    {
        return $query->where('statut', 'payee');
    }

    public function scopeEnAttentePaiement($query)
    {
        return $query->whereIn('statut', ['envoyee', 'payee_partiel']);
    }

    public function scopePourCommercial($query, $commercialId)
    {
        return $query->where('commercial_id', $commercialId);
    }

    // Événements du modèle
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($facture) {
            if (!$facture->numero) {
                $facture->numero = static::genererNumero();
            }
            if (!$facture->date_emission) {
                $facture->date_emission = now();
            }
            if (!$facture->date_echeance) {
                $facture->date_echeance = now()->addDays($facture->delai_paiement ?? 30);
            }
            
            $facture->montant_restant = $facture->montant_ttc;
        });

        static::saved(function ($facture) {
            // Éviter la récursion lors du calcul des montants
            if ($facture->isDirty(['montant_ht', 'montant_tva', 'montant_ttc'])) {
                return;
            }
            $facture->calculerMontants();
        });
    }
}