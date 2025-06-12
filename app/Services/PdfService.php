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
        $devis->load(['chantier.client', 'commercial', 'lignes']);

        $data = [
            'devis' => $devis,
            'entreprise' => config('devis.entreprise'),
            'config' => config('devis.pdf'),
        ];

        $html = View::make('pdf.devis', $data)->render();

        $pdf = Pdf::loadHTML($html);
        
        // Configuration du PDF
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOptions([
            'dpi' => config('devis.pdf.options.dpi', 96),
            'defaultFont' => config('devis.pdf.police.famille', 'Arial'),
            'isRemoteEnabled' => config('devis.pdf.options.enable_remote', true),
            'isPhpEnabled' => config('devis.pdf.options.enable_php', false),
            'isJavascriptEnabled' => config('devis.pdf.options.enable_javascript', false),
        ]);

        return $pdf->output();
    }

    /**
     * Générer le PDF d'une facture
     */
    public function genererFacturePdf(Facture $facture): string
    {
        $facture->load(['chantier.client', 'commercial', 'lignes', 'paiements']);

        $data = [
            'facture' => $facture,
            'entreprise' => config('devis.entreprise'),
            'config' => config('devis.pdf'),
        ];

        $html = View::make('pdf.facture', $data)->render();

        $pdf = Pdf::loadHTML($html);
        
        // Configuration du PDF
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOptions([
            'dpi' => config('devis.pdf.options.dpi', 96),
            'defaultFont' => config('devis.pdf.police.famille', 'Arial'),
            'isRemoteEnabled' => config('devis.pdf.options.enable_remote', true),
            'isPhpEnabled' => config('devis.pdf.options.enable_php', false),
            'isJavascriptEnabled' => config('devis.pdf.options.enable_javascript', false),
        ]);

        return $pdf->output();
    }

    /**
     * Générer un récapitulatif des paiements
     */
    public function genererRecapitulatifPaiements(Facture $facture): string
    {
        $facture->load(['chantier.client', 'paiements' => function($query) {
            $query->where('statut', 'valide')->orderBy('date_paiement');
        }]);

        $data = [
            'facture' => $facture,
            'entreprise' => config('devis.entreprise'),
            'config' => config('devis.pdf'),
        ];

        $html = View::make('pdf.recapitulatif-paiements', $data)->render();

        $pdf = Pdf::loadHTML($html);
        $pdf->setPaper('A4', 'portrait');

        return $pdf->output();
    }

    /**
     * Créer un aperçu HTML (pour debug)
     */
    public function previewDevisHtml(Devis $devis): string
    {
        $devis->load(['chantier.client', 'commercial', 'lignes']);

        $data = [
            'devis' => $devis,
            'entreprise' => config('devis.entreprise'),
            'config' => config('devis.pdf'),
        ];

        return View::make('pdf.devis', $data)->render();
    }

    /**
     * Créer un aperçu HTML d'une facture (pour debug)
     */
    public function previewFactureHtml(Facture $facture): string
    {
        $facture->load(['chantier.client', 'commercial', 'lignes', 'paiements']);

        $data = [
            'facture' => $facture,
            'entreprise' => config('devis.entreprise'),
            'config' => config('devis.pdf'),
        ];

        return View::make('pdf.facture', $data)->render();
    }
}