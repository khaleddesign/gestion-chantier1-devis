<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ChantierController;
use App\Http\Controllers\EtapeController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\CommentaireController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Page d'accueil - Redirect vers dashboard ou login
Route::get('/', function () {
    return Auth::check() ? redirect()->route('dashboard') : redirect()->route('login');
});

// Routes d'authentification manuelles (Laravel UI style)
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login')->middleware('guest');
Route::post('/login', [LoginController::class, 'login'])->middleware('guest');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Routes d'inscription (optionnelles)
Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register')->middleware('guest');
Route::post('/register', [RegisterController::class, 'register'])->middleware('guest');

// Routes de réinitialisation de mot de passe (optionnelles)
Route::get('/password/reset', function () {
    return view('auth.passwords.email');
})->name('password.request')->middleware('guest');

Route::post('/password/email', function (Illuminate\Http\Request $request) {
    $request->validate(['email' => 'required|email']);
    
    // Ici vous pouvez implémenter la logique d'envoi d'email
    // Pour l'instant, on retourne juste un message
    return back()->with('status', 'Si cette adresse email existe, vous recevrez un lien de réinitialisation.');
})->name('password.email')->middleware('guest');

// Routes protégées par l'authentification
Route::middleware(['auth'])->group(function () {
    
    // Dashboard principal (route vers le bon dashboard selon le rôle)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/home', [DashboardController::class, 'index'])->name('home'); // Fallback pour Laravel UI
    
    // ✅ ROUTES SPÉCIFIQUES AVANT LE RESOURCE (ordre CRITIQUE !)
    Route::get('chantiers/export', [ChantierController::class, 'export'])->name('chantiers.export');
    Route::get('chantiers/calendrier/view', [ChantierController::class, 'calendrier'])->name('chantiers.calendrier');
    Route::get('chantiers/search', [ChantierController::class, 'search'])->name('chantiers.search');
    
    // ✅ RESOURCE ROUTE APRÈS (pour éviter les conflits)
    Route::resource('chantiers', ChantierController::class);
    
    // Routes spécifiques avec paramètres (après le resource)
    Route::get('chantiers/{chantier}/etapes', [ChantierController::class, 'etapes'])->name('chantiers.etapes');
    
    // Gestion des étapes (nested routes)
    Route::prefix('chantiers/{chantier}')->group(function () {
        Route::post('etapes', [EtapeController::class, 'store'])->name('etapes.store');
        Route::put('etapes/{etape}', [EtapeController::class, 'update'])->name('etapes.update');
        Route::delete('etapes/{etape}', [EtapeController::class, 'destroy'])->name('etapes.destroy');
        Route::post('etapes/{etape}/toggle', [EtapeController::class, 'toggleComplete'])->name('etapes.toggle');
        Route::put('etapes/{etape}/progress', [EtapeController::class, 'updateProgress'])->name('etapes.progress');
        Route::post('etapes/reorder', [EtapeController::class, 'reorder'])->name('etapes.reorder');
        Route::get('etapes/json', [EtapeController::class, 'getEtapes'])->name('etapes.json');
    });
    
    // Gestion des documents
    Route::prefix('chantiers/{chantier}')->group(function () {
        Route::post('documents', [DocumentController::class, 'store'])->name('documents.store');
    });
   
    Route::delete('documents/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');
    
    // Gestion des commentaires
    Route::prefix('chantiers/{chantier}')->group(function () {
        Route::post('commentaires', [CommentaireController::class, 'store'])->name('commentaires.store');
    });
    Route::delete('commentaires/{commentaire}', [CommentaireController::class, 'destroy'])->name('commentaires.destroy');
    
    // Notifications
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('notifications.index');
        Route::post('{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
        Route::post('mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    });
});

// Routes admin uniquement
Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('admin.index');
    Route::get('dashboard', [AdminController::class, 'index'])->name('admin.dashboard'); // Alias
    
    // Gestion des utilisateurs
    Route::get('users', [AdminController::class, 'users'])->name('admin.users');
    Route::get('users/create', [AdminController::class, 'createUser'])->name('admin.users.create');
    Route::post('users', [AdminController::class, 'storeUser'])->name('admin.users.store');
    Route::get('users/{user}', [AdminController::class, 'showUser'])->name('admin.users.show');
    Route::get('users/{user}/edit', [AdminController::class, 'editUser'])->name('admin.users.edit');
    Route::put('users/{user}', [AdminController::class, 'updateUser'])->name('admin.users.update');
    Route::delete('users/{user}', [AdminController::class, 'destroyUser'])->name('admin.users.destroy');
    Route::patch('users/{user}/toggle', [AdminController::class, 'toggleUser'])->name('admin.users.toggle');
    
    // Actions en lot et export
    Route::post('users/bulk-action', [AdminController::class, 'bulkAction'])->name('admin.users.bulk-action');
    Route::get('users/export', [AdminController::class, 'exportUsers'])->name('admin.users.export');
    
    // Statistiques
    Route::get('statistics', [AdminController::class, 'statistics'])->name('admin.statistics');
    
    // Nettoyage des fichiers orphelins (admin seulement)
    Route::post('cleanup/files', [DocumentController::class, 'cleanupOrphanedFiles'])->name('admin.cleanup.files');
});

