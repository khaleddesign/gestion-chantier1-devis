@extends('layouts.app')

@section('title', 'Factures du chantier')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-purple-50">
    <!-- Header avec breadcrumb -->
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <nav class="flex items-center space-x-2 text-sm text-gray-500">
                        <a href="{{ route('chantiers.index') }}" class="hover:text-gray-700">Chantiers</a>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                        <a href="{{ route('chantiers.show', $chantier) }}" class="hover:text-gray-700">{{ $chantier->titre }}</a>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                        <span class="text-gray-900 font-medium">Factures</span>
                    </nav>
                    <h1 class="mt-2 text-2xl font-bold text-gray-900">
                        Factures - {{ $chantier->titre }}
                    </h1>
                    <p class="text-gray-600">Client : {{ $chantier->client->name }}</p>
                </div>
                
                @can('create', [\App\Models\Facture::class, $chantier])
                <div class="flex space-x-3">
                    <a href="{{ route('chantiers.factures.create', $chantier) }}" 
                       class="inline-flex items-center px-4 py-2 bg-purple-600 text-white text-sm font-medium rounded-lg hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Nouvelle Facture
                    </a>
                </div>
                @endcan
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Statistiques rapides -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Factures</dt>
                            <dd class="text-lg font-semibold text-gray-900">{{ $factures->total() }}</dd>
                        </dl>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">En attente</dt>
                            <dd class="text-lg font-semibold text-gray-900">
                                {{ $factures->whereIn('statut', ['envoyee', 'payee_partiel'])->count() }}
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Payées</dt>
                            <dd class="text-lg font-semibold text-gray-900">
                                {{ $factures->where('statut', 'payee')->count() }}
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">En retard</dt>
                            <dd class="text-lg font-semibold text-gray-900">
                                {{ $factures->where('statut', 'en_retard')->count() }}
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">CA Total</dt>
                            <dd class="text-lg font-semibold text-gray-900">
                                {{ number_format($factures->sum('montant_ttc'), 0) }}€
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Liste des factures -->
        <div class="bg-white shadow-xl rounded-2xl overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-purple-50 to-indigo-50 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Liste des factures</h3>
            </div>

            @if($factures->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Numéro / Titre
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Statut
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Montant TTC
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Paiements
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Échéance
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Commercial
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($factures as $facture)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $facture->numero }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{ Str::limit($facture->titre, 40) }}
                                            </div>
                                            @if($facture->devis)
                                                <div class="text-xs text-blue-600">
                                                    Depuis devis {{ $facture->devis->numero }}
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $badgeClass = match($facture->statut) {
                                                'brouillon' => 'bg-gray-100 text-gray-800',
                                                'envoyee' => 'bg-blue-100 text-blue-800',
                                                'payee_partiel' => 'bg-yellow-100 text-yellow-800',
                                                'payee' => 'bg-green-100 text-green-800',
                                                'en_retard' => 'bg-red-100 text-red-800',
                                                'annulee' => 'bg-gray-100 text-gray-600',
                                                default => 'bg-gray-100 text-gray-800'
                                            };
                                            $statutTexte = match($facture->statut) {
                                                'brouillon' => 'Brouillon',
                                                'envoyee' => 'Envoyée',
                                                'payee_partiel' => 'Payée partiellement',
                                                'payee' => 'Payée',
                                                'en_retard' => 'En retard',
                                                'annulee' => 'Annulée',
                                                default => 'Inconnu'
                                            };
                                        @endphp
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $badgeClass }}">
                                            {{ $statutTexte }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm">
                                            <div class="font-medium text-gray-900">{{ number_format($facture->montant_ttc, 2) }}€</div>
                                            <div class="text-xs text-gray-500">HT: {{ number_format($facture->montant_ht, 2) }}€</div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm">
                                            <div class="font-medium text-green-600">{{ number_format($facture->montant_paye, 2) }}€</div>
                                            @if($facture->montant_restant > 0)
                                                <div class="text-xs text-red-600">Reste: {{ number_format($facture->montant_restant, 2) }}€</div>
                                            @endif
                                        </div>
                                        @if($facture->montant_ttc > 0)
                                            <div class="w-full bg-gray-200 rounded-full h-1.5 mt-1">
                                                <div class="bg-green-500 h-1.5 rounded-full" 
                                                     style="width: {{ min(100, ($facture->montant_paye / $facture->montant_ttc) * 100) }}%"></div>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $facture->date_echeance ? $facture->date_echeance->format('d/m/Y') : '-' }}
                                        @if($facture->date_echeance && $facture->date_echeance->isPast() && $facture->statut !== 'payee')
                                            <div class="text-xs text-red-600">En retard</div>
                                        @elseif($facture->date_echeance && $facture->date_echeance->diffInDays(now()) <= 7 && $facture->statut !== 'payee')
                                            <div class="text-xs text-orange-600">Échéance proche</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $facture->commercial->name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex items-center space-x-2">
                                            <a href="{{ route('chantiers.factures.show', [$chantier, $facture]) }}" 
                                               class="text-purple-600 hover:text-purple-900 transition-colors">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                </svg>
                                            </a>
                                            
                                            @can('update', $facture)
                                                @if(!$facture->estPayee())
                                                    <a href="{{ route('chantiers.factures.edit', [$chantier, $facture]) }}" 
                                                       class="text-gray-600 hover:text-gray-900 transition-colors">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                        </svg>
                                                    </a>
                                                @endif
                                            @endcan

                                            <a href="{{ route('factures.pdf', [$chantier, $facture]) }}" 
                                               target="_blank"
                                               class="text-red-600 hover:text-red-900 transition-colors">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2z"></path>
                                                </svg>
                                            </a>

                                            @can('gererPaiements', $facture)
                                                <div x-data="{ open: false }" class="relative">
                                                    <button @click="open = !open" 
                                                            class="text-gray-600 hover:text-gray-900 transition-colors">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                                                        </svg>
                                                    </button>
                                                    <div x-show="open" 
                                                         @click.away="open = false"
                                                         x-transition
                                                         class="absolute right-0 mt-2 w-56 bg-white rounded-md shadow-lg z-10 border border-gray-200">
                                                        <div class="py-1">
                                                            @if($facture->statut === 'brouillon')
                                                                <form action="{{ route('factures.envoyer', [$chantier, $facture]) }}" method="POST" class="inline">
                                                                    @csrf
                                                                    <button type="submit" 
                                                                            class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                                        Envoyer au client
                                                                    </button>
                                                                </form>
                                                            @endif
                                                            
                                                            @if($facture->montant_restant > 0)
                                                                <button onclick="openModal('paiementModal_{{ $facture->id }}')"
                                                                        class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                                    Ajouter un paiement
                                                                </button>
                                                            @endif

                                                            @if($facture->statut === 'envoyee' && !$facture->estPayee())
                                                                <form action="{{ route('factures.relance', [$chantier, $facture]) }}" method="POST" class="inline">
                                                                    @csrf
                                                                    <button type="submit" 
                                                                            class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                                        Envoyer une relance
                                                                    </button>
                                                                </form>
                                                            @endif
                                                            
                                                            <form action="{{ route('factures.dupliquer', [$chantier, $facture]) }}" method="POST" class="inline">
                                                                @csrf
                                                                <button type="submit" 
                                                                        class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                                    Dupliquer
                                                                </button>
                                                            </form>

                                                            @if($facture->statut === 'brouillon')
                                                                <form action="{{ route('chantiers.factures.destroy', [$chantier, $facture]) }}" 
                                                                      method="POST" 
                                                                      class="inline"
                                                                      onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette facture ?')">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" 
                                                                            class="block w-full text-left px-4 py-2 text-sm text-red-700 hover:bg-red-50">
                                                                        Supprimer
                                                                    </button>
                                                                </form>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                    {{ $factures->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <h3 class="mt-4 text-lg font-medium text-gray-900">Aucune facture</h3>
                    <p class="mt-2 text-gray-500">Commencez par créer une nouvelle facture pour ce chantier.</p>
                    @can('create', [\App\Models\Facture::class, $chantier])
                        <div class="mt-6">
                            <a href="{{ route('chantiers.factures.create', $chantier) }}" 
                               class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                                Créer une facture
                            </a>
                        </div>
                    @endcan
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modales de paiement -->
@foreach($factures->where('montant_restant', '>', 0) as $facture)
    <div id="paiementModal_{{ $facture->id }}" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Ajouter un paiement - {{ $facture->numero }}</h3>
                    <button onclick="closeModal('paiementModal_{{ $facture->id }}')" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <form action="{{ route('factures.paiements.store', [$chantier, $facture]) }}" method="POST">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="montant_{{ $facture->id }}" class="block text-sm font-medium text-gray-700">
                                Montant *
                            </label>
                            <input type="number" 
                                   name="montant" 
                                   id="montant_{{ $facture->id }}"
                                   step="0.01"
                                   max="{{ $facture->montant_restant }}"
                                   class="mt-1 form-input"
                                   placeholder="0,00"
                                   required>
                            <p class="text-xs text-gray-500 mt-1">
                                Montant restant : {{ number_format($facture->montant_restant, 2) }}€
                            </p>
                        </div>
                        
                        <div>
                            <label for="date_paiement_{{ $facture->id }}" class="block text-sm font-medium text-gray-700">
                                Date de paiement *
                            </label>
                            <input type="date" 
                                   name="date_paiement" 
                                   id="date_paiement_{{ $facture->id }}"
                                   value="{{ date('Y-m-d') }}"
                                   class="mt-1 form-input"
                                   required>
                        </div>
                        
                        <div>
                            <label for="mode_paiement_{{ $facture->id }}" class="block text-sm font-medium text-gray-700">
                                Mode de paiement *
                            </label>
                            <select name="mode_paiement" 
                                    id="mode_paiement_{{ $facture->id }}"
                                    class="mt-1 form-select"
                                    required>
                                <option value="">Sélectionner...</option>
                                <option value="virement">Virement</option>
                                <option value="cheque">Chèque</option>
                                <option value="especes">Espèces</option>
                                <option value="cb">Carte bancaire</option>
                                <option value="prelevement">Prélèvement</option>
                                <option value="autre">Autre</option>
                            </select>
                        </div>
                        
                        <div>
                            <label for="reference_paiement_{{ $facture->id }}" class="block text-sm font-medium text-gray-700">
                                Référence
                            </label>
                            <input type="text" 
                                   name="reference_paiement" 
                                   id="reference_paiement_{{ $facture->id }}"
                                   class="mt-1 form-input"
                                   placeholder="N° de transaction, chèque...">
                        </div>
                        
                        <div>
                            <label for="banque_{{ $facture->id }}" class="block text-sm font-medium text-gray-700">
                                Banque
                            </label>
                            <input type="text" 
                                   name="banque" 
                                   id="banque_{{ $facture->id }}"
                                   class="mt-1 form-input"
                                   placeholder="Nom de la banque">
                        </div>
                        
                        <div class="md:col-span-2">
                            <label for="commentaire_{{ $facture->id }}" class="block text-sm font-medium text-gray-700">
                                Commentaire
                            </label>
                            <textarea name="commentaire" 
                                      id="commentaire_{{ $facture->id }}"
                                      rows="2"
                                      class="mt-1 form-textarea"
                                      placeholder="Notes sur ce paiement..."></textarea>
                        </div>
                    </div>
                    
                    <div class="flex justify-end space-x-3">
                        <button type="button" 
                                onclick="closeModal('paiementModal_{{ $facture->id }}')"
                                class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Annuler
                        </button>
                        <button type="submit" 
                                class="px-4 py-2 bg-green-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-green-700">
                            Enregistrer le paiement
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach

<script>
function openModal(modalId) {
    document.getElementById(modalId).classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
}
</script>
@endsection