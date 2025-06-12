<?php
// app/Policies/DevisPolicy.php

namespace App\Policies;

use App\Models\User;
use App\Models\Devis;
use App\Models\Chantier;

class DevisPolicy
{
    /**
     * Peut voir la liste des devis d'un chantier
     */
    public function viewAny(User $user, Chantier $chantier): bool
    {
        // Admin : toujours autorisé
        if ($user->isAdmin()) {
            return true;
        }

        // Commercial rattaché au chantier
        if ($user->isCommercial() && $chantier->commercial_id === $user->id) {
            return true;
        }

        // Client concerné par le chantier
        if ($user->isClient() && $chantier->client_id === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Peut voir un devis spécifique
     */
    public function view(User $user, Devis $devis): bool
    {
        // Admin : toujours autorisé
        if ($user->isAdmin()) {
            return true;
        }

        // Commercial qui a créé le devis
        if ($user->isCommercial() && $devis->commercial_id === $user->id) {
            return true;
        }

        // Client du chantier concerné
        if ($user->isClient() && $devis->chantier->client_id === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Peut créer un devis
     */
    public function create(User $user, Chantier $chantier): bool
    {
        // Seuls admin et commerciaux peuvent créer des devis
        if (!$user->isAdmin() && !$user->isCommercial()) {
            return false;
        }

        // Admin : toujours autorisé
        if ($user->isAdmin()) {
            return true;
        }

        // Commercial rattaché au chantier
        return $user->isCommercial() && $chantier->commercial_id === $user->id;
    }

    /**
     * Peut modifier un devis
     */
    public function update(User $user, Devis $devis): bool
    {
        // Le devis doit pouvoir être modifié
        if (!$devis->peutEtreModifie()) {
            return false;
        }

        // Admin : toujours autorisé
        if ($user->isAdmin()) {
            return true;
        }

        // Commercial qui a créé le devis
        return $user->isCommercial() && $devis->commercial_id === $user->id;
    }

    /**
     * Peut supprimer un devis
     */
    public function delete(User $user, Devis $devis): bool
    {
        // Ne peut pas supprimer un devis accepté ou converti
        if ($devis->statut === 'accepte' || $devis->facture_id) {
            return false;
        }

        // Admin : autorisé
        if ($user->isAdmin()) {
            return true;
        }

        // Commercial qui a créé le devis (et seulement s'il est en brouillon)
        return $user->isCommercial() && 
               $devis->commercial_id === $user->id && 
               $devis->statut === 'brouillon';
    }

    /**
     * Peut envoyer le devis au client
     */
    public function envoyer(User $user, Devis $devis): bool
    {
        // Seul un devis en brouillon peut être envoyé
        if ($devis->statut !== 'brouillon') {
            return false;
        }

        // Admin : autorisé
        if ($user->isAdmin()) {
            return true;
        }

        // Commercial qui a créé le devis
        return $user->isCommercial() && $devis->commercial_id === $user->id;
    }

    /**
     * Peut accepter le devis (côté client)
     */
    public function accepter(User $user, Devis $devis): bool
    {
        // Le devis doit pouvoir être accepté
        if (!$devis->peutEtreAccepte()) {
            return false;
        }

        // Admin : autorisé
        if ($user->isAdmin()) {
            return true;
        }

        // Client du chantier concerné
        return $user->isClient() && $devis->chantier->client_id === $user->id;
    }

    /**
     * Peut refuser le devis (côté client)
     */
    public function refuser(User $user, Devis $devis): bool
    {
        // Le devis doit pouvoir être refusé
        if (!$devis->peutEtreAccepte()) {
            return false;
        }

        // Admin : autorisé
        if ($user->isAdmin()) {
            return true;
        }

        // Client du chantier concerné
        return $user->isClient() && $devis->chantier->client_id === $user->id;
    }

    /**
     * Peut convertir le devis en facture
     */
    public function convertir(User $user, Devis $devis): bool
    {
        // Le devis doit pouvoir être converti
        if (!$devis->peutEtreConverti()) {
            return false;
        }

        // Admin : autorisé
        if ($user->isAdmin()) {
            return true;
        }

        // Commercial qui a créé le devis
        return $user->isCommercial() && $devis->commercial_id === $user->id;
    }

    /**
     * Peut dupliquer le devis
     */
    public function dupliquer(User $user, Devis $devis): bool
    {
        // Admin : autorisé
        if ($user->isAdmin()) {
            return true;
        }

        // Commercial qui a créé le devis ou commercial du chantier
        return $user->isCommercial() && 
               ($devis->commercial_id === $user->id || $devis->chantier->commercial_id === $user->id);
    }

    /**
     * Peut télécharger le PDF du devis
     */
    public function downloadPdf(User $user, Devis $devis): bool
    {
        // Même autorisation que pour voir le devis
        return $this->view($user, $devis);
    }

    /**
     * Peut signer électroniquement le devis
     */
    public function signer(User $user, Devis $devis): bool
    {
        // Le devis doit pouvoir être accepté
        if (!$devis->peutEtreAccepte()) {
            return false;
        }

        // Seul le client peut signer
        return $user->isClient() && $devis->chantier->client_id === $user->id;
    }
}