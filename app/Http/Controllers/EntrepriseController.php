<?php
// app/Http/Controllers/EntrepriseController.php

namespace App\Http\Controllers;

use App\Models\EntrepriseSettings;
use App\Models\Devis;
use App\Models\Facture;
use App\Services\PdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class EntrepriseController extends Controller
{
    protected $pdfService;

    public function __construct(PdfService $pdfService)
    {
        $this->middleware(['auth', 'role:admin']);
        $this->pdfService = $pdfService;
    }

    /**
     * Afficher le formulaire de paramètres entreprise
     */
    public function settings()
    {
        $settings = EntrepriseSettings::getSettings();
        
        return view('admin.entreprise.settings', compact('settings'));
    }

    /**
     * Sauvegarder les paramètres entreprise
     */
    public function store(Request $request)
    {
        try {
            // Validation des données
            $validated = EntrepriseSettings::validateSettings($request->all());
            
            // Traitement du logo
            if ($request->hasFile('logo')) {
                $validated['logo'] = $this->handleLogoUpload($request->file('logo'));
            }
            
            // Mise à jour ou création des paramètres
            EntrepriseSettings::updateSettings($validated);
            
            // Clear du cache de configuration si vous en utilisez un
            if (function_exists('cache')) {
                cache()->forget('entreprise_settings');
            }
            
            return redirect()
                ->route('admin.entreprise.settings')
                ->with('success', 'Paramètres de l\'entreprise mis à jour avec succès.');
                
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()
                ->withErrors($e->validator)
                ->withInput();
                
        } catch (\Exception $e) {
            return back()
                ->with('error', 'Erreur lors de la sauvegarde : ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Gérer l'upload du logo
     */
    private function handleLogoUpload($file): string
    {
        // Supprimer l'ancien logo
        $settings = EntrepriseSettings::first();
        if ($settings && $settings->logo && Storage::disk('public')->exists($settings->logo)) {
            Storage::disk('public')->delete($settings->logo);
        }
        
        // Générer un nom unique pour le fichier
        $filename = 'logo-entreprise-' . Str::random(10) . '.' . $file->getClientOriginalExtension();
        
        // Stocker le nouveau logo
        $path = $file->storeAs('entreprise/logos', $filename, 'public');
        
        return $path;
    }

    /**
     * Générer un aperçu PDF avec les paramètres actuels
     */
    public function previewPdf(Request $request)
    {
        try {
            // Récupérer les données du formulaire ou les paramètres existants
            if ($request->has('preview_data')) {
                $settings = json_decode($request->preview_data, true);
            } else {
                $settings = array_merge($request->all(), EntrepriseSettings::getSettings());
            }
            
            // Créer un devis fictif pour l'aperçu
            $devisFictif = $this->createSampleDevis($settings);
            
            // Générer le PDF
            $pdf = $this->generatePreviewPdf($devisFictif, $settings);
            
            return response($pdf)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="apercu-devis-entreprise.pdf"');
                
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de la génération de l\'aperçu : ' . $e->getMessage());
        }
    }

    /**
     * Créer un devis d'exemple pour l'aperçu
     */
    private function createSampleDevis(array $settings): object
    {
        return (object) [
            'numero' => 'DEV-' . date('Y') . '-001',
            'titre' => 'Exemple de devis',
            'description' => 'Ceci est un exemple de devis généré avec vos paramètres entreprise.',
            'date_emission' => now(),
            'date_validite' => now()->addDays(30),
            'statut' => 'envoye',
            'statut_texte' => 'Envoyé',
            'montant_ht' => 1000.00,
            'montant_tva' => 200.00,
            'montant_ttc' => 1200.00,
            'taux_tva' => $settings['taux_tva_defaut'] ?? 20.00,
            'delai_realisation' => 15,
            'modalites_paiement' => $settings['modalites_paiement_defaut'] ?? 'Paiement à 30 jours',
            'conditions_generales' => $settings['conditions_generales_defaut'] ?? '',
            'client_nom' => 'Client Exemple SARL',
            'client_info' => [
                'nom' => 'Client Exemple SARL',
                'adresse' => '123 Rue de la Paix\n75001 Paris',
                'email' => 'contact@client-exemple.fr',
                'telephone' => '01 23 45 67 89'
            ],
            'chantier' => (object) [
                'titre' => 'Chantier d\'exemple',
                'client' => (object) ['name' => 'Client Exemple SARL']
            ],
            'commercial' => (object) [
                'name' => 'Commercial Exemple'
            ],
            'lignes' => collect([
                (object) [
                    'ordre' => 1,
                    'designation' => 'Prestation exemple 1',
                    'description' => 'Description détaillée de la prestation',
                    'unite' => 'h',
                    'quantite' => 10.00,
                    'prix_unitaire_ht' => 50.00,
                    'taux_tva' => $settings['taux_tva_defaut'] ?? 20.00,
                    'remise_pourcentage' => 0,
                    'montant_ht' => 500.00,
                    'montant_tva' => 100.00,
                    'montant_ttc' => 600.00,
                ],
                (object) [
                    'ordre' => 2,
                    'designation' => 'Prestation exemple 2',
                    'description' => 'Autre prestation avec remise',
                    'unite' => 'unité',
                    'quantite' => 1.00,
                    'prix_unitaire_ht' => 600.00,
                    'taux_tva' => $settings['taux_tva_defaut'] ?? 20.00,
                    'remise_pourcentage' => 10,
                    'montant_ht' => 540.00,
                    'montant_tva' => 108.00,
                    'montant_ttc' => 648.00,
                ]
            ])
        ];
    }

    /**
     * Générer le PDF d'aperçu
     */
    private function generatePreviewPdf($devis, array $settings): string
    {
        // Adapter les settings au format attendu par la vue PDF
        $entreprise = [
            'nom' => $settings['nom'] ?? 'Votre Entreprise',
            'adresse' => $settings['adresse'] ?? '',
            'code_postal' => $settings['code_postal'] ?? '',
            'ville' => $settings['ville'] ?? '',
            'telephone' => $settings['telephone'] ?? '',
            'email' => $settings['email'] ?? '',
            'siret' => $settings['siret'] ?? '',
            'tva_intracommunautaire' => $settings['tva_intracommunautaire'] ?? '',
            'logo' => null, // Pour l'aperçu, on ne gère pas le logo
        ];

        $config = [
            'couleur_principale' => $settings['couleur_principale'] ?? '#2563eb',
        ];

        $data = compact('devis', 'entreprise', 'config');

        // Utiliser la vue PDF des devis
        $html = view('pdf.devis', $data)->render();

        // Générer le PDF avec DomPDF
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html);
        $pdf->setPaper('A4', 'portrait');

        return $pdf->output();
    }

    /**
     * Exporter les paramètres en JSON
     */
    public function export()
    {
        try {
            $settings = EntrepriseSettings::getSettings();
            
            // Supprimer les données sensibles
            unset($settings['logo']);
            
            $filename = 'parametres-entreprise-' . date('Y-m-d') . '.json';
            
            return response()->json($settings)
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
                
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de l\'export : ' . $e->getMessage());
        }
    }

    /**
     * Importer des paramètres depuis un fichier JSON
     */
    public function import(Request $request)
    {
        $request->validate([
            'settings_file' => 'required|file|mimes:json|max:1024'
        ]);

        try {
            $content = file_get_contents($request->file('settings_file')->getPathname());
            $importedSettings = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Fichier JSON invalide');
            }

            // Validation des données importées
            $validated = EntrepriseSettings::validateSettings($importedSettings);
            
            // Mise à jour des paramètres
            EntrepriseSettings::updateSettings($validated);

            return redirect()
                ->route('admin.entreprise.settings')
                ->with('success', 'Paramètres importés avec succès.');

        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de l\'import : ' . $e->getMessage());
        }
    }

    /**
     * Réinitialiser les paramètres aux valeurs par défaut
     */
    public function reset(Request $request)
    {
        $request->validate([
            'confirmation' => 'required|in:RESET'
        ], [
            'confirmation.required' => 'Vous devez taper "RESET" pour confirmer',
            'confirmation.in' => 'Vous devez taper "RESET" pour confirmer'
        ]);

        try {
            // Supprimer l'ancien logo
            $settings = EntrepriseSettings::first();
            if ($settings && $settings->logo) {
                Storage::disk('public')->delete($settings->logo);
            }

            // Réinitialiser avec les valeurs par défaut
            EntrepriseSettings::updateSettings(EntrepriseSettings::getDefaultSettings());

            return redirect()
                ->route('admin.entreprise.settings')
                ->with('success', 'Paramètres réinitialisés aux valeurs par défaut.');

        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de la réinitialisation : ' . $e->getMessage());
        }
    }

    /**
     * Vérifier la configuration de l'entreprise
     */
    public function checkConfiguration()
    {
        $isConfigured = EntrepriseSettings::isConfigured();
        $settings = EntrepriseSettings::getSettings();
        
        $missingFields = [];
        $required = ['nom', 'adresse', 'telephone', 'email', 'siret'];
        
        foreach ($required as $field) {
            if (empty($settings[$field])) {
                $missingFields[] = $field;
            }
        }

        return response()->json([
            'configured' => $isConfigured,
            'missing_fields' => $missingFields,
            'settings' => $settings
        ]);
    }
}