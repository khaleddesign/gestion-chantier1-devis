<?php
// app/Policies/FacturePolicy.php

namespace App\Policies;

use App\Models\User;
use App\Models\Facture;
use App\Models\Chantier;

class FacturePolicy
{
    /**
     * Peut voir la liste des factures d'un chantier
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
     * Peut voir une facture spécifique
     */
    public function view(User $user, Facture $facture): bool
    {
        // Admin : toujours autorisé
        if ($user->isAdmin()) {
            return true;
        }

        // Commercial qui a créé la facture
        if ($user->isCommercial() && $facture->commercial_id === $user->id) {
            return true;
        }

        // Client du chantier concerné
        if ($user->isClient() && $facture->chantier->client_id === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Peut créer une facture
     */
    public function create(User $user, Chantier $chantier): bool
    {
        // Seuls admin et commerciaux peuvent créer des factures
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
     * Peut modifier une facture
     */
    public function update(User $user, Facture $facture): bool
    {
        // Une facture payée ne peut plus être modifiée
        if ($facture->estPayee()) {
            return false;
        }

        // Admin : autorisé (sauf si payée)
        if ($user->isAdmin()) {
            return true;
        }

        // Commercial qui a créé la facture (et seulement si brouillon)
        return $user->isCommercial() && 
               $facture->commercial_id === $user->id && 
               $facture->statut === 'brouillon';
    }

    /**
     * Peut supprimer une facture
     */
    public function delete(User $user, Facture $facture): bool
    {
        // Ne peut pas supprimer une facture payée ou avec des paiements
        if ($facture->estPayee() || $facture->paiements()->exists()) {
            return false;
        }

        // Admin : autorisé
        if ($user->isAdmin()) {
            return true;
        }

        // Commercial qui a créé la facture (et seulement si brouillon)
        return $user->isCommercial() && 
               $facture->commercial_id === $user->id && 
               $facture->statut === 'brouillon';
    }

    /**
     * Peut envoyer la facture au client
     */
    public function envoyer(User $user, Facture $facture): bool
    {
        // Seule une facture en brouillon peut être envoyée
        if ($facture->statut !== 'brouillon') {
            return false;
        }

        // Admin : autorisé
        if ($user->isAdmin()) {
            return true;
        }

        // Commercial qui a créé la facture
        return $user->isCommercial() && $facture->commercial_id === $user->id;
    }

    /**
     * Peut annuler une facture
     */
    public function annuler(User $user, Facture $facture): bool
    {
        // La facture doit pouvoir être annulée
        if (!$facture->peutEtreAnnulee()) {
            return false;
        }

        // Admin : autorisé
        if ($user->isAdmin()) {
            return true;
        }

        // Commercial qui a créé la facture
        return $user->isCommercial() && $facture->commercial_id === $user->id;
    }

    /**
     * Peut gérer les paiements d'une facture
     */
    public function gererPaiements(User $user, Facture $facture): bool
    {
        // Admin : toujours autorisé
        if ($user->isAdmin()) {
            return true;
        }

        // Commercial qui a créé la facture
        return $user->isCommercial() && $facture->commercial_id === $user->id;
    }

    /**
     * Peut ajouter un paiement
     */
    public function ajouterPaiement(User $user, Facture $facture): bool
    {
        // La facture ne doit pas être annulée
        if ($facture->statut === 'annulee') {
            return false;
        }

        return $this->gererPaiements($user, $facture);
    }

    /**
     * Peut envoyer une relance
     */
    public function envoyerRelance(User $user, Facture $facture): bool
    {
        // La facture doit être en retard ou impayée
        if (!in_array($facture->statut, ['envoyee', 'payee_partiel', 'en_retard'])) {
            return false;
        }

        // Admin : autorisé
        if ($user->isAdmin()) {
            return true;
        }

        // Commercial qui a créé la facture
        return $user->isCommercial() && $facture->commercial_id === $user->id;
    }

    /**
     * Peut télécharger le PDF de la facture
     */
    public function downloadPdf(User $user, Facture $facture): bool
    {
        // Même autorisation que pour voir la facture
        return $this->view($user, $facture);
    }

    /**
     * Peut générer un récapitulatif des paiements
     */
    public function voirRecapitulatifPaiements(User $user, Facture $facture): bool
    {
        // Même autorisation que pour voir la facture
        return $this->view($user, $facture);
    }

    /**
     * Peut dupliquer la facture
     */
    public function dupliquer(User $user, Facture $facture): bool
    {
        // Admin : autorisé
        if ($user->isAdmin()) {
            return true;
        }

        // Commercial qui a créé la facture ou commercial du chantier
        return $user->isCommercial() && 
               ($facture->commercial_id === $user->id || $facture->chantier->commercial_id === $user->id);
    }
}