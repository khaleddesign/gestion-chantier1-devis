<?php
// app/Http/Controllers/PaiementController.php

namespace App\Http\Controllers;

use App\Models\Paiement;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaiementController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Mettre à jour un paiement
     */
    public function update(Request $request, Paiement $paiement)
    {
        $this->authorize('gererPaiements', $paiement->facture);

        if (!$paiement->peutEtreModifie()) {
            return back()->with('error', 'Ce paiement ne peut plus être modifié.');
        }

        $validated = $request->validate([
            'montant' => 'required|numeric|min:0.01',
            'date_paiement' => 'required|date|before_or_equal:today',
            'mode_paiement' => 'required|in:virement,cheque,especes,cb,prelevement,autre',
            'reference_paiement' => 'nullable|string|max:255',
            'banque' => 'nullable|string|max:255',
            'commentaire' => 'nullable|string|max:1000',
        ]);

        try {
            $paiement->update($validated);

            // Mettre à jour la facture
            $paiement->facture->mettreAJourPaiements();

            return back()->with('success', 'Paiement modifié avec succès.');

        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de la modification : ' . $e->getMessage());
        }
    }

    /**
     * Supprimer un paiement
     */
    public function destroy(Paiement $paiement)
    {
        $this->authorize('gererPaiements', $paiement->facture);

        if ($paiement->statut === 'valide') {
            return back()->with('error', 'Un paiement validé ne peut pas être supprimé.');
        }

        try {
            $facture = $paiement->facture;
            $paiement->delete();

            // La facture sera mise à jour automatiquement grâce aux événements du modèle

            return back()->with('success', 'Paiement supprimé avec succès.');

        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de la suppression : ' . $e->getMessage());
        }
    }

    /**
     * Valider un paiement
     */
    public function valider(Paiement $paiement)
    {
        $this->authorize('gererPaiements', $paiement->facture);

        if ($paiement->statut !== 'en_attente') {
            return back()->with('error', 'Seuls les paiements en attente peuvent être validés.');
        }

        try {
            $paiement->valider();

            // Notification si la facture est maintenant entièrement payée
            if ($paiement->facture->fresh()->estPayee()) {
                Notification::creerNotification(
                    $paiement->facture->chantier->client_id,
                    $paiement->facture->chantier_id,
                    'facture_payee',
                    'Paiement confirmé',
                    "Votre paiement pour la facture '{$paiement->facture->numero}' a été confirmé. Merci !"
                );
            }

            return back()->with('success', 'Paiement validé avec succès.');

        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de la validation : ' . $e->getMessage());
        }
    }

    /**
     * Rejeter un paiement
     */
    public function rejeter(Request $request, Paiement $paiement)
    {
        $this->authorize('gererPaiements', $paiement->facture);

        if ($paiement->statut !== 'en_attente') {
            return back()->with('error', 'Seuls les paiements en attente peuvent être rejetés.');
        }

        $validated = $request->validate([
            'raison' => 'nullable|string|max:1000',
        ]);

        try {
            $paiement->rejeter($validated['raison']);

            // Notification au client si nécessaire
            if ($paiement->facture->chantier->client_id !== Auth::id()) {
                Notification::creerNotification(
                    $paiement->facture->chantier->client_id,
                    $paiement->facture->chantier_id,
                    'paiement_rejete',
                    'Paiement rejeté',
                    "Le paiement pour la facture '{$paiement->facture->numero}' a été rejeté." . 
                    ($validated['raison'] ? " Raison : {$validated['raison']}" : '')
                );
            }

            return back()->with('success', 'Paiement rejeté.');

        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors du rejet : ' . $e->getMessage());
        }
    }
}