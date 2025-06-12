<?php
// app/Http/Controllers/FactureController.php

namespace App\Http\Controllers;

use App\Models\Chantier;
use App\Models\Facture;
use App\Models\Ligne;
use App\Models\Paiement;
use App\Models\Notification;
use App\Services\PdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FactureController extends Controller
{
    protected $pdfService;

    public function __construct(PdfService $pdfService)
    {
        $this->middleware('auth');
        $this->pdfService = $pdfService;
    }

    /**
     * Liste des factures d'un chantier
     */
    public function index(Chantier $chantier)
    {
        $this->authorize('viewAny', [Facture::class, $chantier]);

        $factures = $chantier->factures()
            ->with(['commercial', 'devis', 'paiements'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('factures.index', compact('chantier', 'factures'));
    }

    /**
     * Formulaire de création d'une facture
     */
    public function create(Chantier $chantier)
    {
        $this->authorize('create', [Facture::class, $chantier]);

        $facture = new Facture([
            'chantier_id' => $chantier->id,
            'commercial_id' => Auth::user()->isCommercial() ? Auth::id() : $chantier->commercial_id,
            'titre' => "Facture pour {$chantier->titre}",
            'client_info' => [
                'nom' => $chantier->client->name,
                'email' => $chantier->client->email,
                'telephone' => $chantier->client->telephone,
                'adresse' => $chantier->client->adresse,
            ],
            'taux_tva' => 20.00,
            'delai_paiement' => 30,
            'conditions_reglement' => config('devis.factures.defauts.conditions_reglement'),
        ]);

        return view('factures.create', compact('chantier', 'facture'));
    }

    /**
     * Sauvegarder une nouvelle facture
     */
    public function store(Request $request, Chantier $chantier)
    {
        $this->authorize('create', [Facture::class, $chantier]);

        $validated = $request->validate([
            'titre' => 'required|string|max:255',
            'description' => 'nullable|string',
            'date_echeance' => 'required|date|after:today',
            'taux_tva' => 'required|numeric|min:0|max:100',
            'delai_paiement' => 'required|integer|min:1',
            'conditions_reglement' => 'nullable|string',
            'reference_commande' => 'nullable|string|max:255',
            'notes_internes' => 'nullable|string',
            
            // Lignes de la facture
            'lignes' => 'required|array|min:1',
            'lignes.*.designation' => 'required|string|max:255',
            'lignes.*.description' => 'nullable|string',
            'lignes.*.unite' => 'required|string|max:50',
            'lignes.*.quantite' => 'required|numeric|min:0.01',
            'lignes.*.prix_unitaire_ht' => 'required|numeric|min:0',
            'lignes.*.taux_tva' => 'nullable|numeric|min:0|max:100',
            'lignes.*.remise_pourcentage' => 'nullable|numeric|min:0|max:100',
            'lignes.*.categorie' => 'nullable|string|max:100',
        ]);

        DB::beginTransaction();
        
        try {
            // Créer la facture
            $facture = $chantier->factures()->create([
                'commercial_id' => Auth::user()->isCommercial() ? Auth::id() : $chantier->commercial_id,
                'titre' => $validated['titre'],
                'description' => $validated['description'],
                'date_echeance' => $validated['date_echeance'],
                'taux_tva' => $validated['taux_tva'],
                'delai_paiement' => $validated['delai_paiement'],
                'conditions_reglement' => $validated['conditions_reglement'],
                'reference_commande' => $validated['reference_commande'],
                'notes_internes' => $validated['notes_internes'],
                'client_info' => [
                    'nom' => $chantier->client->name,
                    'email' => $chantier->client->email,
                    'telephone' => $chantier->client->telephone,
                    'adresse' => $chantier->client->adresse,
                ],
            ]);

            // Créer les lignes
            foreach ($validated['lignes'] as $index => $ligneData) {
                $facture->lignes()->create([
                    'ordre' => $index + 1,
                    'designation' => $ligneData['designation'],
                    'description' => $ligneData['description'],
                    'unite' => $ligneData['unite'],
                    'quantite' => $ligneData['quantite'],
                    'prix_unitaire_ht' => $ligneData['prix_unitaire_ht'],
                    'taux_tva' => $ligneData['taux_tva'] ?? $validated['taux_tva'],
                    'remise_pourcentage' => $ligneData['remise_pourcentage'] ?? 0,
                    'categorie' => $ligneData['categorie'],
                ]);
            }

            // Calculer les totaux
            $facture->calculerMontants();

            DB::commit();

            return redirect()
                ->route('chantiers.factures.show', [$chantier, $facture])
                ->with('success', 'Facture créée avec succès.');

        } catch (\Exception $e) {
            DB::rollback();
            return back()
                ->withInput()
                ->with('error', 'Erreur lors de la création de la facture : ' . $e->getMessage());
        }
    }

    /**
     * Affichage d'une facture
     */
    public function show(Chantier $chantier, Facture $facture)
    {
        $this->authorize('view', $facture);
        
        if ($facture->chantier_id !== $chantier->id) {
            abort(404);
        }

        $facture->load(['commercial', 'devis', 'lignes', 'paiements' => function($query) {
            $query->orderBy('date_paiement', 'desc');
        }]);

        return view('factures.show', compact('chantier', 'facture'));
    }

    /**
     * Formulaire d'édition d'une facture
     */
    public function edit(Chantier $chantier, Facture $facture)
    {
        $this->authorize('update', $facture);
        
        if ($facture->chantier_id !== $chantier->id) {
            abort(404);
        }

        if ($facture->estPayee()) {
            return back()->with('error', 'Cette facture ne peut plus être modifiée car elle est payée.');
        }

        $facture->load('lignes');

        return view('factures.edit', compact('chantier', 'facture'));
    }

    /**
     * Mettre à jour une facture
     */
    public function update(Request $request, Chantier $chantier, Facture $facture)
    {
        $this->authorize('update', $facture);

        if ($facture->chantier_id !== $chantier->id) {
            abort(404);
        }

        if ($facture->estPayee()) {
            return back()->with('error', 'Cette facture ne peut plus être modifiée.');
        }

        $validated = $request->validate([
            'titre' => 'required|string|max:255',
            'description' => 'nullable|string',
            'date_echeance' => 'required|date',
            'taux_tva' => 'required|numeric|min:0|max:100',
            'delai_paiement' => 'required|integer|min:1',
            'conditions_reglement' => 'nullable|string',
            'reference_commande' => 'nullable|string|max:255',
            'notes_internes' => 'nullable|string',
            
            'lignes' => 'required|array|min:1',
            'lignes.*.designation' => 'required|string|max:255',
            'lignes.*.description' => 'nullable|string',
            'lignes.*.unite' => 'required|string|max:50',
            'lignes.*.quantite' => 'required|numeric|min:0.01',
            'lignes.*.prix_unitaire_ht' => 'required|numeric|min:0',
            'lignes.*.taux_tva' => 'nullable|numeric|min:0|max:100',
            'lignes.*.remise_pourcentage' => 'nullable|numeric|min:0|max:100',
            'lignes.*.categorie' => 'nullable|string|max:100',
        ]);

        DB::beginTransaction();
        
        try {
            // Mettre à jour la facture
            $facture->update([
                'titre' => $validated['titre'],
                'description' => $validated['description'],
                'date_echeance' => $validated['date_echeance'],
                'taux_tva' => $validated['taux_tva'],
                'delai_paiement' => $validated['delai_paiement'],
                'conditions_reglement' => $validated['conditions_reglement'],
                'reference_commande' => $validated['reference_commande'],
                'notes_internes' => $validated['notes_internes'],
            ]);

            // Supprimer les anciennes lignes
            $facture->lignes()->delete();

            // Créer les nouvelles lignes
            foreach ($validated['lignes'] as $index => $ligneData) {
                $facture->lignes()->create([
                    'ordre' => $index + 1,
                    'designation' => $ligneData['designation'],
                    'description' => $ligneData['description'],
                    'unite' => $ligneData['unite'],
                    'quantite' => $ligneData['quantite'],
                    'prix_unitaire_ht' => $ligneData['prix_unitaire_ht'],
                    'taux_tva' => $ligneData['taux_tva'] ?? $validated['taux_tva'],
                    'remise_pourcentage' => $ligneData['remise_pourcentage'] ?? 0,
                    'categorie' => $ligneData['categorie'],
                ]);
            }

            // Recalculer les totaux
            $facture->calculerMontants();

            DB::commit();

            return redirect()
                ->route('chantiers.factures.show', [$chantier, $facture])
                ->with('success', 'Facture modifiée avec succès.');

        } catch (\Exception $e) {
            DB::rollback();
            return back()
                ->withInput()
                ->with('error', 'Erreur lors de la modification : ' . $e->getMessage());
        }
    }

    /**
     * Supprimer une facture
     */
    public function destroy(Chantier $chantier, Facture $facture)
    {
        $this->authorize('delete', $facture);

        if ($facture->chantier_id !== $chantier->id) {
            abort(404);
        }

        if ($facture->estPayee() || $facture->paiements()->exists()) {
            return back()->with('error', 'Cette facture ne peut pas être supprimée car elle est payée ou a des paiements associés.');
        }

        $numero = $facture->numero;
        $facture->delete();

        return redirect()
            ->route('chantiers.factures.index', $chantier)
            ->with('success', "Facture {$numero} supprimée avec succès.");
    }

    /**
     * Envoyer la facture au client
     */
    public function envoyer(Chantier $chantier, Facture $facture)
    {
        $this->authorize('envoyer', $facture);

        if ($facture->chantier_id !== $chantier->id) {
            abort(404);
        }

        if ($facture->statut !== 'brouillon') {
            return back()->with('error', 'Seules les factures en brouillon peuvent être envoyées.');
        }

        try {
            // Marquer comme envoyée
            $facture->marquerEnvoyee();

            // Notification au client
            Notification::creerNotification(
                $chantier->client_id,
                $chantier->id,
                'nouvelle_facture',
                'Nouvelle facture reçue',
                "Une nouvelle facture '{$facture->numero}' vous a été envoyée pour le chantier '{$chantier->titre}'."
            );

            // TODO: Envoyer email avec PDF en pièce jointe

            return back()->with('success', 'Facture envoyée au client avec succès.');

        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de l\'envoi : ' . $e->getMessage());
        }
    }

    /**
     * Annuler une facture
     */
    public function annuler(Chantier $chantier, Facture $facture)
    {
        $this->authorize('annuler', $facture);

        if ($facture->chantier_id !== $chantier->id) {
            abort(404);
        }

        if (!$facture->peutEtreAnnulee()) {
            return back()->with('error', 'Cette facture ne peut pas être annulée.');
        }

        try {
            $facture->update(['statut' => 'annulee']);

            // Notification au client
            Notification::creerNotification(
                $chantier->client_id,
                $chantier->id,
                'facture_annulee',
                'Facture annulée',
                "La facture '{$facture->numero}' a été annulée."
            );

            return back()->with('success', 'Facture annulée avec succès.');

        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de l\'annulation : ' . $e->getMessage());
        }
    }

    /**
     * Ajouter un paiement à une facture
     */
    public function ajouterPaiement(Request $request, Chantier $chantier, Facture $facture)
    {
        $this->authorize('gererPaiements', $facture);

        if ($facture->chantier_id !== $chantier->id) {
            abort(404);
        }

        $validated = $request->validate([
            'montant' => 'required|numeric|min:0.01|max:' . $facture->montant_restant,
            'date_paiement' => 'required|date|before_or_equal:today',
            'mode_paiement' => 'required|in:virement,cheque,especes,cb,prelevement,autre',
            'reference_paiement' => 'nullable|string|max:255',
            'banque' => 'nullable|string|max:255',
            'commentaire' => 'nullable|string|max:1000',
        ]);

        try {
            $paiement = $facture->ajouterPaiement(
                $validated['montant'],
                $validated['mode_paiement'],
                [
                    'date_paiement' => $validated['date_paiement'],
                    'reference' => $validated['reference_paiement'],
                    'banque' => $validated['banque'],
                    'commentaire' => $validated['commentaire'],
                ]
            );

            // Notification au client si la facture est entièrement payée
            if ($facture->fresh()->estPayee()) {
                Notification::creerNotification(
                    $chantier->client_id,
                    $chantier->id,
                    'facture_payee',
                    'Paiement reçu',
                    "Votre paiement pour la facture '{$facture->numero}' a été reçu. Merci !"
                );
            }

            return back()->with('success', 'Paiement ajouté avec succès.');

        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de l\'ajout du paiement : ' . $e->getMessage());
        }
    }

    /**
     * Envoyer une relance
     */
    public function envoyerRelance(Chantier $chantier, Facture $facture)
    {
        $this->authorize('envoyerRelance', $facture);

        if ($facture->chantier_id !== $chantier->id) {
            abort(404);
        }

        try {
            $facture->envoyerRelance();

            // Notification au client
            Notification::creerNotification(
                $chantier->client_id,
                $chantier->id,
                'relance_facture',
                'Rappel de paiement',
                "Rappel : la facture '{$facture->numero}' est en attente de règlement."
            );

            return back()->with('success', 'Relance envoyée avec succès.');

        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de l\'envoi de la relance : ' . $e->getMessage());
        }
    }

    /**
     * Télécharger le PDF de la facture
     */
    public function downloadPdf(Chantier $chantier, Facture $facture)
    {
        $this->authorize('downloadPdf', $facture);

        if ($facture->chantier_id !== $chantier->id) {
            abort(404);
        }

        try {
            $pdf = $this->pdfService->genererFacturePdf($facture);
            
            $filename = "facture_{$facture->numero}.pdf";
            
            return response($pdf)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");

        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de la génération du PDF : ' . $e->getMessage());
        }
    }

    /**
     * Prévisualiser le PDF de la facture
     */
    public function previewPdf(Chantier $chantier, Facture $facture)
    {
        $this->authorize('downloadPdf', $facture);

        if ($facture->chantier_id !== $chantier->id) {
            abort(404);
        }

        try {
            $pdf = $this->pdfService->genererFacturePdf($facture);
            
            return response($pdf)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline');

        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de la génération du PDF : ' . $e->getMessage());
        }
    }

    /**
     * Dupliquer une facture
     */
    public function dupliquer(Chantier $chantier, Facture $facture)
    {
        $this->authorize('dupliquer', $facture);

        if ($facture->chantier_id !== $chantier->id) {
            abort(404);
        }

        DB::beginTransaction();

        try {
            // Dupliquer la facture
            $nouvelleFacture = $facture->replicate([
                'id', 'numero', 'created_at', 'updated_at', 
                'statut', 'date_envoi', 'montant_paye', 'montant_restant',
                'date_paiement_complet', 'nb_relances', 'derniere_relance'
            ]);

            $nouvelleFacture->titre = $facture->titre . ' (Copie)';
            $nouvelleFacture->statut = 'brouillon';
            $nouvelleFacture->date_echeance = now()->addDays($facture->delai_paiement);
            $nouvelleFacture->save();

            // Dupliquer les lignes
            foreach ($facture->lignes as $ligne) {
                $nouvelleLigne = $ligne->dupliquer();
                $nouvelleFacture->lignes()->save($nouvelleLigne);
            }

            // Recalculer les totaux
            $nouvelleFacture->calculerMontants();

            DB::commit();

            return redirect()
                ->route('chantiers.factures.edit', [$chantier, $nouvelleFacture])
                ->with('success', "Facture dupliquée avec succès ({$nouvelleFacture->numero}).");

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Erreur lors de la duplication : ' . $e->getMessage());
        }
    }

    /**
     * Récapitulatif des paiements
     */
    public function recapitulatifPaiements(Chantier $chantier, Facture $facture)
    {
        $this->authorize('view', $facture);

        if ($facture->chantier_id !== $chantier->id) {
            abort(404);
        }

        $facture->load(['paiements' => function($query) {
            $query->orderBy('date_paiement', 'desc');
        }]);

        return view('factures.recapitulatif-paiements', compact('chantier', 'facture'));
    }
}