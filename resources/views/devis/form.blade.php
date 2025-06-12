{{-- resources/views/devis/form.blade.php --}}
@extends('layouts.app')

@section('title', isset($devis->id) ? 'Modifier le devis' : 'Nouveau devis')

@push('styles')
<style>
.ligne-item {
    transition: all 0.3s ease;
}
.ligne-item.removing {
    opacity: 0.5;
    transform: translateX(-10px);
}
</style>
@endpush

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    
    {{-- En-tête --}}
    <div class="mb-8">
        <nav class="flex items-center space-x-2 text-sm text-gray-500 mb-4">
            <a href="{{ route('chantiers.index') }}" class="hover:text-gray-700">Chantiers</a>
            <span>›</span>
            <a href="{{ route('chantiers.show', $chantier) }}" class="hover:text-gray-700">{{ $chantier->titre }}</a>
            <span>›</span>
            <a href="{{ route('chantiers.devis.index', $chantier) }}" class="hover:text-gray-700">Devis</a>
            <span>›</span>
            <span class="text-gray-900">{{ isset($devis->id) ? 'Modifier' : 'Nouveau' }}</span>
        </nav>
        
        <h1 class="text-2xl font-bold text-gray-900">
            {{ isset($devis->id) ? 'Modifier le devis' : 'Nouveau devis' }}
        </h1>
        <p class="text-gray-600">{{ $chantier->titre }}</p>
    </div>

    <form method="POST" 
          action="{{ isset($devis->id) ? route('chantiers.devis.update', [$chantier, $devis]) : route('chantiers.devis.store', $chantier) }}"
          id="devis-form" 
          x-data="devisForm()"
          x-init="init()">
        
        @csrf
        @if(isset($devis->id))
            @method('PUT')
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            {{-- Colonne principale --}}
            <div class="lg:col-span-2 space-y-6">
                
                {{-- Informations générales --}}
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-lg font-medium text-gray-900">Informations générales</h3>
                    </div>
                    
                    <div class="card-body space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="titre" class="form-label required">Titre du devis</label>
                                <input type="text" 
                                       id="titre" 
                                       name="titre" 
                                       class="form-input @error('titre') border-red-300 @enderror"
                                       value="{{ old('titre', $devis->titre ?? '') }}" 
                                       required>
                                @error('titre')
                                    <p class="form-error">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label for="date_validite" class="form-label required">Date de validité</label>
                                <input type="date" 
                                       id="date_validite" 
                                       name="date_validite" 
                                       class="form-input @error('date_validite') border-red-300 @enderror"
                                       value="{{ old('date_validite', $devis->date_validite?->format('Y-m-d') ?? now()->addDays(30)->format('Y-m-d')) }}" 
                                       required>
                                @error('date_validite')
                                    <p class="form-error">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        
                        <div>
                            <label for="description" class="form-label">Description</label>
                            <textarea id="description" 
                                      name="description" 
                                      rows="3" 
                                      class="form-input @error('description') border-red-300 @enderror"
                                      placeholder="Description détaillée du devis...">{{ old('description', $devis->description ?? '') }}</textarea>
                            @error('description')
                                <p class="form-error">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Lignes du devis --}}
                <div class="card">
                    <div class="card-header">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-medium text-gray-900">Lignes du devis</h3>
                            <button type="button" 
                                    @click="ajouterLigne()" 
                                    class="btn btn-primary btn-sm">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                </svg>
                                Ajouter une ligne
                            </button>
                        </div>
                    </div>
                    
                    <div class="card-body p-0">
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Désignation</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Unité</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Qté</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Prix unit. HT</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">TVA %</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total HT</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="(ligne, index) in lignes" :key="ligne.id">
                                        <tr class="ligne-item border-t border-gray-200">
                                            <td class="px-4 py-3">
                                                <input type="text" 
                                                       :name="`lignes[${index}][designation]`"
                                                       x-model="ligne.designation"
                                                       class="form-input text-sm"
                                                       placeholder="Désignation du produit/service"
                                                       required>
                                                <input type="text" 
                                                       :name="`lignes[${index}][description]`"
                                                       x-model="ligne.description"
                                                       class="form-input text-sm mt-1"
                                                       placeholder="Description (optionnel)">
                                            </td>
                                            
                                            <td class="px-4 py-3">
                                                <select :name="`lignes[${index}][unite]`"
                                                        x-model="ligne.unite"
                                                        class="form-select text-sm">
                                                    <option value="unité">unité</option>
                                                    <option value="m²">m²</option>
                                                    <option value="ml">ml</option>
                                                    <option value="m³">m³</option>
                                                    <option value="heure">heure</option>
                                                    <option value="jour">jour</option>
                                                    <option value="forfait">forfait</option>
                                                    <option value="kg">kg</option>
                                                    <option value="lot">lot</option>
                                                </select>
                                            </td>
                                            
                                            <td class="px-4 py-3">
                                                <input type="number" 
                                                       :name="`lignes[${index}][quantite]`"
                                                       x-model="ligne.quantite"
                                                       @input="calculerLigne(index)"
                                                       class="form-input text-sm w-20"
                                                       step="0.01"
                                                       min="0.01"
                                                       required>
                                            </td>
                                            
                                            <td class="px-4 py-3">
                                                <div class="flex items-center">
                                                    <input type="number" 
                                                           :name="`lignes[${index}][prix_unitaire_ht]`"
                                                           x-model="ligne.prix_unitaire_ht"
                                                           @input="calculerLigne(index)"
                                                           class="form-input text-sm w-24"
                                                           step="0.01"
                                                           min="0"
                                                           required>
                                                    <span class="text-xs text-gray-500 ml-1">€</span>
                                                </div>
                                            </td>
                                            
                                            <td class="px-4 py-3">
                                                <div class="flex items-center">
                                                    <input type="number" 
                                                           :name="`lignes[${index}][taux_tva]`"
                                                           x-model="ligne.taux_tva"
                                                           @input="calculerLigne(index)"
                                                           class="form-input text-sm w-16"
                                                           step="0.01"
                                                           min="0"
                                                           max="100">
                                                    <span class="text-xs text-gray-500 ml-1">%</span>
                                                </div>
                                            </td>
                                            
                                            <td class="px-4 py-3">
                                                <span class="text-sm font-medium" 
                                                      x-text="formatMontant(ligne.montant_ht)"></span>
                                            </td>
                                            
                                            <td class="px-4 py-3 text-center">
                                                <button type="button" 
                                                        @click="supprimerLigne(index)"
                                                        class="text-red-600 hover:text-red-800"
                                                        title="Supprimer cette ligne">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            </td>
                                        </tr>
                                    </template>
                                    
                                    <tr x-show="lignes.length === 0">
                                        <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                                            <svg class="w-8 h-8 mx-auto mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"