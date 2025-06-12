@extends('layouts.app')

@section('title', 'Facture ' . $facture->numero)

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-purple-50">
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
                        <a href="{{ route('chantiers.factures.index', $chantier) }}" class="hover:text-gray-700">Factures</a>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                        <span class="text-gray-900 font-medium">{{ $facture->numero }}</span>
                    </nav>
                    <div class="mt-2 flex items-center space-x-4">
                        <h1 class="text-2xl font-bold text-gray-900">
                            Facture {{ $facture->numero }}
                        </h1>
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
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $badgeClass }}">
                            {{ $statutTexte }}
                        </span>
                    </div>
                    <p class="text-gray-600 mt-1">{{ $facture->titre }}</p>
                </div>
                
                <div class="flex items-center space-x-3">
                    @can('update', $facture)
                        @if($facture->statut === 'brouillon')
                            <a href="{{ route('chantiers.factures.edit', [$chantier, $facture]) }}"
                               class="inline-flex items-center px-4 py-2 bg-purple-600 text-white text-sm font-medium rounded-lg hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                                Modifier
                            </a>
                            <form action="{{ route('factures.envoyer', [$chantier, $facture]) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" 
                                        onclick="return confirm('Envoyer cette facture au client ?')"
                                        class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                                    </svg>
                                    Envoyer
                                </button>
                            </form>
                        @endif

                        @if($facture->montant_restant > 0)
                            <button onclick="openModal('paiementModal')"
                                    class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                </svg>
                                Ajouter un paiement
                            </button>
                        @endif
                    @endcan

                    <!-- Menu déroulant actions -->
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" 
                                class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
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
                                <a href="{{ route('factures.pdf', [$chantier, $facture]) }}" 
                                   target="_blank"
                                   class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2z"></path>
                                    </svg>
                                    Télécharger PDF
                                </a>
                                
                                @can('update', $facture)
                                    <form action="{{ route('factures.dupliquer', [$chantier, $facture]) }}" method="POST" class="inline w-full">
                                        @csrf
                                        <button type="submit" 
                                                class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                            </svg>
                                            Dupliquer
                                        </button>
                                    </form>

                                    @if($facture->statut === 'envoyee' && !$facture->estPayee())
                                        <form action="{{ route('factures.relance', [$chantier, $facture]) }}" method="POST" class="inline w-full">
                                            @csrf
                                            <button type="submit" 
                                                    class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                                                </svg>
                                                Envoyer une relance
                                            </button>
                                        </form>
                                    @endif
                                @endcan
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
                <!-- Informations de la facture -->
                <div class="bg-white shadow-xl rounded-2xl overflow-hidden">
                    <div class="px-6 py-4 bg-gradient-to-r from-purple-50 to-indigo-50 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Détails de la facture</h3>
                    </div>
                    
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Numéro</label>
                                <p class="mt-1 text-lg font-semibold text-gray-900">{{ $facture->numero }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Date d'émission</label>
                                <p class="mt-1 text-gray-900">{{ $facture->created_at->format('d/m/Y à H:i') }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Date d'échéance</label>
                                <p class="mt-1 text-gray-900">
                                    {{ $facture->date_echeance ? $facture->date_echeance->format('d/m/Y') : 'Non définie' }}
                                    @if($facture->date_echeance && $facture->date_echeance->isPast() && !$facture->estPayee())
                                        <span class="ml-2 text-red-600 text-sm">(En retard)</span>
                                    @elseif($facture->date_echeance && $facture->date_echeance->diffInDays(now()) <= 7 && !$facture->estPayee())
                                        <span class="ml-2 text-orange-600 text-sm">(Échéance proche)</span>
                                    @endif
                                </p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Commercial</label>
                                <p class="mt-1 text-gray-900">{{ $facture->commercial->name }}</p>
                            </div>
                            @if($facture->delai_paiement)
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Délai de paiement</label>
                                    <p class="mt-1 text-gray-900">{{ $facture->delai_paiement }} jours</p>
                                </div>
                            @endif
                            @if($facture->conditions_reglement)
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Conditions de règlement</label>
                                    <p class="mt-1 text-gray-900">{{ $facture->conditions_reglement }}</p>
                                </div>
                            @endif
                            @if($facture->reference_commande)
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Référence commande</label>
                                    <p class="mt-1 text-gray-900">{{ $facture->reference_commande }}</p>
                                </div>
                            @endif
                            @if($facture->devis)
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Devis de référence</label>
                                    <p class="mt-1 text-gray-900">
                                        <a href="{{ route('chantiers.devis.show', [$chantier, $facture->devis]) }}" 
                                           class="text-blue-600 hover:text-blue-800">
                                            {{ $facture->devis->numero }}
                                        </a>
                                    </p>
                                </div>
                            @endif
                        </div>
                        
                        @if($facture->description)
                            <div class="mt-6">
                                <label class="block text-sm font-medium text-gray-500">Description</label>
                                <p class="mt-1 text-gray-900">{{ $facture->description }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Lignes de la facture -->
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
                                @foreach($facture->lignes as $ligne)
                                    <tr>
                                        <td class="px-6 py-4">
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">{{ $ligne->designation }}</div>
                                                @if($ligne->description)
                                                    <div class="text-sm text-gray-500">{{ $ligne->description }}</div>
                                                @endif
                                                @if($ligne->categorie)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800 mt-1">
                                                        {{ $ligne->categorie }}
                                                    </span>
                                                @endif
                                                @if($ligne->remise_pourcentage > 0)
                                                    <div class="text-xs text-green-600 mt-1">
                                                        Remise {{ $ligne->remise_pourcentage }}%
                                                    </div>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ number_format($ligne->quantite, 2) }} {{ $ligne->unite }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ number_format($ligne->prix_unitaire_ht, 2) }}€
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
                                        {{ number_format($facture->montant_ht, 2) }}€
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="4" class="px-6 py-3 text-right text-sm font-medium text-gray-900">
                                        TVA :
                                    </td>
                                    <td class="px-6 py-3 text-sm font-medium text-gray-900">
                                        {{ number_format($facture->montant_tva, 2) }}€
                                    </td>
                                </tr>
                                <tr class="border-t-2 border-gray-300">
                                    <td colspan="4" class="px-6 py-4 text-right text-lg font-bold text-gray-900">
                                        Total TTC :
                                    </td>
                                    <td class="px-6 py-4 text-lg font-bold text-purple-600">
                                        {{ number_format($facture->montant_ttc, 2) }}€
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- Paiements -->
                @if($facture->paiements->count() > 0)
                    <div class="bg-white shadow-xl rounded-2xl overflow-hidden">
                        <div class="px-6 py-4 bg-gradient-to-r from-green-50 to-emerald-50 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">Historique des paiements</h3>
                        </div>
                        
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Date
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Montant
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Mode
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Référence
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($facture->paiements as $paiement)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $paiement->date_paiement->format('d/m/Y') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-green-600">
                                                {{ number_format($paiement->montant, 2) }}€
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ ucfirst($paiement->mode_paiement) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $paiement->reference_paiement ?: '-' }}
                                                @if($paiement->banque)
                                                    <div class="text-xs text-gray-500">{{ $paiement->banque }}</div>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                @can('update', $facture)
                                                    <form action="{{ route('factures.paiements.destroy', [$chantier, $facture, $paiement]) }}" 
                                                          method="POST" 
                                                          class="inline"
                                                          onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce paiement ?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" 
                                                                class="text-red-600 hover:text-red-900">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                            </svg>
                                                        </button>
                                                    </form>
                                                @endcan
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-gray-50">
                                    <tr>
                                        <td class="px-6 py-3 text-sm font-medium text-gray-900">Total payé :</td>
                                        <td class="px-6 py-3 text-sm font-bold text-green-600">
                                            {{ number_format($facture->montant_paye, 2) }}€
                                        </td>
                                        <td colspan="3"></td>
                                    </tr>
                                    @if($facture->montant_restant > 0)
                                        <tr>
                                            <td class="px-6 py-3 text-sm font-medium text-gray-900">Reste à payer :</td>
                                            <td class="px-6 py-3 text-sm font-bold text-red-600">
                                                {{ number_format($facture->montant_restant, 2) }}€
                                            </td>
                                            <td colspan="3"></td>
                                        </tr>
                                    @endif
                                </tfoot>
                            </table>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Statut des paiements -->
                <div class="bg-white shadow-xl rounded-2xl overflow-hidden">
                    <div class="px-6 py-4 bg-gradient-to-r from-indigo-50 to-purple-50 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Statut des paiements</h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium text-gray-500">Montant total</span>
                                <span class="text-lg font-bold text-gray-900">{{ number_format($facture->montant_ttc, 2) }}€</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium text-gray-500">Montant payé</span>
                                <span class="text-lg font-bold text-green-600">{{ number_format($facture->montant_paye, 2) }}€</span>
                            </div>
                            @if($facture->montant_restant > 0)
                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-medium text-gray-500">Reste à payer</span>
                                    <span class="text-lg font-bold text-red-600">{{ number_format($facture->montant_restant, 2) }}€</span>
                                </div>
                            @endif
                            
                            <!-- Barre de progression -->
                            <div class="mt-4">
                                <div class="flex justify-between text-sm text-gray-600 mb-1">
                                    <span>Progression</span>
                                    <span>{{ $facture->montant_ttc > 0 ? round(($facture->montant_paye / $facture->montant_ttc) * 100, 1) : 0 }}%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-3">
                                    <div class="bg-gradient-to-r from-green-500 to-green-600 h-3 rounded-full transition-all duration-300" 
                                         style="width: {{ $facture->montant_ttc > 0 ? min(100, ($facture->montant_paye / $facture->montant_ttc) * 100) : 0 }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Informations client -->
                <div class="bg-white shadow-xl rounded-2xl overflow-hidden">
                    <div class="px-6 py-4 bg-gradient-to-r from-blue-50 to-indigo-50 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Informations client</h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Nom</label>
                                <p class="mt-1 text-gray-900">{{ $facture->client_info['nom'] ?? $chantier->client->name }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Email</label>
                                <p class="mt-1 text-gray-900">{{ $facture->client_info['email'] ?? $chantier->client->email }}</p>
                            </div>
                            @if($facture->client_info['telephone'] ?? $chantier->client->telephone)
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Téléphone</label>
                                    <p class="mt-1 text-gray-900">{{ $facture->client_info['telephone'] ?? $chantier->client->telephone }}</p>
                                </div>
                            @endif
                            @if($facture->client_info['adresse'] ?? $chantier->client->adresse)
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Adresse</label>
                                    <p class="mt-1 text-gray-900">{{ $facture->client_info['adresse'] ?? $chantier->client->adresse }}</p>
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
                                    <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                                        <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Facture créée</p>
                                    <p class="text-xs text-gray-500">{{ $facture->created_at->format('d/m/Y à H:i') }}</p>
                                </div>
                            </div>
                            
                            @if($facture->date_envoi)
                                <div class="flex items-start space-x-3">
                                    <div class="flex-shrink-0">
                                        <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">Facture envoyée</p>
                                        <p class="text-xs text-gray-500">{{ $facture->date_envoi->format('d/m/Y à H:i') }}</p>
                                    </div>
                                </div>
                            @endif
                            
                            @foreach($facture->paiements->take(3) as $paiement)
                                <div class="flex items-start space-x-3">
                                    <div class="flex-shrink-0">
                                        <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">
                                            Paiement reçu ({{ number_format($paiement->montant, 2) }}€)
                                        </p>
                                        <p class="text-xs text-gray-500">{{ $paiement->date_paiement->format('d/m/Y') }}</p>
                                    </div>
                                </div>
                            @endforeach

                            @if($facture->date_paiement_complet)
                                <div class="flex items-start space-x-3">
                                    <div class="flex-shrink-0">
                                        <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">Facture soldée</p>
                                        <p class="text-xs text-gray-500">{{ $facture->date_paiement_complet->format('d/m/Y à H:i') }}</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Notes internes -->
                @if($facture->notes_internes && (Auth::user()->isAdmin() || Auth::user()->isCommercial()))
                    <div class="bg-yellow-50 shadow-xl rounded-2xl overflow-hidden border border-yellow-200">
                        <div class="px-6 py-4 bg-yellow-100 border-b border-yellow-200">
                            <h3 class="text-lg font-semibold text-yellow-800">Notes internes</h3>
                        </div>
                        <div class="p-6">
                            <p class="text-sm text-yellow-700">{{ $facture->notes_internes }}</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal d'ajout de paiement -->
@if($facture->montant_restant > 0)
    <div id="paiementModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Ajouter un paiement</h3>
                    <button onclick="closeModal('paiementModal')" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <form action="{{ route('factures.paiements.store', [$chantier, $facture]) }}" method="POST">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="montant" class="block text-sm font-medium text-gray-700">
                                Montant *
                            </label>
                            <input type="number" 
                                   name="montant" 
                                   id="montant"
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
                            <label for="date_paiement" class="block text-sm font-medium text-gray-700">
                                Date de paiement *
                            </label>
                            <input type="date" 
                                   name="date_paiement" 
                                   id="date_paiement"
                                   value="{{ date('Y-m-d') }}"
                                   class="mt-1 form-input"
                                   required>
                        </div>
                        
                        <div>
                            <label for="mode_paiement" class="block text-sm font-medium text-gray-700">
                                Mode de paiement *
                            </label>
                            <select name="mode_paiement" 
                                    id="mode_paiement"
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
                            <label for="reference_paiement" class="block text-sm font-medium text-gray-700">
                                Référence
                            </label>
                            <input type="text" 
                                   name="reference_paiement" 
                                   id="reference_paiement"
                                   class="mt-1 form-input"
                                   placeholder="N° de transaction, chèque...">
                        </div>
                        
                        <div>
                            <label for="banque" class="block text-sm font-medium text-gray-700">
                                Banque
                            </label>
                            <input type="text" 
                                   name="banque" 
                                   id="banque"
                                   class="mt-1 form-input"
                                   placeholder="Nom de la banque">
                        </div>
                        
                        <div class="md:col-span-2">
                            <label for="commentaire" class="block text-sm font-medium text-gray-700">
                                Commentaire
                            </label>
                            <textarea name="commentaire" 
                                      id="commentaire"
                                      rows="2"
                                      class="mt-1 form-textarea"
                                      placeholder="Notes sur ce paiement..."></textarea>
                        </div>
                    </div>
                    
                    <div class="flex justify-end space-x-3">
                        <button type="button" 
                                onclick="closeModal('paiementModal')"
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
@endif

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