// 🚀 NOUVELLES ROUTES POUR LE DASHBOARD CLIENT
Route::middleware(['auth'])->group(function () {
    
    // ✅ Routes pour les fonctionnalités du dashboard client
    
    // Notation d'un chantier
    Route::post('/chantiers/{chantier}/notation', function(Illuminate\Http\Request $request, App\Models\Chantier $chantier) {
        // Vérifier que l'utilisateur est bien le client de ce chantier
        if (Auth::user()->id !== $chantier->client_id && !Auth::user()->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Non autorisé'], 403);
        }
        
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'commentaire' => 'nullable|string|max:1000'
        ]);
        
        // Pour l'instant, on simule l'enregistrement
        // Vous pouvez créer une table "notations" plus tard si nécessaire
        \Illuminate\Support\Facades\Log::info('Notation reçue', [
            'chantier_id' => $chantier->id,
            'user_id' => Auth::id(),
            'rating' => $validated['rating'],
            'commentaire' => $validated['commentaire']
        ]);
        
        // Créer une notification pour le commercial
        App\Models\Notification::create([
            'user_id' => $chantier->commercial_id,
            'chantier_id' => $chantier->id,
            'type' => 'nouvelle_notation',
            'titre' => 'Nouvelle évaluation client',
            'message' => "Le client " . Auth::user()->name . " a évalué le chantier '{$chantier->titre}' avec " . $validated['rating'] . " étoiles."
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Notation enregistrée avec succès'
        ]);
    })->name('chantiers.notation');
    
    // Documents d'un chantier (API JSON)
    Route::get('/api/chantiers/{chantier}/documents', function(App\Models\Chantier $chantier) {
        // Vérifier les permissions
        if (Auth::user()->id !== $chantier->client_id && 
            Auth::user()->id !== $chantier->commercial_id && 
            !Auth::user()->isAdmin()) {
            return response()->json(['error' => 'Non autorisé'], 403);
        }
        
        $documents = $chantier->documents->map(function($document) {
            return [
                'id' => $document->id,
                'nom_original' => $document->nom_original,
                'description' => $document->description,
                'taille_formatee' => $document->getTailleFormatee(),
                'icone' => $document->getIconeType(),
                'date_upload' => $document->created_at->format('d/m/Y'),
                'download_url' => route('documents.download', $document)
            ];
        });
        
        return response()->json([
            'success' => true,
            'documents' => $documents
        ]);
    })->name('api.chantiers.documents');
    
    // Informations d'un commercial (API JSON)
    Route::get('/api/commercial/{user}', function(App\Models\User $user) {
        if ($user->role !== 'commercial') {
            return response()->json(['error' => 'Utilisateur non trouvé'], 404);
        }
        
        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'telephone' => $user->telephone
        ]);
    })->name('api.commercial.info');
    
    // Demande de rappel
    Route::post('/api/rappel/demander', function(Illuminate\Http\Request $request) {
        $validated = $request->validate([
            'commercial_id' => 'nullable|exists:users,id',
            'message' => 'nullable|string|max:500'
        ]);
        
        $commercialId = $validated['commercial_id'] ?? 
            Auth::user()->chantiersClient()->first()?->commercial_id ?? 
            App\Models\User::where('role', 'commercial')->first()?->id;
        
        if ($commercialId) {
            // Créer une notification pour le commercial
            App\Models\Notification::create([
                'user_id' => $commercialId,
                'chantier_id' => null,
                'type' => 'demande_rappel',
                'titre' => 'Demande de rappel',
                'message' => "Le client " . Auth::user()->name . " demande un rappel. Message: " . ($validated['message'] ?? 'Aucun message spécifique.')
            ]);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Demande de rappel enregistrée'
        ]);
    })->name('api.rappel.demander');
    
    // Avancement des chantiers (pour le rafraîchissement auto)
    Route::get('/api/dashboard/avancement', function() {
        $user = Auth::user();
        
        if ($user->isClient()) {
            $chantiers = $user->chantiersClient()->select('id', 'avancement_global')->get();
        } elseif ($user->isCommercial()) {
            $chantiers = $user->chantiersCommercial()->select('id', 'avancement_global')->get();
        } else {
            $chantiers = App\Models\Chantier::select('id', 'avancement_global')->get();
        }
        
        return response()->json([
            'success' => true,
            'chantiers' => $chantiers
        ]);
    })->name('api.dashboard.avancement');
});

