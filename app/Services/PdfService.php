<?php
// app/Services/PdfService.php - VERSION AMÉLIORÉE

namespace App\Services;

use App\Models\Devis;
use App\Models\Facture;
use App\Models\EntrepriseSettings;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Log;

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
            'entreprise' => $this->getEntrepriseData(),
            'config' => $this->getPdfConfig(),
        ];

        $html = View::make('pdf.devis', $data)->render();

        return $this->generatePdf($html);
    }

    /**
     * Générer le PDF d'une facture
     */
    public function genererFacturePdf(Facture $facture): string
    {
        $facture->load(['chantier.client', 'commercial', 'lignes', 'paiements']);

        $data = [
            'facture' => $facture,
            'entreprise' => $this->getEntrepriseData(),
            'config' => $this->getPdfConfig(),
        ];

        $html = View::make('pdf.facture', $data)->render();

        return $this->generatePdf($html);
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
            'entreprise' => $this->getEntrepriseData(),
            'config' => $this->getPdfConfig(),
        ];

        $html = View::make('pdf.recapitulatif-paiements', $data)->render();

        return $this->generatePdf($html);
    }

    /**
     * Générer une lettre de relance
     */
    public function genererLettreRelance(Facture $facture): string
    {
        $facture->load(['chantier.client', 'commercial']);

        $data = [
            'facture' => $facture,
            'entreprise' => $this->getEntrepriseData(),
            'config' => $this->getPdfConfig(),
            'penalites' => calculer_penalites_retard(
                $facture->montant_restant, 
                $facture->date_echeance
            ),
            'niveau_relance' => determiner_niveau_relance(
                now()->diffInDays($facture->date_echeance)
            ),
        ];

        $html = View::make('pdf.lettre-relance', $data)->render();

        return $this->generatePdf($html);
    }

    /**
     * Générer un bordereau de remise
     */
    public function genererBordereauRemise(array $factures, \Carbon\Carbon $dateRemise): string
    {
        $data = [
            'factures' => collect($factures)->load('chantier.client'),
            'date_remise' => $dateRemise,
            'entreprise' => $this->getEntrepriseData(),
            'config' => $this->getPdfConfig(),
            'totaux' => [
                'nombre_factures' => count($factures),
                'montant_total' => collect($factures)->sum('montant_ttc'),
                'montant_ht' => collect($factures)->sum('montant_ht'),
                'montant_tva' => collect($factures)->sum('montant_tva'),
            ],
        ];

        $html = View::make('pdf.bordereau-remise', $data)->render();

        return $this->generatePdf($html);
    }

    /**
     * Générer un rapport de chiffre d'affaires
     */
    public function genererRapportCA(array $periode, array $donnees): string
    {
        $data = [
            'periode' => $periode,
            'donnees' => $donnees,
            'entreprise' => $this->getEntrepriseData(),
            'config' => $this->getPdfConfig(),
            'date_generation' => now(),
        ];

        $html = View::make('pdf.rapport-ca', $data)->render();

        return $this->generatePdf($html, 'landscape'); // Format paysage pour les rapports
    }

    /**
     * Récupérer les données de l'entreprise pour les PDF
     */
    private function getEntrepriseData(): array
    {
        try {
            $settings = app('entreprise.settings');
            
            // S'assurer que le logo a le bon chemin pour les PDF
            if (!empty($settings['logo'])) {
                $settings['logo'] = storage_path('app/public/' . $settings['logo']);
            }
            
            return $settings;
            
        } catch (\Exception $e) {
            Log::warning('Erreur lors de la récupération des paramètres entreprise pour PDF: ' . $e->getMessage());
            
            // Retourner les valeurs par défaut en cas d'erreur
            return config('entreprise.defaut');
        }
    }

    /**
     * Récupérer la configuration PDF
     */
    private function getPdfConfig(): array
    {
        return config('entreprise.pdf', [
            'format' => 'A4',
            'orientation' => 'portrait',
            'options' => [
                'dpi' => 96,
                'default_font' => 'Arial',
                'enable_remote' => true,
                'enable_php' => false,
                'enable_javascript' => false,
            ],
        ]);
    }

    /**
     * Générer le PDF avec DomPDF
     */
    private function generatePdf(string $html, string $orientation = 'portrait'): string
    {
        try {
            $config = $this->getPdfConfig();
            
            $pdf = Pdf::loadHTML($html);
            
            // Configuration du PDF
            $pdf->setPaper($config['format'], $orientation);
            $pdf->setOptions($config['options']);
            
            return $pdf->output();
            
        } catch (\Exception $e) {
            Log::error('Erreur lors de la génération PDF: ' . $e->getMessage());
            throw new \Exception('Impossible de générer le PDF: ' . $e->getMessage());
        }
    }

    /**
     * Créer un aperçu HTML (pour debug)
     */
    public function previewDevisHtml(Devis $devis): string
    {
        $devis->load(['chantier.client', 'commercial', 'lignes']);

        $data = [
            'devis' => $devis,
            'entreprise' => $this->getEntrepriseData(),
            'config' => $this->getPdfConfig(),
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
            'entreprise' => $this->getEntrepriseData(),
            'config' => $this->getPdfConfig(),
        ];

        return View::make('pdf.facture', $data)->render();
    }

    /**
     * Valider qu'un PDF peut être généré
     */
    public function validatePdfGeneration(): array
    {
        $errors = [];
        $warnings = [];
        
        // Vérifier la configuration entreprise
        if (!entreprise_configured()) {
            $errors[] = 'Les paramètres de l\'entreprise ne sont pas configurés';
        }
        
        // Vérifier les champs essentiels
        $required_fields = ['nom', 'adresse', 'telephone', 'email'];
        foreach ($required_fields as $field) {
            if (empty(entreprise($field))) {
                $errors[] = "Le champ '{$field}' de l'entreprise n'est pas renseigné";
            }
        }
        
        // Vérifier le logo
        $logoPath = entreprise_logo_path();
        if ($logoPath && !file_exists($logoPath)) {
            $warnings[] = 'Le fichier logo est introuvable, il ne sera pas affiché sur les PDF';
        }
        
        // Vérifier DomPDF
        if (!class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            $errors[] = 'Le package DomPDF n\'est pas installé';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings
        ];
    }

    /**
     * Obtenir les formats de PDF disponibles
     */
    public function getAvailableFormats(): array
    {
        return [
            'A4' => 'A4 (210 × 297 mm)',
            'A3' => 'A3 (297 × 420 mm)',
            'A5' => 'A5 (148 × 210 mm)',
            'Letter' => 'Letter (216 × 279 mm)',
            'Legal' => 'Legal (216 × 356 mm)',
        ];
    }

    /**
     * Obtenir les orientations disponibles
     */
    public function getAvailableOrientations(): array
    {
        return [
            'portrait' => 'Portrait',
            'landscape' => 'Paysage',
        ];
    }

    /**
     * Tester la génération PDF avec des données factices
     */
    public function testPdfGeneration(): array
    {
        try {
            // Valider la configuration
            $validation = $this->validatePdfGeneration();
            if (!$validation['valid']) {
                return $validation;
            }
            
            // Créer un devis fictif
            $devisFictif = $this->createSampleDevis();
            
            // Tenter de générer le PDF
            $pdf = $this->genererDevisPdf($devisFictif);
            
            return [
                'valid' => true,
                'message' => 'Test de génération PDF réussi',
                'pdf_size' => strlen($pdf),
                'errors' => [],
                'warnings' => $validation['warnings'] ?? []
            ];
            
        } catch (\Exception $e) {
            return [
                'valid' => false,
                'message' => 'Échec du test de génération PDF',
                'errors' => [$e->getMessage()],
                'warnings' => []
            ];
        }
    }

    /**
     * Créer un devis d'exemple pour les tests
     */
    private function createSampleDevis(): Devis
    {
        $devis = new Devis([
            'numero' => 'TEST-' . date('Y') . '-001',
            'titre' => 'Devis test',
            'description' => 'Devis généré pour tester la configuration PDF',
            'date_emission' => now(),
            'date_validite' => now()->addDays(30),
            'statut' => 'brouillon',
            'montant_ht' => 1000.00,
            'montant_tva' => 200.00,
            'montant_ttc' => 1200.00,
            'taux_tva' => 20.00,
            'modalites_paiement' => 'Test',
        ]);

        // Simuler les relations
        $devis->setRelation('chantier', (object) [
            'titre' => 'Chantier test',
            'client' => (object) ['name' => 'Client Test']
        ]);

        $devis->setRelation('commercial', (object) [
            'name' => 'Commercial Test'
        ]);

        $devis->setRelation('lignes', collect([
            (object) [
                'ordre' => 1,
                'designation' => 'Prestation test',
                'description' => 'Description test',
                'unite' => 'h',
                'quantite' => 10.00,
                'prix_unitaire_ht' => 50.00,
                'taux_tva' => 20.00,
                'remise_pourcentage' => 0,
                'montant_ht' => 500.00,
                'montant_tva' => 100.00,
                'montant_ttc' => 600.00,
            ]
        ]));

        // Ajouter les propriétés dynamiques
        $devis->client_nom = 'Client Test';
        $devis->client_info = [
            'nom' => 'Client Test',
            'adresse' => 'Adresse test',
            'email' => 'test@example.com',
            'telephone' => '01 23 45 67 89'
        ];
        $devis->statut_texte = 'Brouillon';

        return $devis;
    }
}