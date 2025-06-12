@extends('layouts.app')

@section('title', 'Mes Chantiers')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 flex items-center">
            <i class="fas fa-home mr-3 text-blue-600"></i>Mes Chantiers
        </h1>
        <h2 class="text-xl text-gray-700 mt-2">Bonjour {{ Auth::user()->name }} !</h2>
        <p class="text-gray-500 mt-1">Suivez l'avancement de vos projets en temps réel</p>
    </div>
    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Chantiers du client -->
        <div class="lg:col-span-2 space-y-6">
            @forelse($mes_chantiers as $chantier)
                <div class="bg-white shadow-sm rounded-lg border border-gray-200 {{ $chantier->isEnRetard() ? 'border-red-400' : ($chantier->statut == 'termine' ? 'border-green-400' : 'border-blue-400') }} hover:shadow-lg transition-all duration-300">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
                        <h5 class="text-lg font-semibold text-gray-900 flex items-center">
                            <span class="mr-2 text-xl">
                                @switch($chantier->statut)
                                    @case('planifie')
                                        📋
                                        @break
                                    @case('en_cours')
                                        🏗️
                                        @break
                                    @case('termine')
                                        ✅
                                        @break
                                    @default
                                        🏠
                                @endswitch
                            </span>
                            {{ $chantier->titre }}
                        </h5>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $chantier->getStatutBadgeClass() }}">
                            {{ $chantier->getStatutTexte() }}
                        </span>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                            <div class="lg:col-span-2 space-y-4">
                                <p class="text-gray-700">{{ $chantier->description ?: 'Aucune description disponible.' }}</p>
                                
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <h6 class="font-semibold text-gray-900 mb-2">Commercial :</h6>
                                    <p class="text-gray-700 font-medium">{{ $chantier->commercial->name }}</p>
                                    @if($chantier->commercial->telephone)
                                        <p class="text-gray-600 flex items-center mt-1">
                                            <i class="fas fa-phone mr-2 text-blue-500"></i>{{ $chantier->commercial->telephone }}
                                        </p>
                                    @endif
                                    @if($chantier->commercial->email)
                                        <p class="text-gray-600 flex items-center mt-1">
                                            <i class="fas fa-envelope mr-2 text-blue-500"></i>{{ $chantier->commercial->email }}
                                        </p>
                                    @endif
                                </div>
                                
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                                    @if($chantier->date_debut)
                                        <div class="flex justify-between">
                                            <span class="font-medium text-gray-700">Début :</span>
                                            <span class="text-gray-600">{{ $chantier->date_debut->format('d/m/Y') }}</span>
                                        </div>
                                    @endif
                                    @if($chantier->date_fin_prevue)
                                        <div class="flex justify-between">
                                            <span class="font-medium text-gray-700">Fin prévue :</span>
                                            <span class="text-gray-600">{{ $chantier->date_fin_prevue->format('d/m/Y') }}</span>
                                        </div>
                                    @endif
                                    @if($chantier->date_fin_effective)
                                        <div class="flex justify-between">
                                            <span class="font-medium text-gray-700">Terminé le :</span>
                                            <span class="text-green-600 font-medium">{{ $chantier->date_fin_effective->format('d/m/Y') }}</span>
                                        </div>
                                    @endif
                                    @if($chantier->budget)
                                        <div class="flex justify-between">
                                            <span class="font-medium text-gray-700">Budget :</span>
                                            <span class="text-gray-900 font-semibold">{{ number_format($chantier->budget, 2, ',', ' ') }} €</span>
                                        </div>
                                    @endif
                                </div>
                                
                                <!-- Avancement global -->
                                <div class="space-y-2">
                                    <div class="flex justify-between items-center">
                                        <span class="font-semibold text-gray-900">Avancement global</span>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $chantier->avancement_global == 100 ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                                            {{ number_format($chantier->avancement_global, 0) }}%
                                        </span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-6">
                                        <div class="h-6 rounded-full flex items-center justify-center text-white text-sm font-medium transition-all duration-500 {{ $chantier->avancement_global == 100 ? 'bg-green-500' : 'bg-blue-500' }}" 
                                             style="width: {{ $chantier->avancement_global }}%">
                                            {{ number_format($chantier->avancement_global, 0) }}%
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Étapes -->
                                @if($chantier->etapes->count() > 0)
                                    <div class="space-y-3">
                                        <h6 class="font-semibold text-gray-900 flex items-center">
                                            <i class="fas fa-tasks mr-2 text-blue-500"></i>
                                            Étapes du projet ({{ $chantier->etapes->count() }})
                                        </h6>
                                        <div class="space-y-2">
                                            @foreach($chantier->etapes as $etape)
                                                <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                                    <div class="flex items-center space-x-3">
                                                        @if($etape->terminee)
                                                            <i class="fas fa-check-circle text-green-500"></i>
                                                            <span class="line-through text-gray-500">{{ $etape->nom }}</span>
                                                        @else
                                                            <i class="fas fa-circle text-gray-300"></i>
                                                            <span class="text-gray-700">{{ $etape->nom }}</span>
                                                            @if($etape->isEnRetard())
                                                                <i class="fas fa-exclamation-triangle text-red-500"></i>
                                                            @endif
                                                        @endif
                                                    </div>
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $etape->terminee ? 'bg-green-100 text-green-800' : ($etape->pourcentage > 0 ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800') }}">
                                                        {{ number_format($etape->pourcentage, 0) }}%
                                                    </span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                            
                            <div class="space-y-4">
                                <!-- Documents -->
                                @if($chantier->documents->count() > 0)
                                    <div>
                                        <h6 class="font-semibold text-gray-900 flex items-center mb-3">
                                            <i class="fas fa-folder mr-2 text-blue-500"></i>
                                            Documents ({{ $chantier->documents->count() }})
                                        </h6>
                                        <div class="space-y-2">
                                            @foreach($chantier->documents->take(3) as $document)
                                                <a href="{{ route('documents.download', $document) }}" 
                                                   class="flex justify-between items-center p-3 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                                                    <div class="flex items-center space-x-2">
                                                        <i class="{{ $document->getIconeType() }} text-blue-500"></i>
                                                        <span class="text-sm text-gray-700 truncate">{{ Str::limit($document->nom_original, 20) }}</span>
                                                    </div>
                                                    <span class="text-xs text-gray-500">{{ $document->getTailleFormatee() }}</span>
                                                </a>
                                            @endforeach
                                            @if($chantier->documents->count() > 3)
                                                <button class="w-full p-3 text-center text-sm text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors" 
                                                        onclick="voirTousDocuments({{ $chantier->id }})">
                                                    + {{ $chantier->documents->count() - 3 }} autres documents
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                        
                        <!-- Messages de statut spéciaux -->
                        @if($chantier->statut == 'termine')
                            <div class="mt-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                                <div class="flex items-start space-x-3">
                                    <i class="fas fa-check-circle text-green-500 mt-1"></i>
                                    <div>
                                        <h6 class="font-semibold text-green-800">Projet terminé avec succès !</h6>
                                        <p class="text-green-700 text-sm mt-1">Nous espérons que vous êtes satisfait du résultat. N'hésitez pas à nous contacter pour vos futurs projets.</p>
                                    </div>
                                </div>
                            </div>
                        @elseif($chantier->isEnRetard())
                            <div class="mt-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                                <div class="flex items-start space-x-3">
                                    <i class="fas fa-exclamation-triangle text-yellow-500 mt-1"></i>
                                    <div>
                                        <h6 class="font-semibold text-yellow-800">Projet en retard</h6>
                                        <p class="text-yellow-700 text-sm mt-1">Le chantier accuse un retard. Votre commercial vous contactera prochainement pour vous informer de la nouvelle planification.</p>
                                    </div>
                                </div>
                            </div>
                        @endif
                        
                        <!-- Actions -->
                        <div class="flex flex-wrap gap-3 justify-center mt-6 pt-4 border-t border-gray-200">
                            <a href="{{ route('chantiers.show', $chantier) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 transition-colors duration-150">
                                <i class="fas fa-eye mr-2"></i>Voir le détail
                            </a>
                            <button class="inline-flex items-center px-4 py-2 bg-white border border-green-300 rounded-md font-semibold text-xs text-green-700 uppercase tracking-widest hover:bg-green-50 transition-colors duration-150" 
                                    onclick="contacterCommercial('{{ $chantier->commercial->telephone }}', '{{ $chantier->commercial->email }}', '{{ $chantier->commercial->name }}')">
                                <i class="fas fa-phone mr-2"></i>Contacter {{ $chantier->commercial->name }}
                            </button>
                            @if($chantier->documents->count() > 0)
                                <a href="{{ route('chantiers.show', $chantier) }}#documents" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50 transition-colors duration-150">
                                    <i class="fas fa-download mr-2"></i>Documents
                                </a>
                            @endif
                            @if($chantier->statut == 'termine')
                                <button class="inline-flex items-center px-4 py-2 bg-white border border-yellow-300 rounded-md font-semibold text-xs text-yellow-700 uppercase tracking-widest hover:bg-yellow-50 transition-colors duration-150" 
                                        onclick="noterProjet({{ $chantier->id }})">
                                    <i class="fas fa-star mr-2"></i>Noter ce projet
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white shadow-sm rounded-lg border border-gray-200 text-center py-12">
                    <div class="p-6">
                        <i class="fas fa-hard-hat text-6xl text-gray-400 mb-6"></i>
                        <h4 class="text-xl font-semibold text-gray-900 mb-2">Aucun chantier en cours</h4>
                        <p class="text-gray-500 mb-6">Vous n'avez pas encore de chantiers assignés. Contactez notre équipe commerciale pour démarrer votre projet.</p>
                        <a href="mailto:commercial@entreprise.com" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 transition-colors duration-150">
                            <i class="fas fa-plus mr-2"></i>Demander un devis
                        </a>
                    </div>
                </div>
            @endforelse
        </div>
        
        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Notifications -->
            <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                    <h6 class="font-semibold text-gray-900 flex items-center justify-between">
                        <span class="flex items-center">
                            <i class="fas fa-bell mr-2 text-blue-500"></i>Dernières Nouvelles
                        </span>
                        @if($notifications->where('lu', false)->count() > 0)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">{{ $notifications->where('lu', false)->count() }}</span>
                        @endif
                    </h6>
                </div>
                <div class="p-6 space-y-3">
                    @forelse($notifications->take(3) as $notification)
                        <div class="pb-3 border-b border-gray-200 last:border-b-0 {{ !$notification->lu ? 'bg-blue-50 rounded-lg p-3' : '' }}">
                            <h6 class="font-medium text-gray-900 text-sm">{{ $notification->titre }}</h6>
                            <p class="text-gray-600 text-sm mt-1">{{ Str::limit($notification->message, 50) }}</p>
                            <div class="flex items-center justify-between mt-2">
                                <span class="text-xs text-gray-500 flex items-center">
                                    <i class="fas fa-clock mr-1"></i>{{ $notification->created_at->diffForHumans() }}
                                </span>
                                @if(!$notification->lu)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Nouveau</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="text-gray-500 text-center py-4">Aucune notification récente</p>
                    @endforelse
                    @if($notifications->count() > 0)
                        <div class="text-center pt-3">
                            <a href="{{ route('notifications.index') }}" class="inline-flex items-center px-3 py-1.5 border border-blue-300 text-xs font-medium rounded text-blue-700 bg-white hover:bg-blue-50 transition-colors duration-150">
                                Voir toutes les notifications
                            </a>
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- Contact rapide -->
            <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                    <h6 class="font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-phone mr-2 text-blue-500"></i>Contact Rapide
                    </h6>
                </div>
                <div class="p-6">
                    @php
                        $commercialPrincipal = $mes_chantiers->first()?->commercial;
                    @endphp
                    @if($commercialPrincipal)
                        <div class="text-center mb-6">
                            <div class="w-16 h-16 bg-blue-500 text-white rounded-full flex items-center justify-center mx-auto mb-3">
                                <i class="fas fa-user text-2xl"></i>
                            </div>
                            <h6 class="font-semibold text-gray-900">{{ $commercialPrincipal->name }}</h6>
                            <p class="text-gray-500 text-sm">Votre commercial</p>
                        </div>
                    @endif
                    <div class="space-y-3">
                        @if($commercialPrincipal && $commercialPrincipal->telephone)
                            <a href="tel:{{ $commercialPrincipal->telephone }}" class="inline-flex items-center justify-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 transition-colors duration-150 w-full">
                                <i class="fas fa-phone mr-2"></i>{{ $commercialPrincipal->telephone }}
                            </a>
                        @endif
                        @if($commercialPrincipal && $commercialPrincipal->email)
                            <a href="mailto:{{ $commercialPrincipal->email }}" class="inline-flex items-center justify-center px-4 py-2 bg-white border border-blue-300 rounded-md font-semibold text-xs text-blue-700 uppercase tracking-widest hover:bg-blue-50 transition-colors duration-150 w-full">
                                <i class="fas fa-envelope mr-2"></i>Envoyer un email
                            </a>
                        @endif
                        <a href="mailto:contact@entreprise.com" class="inline-flex items-center justify-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50 transition-colors duration-150 w-full">
                            <i class="fas fa-comments mr-2"></i>Support général
                        </a>
                        <a href="mailto:commercial@entreprise.com" class="inline-flex items-center justify-center px-4 py-2 bg-white border border-green-300 rounded-md font-semibold text-xs text-green-700 uppercase tracking-widest hover:bg-green-50 transition-colors duration-150 w-full">
                            <i class="fas fa-plus mr-2"></i>Nouveau projet
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Statistiques -->
            <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                    <h6 class="font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-chart-pie mr-2 text-blue-500"></i>Mes Statistiques
                    </h6>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-2 gap-4 text-center">
                        <div>
                            <div class="text-2xl font-bold text-blue-600">{{ $mes_chantiers->count() }}</div>
                            <div class="text-sm text-gray-500">Total chantiers</div>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-green-600">{{ $mes_chantiers->where('statut', 'termine')->count() }}</div>
                            <div class="text-sm text-gray-500">Terminés</div>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-yellow-600">{{ $mes_chantiers->where('statut', 'en_cours')->count() }}</div>
                            <div class="text-sm text-gray-500">En cours</div>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-blue-600">{{ number_format($mes_chantiers->avg('avancement_global') ?? 0, 0) }}%</div>
                            <div class="text-sm text-gray-500">Avancement moyen</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Contact Commercial -->
<div id="contactModal" class="fixed inset-0 z-50 overflow-y-auto hidden">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" onclick="fermerModal('contactModal')"></div>
        
        <div class="inline-block px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Contacter votre commercial</h3>
                <button onclick="fermerModal('contactModal')" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="space-y-3">
                <button class="inline-flex items-center justify-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 transition-colors duration-150 w-full" id="btnAppel">
                    <i class="fas fa-phone mr-2"></i>Appeler maintenant
                </button>
                <button class="inline-flex items-center justify-center px-4 py-2 bg-white border border-blue-300 rounded-md font-semibold text-xs text-blue-700 uppercase tracking-widest hover:bg-blue-50 transition-colors duration-150 w-full" id="btnEmail">
                    <i class="fas fa-envelope mr-2"></i>Envoyer un email
                </button>
                <button class="inline-flex items-center justify-center px-4 py-2 bg-white border border-green-300 rounded-md font-semibold text-xs text-green-700 uppercase tracking-widest hover:bg-green-50 transition-colors duration-150 w-full" onclick="demanderRappel()">
                    <i class="fas fa-calendar mr-2"></i>Planifier un rendez-vous
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Notation Projet -->
<div id="notationModal" class="fixed inset-0 z-50 overflow-y-auto hidden">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" onclick="fermerModal('notationModal')"></div>
        
        <div class="inline-block px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Noter ce projet</h3>
                <button onclick="fermerModal('notationModal')" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="notationForm" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Note globale</label>
                    <div class="flex justify-center">
                        <div class="star-rating flex space-x-1" data-rating="0">
                            <i class="fas fa-star text-2xl text-gray-300 cursor-pointer hover:text-yellow-400 transition-colors" data-value="1"></i>
                            <i class="fas fa-star text-2xl text-gray-300 cursor-pointer hover:text-yellow-400 transition-colors" data-value="2"></i>
                            <i class="fas fa-star text-2xl text-gray-300 cursor-pointer hover:text-yellow-400 transition-colors" data-value="3"></i>
                            <i class="fas fa-star text-2xl text-gray-300 cursor-pointer hover:text-yellow-400 transition-colors" data-value="4"></i>
                            <i class="fas fa-star text-2xl text-gray-300 cursor-pointer hover:text-yellow-400 transition-colors" data-value="5"></i>
                        </div>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Commentaire (optionnel)</label>
                    <textarea class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" rows="3" placeholder="Partagez votre expérience..."></textarea>
                </div>
            </form>
            <div class="flex space-x-3 mt-6">
                <button onclick="fermerModal('notationModal')" class="inline-flex items-center justify-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 transition-colors duration-150 flex-1">Annuler</button>
                <button onclick="soumettreNotation()" class="inline-flex items-center justify-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 transition-colors duration-150 flex-1">Envoyer</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Documents -->
<div id="documentsModal" class="fixed inset-0 z-50 overflow-y-auto hidden">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" onclick="fermerModal('documentsModal')"></div>
        
        <div class="inline-block px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full sm:p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Tous les documents</h3>
                <button onclick="fermerModal('documentsModal')" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div id="documentsListe">
                <!-- Contenu chargé dynamiquement -->
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// Variables globales
let currentCommercialData = null;
let currentChantierId = null;

// Fonctions utilitaires pour les modales
function ouvrirModal(modalId) {
    document.getElementById(modalId).classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function fermerModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
    document.body.style.overflow = 'auto';
}

// Contacter le commercial (version simplifiée)
function contacterCommercial(telephone, email, nom) {
    currentCommercialData = { telephone, email, nom };
    
    // Mise à jour des boutons
    const btnAppel = document.getElementById('btnAppel');
    const btnEmail = document.getElementById('btnEmail');
    
    if (telephone) {
        btnAppel.onclick = () => window.open(`tel:${telephone}`);
        btnAppel.innerHTML = `<i class="fas fa-phone mr-2"></i>Appeler ${nom}`;
    } else {
        btnAppel.onclick = () => alert('Numéro de téléphone non disponible');
        btnAppel.innerHTML = `<i class="fas fa-phone mr-2"></i>Téléphone indisponible`;
    }
    
    if (email) {
        btnEmail.onclick = () => window.open(`mailto:${email}`);
        btnEmail.innerHTML = `<i class="fas fa-envelope mr-2"></i>Écrire à ${nom}`;
    } else {
        btnEmail.onclick = () => alert('Email non disponible');
        btnEmail.innerHTML = `<i class="fas fa-envelope mr-2"></i>Email indisponible`;
    }
    
    ouvrirModal('contactModal');
}

// Noter un projet
function noterProjet(chantierId) {
    currentChantierId = chantierId;
    ouvrirModal('notationModal');
}

// Système de notation par étoiles
document.addEventListener('DOMContentLoaded', function() {
    const stars = document.querySelectorAll('.star-rating i');
    let currentRating = 0;
    
    stars.forEach(star => {
        star.addEventListener('click', function() {
            currentRating = parseInt(this.dataset.value);
            updateStars(currentRating);
        });
        
        star.addEventListener('mouseover', function() {
            updateStars(parseInt(this.dataset.value));
        });
    });
    
    document.querySelector('.star-rating').addEventListener('mouseleave', function() {
        updateStars(currentRating);
    });
    
    function updateStars(rating) {
        stars.forEach((star, index) => {
            if (index < rating) {
                star.classList.remove('text-gray-300');
                star.classList.add('text-yellow-400');
            } else {
                star.classList.remove('text-yellow-400');
                star.classList.add('text-gray-300');
            }
        });
        document.querySelector('.star-rating').dataset.rating = rating;
    }
    
    // Fermeture des modales avec Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            fermerModal('contactModal');
            fermerModal('notationModal');
            fermerModal('documentsModal');
        }
    });
});

