<?php
// app/Services/PdfService.php

namespace App\Services;

use App\Models\Devis;
use App\Models\Facture;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\View;

class PdfService
{
    /**
     * Générer le PDF d'un devis
     */
    public function genererDevisPdf(Devis $devis): string
    {
        $data = $this->preparerDonneesDevis($devis);
        
        $html = View::make('pdf.devis', $data)->render();
        
        $pdf = Pdf::loadHTML($html)
            ->setPaper('A4', 'portrait')
            ->setOptions([
                'defaultFont' => 'Arial',
                'isRemoteEnabled' => true,
                'isHtml5ParserEnabled' => true,
                'chroot' => public_path(),
            ]);

        return $pdf->output();
    }

    /**
     * Générer le PDF d'une facture
     */
    public function genererFacturePdf(Facture $facture): string
    {
        $data = $this->preparerDonneesFacture($facture);
        
        $html = View::make('pdf.facture', $data)->render();
        
        $pdf = Pdf::loadHTML($html)
            ->setPaper('A4', 'portrait')
            ->setOptions([
                'defaultFont' => 'Arial',
                'isRemoteEnabled' => true,
                'isHtml5ParserEnabled' => true,
                'chroot' => public_path(),
            ]);

        return $pdf->output();
    }

    /**
     * Préparer les données pour le PDF du devis
     */
    private function preparerDonneesDevis(Devis $devis): array
    {
        $devis->load(['chantier.client', 'commercial', 'lignes']);

        // Informations de l'entreprise (depuis config)
        $entreprise = [
            'nom' => config('chantiers.company.name', 'Gestion Chantiers'),
            'adresse' => config('chantiers.company.address', ''),
            'telephone' => config('chantiers.company.phone', ''),
            'email' => config('chantiers.company.email', ''),
            'site_web' => config('chantiers.company.website', ''),
            'siret' => config('chantiers.company.siret', ''),
            'tva_intracommunautaire' => config('chantiers.company.tva_intra', ''),
        ];

        // Regrouper les lignes par catégorie
        $lignesParCategorie = $devis->lignes->groupBy('categorie');

        // Calculer les totaux par taux de TVA
        $totauxTva = $devis->lignes->groupBy('taux_tva')->map(function ($lignes) {
            return [
                'montant_ht' => $lignes->sum('montant_ht'),
                'montant_tva' => $lignes->sum('montant_tva'),
                'montant_ttc' => $lignes->sum('montant_ttc'),
            ];
        });

        return [
            'devis' => $devis,
            'chantier' => $devis->chantier,
            'client' => $devis->chantier->client,
            'commercial' => $devis->commercial,
            'entreprise' => $entreprise,
            'lignes' => $devis->lignes,
            'lignes_par_categorie' => $lignesParCategorie,
            'totaux_tva' => $totauxTva,
            'date_generation' => now(),
        ];
    }

    /**
     * Préparer les données pour le PDF de la facture
     */
    private function preparerDonneesFacture(Facture $facture): array
    {
        $facture->load(['chantier.client', 'commercial', 'lignes', 'paiements' => function($query) {
            $query->where('statut', 'valide')->orderBy('date_paiement');
        }]);

        $entreprise = [
            'nom' => config('chantiers.company.name', 'Gestion Chantiers'),
            'adresse' => config('chantiers.company.address', ''),
            'telephone' => config('chantiers.company.phone', ''),
            'email' => config('chantiers.company.email', ''),
            'site_web' => config('chantiers.company.website', ''),
            'siret' => config('chantiers.company.siret', ''),
            'tva_intracommunautaire' => config('chantiers.company.tva_intra', ''),
        ];

        $lignesParCategorie = $facture->lignes->groupBy('categorie');

        $totauxTva = $facture->lignes->groupBy('taux_tva')->map(function ($lignes) {
            return [
                'montant_ht' => $lignes->sum('montant_ht'),
                'montant_tva' => $lignes->sum('montant_tva'),
                'montant_ttc' => $lignes->sum('montant_ttc'),
            ];
        });

        return [
            'facture' => $facture,
            'chantier' => $facture->chantier,
            'client' => $facture->chantier->client,
            'commercial' => $facture->commercial,
            'entreprise' => $entreprise,
            'lignes' => $facture->lignes,
            'lignes_par_categorie' => $lignesParCategorie,
            'totaux_tva' => $totauxTva,
            'paiements' => $facture->paiements,
            'date_generation' => now(),
        ];
    }

