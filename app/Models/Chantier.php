<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// ** On n'importe qu'une seule fois chaque classe externe **
use App\Models\User;
use App\Models\Etape;
use App\Models\Document;
use App\Models\Commentaire;
use App\Models\Notification;

class Chantier extends Model
{
    use HasFactory;

    protected $fillable = [
        'titre',
        'description',
        'client_id',
        'commercial_id',
        'statut',
        'date_debut',
        'date_fin_prevue',
        'date_fin_effective',
        'budget',
        'notes',
        'avancement_global',
        'active', // si ce champ existe bien dans votre migration
    ];

    protected $casts = [
        'date_debut'         => 'date',
        'date_fin_prevue'    => 'date',
        'date_fin_effective' => 'date',
        'budget'             => 'decimal:2',
        'avancement_global'  => 'decimal:2',
        'active'             => 'boolean',
    ];

    // Relations
    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function commercial()
    {
        return $this->belongsTo(User::class, 'commercial_id');
    }

    public function etapes()
    {
        return $this->hasMany(Etape::class)->orderBy('ordre');
    }

    public function documents()
    {
        return $this->hasMany(Document::class)->orderBy('created_at', 'desc');
    }

    public function commentaires()
    {
        return $this->hasMany(Commentaire::class)->orderBy('created_at', 'desc');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    // Méthodes métier
    public function calculerAvancement()
    {
        $etapes = $this->etapes;
        if ($etapes->count() === 0) {
            return 0;
        }

        $total   = $etapes->sum('pourcentage');
        $moyenne = $total / $etapes->count();

        $this->update(['avancement_global' => $moyenne]);
        return $moyenne;
    }

    /**
     * Retourne les classes Tailwind CSS pour le badge de statut
     */
    public function getStatutBadgeClass()
    {
        return match ($this->statut) {
            'planifie' => 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800',
            'en_cours' => 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800',
            'termine'  => 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800',
            default    => 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800',
        };
    }

    /**
     * Retourne la couleur Tailwind pour la barre de progression
     */
    public function getProgressBarColor()
    {
        return match ($this->statut) {
            'planifie' => 'bg-gray-400',
            'en_cours' => 'bg-blue-500',
            'termine'  => 'bg-green-500',
            default    => 'bg-gray-400',
        };
    }

    /**
     * Retourne l'icône correspondant au statut (Heroicons)
     */
    public function getStatutIcon()
    {
        return match ($this->statut) {
            'planifie' => 'clock',
            'en_cours' => 'play',
            'termine'  => 'check-circle',
            default    => 'question-mark-circle',
        };
    }

    public function getStatutTexte()
    {
        return match ($this->statut) {
            'planifie' => 'Planifié',
            'en_cours' => 'En cours',
            'termine'  => 'Terminé',
            default    => 'Inconnu',
        };
    }

    public function isEnRetard()
    {
        return $this->date_fin_prevue
            && $this->date_fin_prevue->isPast()
            && $this->statut !== 'termine';
    }

    /**
     * Retourne les classes CSS pour indiquer un retard
     */
    public function getRetardClass()
    {
        if ($this->isEnRetard()) {
            return 'text-red-600 font-semibold';
        }
        return '';
    }
}


// Ajout à app/Models/Chantier.php - Relations pour devis/factures

// Ajouter ces méthodes dans la classe Chantier existante :

public function devis()
{
    return $this->hasMany(Devis::class)->orderBy('created_at', 'desc');
}

public function factures()
{
    return $this->hasMany(Facture::class)->orderBy('created_at', 'desc');
}

public function devisActifs()
{
    return $this->hasMany(Devis::class)->whereIn('statut', ['brouillon', 'envoye']);
}

public function devisAcceptes()
{
    return $this->hasMany(Devis::class)->where('statut', 'accepte');
}

public function facturesImpayees()
{
    return $this->hasMany(Facture::class)->whereIn('statut', ['envoyee', 'payee_partiel', 'en_retard']);
}

// Méthodes métier pour les devis/factures
public function getMontantTotalDevisAttribute(): float
{
    return $this->devisAcceptes->sum('montant_ttc');
}

public function getMontantTotalFacturesAttribute(): float
{
    return $this->factures->sum('montant_ttc');
}

public function getMontantRestantAPayerAttribute(): float
{
    return $this->factures->sum('montant_restant');
}

public function getAvancementFacturationAttribute(): float
{
    $totalDevis = $this->getMontantTotalDevisAttribute();
    $totalFactures = $this->getMontantTotalFacturesAttribute();
    
    return $totalDevis > 0 ? round(($totalFactures / $totalDevis) * 100, 1) : 0;
}

public function getTauxPaiementAttribute(): float
{
    $totalFactures = $this->getMontantTotalFacturesAttribute();
    $montantPaye = $this->factures->sum('montant_paye');
    
    return $totalFactures > 0 ? round(($montantPaye / $totalFactures) * 100, 1) : 0;
}

public function aDesFacturesEnRetard(): bool
{
    return $this->factures()->enRetard()->exists();
}

public function peutAvoirNouveauDevis(): bool
{
    return in_array($this->statut, ['planifie', 'en_cours']);
}