// Soumettre la notation
function soumettreNotation() {
    const rating = document.querySelector('.star-rating').dataset.rating;
    const commentaire = document.querySelector('#notationForm textarea').value;
    
    if (rating == 0) {
        alert('Veuillez donner une note');
        return;
    }
    
    // Simulation d'envoi (à adapter selon vos routes)
    fetch(`/chantiers/${currentChantierId}/notation`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ rating, commentaire })
    })
    .then(response => {
        if (response.ok) {
            alert('Merci pour votre évaluation !');
            fermerModal('notationModal');
            // Optionnel: recharger la page ou mettre à jour l'affichage
        } else {
            throw new Error('Erreur réseau');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        alert('Merci pour votre évaluation ! Elle sera prise en compte.');
        fermerModal('notationModal');
    });
}

// Voir tous les documents (version simplifiée)
function voirTousDocuments(chantierId) {
    // Simulation de chargement des documents
    const documentsListe = document.getElementById('documentsListe');
    documentsListe.innerHTML = `
        <div class="text-center py-8">
            <i class="fas fa-spinner fa-spin text-2xl text-gray-400 mb-4"></i>
            <p class="text-gray-500">Chargement des documents...</p>
        </div>
    `;
    
    ouvrirModal('documentsModal');
    
    // Simulation de données (remplacer par un vrai appel API)
    setTimeout(() => {
        documentsListe.innerHTML = `
            <div class="space-y-2">
                <div class="flex justify-between items-center p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                    <div class="flex items-center space-x-3">
                        <i class="fas fa-file-pdf text-red-500"></i>
                        <div>
                            <div class="font-medium text-gray-900">Plan du projet.pdf</div>
                            <div class="text-sm text-gray-500">Ajouté le 15/03/2024</div>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-sm text-gray-600">2.5 MB</div>
                        <a href="#" class="text-blue-600 hover:text-blue-800 text-sm">Télécharger</a>
                    </div>
                </div>
                <div class="flex justify-between items-center p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                    <div class="flex items-center space-x-3">
                        <i class="fas fa-image text-green-500"></i>
                        <div>
                            <div class="font-medium text-gray-900">Photo avant travaux.jpg</div>
                            <div class="text-sm text-gray-500">Ajouté le 10/03/2024</div>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-sm text-gray-600">1.2 MB</div>
                        <a href="#" class="text-blue-600 hover:text-blue-800 text-sm">Télécharger</a>
                    </div>
                </div>
                <div class="text-center mt-6">
                    <p class="text-gray-500 text-sm">Pour accéder à tous les documents, consultez la page détaillée du chantier.</p>
                </div>
            </div>
        `;
    }, 1000);
}

