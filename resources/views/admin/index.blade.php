@extends('layouts.app')

@section('title', 'Administration - Gestion des Utilisateurs')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                <svg class="inline-block w-6 h-6 mr-2 text-primary-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                </svg>
                Administration - Gestion des Utilisateurs
            </h1>
        </div>
        <div class="mt-4 sm:mt-0">
            <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM3 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 019.374 21c-2.331 0-4.512-.645-6.374-1.766z" />
                </svg>
                Nouvel Utilisateur
            </a>
        </div>
    </div>
    
    <!-- Filtres -->
    <div class="card mb-6">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                <div class="lg:col-span-2">
                    <input type="text" 
                           name="search" 
                           class="form-input" 
                           placeholder="Rechercher par nom ou email..." 
                           value="{{ request('search') }}">
                </div>
                <div>
                    <select name="role" class="form-select">
                        <option value="">Tous les rôles</option>
                        <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="commercial" {{ request('role') == 'commercial' ? 'selected' : '' }}>Commercial</option>
                        <option value="client" {{ request('role') == 'client' ? 'selected' : '' }}>Client</option>
                    </select>
                </div>
                <div>
                    <select name="active" class="form-select">
                        <option value="">Tous les statuts</option>
                        <option value="1" {{ request('active') == '1' ? 'selected' : '' }}>Actifs</option>
                        <option value="0" {{ request('active') == '0' ? 'selected' : '' }}>Inactifs</option>
                    </select>
                </div>
                <div>
                    <button type="submit" class="btn btn-primary w-full">
                        <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                        </svg>
                        Filtrer
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Statistiques -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="card text-center">
            <div class="card-body">
                <div class="text-2xl font-bold text-gray-900">{{ $users->total() }}</div>
                <div class="text-sm text-gray-500">Total Utilisateurs</div>
            </div>
        </div>
        <div class="card text-center">
            <div class="card-body">
                <div class="text-2xl font-bold text-primary-600">{{ $users->where('role', 'client')->count() }}</div>
                <div class="text-sm text-gray-500">Clients</div>
            </div>
        </div>
        <div class="card text-center">
            <div class="card-body">
                <div class="text-2xl font-bold text-warning-600">{{ $users->where('role', 'commercial')->count() }}</div>
                <div class="text-sm text-gray-500">Commerciaux</div>
            </div>
        </div>
        <div class="card text-center">
            <div class="card-body">
                <div class="text-2xl font-bold text-danger-600">{{ $users->where('role', 'admin')->count() }}</div>
                <div class="text-sm text-gray-500">Admins</div>
            </div>
        </div>
    </div>
    
    <!-- Liste des utilisateurs -->
    <div class="card">
        <div class="card-body p-0">
            @if($users->count() > 0)
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    ID
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Utilisateur
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Email
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Rôle
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Téléphone
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Statut
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Inscrit le
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($users as $user)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $user->id }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-8 w-8">
                                                <div class="h-8 w-8 rounded-full bg-primary-600 flex items-center justify-center">
                                                    <span class="text-xs font-medium text-white">
                                                        {{ substr($user->name, 0, 2) }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="ml-3">
                                                <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                                @if($user->chantiersClient->count() > 0)
                                                    <div class="text-xs text-gray-500">
                                                        {{ $user->chantiersClient->count() }} chantier(s)
                                                    </div>
                                                @elseif($user->chantiersCommercial->count() > 0)
                                                    <div class="text-xs text-gray-500">
                                                        {{ $user->chantiersCommercial->count() }} chantier(s)
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $user->email }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @switch($user->role)
                                            @case('admin')
                                                <span class="badge badge-danger">Admin</span>
                                                @break
                                            @case('commercial')
                                                <span class="badge badge-warning">Commercial</span>
                                                @break
                                            @case('client')
                                                <span class="badge badge-primary">Client</span>
                                                @break
                                        @endswitch
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $user->telephone ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($user->active)
                                            <span class="badge badge-success">Actif</span>
                                        @else
                                            <span class="badge badge-secondary">Inactif</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $user->created_at->format('d/m/Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <div class="flex space-x-2">
                                            <a href="{{ route('admin.users.edit', $user) }}" 
                                               class="text-primary-600 hover:text-primary-900"
                                               title="Modifier">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                                </svg>
                                            </a>
                                            
                                            <form method="POST" action="{{ route('admin.users.toggle', $user) }}" class="inline">
                                                @csrf
                                                <button type="submit" 
                                                        class="text-{{ $user->active ? 'warning' : 'success' }}-600 hover:text-{{ $user->active ? 'warning' : 'success' }}-900"
                                                        title="{{ $user->active ? 'Désactiver' : 'Activer' }}">
                                                    @if($user->active)
                                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636M5.636 18.364l12.728-12.728" />
                                                        </svg>
                                                    @else
                                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                        </svg>
                                                    @endif
                                                </button>
                                            </form>
                                            
                                            @if($user->id !== Auth::id() && !($user->isAdmin() && App\Models\User::where('role', 'admin')->count() <= 1))
                                                <form method="POST" action="{{ route('admin.users.destroy', $user) }}" 
                                                      class="inline"
                                                      onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" 
                                                            class="text-danger-600 hover:text-danger-900"
                                                            title="Supprimer">
                                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                                        </svg>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                    {{ $users->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Aucun utilisateur trouvé</h3>
                    <p class="text-gray-500 mb-4">Modifiez vos critères de recherche ou créez un nouvel utilisateur.</p>
                    <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                        Créer un utilisateur
                    </a>
                </div>
            @endif
        </div>
    </div>
    
    <!-- Actions en masse -->
    <div class="card mt-6">
        <div class="card-header">
            <h6 class="text-lg font-medium text-gray-900">Actions rapides</h6>
        </div>
        <div class="card-body">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <button class="btn btn-outline w-full" onclick="exportUsers()">
                    <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m.75 12l3 3m0 0l3-3m-3 3v-6m-1.5-9H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                    </svg>
                    Exporter en Excel
                </button>
                <button class="btn btn-outline w-full" onclick="cleanupInactiveUsers()">
                    <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                    </svg>
                    Nettoyer les comptes inactifs
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function exportUsers() {
    // Ajouter les paramètres de recherche actuels
    const params = new URLSearchParams(window.location.search);
    params.append('export', 'excel');
    window.location.href = '{{ route("admin.index") }}?' + params.toString();
}

function cleanupInactiveUsers() {
    if (confirm('Voulez-vous supprimer tous les comptes inactifs depuis plus de 6 mois ?')) {
        // Implémenter l'action de nettoyage
        alert('Fonctionnalité à implémenter');
    }
}

// Auto-submit sur changement de filtre
document.addEventListener('DOMContentLoaded', function() {
    const filters = document.querySelectorAll('select[name="role"], select[name="active"]');
    filters.forEach(filter => {
        filter.addEventListener('change', function() {
            this.form.submit();
        });
    });
});
</script>
@endsection