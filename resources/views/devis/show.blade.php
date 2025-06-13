@extends('layouts.app')

@section('title', 'Devis ' . $devis->numero)

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-blue-50">
    <!-- Header -->
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
                        <a href="{{ route('chantiers.devis.index', $chantier) }}" class="hover:text-gray-700">Devis</a>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                        <span class="text-gray-900 font-medium">{{ $devis->numero }}</span>
                    </nav>
                    <div class="mt-2 flex items-center space-x-4">
                        <h1 class="text-2xl font-bold text-gray-900">
                            Devis {{ $devis->numero }}
                        </h1>
                        @php
                            $badgeClass = match($devis->statut) {
                                'brouillon' => 'bg-gray-100 text-gray-800',
                                'envoye' => 'bg-blue-100 text-blue-800',
                                'accepte' => 'bg-green-100 text-green-800',
                                'refuse' => 'bg-red-100 text-red-800',
                                'expire' => 'bg-yellow-100 text-yellow-800',
                                default => 'bg-gray-100 text-gray-800'
                            };
                            $statutTexte = match($devis->statut) {
                                'brouillon' => 'Brouillon',
                                'envoye' => 'Envoyé',
                                'accepte' => 'Accepté',
                                'refuse' => 'Refusé',
                                'expire' => 'Expiré',
                                default => 'Inconnu'
                            };
                        @endphp
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $badgeClass }}">
                            {{ $statutTexte }}
                        </span>
                    </div>
                    <p class="text-gray-600 mt-1">{{ $devis->titre }}</p>
                </div>
                
                <div class="flex items-center space-x-3">
                    <!-- Actions selon le rôle et le statut -->
                    @if(Auth::user()->isClient() && $chantier->client_id === Auth::id())
                        @if($devis->statut === 'envoye' && $devis->peutEtreAccepte())
                            <button onclick="openModal('acceptModal')"
                                    class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Accepter le devis
                            </button>
                            <button onclick="openModal('refuseModal')"
                                    class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                Refuser
                            </button>
                        @endif
                    @endif

                    @can('update', $chantier)
                        @if($devis->statut === 'brouillon')
                            <a href="{{ route('chantiers.devis.edit', [$chantier, $devis]) }}"
                               class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                                Modifier
                            </a>
                            <form action="{{ route('devis.envoyer', [$chantier, $devis]) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" 
                                        onclick="return confirm('Envoyer ce devis au client ?')"
                                        class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                                    </svg>
                                    Envoyer
                                </button>
                            </form>
                        @endif

                        @if($devis->statut === 'accepte' && !$devis->facture_id)
                            <form action="{{ route('devis.convertir', [$chantier, $devis]) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" 
                                        onclick="return confirm('Convertir ce devis en facture ?')"
                                        class="inline-flex items-center px-4 py-2 bg-purple-600 text-white text-sm font-medium rounded-lg hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    Convertir en facture
                                </button>
                            </form>
                        @endif
                    @endcan

                    <!-- Menu déroulant actions -->
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" 
                                class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Actions
                            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div x-show="open" 
                             @click.away="open = false"
                             x-transition
                             class="absolute right-0 mt-2 w-56 bg-white rounded-md shadow-lg z-20 border border-gray-200">
                            <div class="py-1">
                                <a href="{{ route('devis.pdf', [$chantier, $devis]) }}" 
                                   target="_blank"
                                   class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2z"></path>
                                    </svg>
                                    Télécharger PDF
                                </a>
                                <a href="{{ route('devis.preview', [$chantier, $devis]) }}" 
                                   target="_blank"
                                   class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                    Prévisualiser PDF
                                </a>
                                @can('update', $chantier)
                                    <form action="{{ route('devis.dupliquer', [$chantier, $devis]) }}" method="POST" class="inline w-full">
                                        @csrf
                                        <button type="submit" 
                                                class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                            </svg>
                                            Dupliquer
                                        </button>
                                    </form>
                                @endcan
                                <button onclick="copyToClipboard('{{ route('devis.public', [$devis, hash('sha256', $devis->id . $devis->numero . config('app.key'))]) }}')"
                                        class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                                    </svg>
                                    Copier lien public
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Contenu principal -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Informations du devis -->
                <div class="bg-white shadow-xl rounded-2xl overflow-hidden">
                    <div class="px-6 py-4 bg-gradient-to-r from-blue-50 to-indigo-50 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Détails du devis</h3>
                    </div>
                    
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Numéro</label>
                                <p class="mt-1 text-lg font-semibold text-gray-900">{{ $devis->numero }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Date de création</label>
                                <p class="mt-1 text-gray-900">{{ $devis->created_at->format('d/m/Y à H:i') }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Date de validité</label>
                                <p class="mt-1 text-gray-900">
                                    {{ $devis->date_validite ? $devis->date_validite->format('d/m/Y') : 'Non définie' }}
                                    @if($devis->date_validite && $devis->date_validite->isPast() && $devis->statut !== 'accepte')
                                        <span class="ml-2 text-red-600 text-sm">(Expiré)</span>
                                    @endif
                                </p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Commercial</label>
                                <p class="mt-1 text-gray-900">{{ $devis->commercial->name }}</p>
                            </div>
                            @if($devis->delai_realisation)
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Délai de réalisation</label>
                                    <p class="mt-1 text-gray-900">{{ $devis->delai_realisation }} jours</p>
                                </div>
                            @endif
                            @if($devis->modalites_paiement)
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Modalités de paiement</label>
                                    <p class="mt-1 text-gray-900">{{ $devis->modalites_paiement }}</p>
                                </div>
                            @endif
                        </div>
                        
                        @if($devis->description)
                            <div class="mt-6">
                                <label class="block text-sm font-medium text-gray-500">Description</label>
                                <p class="mt-1 text-gray-900">{{ $devis->description }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Lignes du devis -->
                <div class="bg-white shadow-xl rounded-2xl overflow-hidden">
                    <div class="px-6 py-4 bg-gradient-to-r from-green-50 to-emerald-50 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Détail des prestations</h3>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Désignation
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Qté
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Prix unitaire HT
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        TVA
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Total HT
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($devis->lignes as $ligne)
                                    <tr>
                                        <td class="px-6 py-4">
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">{{ $ligne->designation }}</div>
                                                @if($ligne->description)
                                                    <div class="text-sm text-gray-500">{{ $ligne->description }}</div>
                                                @endif
                                                @if($ligne->categorie)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 mt-1">
                                                        {{ $ligne->categorie }}
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ number_format($ligne->quantite, 2) }} {{ $ligne->unite }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ number_format($ligne->prix_unitaire_ht, 2) }}€
                                            @if($ligne->remise_pourcentage > 0)
                                                <div class="text-xs text-green-600">
                                                    Remise {{ $ligne->remise_pourcentage }}%
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ number_format($ligne->taux_tva, 1) }}%
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ number_format($ligne->montant_ht, 2) }}€
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-gray-50">
                                <tr>
                                    <td colspan="4" class="px-6 py-4 text-right text-sm font-medium text-gray-900">
                                        Total HT :
                                    </td>
                                    <td class="px-6 py-4 text-sm font-bold text-gray-900">
                                        {{ number_format($devis->montant_ht, 2) }}€
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="4" class="px-6 py-3 text-right text-sm font-medium text-gray-900">
                                        TVA :
                                    </td>
                                    <td class="px-6 py-3 text-sm font-medium text-gray-900">
                                        {{ number_format($devis->montant_tva, 2) }}€
                                    </td>
                                </tr>
                                <tr class="border-t-2 border-gray-300">
                                    <td colspan="4" class="px-6 py-4 text-right text-lg font-bold text-gray-900">
                                        Total TTC :
                                    </td>
                                    <td class="px-6 py-4 text-lg font-bold text-blue-600">
                                        {{ number_format($devis->montant_ttc, 2) }}€
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- Conditions générales -->
                @if($devis->conditions_generales)
                    <div class="bg-white shadow-xl rounded-2xl overflow-hidden">
                        <div class="px-6 py-4 bg-gradient-to-r from-purple-50 to-pink-50 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">Conditions générales</h3>
                        </div>
                        <div class="p-6">
                            <div class="prose prose-sm max-w-none text-gray-700">
                                {!! nl2br(e($devis->conditions_generales)) !!}
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Informations client -->
                <div class="bg-white shadow-xl rounded-2xl overflow-hidden">
                    <div class="px-6 py-4 bg-gradient-to-r from-blue-50 to-indigo-50 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Informations client</h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Nom</label>
                                <p class="mt-1 text-gray-900">{{ $devis->client_info['nom'] ?? $chantier->client->name }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Email</label>
                                <p class="mt-1 text-gray-900">{{ $devis->client_info['email'] ?? $chantier->client->email }}</p>
                            </div>
                            @if($devis->client_info['telephone'] ?? $chantier->client->telephone)
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Téléphone</label>
                                    <p class="mt-1 text-gray-900">{{ $devis->client_info['telephone'] ?? $chantier->client->telephone }}</p>
                                </div>
                            @endif
                            @if($devis->client_info['adresse'] ?? $chantier->client->adresse)
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Adresse</label>
                                    <p class="mt-1 text-gray-900">{{ $devis->client_info['adresse'] ?? $chantier->client->adresse }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Historique -->
                <div class="bg-white shadow-xl rounded-2xl overflow-hidden">
                    <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-blue-50 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Historique</h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            <div class="flex items-start space-x-3">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Devis créé</p>
                                    <p class="text-xs text-gray-500">{{ $devis->created_at->format('d/m/Y à H:i') }}</p>
                                </div>
                            </div>
                            
                            @if($devis->date_envoi)
                                <div class="flex items-start space-x-3">
                                    <div class="flex-shrink-0">
                                        <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">Devis envoyé</p>
                                        <p class="text-xs text-gray-500">{{ $devis->date_envoi->format('d/m/Y à H:i') }}</p>
                                    </div>
                                </div>
                            @endif
                            
                            @if($devis->date_reponse)
                                <div class="flex items-start space-x-3">
                                    <div class="flex-shrink-0">
                                        @if($devis->statut === 'accepte')
                                            <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                            </div>
                                        @else
                                            <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                                                <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                            </div>
                                        @endif
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">
                                            Devis {{ $devis->statut === 'accepte' ? 'accepté' : 'refusé' }}
                                        </p><p class="text-xs text-gray-500">{{ $devis->date_reponse->format('d/m/Y à H:i') }}</p>
                                   </div>
                               </div>
                           @endif

                           @if($devis->facture_id)
                               <div class="flex items-start space-x-3">
                                   <div class="flex-shrink-0">
                                       <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                                           <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                               <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                           </svg>
                                       </div>
                                   </div>
                                   <div>
                                       <p class="text-sm font-medium text-gray-900">Converti en facture</p>
                                       <p class="text-xs text-gray-500">{{ $devis->converted_at?->format('d/m/Y à H:i') }}</p>
                                       @if($devis->facture)
                                           <a href="{{ route('chantiers.factures.show', [$chantier, $devis->facture]) }}" 
                                              class="text-xs text-blue-600 hover:text-blue-800">
                                               Voir la facture {{ $devis->facture->numero }}
                                           </a>
                                       @endif
                                   </div>
                               </div>
                           @endif
                       </div>
                   </div>
               </div>

               <!-- Notes internes -->
               @if($devis->notes_internes && (Auth::user()->isAdmin() || Auth::user()->isCommercial()))
                   <div class="bg-yellow-50 shadow-xl rounded-2xl overflow-hidden border border-yellow-200">
                       <div class="px-6 py-4 bg-yellow-100 border-b border-yellow-200">
                           <h3 class="text-lg font-semibold text-yellow-800">Notes internes</h3>
                       </div>
                       <div class="p-6">
                           <p class="text-sm text-yellow-700">{{ $devis->notes_internes }}</p>
                       </div>
                   </div>
               @endif
           </div>
       </div>
   </div>
</div>

<!-- Modal d'acceptation du devis (côté client) -->
@if(Auth::user()->isClient() && $chantier->client_id === Auth::id() && $devis->statut === 'envoye')
   <div id="acceptModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
       <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
           <div class="mt-3">
               <div class="flex items-center justify-between mb-4">
                   <h3 class="text-lg font-medium text-gray-900">Accepter le devis</h3>
                   <button onclick="closeModal('acceptModal')" class="text-gray-400 hover:text-gray-600">
                       <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                           <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                       </svg>
                   </button>
               </div>
               
               <form action="{{ route('devis.accepter', [$chantier, $devis]) }}" method="POST">
                   @csrf
                   <div class="mb-4">
                       <label for="commentaire_client" class="block text-sm font-medium text-gray-700">
                           Commentaire (optionnel)
                       </label>
                       <textarea name="commentaire_client" 
                                 id="commentaire_client" 
                                 rows="3" 
                                 class="mt-1 form-textarea"
                                 placeholder="Votre commentaire..."></textarea>
                   </div>
                   
                   <div class="mb-6">
                       <label class="flex items-center">
                           <input type="checkbox" class="form-checkbox" required>
                           <span class="ml-2 text-sm text-gray-700">
                               J'accepte les conditions générales et confirme cette commande
                           </span>
                       </label>
                   </div>
                   
                   <div class="flex justify-end space-x-3">
                       <button type="button" 
                               onclick="closeModal('acceptModal')"
                               class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                           Annuler
                       </button>
                       <button type="submit" 
                               class="px-4 py-2 bg-green-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-green-700">
                           Confirmer l'acceptation
                       </button>
                   </div>
               </form>
           </div>
       </div>
   </div>

   <!-- Modal de refus du devis -->
   <div id="refuseModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
       <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
           <div class="mt-3">
               <div class="flex items-center justify-between mb-4">
                   <h3 class="text-lg font-medium text-gray-900">Refuser le devis</h3>
                   <button onclick="closeModal('refuseModal')" class="text-gray-400 hover:text-gray-600">
                       <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                           <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                       </svg>
                   </button>
               </div>
               
               <form action="{{ route('devis.refuser', [$chantier, $devis]) }}" method="POST">
                   @csrf
                   <div class="mb-6">
                       <label for="raison_refus" class="block text-sm font-medium text-gray-700">
                           Raison du refus (optionnel)
                       </label>
                       <textarea name="raison_refus" 
                                 id="raison_refus" 
                                 rows="3" 
                                 class="mt-1 form-textarea"
                                 placeholder="Expliquez pourquoi vous refusez ce devis..."></textarea>
                   </div>
                   
                   <div class="flex justify-end space-x-3">
                       <button type="button" 
                               onclick="closeModal('refuseModal')"
                               class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                           Annuler
                       </button>
                       <button type="submit" 
                               class="px-4 py-2 bg-red-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-red-700">
                           Confirmer le refus
                       </button>
                   </div>
               </form>
           </div>
       </div>
   </div>
@endif

<script>
function copyToClipboard(text) {
   navigator.clipboard.writeText(text).then(() => {
       // Optionnel : afficher un message de confirmation
       alert('Lien copié dans le presse-papiers !');
   });
}

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