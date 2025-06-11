<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'telephone',
        'adresse',
        'active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'active' => 'boolean',
        ];
    }

    // Relations
    public function chantiersClient()
    {
        return $this->hasMany(Chantier::class, 'client_id');
    }

    public function chantiersCommercial()
    {
        return $this->hasMany(Chantier::class, 'commercial_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function commentaires()
    {
        return $this->hasMany(Commentaire::class);
    }

    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    // Méthodes utilitaires pour les rôles
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isCommercial(): bool
    {
        return $this->role === 'commercial';
    }

    public function isClient(): bool
    {
        return $this->role === 'client';
    }

    // Méthode pour compter les notifications non lues
    public function getNotificationsNonLues(): int
    {
        return $this->notifications()->where('lu', false)->count();
    }

    // Méthode pour obtenir les chantiers selon le rôle
    public function getChantiers()
    {
        switch ($this->role) {
            case 'admin':
                return Chantier::all();
            case 'commercial':
                return $this->chantiersCommercial;
            case 'client':
                return $this->chantiersClient;
            default:
                return collect();
        }
    }

    // Statistiques pour l'utilisateur
    public function getStats(): array
    {
        $stats = [
            'total_chantiers' => 0,
            'chantiers_en_cours' => 0,
            'chantiers_termines' => 0,
            'notifications_non_lues' => $this->getNotificationsNonLues(),
        ];

        if ($this->isAdmin()) {
            $stats['total_chantiers'] = Chantier::count();
            $stats['chantiers_en_cours'] = Chantier::where('statut', 'en_cours')->count();
            $stats['chantiers_termines'] = Chantier::where('statut', 'termine')->count();
        } elseif ($this->isCommercial()) {
            $stats['total_chantiers'] = $this->chantiersCommercial()->count();
            $stats['chantiers_en_cours'] = $this->chantiersCommercial()->where('statut', 'en_cours')->count();
            $stats['chantiers_termines'] = $this->chantiersCommercial()->where('statut', 'termine')->count();
        } elseif ($this->isClient()) {
            $stats['total_chantiers'] = $this->chantiersClient()->count();
            $stats['chantiers_en_cours'] = $this->chantiersClient()->where('statut', 'en_cours')->count();
            $stats['chantiers_termines'] = $this->chantiersClient()->where('statut', 'termine')->count();
        }

        return $stats;
    }

    // Scope pour filtrer les utilisateurs actifs
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    // Scope pour filtrer par rôle
    public function scopeRole($query, $role)
    {
        return $query->where('role', $role);
    }
}