    /**
     * Générer un récapitulatif des paiements
     */
    public function genererRecapitulatifPaiements(Facture $facture): string
    {
        $facture->load(['chantier.client', 'paiements' => function($query) {
            $query->orderBy('date_paiement');
        }]);

        $entreprise = [
            'nom' => config('chantiers.company.name', 'Gestion Chantiers'),
            'adresse' => config('chantiers.company.address', ''),
            'telephone' => config('chantiers.company.phone', ''),
            'email' => config('chantiers.company.email', ''),
        ];

        $data = [
            'facture' => $facture,
            'chantier' => $facture->chantier,
            'client' => $facture->chantier->client,
            'entreprise' => $entreprise,
            'paiements' => $facture->paiements,
            'date_generation' => now(),
        ];

        $html = View::make('pdf.recapitulatif-paiements', $data)->render();
        
        $pdf = Pdf::loadHTML($html)
            ->setPaper('A4', 'portrait')
            ->setOptions([
                'defaultFont' => 'Arial',
                'isRemoteEnabled' => true,
                'isHtml5ParserEnabled' => true,
                'chroot' => public_path(),
            ]);

        return $pdf->output();
    }

    /**
     * Générer un rapport de chiffre d'affaires
     */
    public function genererRapportCA(array $factures, array $filtres = []): string
    {
        $totalHT = collect($factures)->sum('montant_ht');
        $totalTTC = collect($factures)->sum('montant_ttc');
        $totalPaye = collect($factures)->sum('montant_paye');

        $facturesParMois = collect($factures)->groupBy(function ($facture) {
            return $facture->date_emission->format('Y-m');
        });

        $facturesParStatut = collect($factures)->groupBy('statut');

        $data = [
            'factures' => $factures,
            'factures_par_mois' => $facturesParMois,
            'factures_par_statut' => $facturesParStatut,
            'filtres' => $filtres,
            'totaux' => [
                'total_ht' => $totalHT,
                'total_ttc' => $totalTTC,
                'total_paye' => $totalPaye,
                'total_impaye' => $totalTTC - $totalPaye,
                'nombre_factures' => count($factures),
            ],
            'date_generation' => now(),
        ];

        $html = View::make('pdf.rapport-ca', $data)->render();
        
        $pdf = Pdf::loadHTML($html)
            ->setPaper('A4', 'landscape')
            ->setOptions([
                'defaultFont' => 'Arial',
                'isRemoteEnabled' => true,
                'isHtml5ParserEnabled' => true,
                'chroot' => public_path(),
            ]);

        return $pdf->output();
    }

    /**
     * Générer une lettre de relance
     */
    public function genererLettreRelance(Facture $facture, int $numeroRelance = 1): string
    {
        $facture->load(['chantier.client', 'commercial']);

        $entreprise = [
            'nom' => config('chantiers.company.name', 'Gestion Chantiers'),
            'adresse' => config('chantiers.company.address', ''),
            'telephone' => config('chantiers.company.phone', ''),
            'email' => config('chantiers.company.email', ''),
        ];

        $joursRetard = now()->diffInDays($facture->date_echeance);

        $data = [
            'facture' => $facture,
            'chantier' => $facture->chantier,
            'client' => $facture->chantier->client,
            'commercial' => $facture->commercial,
            'entreprise' => $entreprise,
            'numero_relance' => $numeroRelance,
            'jours_retard' => $joursRetard,
            'date_generation' => now(),
        ];

        $html = View::make('pdf.lettre-relance', $data)->render();
        
        $pdf = Pdf::loadHTML($html)
            ->setPaper('A4', 'portrait')
            ->setOptions([
                'defaultFont' => 'Arial',
                'isRemoteEnabled' => true,
                'isHtml5ParserEnabled' => true,
                'chroot' => public_path(),
            ]);

        return $pdf->output();
    }

    /**
     * Générer un bordereau de remise de chèques
     */
    public function genererBordereauRemise(array $paiements): string
    {
        $totalRemise = collect($paiements)->sum('montant');
        $nombreCheques = count($paiements);

        $entreprise = [
            'nom' => config('chantiers.company.name', 'Gestion Chantiers'),
            'adresse' => config('chantiers.company.address', ''),
            'rib' => config('chantiers.company.rib', ''),
        ];

        $data = [
            'paiements' => $paiements,
            'entreprise' => $entreprise,
            'total_remise' => $totalRemise,
            'nombre_cheques' => $nombreCheques,
            'date_remise' => now(),
            'date_generation' => now(),
        ];

        $html = View::make('pdf.bordereau-remise', $data)->render();
        
        $pdf = Pdf::loadHTML($html)
            ->setPaper('A4', 'portrait')
            ->setOptions([
                'defaultFont' => 'Arial',
                'isRemoteEnabled' => true,
                'isHtml5ParserEnabled' => true,
                'chroot' => public_path(),
            ]);

        return $pdf->output();
    }
}