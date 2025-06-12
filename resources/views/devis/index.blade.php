{{-- resources/views/devis/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Devis - ' . $chantier->titre)

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    
    {{-- En-tête --}}
    <div class="mb-8">
        <nav class="flex items-center space-x-2 text-sm text-gray-500 mb-4">
            <a href="{{ route('chantiers.index') }}" class="hover:text-gray-700">Chantiers</a>
            <span>›</span>
            <a href="{{ route('chantiers.show', $chantier) }}" class="hover:text-gray-700">{{ $chantier->titre }}</a>
            <span>›</span>
            <span class="text-gray-900">Devis</span>
        </nav>
        
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Devis du chantier</h1>
                <p class="text-gray-600">{{ $chantier->titre }}</p>
            </div>
            
            @can('create', [App\Models\Devis::class, $chantier])
                <a href="{{ route('chantiers.devis.create', $chantier) }}" 
                   class="btn btn-primary">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Nouveau devis
                </a>
            @endcan
        </div>
    </div>

    {{-- Statistiques rapides --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="card">
            <div class="card-body">
                <div class="flex items-center">
                    <div class="p-2 bg-blue-100 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-600">Total devis</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $devis->total() }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="flex items-center">
                    <div class="p-2 bg-yellow-100 rounded-lg">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-600">En attente</p>
                        <p class="text-2xl font-semibold text-gray-900">
                            {{ $devis->where('statut', 'envoye')->count() }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="flex items-center">
                    <div class="p-2 bg-green-100 rounded-lg">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-600">Acceptés</p>
                        <p class="text-2xl font-semibold text-gray-900">
                            {{ $devis->where('statut', 'accepte')->count() }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="flex items-center">
                    <div class="p-2 bg-indigo-100 rounded-lg">
                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-600">Montant total</p>
                        <p class="text-2xl font-semibold text-gray-900">
                            {{ number_format($devis->where('statut', 'accepte')->sum('montant_ttc'), 2, ',', ' ') }} €
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Liste des devis --}}
    <div class="card">
        <div class="card-header">
            <h3 class="text-lg font-medium text-gray-900">Liste des devis</h3>
        </div>
        
        <div class="card-body p-0">
            @if($devis->count() > 0)
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Numéro</th>
                                <th>Titre</th>
                                <th>Date émission</th>
                                <th>Date validité</th>
                                <th>Statut</th>
                                <th>Montant TTC</th>
                                <th>Commercial</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($devis as $devisItem)
                                <tr class="hover:bg-gray-50">
                                    <td>
                                        <span class="font-mono text-sm">{{ $devisItem->numero }}</span>
                                    </td>
                                    
                                    <td>
                                        <a href="{{ route('chantiers.devis.show', [$chantier, $devisItem]) }}" 
                                           class="text-blue-600 hover:text-blue-800 font-medium">
                                            {{ $devisItem->titre }}
                                        </a>
                                        @if($devisItem->description)
                                            <p class="text-sm text-gray-500 truncate max-w-xs">
                                                {{ $devisItem->description }}
                                            </p>
                                        @endif
                                    </td>
                                    
                                    <td>
                                        <span class="text-sm text-gray-900">
                                            {{ $devisItem->date_emission->format('d/m/Y') }}
                                        </span>
                                    </td>
                                    
                                    <td>
                                        <span class="text-sm {{ $devisItem->isExpire() ? 'text-red-600 font-medium' : 'text-gray-900' }}">
                                            {{ $devisItem->date_validite->format('d/m/Y') }}
                                        </span>
                                        @if($devisItem->isExpire() && $devisItem->statut === 'envoye')
                                            <span class="block text-xs text-red-500">Expiré</span>
                                        @endif
                                    </td>
                                    
                                    <td>
                                        <span class="badge {{ $devisItem->getStatutBadgeClassAttribute() }}">
                                            {{ $devisItem->getStatutTexteAttribute() }}
                                        </span>
                                    </td>
                                    
                                    <td>
                                        <span class="font-semibold text-gray-900">
                                            {{ number_format($devisItem->montant_ttc, 2, ',', ' ') }} €
                                        </span>
                                        <span class="block text-xs text-gray-500">
                                            HT: {{ number_format($devisItem->montant_ht, 2, ',', ' ') }} €
                                        </span>
                                    </td>
                                    
                                    <td>
                                        <span class="text-sm text-gray-900">{{ $devisItem->commercial->name }}</span>
                                    </td>
                                    
                                    <td class="text-right">
                                        <div class="flex items-center justify-end space-x-2">
                                            {{-- Voir --}}
                                            <a href="{{ route('chantiers.devis.show', [$chantier, $devisItem]) }}" 
                                               class="btn btn-sm btn-outline" title="Voir le devis">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                          d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                          d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                            </a>
                                            
                                            {{-- PDF --}}
                                            <a href="{{ route('devis.pdf', [$chantier, $devisItem]) }}" 
                                               class="btn btn-sm btn-outline" title="Télécharger PDF" target="_blank">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                          d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                </svg>
                                            </a>
                                            
                                            {{-- Actions spécifiques selon le statut --}}
                                            @if($devisItem->statut === 'brouillon')
                                                @can('update', $devisItem)
                                                    <a href="{{ route('chantiers.devis.edit', [$chantier, $devisItem]) }}" 
                                                       class="btn btn-sm btn-outline" title="Modifier">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                                  d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                        </svg>
                                                    </a>
                                                    
                                                    <form action="{{ route('devis.envoyer', [$chantier, $devisItem]) }}" 
                                                          method="POST" class="inline">
                                                        @csrf
                                                        <button type="submit" 
                                                                class="btn btn-sm btn-primary" 
                                                                title="Envoyer au client"
                                                                onclick="return confirm('Envoyer ce devis au client ?')">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                                      d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                                            </svg>
                                                        </button>
                                                    </form>
                                                @endcan
                                            @endif
                                            
                                            @if($devisItem->statut === 'accepte' && !$devisItem->facture_id)
                                                @can('convertir', $devisItem)
                                                    <form action="{{ route('devis.convertir', [$chantier, $devisItem]) }}" 
                                                          method="POST" class="inline">
                                                        @csrf
                                                        <button type="submit" 
                                                                class="btn btn-sm btn-success" 
                                                                title="Convertir en facture"
                                                                onclick="return confirm('Convertir ce devis en facture ?')">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                                      d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                                            </svg>
                                                        </button>
                                                    </form>
                                                @endcan
                                            @endif
                                            
                                            {{-- Menu déroulant pour plus d'actions --}}
                                            <div class="relative inline-block text-left" x-data="{ open: false }">
                                                <button @click="open = !open" 
                                                        class="btn btn-sm btn-outline">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                              d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/>
                                                    </svg>
                                                </button>
                                                
                                                <div x-show="open" 
                                                     @click.away="open = false"
                                                     x-transition:enter="transition ease-out duration-100"
                                                     x-transition:enter-start="transform opacity-0 scale-95"
                                                     x-transition:enter-end="transform opacity-100 scale-100"
                                                     x-transition:leave="transition ease-in duration-75"
                                                     x-transition:leave-start="transform opacity-100 scale-100"
                                                     x-transition:leave-end="transform opacity-0 scale-95"
                                                     class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-10">
                                                    <div class="py-1">
                                                        @can('dupliquer', $devisItem)
                                                            <form action="{{ route('devis.dupliquer', [$chantier, $devisItem]) }}" method="POST">
                                                                @csrf
                                                                <button type="submit" class="dropdown-item">
                                                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                                              d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                                                    </svg>
                                                                    Dupliquer
                                                                </button>
                                                            </form>
                                                        @endcan
                                                        
                                                        @can('delete', $devisItem)
                                                            <form action="{{ route('chantiers.devis.destroy', [$chantier, $devisItem]) }}" 
                                                                  method="POST" 
                                                                  onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce devis ?')">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="dropdown-item text-red-600 hover:text-red-800">
                                                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                                    </svg>
                                                                    Supprimer
                                                                </button>
                                                            </form>
                                                        @endcan
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                {{-- Pagination --}}
                @if($devis->hasPages())
                    <div class="px-6 py-4 border-t border-gray-200">
                        {{ $devis->links() }}
                    </div>
                @endif
            @else
                <div class="text-center py-12">
                    <svg class="w-12 h-12 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Aucun devis</h3>
                    <p class="text-gray-500 mb-6">Ce chantier n'a pas encore de devis.</p>
                    
                    @can('create', [App\Models\Devis::class, $chantier])
                        <a href="{{ route('chantiers.devis.create', $chantier) }}" 
                           class="btn btn-primary">
                            Créer le premier devis
                        </a>
                    @endcan
                </div>
            @endif
        </div>
    </div>
</div>

<style>
.dropdown-item {
    @apply block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900 transition-colors duration-150;
}
</style>
@endsection