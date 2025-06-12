<?php
// app/Http/Controllers/AdminController.php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Chantier;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    // --- DASHBOARD ADMIN ---
    public function index()
    {
        $stats = [
            'total_users'           => User::count(),
            'total_chantiers'       => Chantier::count(),
            'chantiers_actifs'      => Chantier::where('statut', 'en_cours')->count(),
            'chantiers_termines'    => Chantier::where('statut', 'termine')->count(),
            'chantiers_en_retard'   => Chantier::whereDate('date_fin_prevue', '<', now())
                                              ->where('statut', '!=', 'termine')
                                              ->count(),
            'notifications_non_lues'=> Notification::where('lu', false)->count(),
            'utilisateurs_actifs'   => User::where('active', true)->count(),
            'avancement_moyen'      => Chantier::avg('avancement_global') ?? 0,
        ];

        $chantiers_recents     = Chantier::with(['client','commercial'])->latest()->take(5)->get();
        $notifications_recentes = Notification::with(['user','chantier'])->latest()->take(10)->get();

        return view('admin.index', compact('stats','chantiers_recents','notifications_recentes'));
    }

    // --- LISTE UTILISATEURS ---
    public function users(Request $request)
    {
        $query = User::query();

        // Filtres
        if ($request->filled('role'))    { $query->where('role', $request->role); }
        if ($request->filled('active'))  { $query->where('active', $request->active === '1'); }
        if ($request->filled('search'))  {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name','like',"%{$search}%")
                  ->orWhere('email','like',"%{$search}%")
                  ->orWhere('telephone','like',"%{$search}%");
            });
        }

        $users = $query->withCount(['chantiersClient','chantiersCommercial'])
                       ->orderBy('name')
                       ->paginate(20)
                       ->withQueryString();

        $stats = [
            'total'       => User::count(),
            'admins'      => User::where('role','admin')->count(),
            'commerciaux' => User::where('role','commercial')->count(),
            'clients'     => User::where('role','client')->count(),
            'actifs'      => User::where('active',true)->count(),
        ];

        return view('admin.users.index', compact('users','stats'));
    }

    public function createUser() { return view('admin.users.create'); }

    public function storeUser(Request $request)
    {
        $request->validate([
            'name'      => ['required','string','max:255'],
            'email'     => ['required','string','email','max:255','unique:users'],
            'password'  => ['required','confirmed', Rules\Password::defaults()],
            'role'      => ['required','in:admin,commercial,client'],
            'telephone' => ['nullable','string','max:20'],
            'adresse'   => ['nullable','string','max:500'],
            'active'    => ['boolean'],
        ]);

        $user = User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'role'      => $request->role,
            'telephone' => $request->telephone,
            'adresse'   => $request->adresse,
            'active'    => $request->boolean('active', true),
        ]);

        // Notification système
        try {
            $data = [
                'user_id'  => $user->id,
                'titre'    => 'Compte créé',
                'message'  => 'Votre compte a été créé avec succès.',
                'type'     => 'compte_cree',
            ];
            if (Schema::hasColumn('notifications','chantier_id')) { $data['chantier_id'] = null; }
            Notification::create($data);
        } catch (\Exception $e) {
            Log::warning('Notification création user échouée : '.$e->getMessage());
        }

        return redirect()->route('admin.users')->with('success','Utilisateur créé.');
    }

    public function showUser(User $user)
    {
        $user->load(['chantiersClient','chantiersCommercial','notifications']);
        $stats = [
            'chantiers_client'      => $user->chantiersClient()->count(),
            'chantiers_commercial'  => $user->chantiersCommercial()->count(),
            'notifications_non_lues'=> $user->notifications()->where('lu',false)->count(),
            'derniere_connexion'    => $user->updated_at->format('d/m/Y H:i'),
        ];
        return view('admin.users.show', compact('user','stats'));
    }

    public function editUser(User $user) { return view('admin.users.edit', compact('user')); }

    public function updateUser(Request $request, User $user)
    {
        $request->validate([
            'name'      => ['required','string','max:255'],
            'email'     => ['required','string','email','max:255','unique:users,email,'.$user->id],
            'password'  => ['nullable','confirmed', Rules\Password::defaults()],
            'role'      => ['required','in:admin,commercial,client'],
            'telephone' => ['nullable','string','max:20'],
            'adresse'   => ['nullable','string','max:500'],
            'active'    => ['boolean'],
        ]);

        // ne pas désactiver le dernier admin actif
        if ($user->isAdmin() && !$request->boolean('active') && User::where('role','admin')->where('active',true)->count() <= 1) {
            return back()->with('error','Impossible de désactiver le dernier administrateur actif.');
        }

        $data = $request->only(['name','email','role','telephone','adresse']);
        $data['active'] = $request->boolean('active', true);
        if ($request->filled('password')) { $data['password'] = Hash::make($request->password); }
        $user->update($data);

        return redirect()->route('admin.users')->with('success','Utilisateur modifié.');
    }

    public function destroyUser(User $user)
    {
        if ($user->isAdmin() && User::where('role','admin')->count() <= 1) {
            return back()->with('error','Impossible de supprimer le dernier administrateur.');
        }
        if ($user->chantiersClient()->exists() || $user->chantiersCommercial()->exists()) {
            return back()->with('error','Utilisateur lié à des chantiers.');
        }
        $name = $user->name;
        $user->delete();
        return redirect()->route('admin.users')->with('success',"Utilisateur {$name} supprimé.");
    }

    public function toggleUser(User $user)
    {
        if ($user->isAdmin() && $user->active && User::where('role','admin')->where('active',true)->count() <= 1) {
            return back()->with('error','Impossible de désactiver le dernier administrateur actif.');
        }
        $user->update(['active'=> !$user->active]);
        return back()->with('success','Statut utilisateur mis à jour.');
    }

    // --- STATISTIQUES AVANCÉES ---
    public function statistics()
    {
        try {
            $users_by_role       = User::selectRaw('role, COUNT(*) as count')->groupBy('role')->pluck('count','role');
            $chantiers_by_status = Chantier::selectRaw('statut, COUNT(*) as count')->groupBy('statut')->pluck('count','statut');
            $chantiers_by_month  = Chantier::selectRaw('MONTH(created_at) as month, COUNT(*) as count')
                                           ->whereYear('created_at', date('Y'))
                                           ->groupBy('month')->pluck('count','month');
            $average_progress    = round(Chantier::avg('avancement_global') ?? 0, 1);
            $users_active_last_month = User::where('updated_at','>=',now()->subMonth())->count();
            $chantiers_en_retard = Chantier::whereDate('date_fin_prevue','<',now())->where('statut','!=','termine')->count();

            $monthly_data = collect(range(1,12))->mapWithKeys(function ($m) use ($chantiers_by_month) {
                return [$m => [
                    'month'           => $m,
                    'total_chantiers' => $chantiers_by_month->get($m,0),
                ]];
            });

            $performance_data = User::where('role','commercial')
                ->leftJoin('chantiers','users.id','=','chantiers.commercial_id')
                ->selectRaw('users.id, users.name, COUNT(chantiers.id) as total_chantiers, AVG(chantiers.avancement_global) as avg_progress')
                ->groupBy('users.id','users.name')
                ->having('total_chantiers','>',0)
                ->orderByDesc('total_chantiers')
                ->limit(10)
                ->get();

            $stats = compact('users_by_role','chantiers_by_status','chantiers_by_month','average_progress',
                            'users_active_last_month','chantiers_en_retard','monthly_data');

            return view('admin.statistics', compact('stats','performance_data'));
        } catch (\Exception $e) {
            Log::error('AdminController@statistics : '.$e->getMessage());
            return view('admin.statistics', [
                'stats'            => collect(),
                'performance_data' => collect(),
            ])->with('error','Erreur chargement statistiques.');
        }
    }

    // --- ACTIONS GROUPE ---
    public function bulkAction(Request $r)
    {
        $r->validate([
            'action'   => ['required','in:activate,deactivate,delete'],
            'user_ids' => ['required','array'],
            'user_ids.*' => ['exists:users,id'],
        ]);

        $users = User::whereIn('id',$r->user_ids)->get();
        $count = 0;
        foreach ($users as $u) {
            switch ($r->action) {
                case 'activate':   if (!$u->active) { $u->update(['active'=>true]); $count++; } break;
                case 'deactivate': if ($u->active && !($u->isAdmin() && User::where('role','admin')->where('active',true)->count()<=1)) {
                                        $u->update(['active'=>false]); $count++; }
                    break;
                case 'delete':     if (!($u->isAdmin() && User::where('role','admin')->count()<=1) &&
                                    !$u->chantiersClient()->exists() && !$u->chantiersCommercial()->exists()) {
                                        $u->delete(); $count++; }
                    break;
            }
        }
        $msg = ['activate'=>'activés','deactivate'=>'désactivés','delete'=>'supprimés'][$r->action];
        return back()->with('success',"{$count} utilisateurs {$msg}.");
    }

    public function exportUsers(Request $request)
    {
        $users = User::when($request->role,    fn($q,$role)=>$q->where('role',$role))
                     ->when($request->active!==null, fn($q)=>$q->where('active',request('active')))
                     ->get();

        $filename = 'users_export_'.now()->format('Y-m-d_H-i-s').'.csv';
        $headers  = ['Content-Type'=>'text/csv','Content-Disposition'=>'attachment; filename="'.$filename.'"'];

        $callback = function () use ($users) {
            $f = fopen('php://output','w');
            fputcsv($f,['Nom','Email','Rôle','Téléphone','Actif','Créé le']);
            foreach ($users as $u) {
                fputcsv($f,[$u->name,$u->email,$u->role,$u->telephone,$u->active?'Oui':'Non',$u->created_at->format('d/m/Y H:i')]);
            }
            fclose($f);
        };

        return response()->stream($callback,200,$headers);
    }
}
