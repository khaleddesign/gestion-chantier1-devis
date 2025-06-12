<?php

namespace App\Http\Controllers;

use App\Models\Chantier;
use App\Models\Document;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;

class DocumentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function store(Request $request, Chantier $chantier)
    {
        $this->authorize('update', $chantier);
        
        $request->validate([
            'fichiers' => 'required|array|max:10',
            'fichiers.*' => [
                'required',
                'file',
                'max:10240', // 10MB
                File::types(['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx'])
                    ->max(10 * 1024), // 10MB en KB
            ],
            'description' => 'nullable|string|max:1000',
            'type' => 'required|in:image,document,plan,facture,autre',
        ], [
            'fichiers.*.max' => 'Chaque fichier ne doit pas dépasser 10 MB.',
            'fichiers.*.mimes' => 'Format de fichier non autorisé.',
        ]);

        $documentsUploades = [];
        $errors = [];

        foreach ($request->file('fichiers') as $index => $fichier) {
            try {
                // Vérification supplémentaire du MIME type réel
                $mimeType = $fichier->getMimeType();
                $allowedMimes = [
                    'image/jpeg', 'image/png', 'image/gif',
                    'application/pdf',
                    'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                ];

                if (!in_array($mimeType, $allowedMimes)) {
                    $errors[] = "Le fichier {$fichier->getClientOriginalName()} a un type MIME non autorisé: {$mimeType}";
                    continue;
                }

                // Génération d'un nom de fichier sécurisé
                $nomOriginal = $fichier->getClientOriginalName();
                $extension = $fichier->getClientOriginalExtension();
                $nomFichier = Str::uuid() . '.' . $extension;
                
                // Stockage sécurisé
                $chemin = $fichier->storeAs(
                    'documents/' . $chantier->id, 
                    $nomFichier, 
                    'public'
                );
                
                if (!$chemin) {
                    $errors[] = "Erreur lors de l'upload de {$nomOriginal}";
                    continue;
                }
                
                $document = Document::create([
                    'chantier_id' => $chantier->id,
                    'user_id' => Auth::id(),
                    'nom_original' => $nomOriginal,
                    'nom_fichier' => $nomFichier,
                    'chemin' => $chemin,
                    'type_mime' => $mimeType,
                    'taille' => $fichier->getSize(),
                    'description' => $request->description,
                    'type' => $request->type,
                ]);
                
                $documentsUploades[] = $document;
                
            } catch (\Exception $e) {
                $errors[] = "Erreur lors du traitement de {$fichier->getClientOriginalName()}: " . $e->getMessage();
            }
        }

        // Notification au client si des documents ont été uploadés
        if (count($documentsUploades) > 0 && Auth::user()->isCommercial()) {
            Notification::creerNotification(
                $chantier->client_id,
                $chantier->id,
                'nouveau_document',
                'Nouveaux documents ajoutés',
                count($documentsUploades) . " nouveau(x) document(s) ont été ajoutés au chantier '{$chantier->titre}'"
            );
        }

        $message = count($documentsUploades) . ' document(s) uploadé(s) avec succès.';
        if (!empty($errors)) {
            $message .= ' Erreurs: ' . implode(', ', $errors);
        }

        return redirect()->route('chantiers.show', $chantier)
                        ->with(count($documentsUploades) > 0 ? 'success' : 'warning', $message);
    }

    /**
     * 🔧 VERSION CORRIGÉE - Télécharger un document
     */
    public function download(Document $document)
    {
        // Vérifier les autorisations
        $this->authorize('view', $document->chantier);
        
        // Log de la tentative de téléchargement
        Log::info('Tentative de téléchargement', [
            'document_id' => $document->id,
            'nom_original' => $document->nom_original,
            'chemin_stocke' => $document->chemin,
            'user_id' => Auth::id()
        ]);
        
        // 📁 Recherche intelligente du fichier
        $cheminFichier = $this->trouverFichier($document);
        
        if (!$cheminFichier) {
            Log::error('Fichier non trouvé', [
                'document_id' => $document->id,
                'chemin_original' => $document->chemin,
                'storage_files' => Storage::disk('public')->allFiles()
            ]);
            
            abort(404, 'Le fichier demandé est introuvable sur le serveur.');
        }
        
        try {
            // 📥 Téléchargement sécurisé
            $cheminComplet = Storage::disk('public')->path($cheminFichier);
            
            // Vérifier que le fichier existe physiquement
            if (!file_exists($cheminComplet)) {
                Log::error('Fichier physique inexistant', [
                    'document_id' => $document->id,
                    'chemin_calcule' => $cheminComplet
                ]);
                abort(404, 'Fichier physique non trouvé.');
            }
            
            // Mettre à jour le chemin si nécessaire
            if ($document->chemin !== $cheminFichier) {
                $document->update(['chemin' => $cheminFichier]);
                Log::info('Chemin de document mis à jour', [
                    'document_id' => $document->id,
                    'ancien_chemin' => $document->chemin,
                    'nouveau_chemin' => $cheminFichier
                ]);
            }
            
            // Retourner le fichier
            return response()->download(
                $cheminComplet,
                $document->nom_original,
                [
                    'Content-Type' => $document->type_mime ?: 'application/octet-stream'
                ]
            );
            
        } catch (\Exception $e) {
            Log::error('Erreur lors du téléchargement', [
                'document_id' => $document->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            abort(500, 'Erreur lors du téléchargement du fichier.');
        }
    }

    /**
     * 🔍 Recherche intelligente d'un fichier
     */
    private function trouverFichier(Document $document): ?string
    {
        // Chemins possibles à tester
        $cheminsPossibles = [
            $document->chemin, // Chemin original en base
            'documents/' . $document->nom_fichier, // Dans le dossier documents
            'documents/' . $document->chantier_id . '/' . $document->nom_fichier, // Avec ID chantier
            $document->nom_fichier, // Juste le nom du fichier
        ];
        
        // Éliminer les doublons
        $cheminsPossibles = array_unique($cheminsPossibles);
        
        foreach ($cheminsPossibles as $chemin) {
            if (Storage::disk('public')->exists($chemin)) {
                Log::info('Fichier trouvé', [
                    'document_id' => $document->id,
                    'chemin_trouve' => $chemin,
                    'chemin_original' => $document->chemin
                ]);
                return $chemin;
            }
        }
        
        // Recherche par nom de fichier dans tous les dossiers
        $tousLesFichiers = Storage::disk('public')->allFiles();
        foreach ($tousLesFichiers as $fichier) {
            if (basename($fichier) === $document->nom_fichier) {
                Log::info('Fichier trouvé par recherche globale', [
                    'document_id' => $document->id,
                    'chemin_trouve' => $fichier
                ]);
                return $fichier;
            }
        }
        
        return null;
    }

    /**
     * 🗑️ Supprimer un document
     */
    public function destroy(Document $document)
    {
        $this->authorize('update', $document->chantier);
        
        // Tentative de suppression du fichier physique
        $cheminFichier = $this->trouverFichier($document);
        if ($cheminFichier && Storage::disk('public')->exists($cheminFichier)) {
            Storage::disk('public')->delete($cheminFichier);
        }
        
        $document->delete();
        
        return redirect()->route('chantiers.show', $document->chantier)
                        ->with('success', 'Document supprimé avec succès.');
    }

    /**
     * 🔧 Méthode pour corriger les chemins des documents (admin uniquement)
     */
    public function fixPaths()
    {
        // Vérifier les permissions (admin seulement)
        if (!Auth::user() || !Auth::user()->isAdmin()) {
            abort(403, 'Accès non autorisé');
        }
        
        $documents = Document::all();
        $corriges = 0;
        $erreurs = [];
        
        foreach ($documents as $document) {
            $cheminCorrect = $this->trouverFichier($document);
            
            if ($cheminCorrect && $cheminCorrect !== $document->chemin) {
                $document->update(['chemin' => $cheminCorrect]);
                $corriges++;
            } elseif (!$cheminCorrect) {
                $erreurs[] = [
                    'id' => $document->id,
                    'nom' => $document->nom_original,
                    'chemin' => $document->chemin
                ];
            }
        }
        
        return response()->json([
            'success' => true,
            'total_documents' => $documents->count(),
            'corriges' => $corriges,
            'erreurs' => $erreurs
        ]);
    }

    /**
     * 👁️ Afficher un document dans le navigateur (pour PDF/images)
     */
    public function view(Document $document)
    {
        $this->authorize('view', $document->chantier);
        
        $cheminFichier = $this->trouverFichier($document);
        
        if (!$cheminFichier) {
            abort(404, 'Fichier non trouvé');
        }
        
        try {
            $contenu = Storage::disk('public')->get($cheminFichier);
            $typeMime = Storage::disk('public')->mimeType($cheminFichier) ?: $document->type_mime;
            
            return Response::make($contenu, 200, [
                'Content-Type' => $typeMime,
                'Content-Disposition' => 'inline; filename="' . $document->nom_original . '"',
                'Cache-Control' => 'public, max-age=3600', // Cache 1 heure
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erreur affichage document', [
                'document_id' => $document->id,
                'error' => $e->getMessage()
            ]);
            
            abort(500, 'Erreur lors de l\'affichage du document');
        }
    }

    /**
     * 📊 API: Liste des documents d'un chantier (JSON)
     */
    public function apiList(Chantier $chantier)
    {
        $this->authorize('view', $chantier);
        
        $documents = $chantier->documents()->orderBy('created_at', 'desc')->get()->map(function($document) {
            return [
                'id' => $document->id,
                'nom_original' => $document->nom_original,
                'description' => $document->description,
                'type' => $document->type,
                'taille_formatee' => $document->getTailleFormatee(),
                'icone' => $document->getIconeType(),
                'date_upload' => $document->created_at->format('d/m/Y H:i'),
                'download_url' => route('documents.download', $document),
                'view_url' => $document->isImage() || $document->type_mime === 'application/pdf' 
                    ? route('documents.view', $document) 
                    : null,
                'user' => $document->user ? $document->user->name : 'Système'
            ];
        });
        
        return response()->json([
            'success' => true,
            'documents' => $documents,
            'total' => $documents->count()
        ]);
    }

    /**
     * 📦 Télécharger tous les documents d'un chantier (ZIP)
     */
    public function downloadAll(Chantier $chantier)
    {
        $this->authorize('view', $chantier);
        
        $documents = $chantier->documents;
        
        if ($documents->isEmpty()) {
            return redirect()->back()->with('warning', 'Aucun document à télécharger.');
        }
        
        try {
            $zip = new \ZipArchive();
            $zipFileName = 'chantier_' . $chantier->id . '_documents_' . now()->format('Y-m-d_H-i-s') . '.zip';
            $zipPath = storage_path('app/temp/' . $zipFileName);
            
            // Créer le dossier temp s'il n'existe pas
            if (!file_exists(dirname($zipPath))) {
                mkdir(dirname($zipPath), 0755, true);
            }
            
            if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== TRUE) {
                throw new \Exception('Impossible de créer le fichier ZIP');
            }
            
            $addedFiles = 0;
            foreach ($documents as $document) {
                $cheminFichier = $this->trouverFichier($document);
                
                if ($cheminFichier) {
                    $cheminComplet = Storage::disk('public')->path($cheminFichier);
                    if (file_exists($cheminComplet)) {
                        $zip->addFile($cheminComplet, $document->nom_original);
                        $addedFiles++;
                    }
                }
            }
            
            $zip->close();
            
            if ($addedFiles === 0) {
                if (file_exists($zipPath)) {
                    unlink($zipPath);
                }
                return redirect()->back()->with('error', 'Aucun fichier disponible pour le téléchargement.');
            }
            
            // Programmer la suppression du fichier ZIP après téléchargement
            $this->scheduleZipDeletion($zipPath);
            
            return response()->download($zipPath, $zipFileName)->deleteFileAfterSend(true);
            
        } catch (\Exception $e) {
            Log::error('Erreur création ZIP', [
                'chantier_id' => $chantier->id,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()->with('error', 'Erreur lors de la création de l\'archive.');
        }
    }

    /**
     * 🧹 Programmer la suppression d'un fichier ZIP temporaire
     */
    private function scheduleZipDeletion($zipPath)
    {
        // Supprimer le fichier après 1 heure
        \Illuminate\Support\Facades\Artisan::call('schedule:work', [
            '--stop-when-empty' => true
        ]);
        
        // Alternative : suppression immédiate en arrière-plan (optionnel)
        if (function_exists('fastcgi_finish_request')) {
            register_shutdown_function(function() use ($zipPath) {
                sleep(30); // Attendre 30 secondes
                if (file_exists($zipPath)) {
                    unlink($zipPath);
                }
            });
        }
    }

    /**
     * 🔧 Nettoyage des fichiers orphelins (admin uniquement)
     */
    public function cleanupOrphanedFiles()
    {
        if (!Auth::user() || !Auth::user()->isAdmin()) {
            abort(403);
        }
        
        $directories = Storage::disk('public')->directories('documents');
        $deletedFiles = 0;
        $deletedDirectories = 0;
        
        foreach ($directories as $dir) {
            $chantierId = basename($dir);
            
            // Vérifier si le chantier existe encore
            if (!Chantier::find($chantierId)) {
                Storage::disk('public')->deleteDirectory($dir);
                $deletedDirectories++;
                continue;
            }
            
            // Vérifier les fichiers dans le dossier
            $files = Storage::disk('public')->files($dir);
            foreach ($files as $file) {
                $filename = basename($file);
                
                // Vérifier si le document existe en base
                if (!Document::where('nom_fichier', $filename)->exists()) {
                    Storage::disk('public')->delete($file);
                    $deletedFiles++;
                }
            }
        }
        
        // Nettoyer les fichiers ZIP temporaires
        $tempPath = storage_path('app/temp/');
        if (file_exists($tempPath)) {
            $tempFiles = glob($tempPath . 'chantier_*_documents_*.zip');
            foreach ($tempFiles as $tempFile) {
                if (filemtime($tempFile) < time() - 3600) { // Plus vieux que 1 heure
                    unlink($tempFile);
                    $deletedFiles++;
                }
            }
        }
        
        return response()->json([
            'success' => true,
            'deleted_files' => $deletedFiles,
            'deleted_directories' => $deletedDirectories,
            'message' => "{$deletedFiles} fichiers et {$deletedDirectories} dossiers orphelins supprimés."
        ]);
    }

    /**
     * 🔍 Debug: Informations détaillées sur le stockage (local uniquement)
     */
    public function debugInfo()
    {
        if (!app()->environment('local') || !Auth::user()->isAdmin()) {
            abort(403);
        }
        
        $storageInfo = [
            'storage_path' => storage_path('app/public/'),
            'public_path' => public_path('storage/'),
            'storage_link_exists' => is_link(public_path('storage')),
            'storage_link_target' => is_link(public_path('storage')) ? readlink(public_path('storage')) : null,
            'storage_writable' => is_writable(storage_path('app/public/')),
            'public_writable' => is_writable(public_path('storage/')),
        ];
        
        $files = Storage::disk('public')->allFiles();
        $directories = Storage::disk('public')->allDirectories();
        
        $documentsInfo = Document::select('id', 'nom_original', 'nom_fichier', 'chemin', 'chantier_id')
            ->with('chantier:id,titre')
            ->get()
            ->map(function($doc) {
                return [
                    'id' => $doc->id,
                    'nom_original' => $doc->nom_original,
                    'chemin' => $doc->chemin,
                    'fichier_existe' => Storage::disk('public')->exists($doc->chemin),
                    'chantier' => $doc->chantier ? $doc->chantier->titre : 'Supprimé'
                ];
            });
        
        return response()->json([
            'storage_info' => $storageInfo,
            'directories' => $directories,
            'files' => array_slice($files, 0, 50), // Limiter à 50 pour éviter la surcharge
            'total_files' => count($files),
            'documents_db' => $documentsInfo,
            'routes' => [
                'download' => route('documents.download', ['document' => 1]),
                'view' => url('/documents/1/view'),
                'api_list' => url('/api/chantiers/1/documents'),
            ]
        ]);
    }
}