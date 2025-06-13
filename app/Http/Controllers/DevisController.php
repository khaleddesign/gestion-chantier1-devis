<?php
// app/Http/Controllers/DevisController.php

namespace App\Http\Controllers;

use App\Models\Chantier;
use App\Models\Devis;
use App\Models\Facture;
use App\Models\Ligne;
use App\Models\Notification;
use App\Services\PdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DevisController extends Controller
{
    protected $pdfService;

    public function __construct(PdfService $pdfService)
    {
        $this->middleware('auth');
        $this->pdfService = $pdfService;
    }

    /**
     * Liste des devis d'un chantier
     */
    public function index(Chantier $chantier)
    {
        $this->authorize('view', $chantier);

        $devis = $chantier->devis()
            ->with(['commercial', 'lignes'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('devis.index', compact('chantier', 'devis'));
    }

    /**
     * Formulaire de création d'un devis
     */
    public function create(Chantier $chantier)
    {
        $this->authorize('update', $chantier);

        // Vérifier que l'utilisateur peut créer des devis
        if (!Auth::user()->isAdmin() && !Auth::user()->isCommercial()) {
            abort(403, 'Seuls les commerciaux et admins peuvent créer des devis.');
        }

        $devis = new Devis([
            'chantier_id' => $chantier->id,
            'commercial_id' => Auth::user()->isCommercial() ? Auth::id() : $chantier->commercial_id,
            'titre' => "Devis pour {$chantier->titre}",
            'client_info' => [
                'nom' => $chantier->client->name,
                'email' => $chantier->client->email,
                'telephone' => $chantier->client->telephone,
                'adresse' => $chantier->client->adresse,
            ],
            'taux_tva' => 20.00,
            'delai_realisation' => 30,
            'modalites_paiement' => 'Paiement à 30 jours fin de mois',
            'conditions_generales' => config('chantiers.devis.conditions_generales_defaut', ''),
        ]);

        return view('devis.create', compact('chantier', 'devis'));
    }

    /**
     * Sauvegarder un nouveau devis
     */
    public function store(Request $request, Chantier $chantier)
    {
        $this->authorize('update', $chantier);

        $validated = $request->validate([
            'titre' => 'required|string|max:255',
            'description' => 'nullable|string',
            'date_validite' => 'required|date|after:today',
            'taux_tva' => 'required|numeric|min:0|max:100',
            'delai_realisation' => 'nullable|integer|min:1',
            'modalites_paiement' => 'nullable|string',
            'conditions_generales' => 'nullable|string',
            'notes_internes' => 'nullable|string',
            
            // Lignes du devis
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
            // Créer le devis
            $devis = $chantier->devis()->create([
                'commercial_id' => Auth::user()->isCommercial() ? Auth::id() : $chantier->commercial_id,
                'titre' => $validated['titre'],
                'description' => $validated['description'],
                'date_validite' => $validated['date_validite'],
                'taux_tva' => $validated['taux_tva'],
                'delai_realisation' => $validated['delai_realisation'],
                'modalites_paiement' => $validated['modalites_paiement'],
                'conditions_generales' => $validated['conditions_generales'],
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
                // Calculer les montants
                $quantite = floatval($ligneData['quantite']);
                $prixUnitaire = floatval($ligneData['prix_unitaire_ht']);
                $tauxTva = floatval($ligneData['taux_tva'] ?? $validated['taux_tva']);
                $remisePourcentage = floatval($ligneData['remise_pourcentage'] ?? 0);
                
                $montantBrut = $quantite * $prixUnitaire;
                $remiseMontant = $montantBrut * ($remisePourcentage / 100);
                $montantHt = $montantBrut - $remiseMontant;
                $montantTva = $montantHt * ($tauxTva / 100);
                $montantTtc = $montantHt + $montantTva;
                
                $devis->lignes()->create([
                    'ordre' => $index + 1,
                    'designation' => $ligneData['designation'],
                    'description' => $ligneData['description'] ?? null,
                    'unite' => $ligneData['unite'],
                    'quantite' => $quantite,
                    'prix_unitaire_ht' => $prixUnitaire,
                    'taux_tva' => $tauxTva,
                    'remise_pourcentage' => $remisePourcentage,
                    'remise_montant' => $remiseMontant,
                    'categorie' => $ligneData['categorie'] ?? null,
                    'montant_ht' => $montantHt,
                    'montant_tva' => $montantTva,
                    'montant_ttc' => $montantTtc,
                ]);
            }

            // Calculer les totaux
            $devis->calculerMontants();

            DB::commit();

            return redirect()
                ->route('chantiers.devis.show', [$chantier, $devis])
                ->with('success', 'Devis créé avec succès.');

        } catch (\Exception $e) {
            DB::rollback();
            return back()
                ->withInput()
                ->with('error', 'Erreur lors de la création du devis : ' . $e->getMessage());
        }
    }

    /**
     * Affichage d'un devis
     */
    public function show(Chantier $chantier, Devis $devis)
    {
        $this->authorize('view', $chantier);
        
        // Vérifier que le devis appartient au chantier
        if ($devis->chantier_id !== $chantier->id) {
            abort(404);
        }

        $devis->load(['commercial', 'lignes', 'facture']);

        return view('devis.show', compact('chantier', 'devis'));
    }

    /**
     * Formulaire d'édition d'un devis
     */
    public function edit(Chantier $chantier, Devis $devis)
    {
        $this->authorize('update', $chantier);
        
        if ($devis->chantier_id !== $chantier->id) {
            abort(404);
        }

        // Vérifier que le devis peut être modifié
        if (!$devis->peutEtreModifie()) {
            return back()->with('error', 'Ce devis ne peut plus être modifié.');
        }

        $devis->load('lignes');

        return view('devis.edit', compact('chantier', 'devis'));
    }

    /**
     * Mettre à jour un devis
     */
    public function update(Request $request, Chantier $chantier, Devis $devis)
    {
        $this->authorize('update', $chantier);

        if ($devis->chantier_id !== $chantier->id) {
            abort(404);
        }

        if (!$devis->peutEtreModifie()) {
            return back()->with('error', 'Ce devis ne peut plus être modifié.');
        }

        $validated = $request->validate([
            'titre' => 'required|string|max:255',
            'description' => 'nullable|string',
            'date_validite' => 'required|date|after:today',
            'taux_tva' => 'required|numeric|min:0|max:100',
            'delai_realisation' => 'nullable|integer|min:1',
            'modalites_paiement' => 'nullable|string',
            'conditions_generales' => 'nullable|string',
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
            // Mettre à jour le devis
            $devis->update([
                'titre' => $validated['titre'],
                'description' => $validated['description'],
                'date_validite' => $validated['date_validite'],
                'taux_tva' => $validated['taux_tva'],
                'delai_realisation' => $validated['delai_realisation'],
                'modalites_paiement' => $validated['modalites_paiement'],
                'conditions_generales' => $validated['conditions_generales'],
                'notes_internes' => $validated['notes_internes'],
            ]);

            // Supprimer les anciennes lignes
            $devis->lignes()->delete();

            // Créer les nouvelles lignes
            foreach ($validated['lignes'] as $index => $ligneData) {
                $devis->lignes()->create([
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
            $devis->calculerMontants();

            DB::commit();

            return redirect()
                ->route('chantiers.devis.show', [$chantier, $devis])
                ->with('success', 'Devis modifié avec succès.');

        } catch (\Exception $e) {
            DB::rollback();
            return back()
                ->withInput()
                ->with('error', 'Erreur lors de la modification : ' . $e->getMessage());
        }
    }

    /**
     * Supprimer un devis
     */
    public function destroy(Chantier $chantier, Devis $devis)
    {
        $this->authorize('update', $chantier);

        if ($devis->chantier_id !== $chantier->id) {
            abort(404);
        }

        // Vérifier que le devis peut être supprimé
        if ($devis->statut === 'accepte' || $devis->facture_id) {
            return back()->with('error', 'Ce devis ne peut pas être supprimé car il a été accepté ou converti en facture.');
        }

        $numero = $devis->numero;
        $devis->delete();

        return redirect()
            ->route('chantiers.devis.index', $chantier)
            ->with('success', "Devis {$numero} supprimé avec succès.");
    }

    /**
     * Envoyer le devis au client
     */
    public function envoyer(Chantier $chantier, Devis $devis)
    {
        $this->authorize('update', $chantier);

        if ($devis->chantier_id !== $chantier->id) {
            abort(404);
        }

        if ($devis->statut !== 'brouillon') {
            return back()->with('error', 'Seuls les devis en brouillon peuvent être envoyés.');
        }

        try {
            // Marquer comme envoyé
            $devis->marquerEnvoye();

            // Notification au client
            Notification::creerNotification(
                $chantier->client_id,
                $chantier->id,
                'nouveau_devis',
                'Nouveau devis reçu',
                "Un nouveau devis '{$devis->titre}' vous a été envoyé pour le chantier '{$chantier->titre}'."
            );

            // TODO: Envoyer email avec PDF en pièce jointe
            // Mail::to($chantier->client->email)->send(new DevisEnvoye($devis));

            return back()->with('success', 'Devis envoyé au client avec succès.');

        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de l\'envoi : ' . $e->getMessage());
        }
    }

    /**
     * Accepter un devis (côté client)
     */
    public function accepter(Request $request, Chantier $chantier, Devis $devis)
    {
        // Vérifier que l'utilisateur est le client du chantier
        if (!Auth::user()->isAdmin() && Auth::id() !== $chantier->client_id) {
            abort(403, 'Seul le client peut accepter ce devis.');
        }

        if ($devis->chantier_id !== $chantier->id) {
            abort(404);
        }

        if (!$devis->peutEtreAccepte()) {
            return back()->with('error', 'Ce devis ne peut plus être accepté (expiré ou déjà traité).');
        }

        // Validation pour signature électronique (optionnel)
        $request->validate([
            'signature' => 'nullable|string',
            'commentaire_client' => 'nullable|string|max:1000',
        ]);

        try {
            // Accepter le devis
            $devis->accepter();

            // Signature électronique si fournie
            if ($request->filled('signature')) {
                $devis->signerElectroniquement(
                    $request->signature,
                    $request->ip()
                );
            }

            // Notification au commercial
            Notification::creerNotification(
                $devis->commercial_id,
                $chantier->id,
                'devis_accepte',
                'Devis accepté',
                "Le client {$chantier->client->name} a accepté le devis '{$devis->titre}'."
            );

            return back()->with('success', 'Devis accepté avec succès. Merci pour votre confiance !');

        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de l\'acceptation : ' . $e->getMessage());
        }
    }

    /**
     * Refuser un devis (côté client)
     */
    public function refuser(Request $request, Chantier $chantier, Devis $devis)
    {
        if (!Auth::user()->isAdmin() && Auth::id() !== $chantier->client_id) {
            abort(403);
        }

        if ($devis->chantier_id !== $chantier->id) {
            abort(404);
        }

        if (!$devis->peutEtreAccepte()) {
            return back()->with('error', 'Ce devis ne peut plus être refusé.');
        }

        $request->validate([
            'raison_refus' => 'nullable|string|max:1000',
        ]);

        try {
            $devis->refuser();

            // Notification au commercial
            Notification::creerNotification(
                $devis->commercial_id,
                $chantier->id,
                'devis_refuse',
                'Devis refusé',
                "Le client {$chantier->client->name} a refusé le devis '{$devis->titre}'." .
                ($request->raison_refus ? " Raison : {$request->raison_refus}" : '')
            );

            return back()->with('success', 'Devis refusé. Votre retour a été transmis.');

        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors du refus : ' . $e->getMessage());
        }
    }

    /**
     * Convertir un devis en facture
     */
    public function convertirEnFacture(Chantier $chantier, Devis $devis)
    {
        $this->authorize('update', $chantier);

        if ($devis->chantier_id !== $chantier->id) {
            abort(404);
        }

        if (!$devis->peutEtreConverti()) {
            return back()->with('error', 'Ce devis ne peut pas être converti en facture.');
        }

        DB::beginTransaction();

        try {
            // Créer la facture
            $facture = Facture::create([
                'chantier_id' => $chantier->id,
                'commercial_id' => $devis->commercial_id,
                'devis_id' => $devis->id,
                'titre' => $devis->titre,
                'description' => $devis->description,
                'client_info' => $devis->client_info,
                'montant_ht' => $devis->montant_ht,
                'montant_tva' => $devis->montant_tva,
                'montant_ttc' => $devis->montant_ttc,
                'taux_tva' => $devis->taux_tva,
                'conditions_reglement' => $devis->modalites_paiement,
                'delai_paiement' => 30,
            ]);

            // Copier les lignes du devis vers la facture
            foreach ($devis->lignes as $ligneDevis) {
                $facture->lignes()->create([
                    'ordre' => $ligneDevis->ordre,
                    'designation' => $ligneDevis->designation,
                    'description' => $ligneDevis->description,
                    'unite' => $ligneDevis->unite,
                    'quantite' => $ligneDevis->quantite,
                    'prix_unitaire_ht' => $ligneDevis->prix_unitaire_ht,
                    'taux_tva' => $ligneDevis->taux_tva,
                    'montant_ht' => $ligneDevis->montant_ht,
                    'montant_tva' => $ligneDevis->montant_tva,
                    'montant_ttc' => $ligneDevis->montant_ttc,
                    'remise_pourcentage' => $ligneDevis->remise_pourcentage,
                    'remise_montant' => $ligneDevis->remise_montant,
                    'categorie' => $ligneDevis->categorie,
                ]);
            }

            // Marquer le devis comme converti
            $devis->update([
                'facture_id' => $facture->id,
                'converted_at' => now(),
            ]);

            // Notification au client
            Notification::creerNotification(
                $chantier->client_id,
                $chantier->id,
                'nouvelle_facture',
                'Nouvelle facture générée',
                "Une facture '{$facture->numero}' a été générée à partir du devis '{$devis->numero}'."
            );

            DB::commit();

            return redirect()
                ->route('chantiers.factures.show', [$chantier, $facture])
                ->with('success', "Devis converti en facture {$facture->numero} avec succès.");

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Erreur lors de la conversion : ' . $e->getMessage());
        }
    }

    /**
     * Télécharger le PDF du devis
     */
    public function downloadPdf(Chantier $chantier, Devis $devis)
    {
        $this->authorize('view', $chantier);

        if ($devis->chantier_id !== $chantier->id) {
            abort(404);
        }

        try {
            $pdf = $this->pdfService->genererDevisPdf($devis);
            
            $filename = "devis_{$devis->numero}.pdf";
            
            return response($pdf)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");

        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de la génération du PDF : ' . $e->getMessage());
        }
    }

    /**
     * Prévisualiser le PDF du devis
     */
    public function previewPdf(Chantier $chantier, Devis $devis)
    {
        $this->authorize('view', $chantier);

        if ($devis->chantier_id !== $chantier->id) {
            abort(404);
        }

        try {
            $pdf = $this->pdfService->genererDevisPdf($devis);
            
            return response($pdf)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline');

        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de la génération du PDF : ' . $e->getMessage());
        }
    }

    /**
     * Dupliquer un devis
     */
    public function dupliquer(Chantier $chantier, Devis $devis)
    {
        $this->authorize('update', $chantier);

        if ($devis->chantier_id !== $chantier->id) {
            abort(404);
        }

        DB::beginTransaction();

        try {
            // Dupliquer le devis
            $nouveauDevis = $devis->replicate([
                'id', 'numero', 'created_at', 'updated_at', 
                'statut', 'date_envoi', 'date_reponse', 
                'signature_client', 'signed_at', 'signature_ip',
                'facture_id', 'converted_at'
            ]);

            $nouveauDevis->titre = $devis->titre . ' (Copie)';
            $nouveauDevis->statut = 'brouillon';
            $nouveauDevis->date_validite = now()->addDays(30);
            $nouveauDevis->save();

            // Dupliquer les lignes
            foreach ($devis->lignes as $ligne) {
                $nouvelleLigne = $ligne->dupliquer();
                $nouveauDevis->lignes()->save($nouvelleLigne);
            }

            // Recalculer les totaux
            $nouveauDevis->calculerMontants();

            DB::commit();

            return redirect()
                ->route('chantiers.devis.edit', [$chantier, $nouveauDevis])
                ->with('success', "Devis dupliqué avec succès ({$nouveauDevis->numero}).");

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Erreur lors de la duplication : ' . $e->getMessage());
        }
    }
}