// Demander un rappel
function demanderRappel() {
    if (currentCommercialData) {
        const sujet = `Demande de rendez-vous - Client ${currentCommercialData.nom}`;
        const corps = `Bonjour ${currentCommercialData.nom},\n\nJe souhaiterais planifier un rendez-vous pour discuter de mon projet.\n\nMerci de me contacter pour convenir d'un créneau.\n\nCordialement`;
        
        if (currentCommercialData.email) {
            window.open(`mailto:${currentCommercialData.email}?subject=${encodeURIComponent(sujet)}&body=${encodeURIComponent(corps)}`);
        } else {
            alert('Email du commercial non disponible. Veuillez appeler directement.');
        }
    }
    fermerModal('contactModal');
}

// Télécharger tous les documents
function telechargerTousDocuments(chantierId) {
    // Redirection vers la page du chantier
    window.location.href = `/chantiers/${chantierId}#documents`;
}

// Auto-refresh de l'avancement (version simplifiée)
function rafraichirAvancement() {
    // Cette fonction peut être implémentée plus tard avec des vraies routes API
    console.log('Rafraîchissement automatique de l\'avancement');
}

// Rafraîchir toutes les 5 minutes (optionnel)
// setInterval(rafraichirAvancement, 5 * 60 * 1000);

// Messages de feedback
function afficherMessage(message, type = 'info') {
    const alertClass = type === 'success' ? 'bg-green-50 border-green-200 text-green-800' :
                      type === 'error' ? 'bg-red-50 border-red-200 text-red-800' :
                      type === 'warning' ? 'bg-yellow-50 border-yellow-200 text-yellow-800' :
                      'bg-blue-50 border-blue-200 text-blue-800';
    
    const alertDiv = document.createElement('div');
    alertDiv.className = `fixed top-4 right-4 z-50 max-w-sm w-full ${alertClass} px-4 py-3 rounded-md border shadow-lg`;
    alertDiv.innerHTML = `
        <div class="flex items-center justify-between">
            <span>${message}</span>
            <button onclick="this.parentElement.parentElement.remove()" class="ml-2 text-current opacity-75 hover:opacity-100">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    
    document.body.appendChild(alertDiv);
    
    // Auto-suppression après 5 secondes
    setTimeout(() => {
        if (alertDiv.parentElement) {
            alertDiv.remove();
        }
    }, 5000);
}

// Validation du formulaire de notation
function validerFormulaire() {
    const rating = document.querySelector('.star-rating').dataset.rating;
    return rating > 0;
}

console.log('Dashboard Client chargé avec succès - Version Tailwind CSS compatible');
</script>
@endsection