// Routes API pour les appels AJAX (sécurisées) - EXISTANTES
Route::middleware(['auth'])->prefix('api')->group(function () {
    Route::get('chantiers/{chantier}/avancement', function (App\Models\Chantier $chantier) {
        // Vérification des autorisations
        if (!auth()->user()->can('view', $chantier)) {
            abort(403, 'Accès non autorisé');
        }
        
        return response()->json([
            'avancement' => $chantier->avancement_global,
            'etapes' => $chantier->etapes->map(function ($etape) {
                return [
                    'id' => $etape->id,
                    'nom' => $etape->nom,
                    'pourcentage' => $etape->pourcentage,
                    'terminee' => $etape->terminee,
                ];
            }),
        ]);
    })->name('api.chantiers.avancement');
    
    Route::get('notifications/count', function () {
        $count = auth()->user()->getNotificationsNonLues();
        return response()->json(['count' => $count]);
    })->name('api.notifications.count');
    
    // API pour les statistiques (admin seulement)
    Route::middleware(['role:admin'])->get('admin/stats', function () {
        return response()->json([
            'total_users' => \App\Models\User::count(),
            'total_chantiers' => \App\Models\Chantier::count(),
            'chantiers_actifs' => \App\Models\Chantier::where('statut', 'en_cours')->count(),
            'chantiers_termines' => \App\Models\Chantier::where('statut', 'termine')->count(),
            'chantiers_en_retard' => \App\Models\Chantier::whereDate('date_fin_prevue', '<', now())
                ->where('statut', '!=', 'termine')
                ->count(),
        ]);
    })->name('api.admin.stats');
    
    // API pour la recherche de chantiers
    Route::get('chantiers/search', function (Illuminate\Http\Request $request) {
        $query = $request->get('q', '');
        $user = auth()->user();
        
        $chantiersQuery = \App\Models\Chantier::query();
        
        // Filtrage selon le rôle
        if ($user->isCommercial()) {
            $chantiersQuery->where('commercial_id', $user->id);
        } elseif ($user->isClient()) {
            $chantiersQuery->where('client_id', $user->id);
        }
        
        $chantiers = $chantiersQuery->where(function ($q) use ($query) {
            $q->where('titre', 'like', "%{$query}%")
                ->orWhere('description', 'like', "%{$query}%");
        })
            ->with(['client', 'commercial'])
            ->limit(10)
            ->get()
            ->map(function ($chantier) {
                return [
                    'id' => $chantier->id,
                    'titre' => $chantier->titre,
                    'description' => $chantier->description,
                    'client' => $chantier->client->name,
                    'commercial' => $chantier->commercial->name,
                    'statut' => $chantier->statut,
                    'url' => route('chantiers.show', $chantier),
                ];
            });
        
        return response()->json($chantiers);
    })->name('api.chantiers.search');
});

// Routes d'erreur personnalisées
Route::fallback(function () {
    if (request()->expectsJson()) {
        return response()->json(['error' => 'Route non trouvée'], 404);
    }
    return response()->view('errors.404', [], 404);
});

// Routes de test (à supprimer en production)
if (app()->environment('local')) {
    Route::get('/test-email', function () {
        return view('emails.notification', [
            'notification' => \App\Models\Notification::first() ?? new \App\Models\Notification(),
            'user' => \App\Models\User::first() ?? new \App\Models\User(),
            'chantier' => \App\Models\Chantier::first() ?? new \App\Models\Chantier(),
        ]);
    });
    
    // 🧪 Route de test pour le dashboard client
    Route::get('/test-dashboard', function () {
        if (!Auth::check()) {
            return redirect()->route('login');
        }
        
        return view('dashboard.client', [
            'mes_chantiers' => Auth::user()->chantiersClient,
            'notifications' => Auth::user()->notifications()->latest()->limit(5)->get()
        ]);
    })->middleware('auth');
}

Route::middleware(['auth'])->group(function () {
    
    // 📁 Routes de téléchargement des documents (versions alternatives)
    Route::get('documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');
    Route::get('documents/{document}/view', [DocumentController::class, 'view'])->name('documents.view');
    Route::delete('documents/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');
    
    // 📦 Téléchargement groupé
    Route::get('chantiers/{chantier}/documents/download-all', [DocumentController::class, 'downloadAll'])->name('chantiers.documents.download-all');
    
    // 📊 API pour les documents
    Route::get('api/chantiers/{chantier}/documents', [DocumentController::class, 'apiList'])->name('api.chantiers.documents');
    
    // 🔧 Routes de maintenance (admin uniquement)
    Route::middleware(['role:admin'])->group(function () {
        Route::post('admin/documents/fix-paths', [DocumentController::class, 'fixPaths'])->name('admin.documents.fix-paths');
        Route::post('admin/documents/cleanup', [DocumentController::class, 'cleanupOrphanedFiles'])->name('admin.documents.cleanup');
        
        // 🔍 Debug (environnement local uniquement)
        if (app()->environment('local')) {
            Route::get('debug/documents', [DocumentController::class, 'debugInfo'])->name('debug.documents');
        }
    });
});