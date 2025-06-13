@extends('layouts.app')

@section('title', 'Modifier la facture ' . $facture->numero)

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
                        <a href="{{ route('chantiers.factures.show', [$chantier, $facture]) }}" class="hover:text-gray-700">{{ $facture->numero }}</a>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                        <span class="text-gray-900 font-medium">Modifier</span>
                    </nav>
                    <h1 class="mt-2 text-2xl font-bold text-gray-900">
                        Modifier la facture {{ $facture->numero }}
                    </h1>
                    <p class="text-gray-600">{{ $facture->titre }}</p>
                </div>
                
                <div class="flex items-center space-x-2">
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
            </div>
        </div>
    </div>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @if($facture->estPayee())
            <div class="rounded-md bg-yellow-50 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-yellow-800">
                            Attention : Facture payée
                        </h3>
                        <div class="mt-2 text-sm text-yellow-700">
                            <p>Cette facture est déjà payée. La modification pourrait avoir des conséquences sur la comptabilité.</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <form action="{{ route('chantiers.factures.update', [$chantier, $facture]) }}" method="POST" x-data="factureEditForm()">
            @csrf
            @method('PUT')
            
            <div class="space-y-8">
                <!-- Informations générales -->
                <div class="bg-white shadow-xl rounded-2xl overflow-hidden">
                    <div class="px-6 py-4 bg-gradient-to-r from-purple-50 to-indigo-50 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                            <svg class="w-6 h-6 text-purple-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Informations générales
                        </h3>
                    </div>
                    
                    <div class="p-6">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <div>
                                <label for="titre" class="block text-sm font-medium text-gray-700 mb-2">
                                    Titre de la facture *
                                </label>
                                <input type="text" 
                                       name="titre" 
                                       id="titre" 
                                       value="{{ old('titre', $facture->titre) }}"
                                       class="form-input @error('titre') border-red-300 @enderror"
                                       required>
                                @error('titre')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="date_echeance" class="block text-sm font-medium text-gray-700 mb-2">
                                    Date d'échéance *
                                </label>
                                <input type="date" 
                                       name="date_echeance" 
                                       id="date_echeance" 
                                       value="{{ old('date_echeance', $facture->date_echeance?->format('Y-m-d')) }}"
                                       class="form-input @error('date_echeance') border-red-300 @enderror"
                                       required>
                                @error('date_echeance')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="delai_paiement" class="block text-sm font-medium text-gray-700 mb-2">
                                    Délai de paiement (jours) *
                                </label>
                                <input type="number" 
                                       name="delai_paiement" 
                                       id="delai_paiement" 
                                       value="{{ old('delai_paiement', $facture->delai_paiement) }}"
                                       class="form-input @error('delai_paiement') border-red-300 @enderror"
                                       min="1"
                                       required>
                                @error('delai_paiement')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="reference_commande" class="block text-sm font-medium text-gray-700 mb-2">
                                    Référence commande
                                </label>
                                <input type="text" 
                                       name="reference_commande" 
                                       id="reference_commande" 
                                       value="{{ old('reference_commande', $facture->reference_commande) }}"
                                       class="form-input @error('reference_commande') border-red-300 @enderror"
                                       placeholder="N° de commande, bon de commande...">
                                @error('reference_commande')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="lg:col-span-2">
                                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                                    Description
                                </label>
                                <textarea name="description" 
                                          id="description" 
                                          rows="3" 
                                          class="form-textarea @error('description') border-red-300 @enderror">{{ old('description', $facture->description) }}</textarea>
                                @error('description')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Lignes de la facture -->
                <div class="bg-white shadow-xl rounded-2xl overflow-hidden">
                    <div class="px-6 py-4 bg-gradient-to-r from-green-50 to-emerald-50 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                <svg class="w-6 h-6 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2H9z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 9l2 2 4-4"></path>
                                </svg>
                                Lignes de facturation
                            </h3>
                            <button type="button" 
                                    @click="ajouterLigne()"
                                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Ajouter une ligne
                            </button>
                        </div>
                    </div>
                    
                    <div class="p-6">
                        <div class="overflow-x-auto">
                            <table class="min-w-full">
                                <thead>
                                    <tr class="border-b border-gray-200">
                                        <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-2">Désignation</th>
                                        <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-2">Qté</th>
                                        <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-2">Unité</th>
                                        <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-2">Prix unitaire HT</th>
                                        <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-2">TVA %</th>
                                        <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-2">Remise %</th>
                                        <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-2">Total HT</th>
                                        <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-2">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="(ligne, index) in lignes" :key="index">
                                        <tr class="border-b border-gray-100">
                                            <td class="py-3">
                                                <input type="text" 
                                                       :name="`lignes[${index}][designation]`"
                                                       x-model="ligne.designation"
                                                       @input="calculerLigne(index)"
                                                       class="form-input text-sm"
                                                       placeholder="Désignation..."
                                                       required>
                                                <input type="text" 
                                                       :name="`lignes[${index}][description]`"
                                                       x-model="ligne.description"
                                                       class="form-input text-xs mt-1"
                                                       placeholder="Description (optionnel)">
                                                <input type="text" 
                                                       :name="`lignes[${index}][categorie]`"
                                                       x-model="ligne.categorie"
                                                       class="form-input text-xs mt-1"
                                                       placeholder="Catégorie (optionnel)">
                                            </td>
                                            <td class="py-3">
                                                <input type="number" 
                                                       :name="`lignes[${index}][quantite]`"
                                                       x-model="ligne.quantite"
                                                       @input="calculerLigne(index)"
                                                       class="form-input text-sm w-20"
                                                       step="0.01"
                                                       min="0"
                                                       required>
                                            </td>
                                            <td class="py-3">
                                                <select :name="`lignes[${index}][unite]`"
                                                        x-model="ligne.unite"
                                                        class="form-select text-sm w-24">
                                                    <option value="pièce">pièce</option>
                                                    <option value="m²">m²</option>
                                                    <option value="ml">ml</option>
                                                    <option value="heure">heure</option>
                                                    <option value="jour">jour</option>
                                                    <option value="forfait">forfait</option>
                                                    <option value="kg">kg</option>
                                                    <option value="litre">litre</option>
                                                </select>
                                            </td>
                                            <td class="py-3">
                                                <input type="number" 
                                                       :name="`lignes[${index}][prix_unitaire_ht]`"
                                                       x-model="ligne.prix_unitaire_ht"
                                                       @input="calculerLigne(index)"
                                                       class="form-input text-sm w-28"
                                                       step="0.01"
                                                       min="0"
                                                       required>
                                            </td>
                                            <td class="py-3">
                                                <input type="number" 
                                                       :name="`lignes[${index}][taux_tva]`"
                                                       x-model="ligne.taux_tva"
                                                       @input="calculerLigne(index)"
                                                       class="form-input text-sm w-20"
                                                       step="0.01"
                                                       min="0"
                                                       max="100">
                                            </td>
                                            <td class="py-3">
                                                <input type="number" 
                                                       :name="`lignes[${index}][remise_pourcentage]`"
                                                       x-model="ligne.remise_pourcentage"
                                                       @input="calculerLigne(index)"
                                                       class="form-input text-sm w-20"
                                                       step="0.01"
                                                       min="0"
                                                       max="100">
                                            </td>
                                            <td class="py-3">
                                                <div class="text-sm font-medium text-gray-900" x-text="formatMoney(ligne.total_ht)"></div>
                                            </td>
                                            <td class="py-3">
                                                <button type="button" 
                                                        @click="supprimerLigne(index)"
                                                        class="text-red-600 hover:text-red-900">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                </button>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                                <tfoot class="border-t-2 border-gray-300">
                                    <tr>
                                        <td colspan="6" class="py-4 text-right text-lg font-semibold text-gray-900">
                                            Total HT :
                                        </td>
                                        <td class="py-4 text-lg font-bold text-gray-900" x-text="formatMoney(totalHT)"></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td colspan="6" class="py-2 text-right text-gray-700">
                                            TVA :
                                        </td>
                                        <td class="py-2 text-gray-900" x-text="formatMoney(totalTVA)"></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td colspan="6" class="py-2 text-right text-xl font-bold text-gray-900">
                                            Total TTC :
                                        </td>
                                        <td class="py-2 text-xl font-bold text-purple-600" x-text="formatMoney(totalTTC)"></td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        
                        <div x-show="lignes.length === 0" class="text-center py-8">
                            <p class="text-gray-500 mb-4">Aucune ligne</p>
                            <button type="button" 
                                    @click="ajouterLigne()"
                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700">
                                Ajouter une ligne
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Paramètres et conditions -->
                <div class="bg-white shadow-xl rounded-2xl overflow-hidden">
                    <div class="px-6 py-4 bg-gradient-to-r from-indigo-50 to-purple-50 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                            <svg class="w-6 h-6 text-indigo-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            Paramètres et conditions
                        </h3>
                    </div>
                    
                    <div class="p-6">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <div>
                                <label for="taux_tva" class="block text-sm font-medium text-gray-700 mb-2">
                                    Taux TVA par défaut (%) *
                                </label>
                                <input type="number" 
                                       name="taux_tva" 
                                       id="taux_tva" 
                                       value="{{ old('taux_tva', $facture->taux_tva) }}"
                                       class="form-input @error('taux_tva') border-red-300 @enderror"
                                       step="0.01"
                                       min="0"
                                       max="100"
                                       required>
                                @error('taux_tva')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="conditions_reglement" class="block text-sm font-medium text-gray-700 mb-2">
                                    Conditions de règlement
                                </label>
                                <input type="text" 
                                       name="conditions_reglement" 
                                       id="conditions_reglement" 
                                       value="{{ old('conditions_reglement', $facture->conditions_reglement) }}"
                                       class="form-input @error('conditions_reglement') border-red-300 @enderror"
                                       placeholder="Ex: Paiement à 30 jours fin de mois">
                                @error('conditions_reglement')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="lg:col-span-2">
                                <label for="notes_internes" class="block text-sm font-medium text-gray-700 mb-2">
                                    Notes internes
                                </label>
                                <textarea name="notes_internes" 
                                          id="notes_internes" 
                                          rows="3" 
                                          class="form-textarea @error('notes_internes') border-red-300 @enderror"
                                          placeholder="Notes visibles uniquement par votre équipe">{{ old('notes_internes', $facture->notes_internes) }}</textarea>
                                @error('notes_internes')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-between bg-white px-6 py-4 rounded-xl shadow-lg">
                    <a href="{{ route('chantiers.factures.show', [$chantier, $facture]) }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Annuler
                    </a>
                    
                    <div class="flex space-x-3">
                        <button type="submit" 
                                :disabled="lignes.length === 0"
                                class="inline-flex items-center px-6 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
                            </svg>
                            Enregistrer les modifications
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function factureEditForm() {
    return {
        lignes: @json($facture->lignes->map(function($ligne) {
            return [
                'designation' => $ligne->designation,
                'description' => $ligne->description,
                'quantite' => $ligne->quantite,
                'unite' => $ligne->unite,
                'prix_unitaire_ht' => $ligne->prix_unitaire_ht,
                'taux_tva' => $ligne->taux_tva,
                'remise_pourcentage' => $ligne->remise_pourcentage ?? 0,
                'categorie' => $ligne->categorie,
                'total_ht' => $ligne->montant_ht
            ];
        })->values()),
        
        init() {
            // Recalculer tous les totaux au chargement
            this.lignes.forEach((ligne, index) => {
                this.calculerLigne(index);
            });
        },
        
        ajouterLigne() {
            this.lignes.push({
                designation: '',
                description: '',
                quantite: 1,
                unite: 'pièce',
                prix_unitaire_ht: 0,
                taux_tva: {{ $facture->taux_tva }},
                remise_pourcentage: 0,
                categorie: '',
                total_ht: 0
            });
        },
        
        supprimerLigne(index) {
            if (confirm('Êtes-vous sûr de vouloir supprimer cette ligne ?')) {
                this.lignes.splice(index, 1);
            }
        },
        
        calculerLigne(index) {
            const ligne = this.lignes[index];
            const quantite = parseFloat(ligne.quantite) || 0;
            const prixUnitaire = parseFloat(ligne.prix_unitaire_ht) || 0;
            const remise = parseFloat(ligne.remise_pourcentage) || 0;
            
            const totalBrut = quantite * prixUnitaire;
            const montantRemise = totalBrut * (remise / 100);
            ligne.total_ht = totalBrut - montantRemise;
        },
        
        get totalHT() {
            return this.lignes.reduce((total, ligne) => total + (ligne.total_ht || 0), 0);
        },
        
        get totalTVA() {
            return this.lignes.reduce((total, ligne) => {
                const tva = parseFloat(ligne.taux_tva) || 0;
                return total + ((ligne.total_ht || 0) * (tva / 100));
            }, 0);
        },
        
        get totalTTC() {
            return this.totalHT + this.totalTVA;
        },
        
        formatMoney(amount) {
            return new Intl.NumberFormat('fr-FR', {
                style: 'currency',
                currency: 'EUR'
            }).format(amount || 0);
        }
    }
}
</script>
@endsection