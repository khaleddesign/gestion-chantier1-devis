@extends('layouts.app')

@section('title', 'Paiements - ' . $facture->numero)

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-green-50">
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
                        <a href="{{ route('chantiers.factures.show', [$chantier, $facture]) }}" class="hover:text-gray-700">{{ $facture->numero }}</a>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                        <span class="text-gray-900 font-medium">Paiements</span>
                    </nav>
                    <h1 class="mt-2 text-2xl font-bold text-gray-900">
                        Gestion des paiements - {{ $facture->numero }}
                    </h1>
                    <p class="text-gray-600">{{ $facture->titre }}</p>
                </div>
                
                @can('gererPaiements', $facture)
                    @if($facture->montant_restant > 0)
                        <button onclick="openModal('paiementModal')"
                                class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Ajouter un paiement
                        </button>
                    @endif
                @endcan
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            <!-- Résumé des paiements -->
            <div class="lg:col-span-4">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-200">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Montant total</dt>
                                    <dd class="text-2xl font-bold text-gray-900">{{ number_format($facture->montant_ttc, 2) }}€</dd>
                                </dl>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-200">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Montant payé</dt>
                                    <dd class="text-2xl font-bold text-green-600">{{ number_format($facture->montant_paye, 2) }}€</dd>
                                </dl>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-200">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Reste à payer</dt>
                                    <dd class="text-2xl font-bold text-red-600">{{ number_format($facture->montant_restant, 2) }}€</dd>
                                </dl>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-200">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Progression</dt>
                                    <dd class="text-2xl font-bold text-blue-600">
                                        {{ $facture->montant_ttc > 0 ? round(($facture->montant_paye / $facture->montant_ttc) * 100, 1) : 0 }}%
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Liste des paiements -->
            <div class="lg:col-span-3">
                <div class="bg-white shadow-xl rounded-2xl overflow-hidden">
                    <div class="px-6 py-4 bg-gradient-to-r from-green-50 to-emerald-50 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-900">Historique des paiements</h3>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                {{ $facture->paiements->count() }} paiement(s)
                            </span>
                        </div>
                    </div>

                    @if($facture->paiements->count() > 0)
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
                                            Mode de paiement
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Référence
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Statut
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($facture->paiements as $paiement)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $paiement->date_paiement->format('d/m/Y') }}
                                                </div>
                                                <div class="text-xs text-gray-500">
                                                    {{ $paiement->created_at->format('H:i') }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-lg font-bold text-green-600">
                                                    {{ number_format($paiement->montant, 2) }}€
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    @php
                                                        $iconClass = match($paiement->mode_paiement) {
                                                            'virement' => 'text-blue-600',
                                                            'cheque' => 'text-yellow-600',
                                                            'especes' => 'text-green-600',
                                                            'cb' => 'text-purple-600',
                                                            'prelevement' => 'text-indigo-600',
                                                            default => 'text-gray-600'
                                                        };
                                                    @endphp
                                                    <svg class="w-5 h-5 {{ $iconClass }} mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                                    </svg>
                                                    <div>
                                                        <div class="text-sm font-medium text-gray-900">
                                                            {{ ucfirst(str_replace('_', ' ', $paiement->mode_paiement)) }}
                                                        </div>
                                                        @if($paiement->banque)
                                                            <div class="text-xs text-gray-500">{{ $paiement->banque }}</div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">
                                                    {{ $paiement->reference_paiement ?: '-' }}
                                                </div>
                                                @if($paiement->commentaire)
                                                    <div class="text-xs text-gray-500 mt-1">
                                                        {{ Str::limit($paiement->commentaire, 50) }}
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @php
                                                    $statutBadge = match($paiement->statut ?? 'valide') {
                                                        'en_attente' => 'bg-yellow-100 text-yellow-800',
                                                        'valide' => 'bg-green-100 text-green-800',
                                                        'rejete' => 'bg-red-100 text-red-800',
                                                        default => 'bg-gray-100 text-gray-800'
                                                    };
                                                @endphp
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statutBadge }}">
                                                    {{ ucfirst($paiement->statut ?? 'Validé') }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                @can('gererPaiements', $facture)
                                                    <div class="flex items-center space-x-2">
                                                        <button onclick="openModal('editModal_{{ $paiement->id }}')"
                                                                class="text-blue-600 hover:text-blue-900">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                            </svg>
                                                        </button>
                                                        <form action="{{ route('paiements.destroy', $paiement) }}" 
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
                                                    </div>
                                                @endcan
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-gray-50">
                                    <tr>
                                        <td class="px-6 py-3 text-sm font-medium text-gray-900">Total :</td>
                                        <td class="px-6 py-3 text-lg font-bold text-green-600">
                                            {{ number_format($facture->montant_paye, 2) }}€
                                        </td>
                                        <td colspan="4"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-12">
                            <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                            </svg>
                            <h3 class="mt-4 text-lg font-medium text-gray-900">Aucun paiement</h3>
                            <p class="mt-2 text-gray-500">Cette facture n'a pas encore reçu de paiement.</p>
                            @can('gererPaiements', $facture)
                                <div class="mt-6">
                                    <button onclick="openModal('paiementModal')"
                                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700">
                                        Ajouter le premier paiement
                                    </button>
                                </div>
                            @endcan
                        </div>
                    @endif
                </div>
            </div>

            <!-- Sidebar informations -->
            <div class="space-y-6">
                <!-- Progression visuelle -->
                <div class="bg-white shadow-xl rounded-2xl overflow-hidden">
                    <div class="px-6 py-4 bg-gradient-to-r from-indigo-50 to-purple-50 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Progression</h3>
                    </div>
                    <div class="p-6">
                        <div class="text-center">
                            <div class="relative inline-flex items-center justify-center w-32 h-32">
                                <!-- Cercle de progression -->
                                @php
                                    $progression = $facture->montant_ttc > 0 ? ($facture->montant_paye / $facture->montant_ttc) * 100 : 0;
                                    $circumference = 2 * M_PI * 45; // rayon de 45
                                    $strokeDasharray = $circumference;
                                    $strokeDashoffset = $circumference - ($progression / 100 * $circumference);
                                @endphp
                                <svg class="w-32 h-32 transform -rotate-90" viewBox="0 0 100 100">
                                    <!-- Cercle de fond -->
                                    <circle cx="50" cy="50" r="45" stroke="#e5e7eb" stroke-width="8" fill="none"/>
                                    <!-- Cercle de progression -->
                                    <circle cx="50" cy="50" r="45" 
                                            stroke="{{ $progression >= 100 ? '#10b981' : '#6366f1' }}" 
                                            stroke-width="8" 
                                            fill="none"
                                            stroke-dasharray="{{ $strokeDasharray }}"
                                            stroke-dashoffset="{{ $strokeDashoffset }}"
                                            stroke-linecap="round"
                                            class="transition-all duration-1000"/>
                                </svg>
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <span class="text-2xl font-bold {{ $progression >= 100 ? 'text-green-600' : 'text-indigo-600' }}">
                                        {{ round($progression, 1) }}%
                                    </span>
                                </div>
                            </div>
                            <div class="mt-4">
                                <p class="text-sm text-gray-600">Facture payée à {{ round($progression, 1) }}%</p>
                                @if($progression >= 100)
                                    <div class="mt-2 inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        Facture soldée
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Informations facture -->
                <div class="bg-white shadow-xl rounded-2xl overflow-hidden">
                    <div class="px-6 py-4 bg-gradient-to-r from-purple-50 to-pink-50 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Informations facture</h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Numéro</label>
                                <p class="mt-1 font-semibold text-gray-900">{{ $facture->numero }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Date d'émission</label>
                                <p class="mt-1 text-gray-900">{{ $facture->created_at->format('d/m/Y') }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Date d'échéance</label>
                                <p class="mt-1 text-gray-900">
                                    {{ $facture->date_echeance ? $facture->date_echeance->format('d/m/Y') : '-' }}
                                    @if($facture->date_echeance && $facture->date_echeance->isPast() && !$facture->estPayee())
                                        <span class="block text-red-600 text-sm">En retard</span>
                                    @endif
                                </p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Client</label>
                                <p class="mt-1 text-gray-900">{{ $chantier->client->name }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Commercial</label>
                                <p class="mt-1 text-gray-900">{{ $facture->commercial->name }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions rapides -->
                <div class="bg-white shadow-xl rounded-2xl overflow-hidden">
                    <div class="px-6 py-4 bg-gradient-to-r from-blue-50 to-indigo-50 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Actions rapides</h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-3">
                            <a href="{{ route('chantiers.factures.show', [$chantier, $facture]) }}"
                               class="w-full flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                Voir la facture
                            </a>
                            
                            <a href="{{ route('factures.pdf', [$chantier, $facture]) }}" 
                               target="_blank"
                               class="w-full flex items-center justify-center px-4 py-2 border border-red-300 rounded-md shadow-sm text-sm font-medium text-red-700 bg-red-50 hover:bg-red-100">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2z"></path>
                                </svg>
                                Télécharger PDF
                            </a>

                            @can('gererPaiements', $facture)
                                @if(!$facture->estPayee() && $facture->statut === 'envoyee')
                                    <form action="{{ route('factures.relance', [$chantier, $facture]) }}" method="POST">
                                        @csrf
                                        <button type="submit"
                                                class="w-full flex items-center justify-center px-4 py-2 border border-orange-300 rounded-md shadow-sm text-sm font-medium text-orange-700 bg-orange-50 hover:bg-orange-100">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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

<!-- Modales d'édition des paiements -->
@foreach($facture->paiements as $paiement)
    <div id="editModal_{{ $paiement->id }}" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Modifier le paiement</h3>
                    <button onclick="closeModal('editModal_{{ $paiement->id }}')" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <form action="{{ route('paiements.update', $paiement) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="montant_{{ $paiement->id }}" class="block text-sm font-medium text-gray-700">
                                Montant *
                            </label>
                            <input type="number" 
                                   name="montant" 
                                   id="montant_{{ $paiement->id }}"
                                   step="0.01"
                                   value="{{ $paiement->montant }}"
                                   class="mt-1 form-input"
                                   required>
                        </div>
                        
                        <div>
                            <label for="date_paiement_{{ $paiement->id }}" class="block text-sm font-medium text-gray-700">
                                Date de paiement *
                            </label>
                            <input type="date" 
                                   name="date_paiement" 
                                   id="date_paiement_{{ $paiement->id }}"
                                   value="{{ $paiement->date_paiement->format('Y-m-d') }}"
                                   class="mt-1 form-input"
                                   required>
                        </div>
                        
                        <div>
                            <label for="mode_paiement_{{ $paiement->id }}" class="block text-sm font-medium text-gray-700">
                                Mode de paiement *
                            </label>
                            <select name="mode_paiement" 
                                    id="mode_paiement_{{ $paiement->id }}"
                                    class="mt-1 form-select"
                                    required>
                                <option value="virement" {{ $paiement->mode_paiement === 'virement' ? 'selected' : '' }}>Virement</option>
                                <option value="cheque" {{ $paiement->mode_paiement === 'cheque' ? 'selected' : '' }}>Chèque</option>
                                <option value="especes" {{ $paiement->mode_paiement === 'especes' ? 'selected' : '' }}>Espèces</option>
                                <option value="cb" {{ $paiement->mode_paiement === 'cb' ? 'selected' : '' }}>Carte bancaire</option>
                                <option value="prelevement" {{ $paiement->mode_paiement === 'prelevement' ? 'selected' : '' }}>Prélèvement</option>
                                <option value="autre" {{ $paiement->mode_paiement === 'autre' ? 'selected' : '' }}>Autre</option>
                            </select>
                        </div>
                        
                        <div>
                            <label for="reference_paiement_{{ $paiement->id }}" class="block text-sm font-medium text-gray-700">
                                Référence
                            </label>
                            <input type="text" 
                                   name="reference_paiement" 
                                   id="reference_paiement_{{ $paiement->id }}"
                                   value="{{ $paiement->reference_paiement }}"
                                   class="mt-1 form-input">
                        </div>
                        
                        <div>
                            <label for="banque_{{ $paiement->id }}" class="block text-sm font-medium text-gray-700">
                                Banque
                            </label>
                            <input type="text" 
                                   name="banque" 
                                   id="banque_{{ $paiement->id }}"
                                   value="{{ $paiement->banque }}"
                                   class="mt-1 form-input">
                        </div>
                        
                        <div class="md:col-span-2">
                            <label for="commentaire_{{ $paiement->id }}" class="block text-sm font-medium text-gray-700">
                                Commentaire
                            </label>
                            <textarea name="commentaire" 
                                      id="commentaire_{{ $paiement->id }}"
                                      rows="2"
                                      class="mt-1 form-textarea">{{ $paiement->commentaire }}</textarea>
                        </div>
                    </div>
                    
                    <div class="flex justify-end space-x-3">
                        <button type="button" 
                                onclick="closeModal('editModal_{{ $paiement->id }}')"
                                class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Annuler
                        </button>
                        <button type="submit" 
                                class="px-4 py-2 bg-blue-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-blue-700">
                            Modifier
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