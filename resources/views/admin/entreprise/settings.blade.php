@extends('layouts.app')

@section('title', 'Paramètres de l\'entreprise')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- En-tête -->
    <div class="mb-6">
        <nav class="flex text-gray-600 text-sm mb-2">
            <a href="{{ route('admin.index') }}" class="hover:text-blue-600">Administration</a>
            <span class="mx-2">/</span>
            <span class="text-gray-800">Paramètres entreprise</span>
        </nav>
        
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Paramètres de l'entreprise</h1>
                <p class="text-gray-600 mt-1">Configuration des informations utilisées dans les devis et factures</p>
            </div>
            
            <div class="flex gap-2">
                <a href="{{ route('admin.entreprise.preview-pdf') }}" 
                   class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg flex items-center gap-2" 
                   target="_blank">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Aperçu PDF
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-6">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-6">
            {{ session('error') }}
        </div>
    @endif

    <form action="{{ route('admin.entreprise.settings.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        
        <!-- Informations générales -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-4m-5 0H9m0 0H7m2 0v-5a2 2 0 012-2h2a2 2 0 012 2v5m-6 0V9a2 2 0 012-2h2a2 2 0 012 2v8"></path>
                </svg>
                Informations générales
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="nom" class="block text-sm font-medium text-gray-700 mb-2">
                        Nom de l'entreprise *
                    </label>
                    <input type="text" id="nom" name="nom" 
                           value="{{ old('nom', $settings['nom'] ?? '') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           required>
                    @error('nom')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="forme_juridique" class="block text-sm font-medium text-gray-700 mb-2">
                        Forme juridique
                    </label>
                    <select id="forme_juridique" name="forme_juridique" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Sélectionner</option>
                        <option value="SARL" {{ old('forme_juridique', $settings['forme_juridique'] ?? '') == 'SARL' ? 'selected' : '' }}>SARL</option>
                        <option value="SAS" {{ old('forme_juridique', $settings['forme_juridique'] ?? '') == 'SAS' ? 'selected' : '' }}>SAS</option>
                        <option value="SASU" {{ old('forme_juridique', $settings['forme_juridique'] ?? '') == 'SASU' ? 'selected' : '' }}>SASU</option>
                        <option value="EURL" {{ old('forme_juridique', $settings['forme_juridique'] ?? '') == 'EURL' ? 'selected' : '' }}>EURL</option>
                        <option value="SA" {{ old('forme_juridique', $settings['forme_juridique'] ?? '') == 'SA' ? 'selected' : '' }}>SA</option>
                        <option value="SNC" {{ old('forme_juridique', $settings['forme_juridique'] ?? '') == 'SNC' ? 'selected' : '' }}>SNC</option>
                        <option value="Auto-entrepreneur" {{ old('forme_juridique', $settings['forme_juridique'] ?? '') == 'Auto-entrepreneur' ? 'selected' : '' }}>Auto-entrepreneur</option>
                        <option value="Autre" {{ old('forme_juridique', $settings['forme_juridique'] ?? '') == 'Autre' ? 'selected' : '' }}>Autre</option>
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label for="adresse" class="block text-sm font-medium text-gray-700 mb-2">
                        Adresse *
                    </label>
                    <textarea id="adresse" name="adresse" rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                              required>{{ old('adresse', $settings['adresse'] ?? '') }}</textarea>
                    @error('adresse')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="code_postal" class="block text-sm font-medium text-gray-700 mb-2">
                        Code postal *
                    </label>
                    <input type="text" id="code_postal" name="code_postal" 
                           value="{{ old('code_postal', $settings['code_postal'] ?? '') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           maxlength="10" required>
                    @error('code_postal')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="ville" class="block text-sm font-medium text-gray-700 mb-2">
                        Ville *
                    </label>
                    <input type="text" id="ville" name="ville" 
                           value="{{ old('ville', $settings['ville'] ?? '') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           required>
                    @error('ville')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Contact -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                </svg>
                Informations de contact
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="telephone" class="block text-sm font-medium text-gray-700 mb-2">
                        Téléphone *
                    </label>
                    <input type="tel" id="telephone" name="telephone" 
                           value="{{ old('telephone', $settings['telephone'] ?? '') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           required>
                    @error('telephone')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="telephone_mobile" class="block text-sm font-medium text-gray-700 mb-2">
                        Téléphone mobile
                    </label>
                    <input type="tel" id="telephone_mobile" name="telephone_mobile" 
                           value="{{ old('telephone_mobile', $settings['telephone_mobile'] ?? '') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        Email *
                    </label>
                    <input type="email" id="email" name="email" 
                           value="{{ old('email', $settings['email'] ?? '') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           required>
                    @error('email')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="site_web" class="block text-sm font-medium text-gray-700 mb-2">
                        Site web
                    </label>
                    <input type="url" id="site_web" name="site_web" 
                           value="{{ old('site_web', $settings['site_web'] ?? '') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="https://www.example.com">
                </div>
            </div>
        </div>

        <!-- Informations légales -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.031 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                </svg>
                Informations légales
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="siret" class="block text-sm font-medium text-gray-700 mb-2">
                        SIRET *
                    </label>
                    <input type="text" id="siret" name="siret" 
                           value="{{ old('siret', $settings['siret'] ?? '') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           maxlength="17" pattern="[0-9]{14}" required>
                    <p class="text-gray-500 text-xs mt-1">14 chiffres</p>
                    @error('siret')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="tva_intracommunautaire" class="block text-sm font-medium text-gray-700 mb-2">
                        N° TVA intracommunautaire
                    </label>
                    <input type="text" id="tva_intracommunautaire" name="tva_intracommunautaire" 
                           value="{{ old('tva_intracommunautaire', $settings['tva_intracommunautaire'] ?? '') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="FR12345678901">
                </div>

                <div>
                    <label for="capital" class="block text-sm font-medium text-gray-700 mb-2">
                        Capital social (€)
                    </label>
                    <input type="number" id="capital" name="capital" 
                           value="{{ old('capital', $settings['capital'] ?? '') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           min="0" step="0.01">
                </div>

                <div>
                    <label for="code_ape" class="block text-sm font-medium text-gray-700 mb-2">
                        Code APE/NAF
                    </label>
                    <input type="text" id="code_ape" name="code_ape" 
                           value="{{ old('code_ape', $settings['code_ape'] ?? '') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           maxlength="6" placeholder="1234Z">
                </div>
            </div>
        </div>

        <!-- Coordonnées bancaires -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                </svg>
                Coordonnées bancaires
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="banque" class="block text-sm font-medium text-gray-700 mb-2">
                        Nom de la banque
                    </label>
                    <input type="text" id="banque" name="banque" 
                           value="{{ old('banque', $settings['banque'] ?? '') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div>
                    <label for="bic" class="block text-sm font-medium text-gray-700 mb-2">
                        Code BIC/SWIFT
                    </label>
                    <input type="text" id="bic" name="bic" 
                           value="{{ old('bic', $settings['bic'] ?? '') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           maxlength="11" placeholder="BNPAFRPPXXX">
                </div>

                <div class="md:col-span-2">
                    <label for="iban" class="block text-sm font-medium text-gray-700 mb-2">
                        IBAN
                    </label>
                    <input type="text" id="iban" name="iban" 
                           value="{{ old('iban', $settings['iban'] ?? '') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           maxlength="34" placeholder="FR14 2004 1010 0505 0001 3M02 606">
                </div>
            </div>
        </div>

        <!-- Logo et branding -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                Logo et branding
            </h3>
            
            <div class="space-y-6">
                <div>
                    <label for="logo" class="block text-sm font-medium text-gray-700 mb-2">
                        Logo de l'entreprise
                    </label>
                    @if(isset($settings['logo']) && $settings['logo'])
                        <div class="mb-4 p-4 bg-gray-50 rounded-lg">
                            <p class="text-sm text-gray-600 mb-2">Logo actuel :</p>
                            <img src="{{ asset('storage/' . $settings['logo']) }}" 
                                 alt="Logo actuel" 
                                 class="max-h-20 rounded border">
                        </div>
                    @endif
                    <input type="file" id="logo" name="logo" 
                           accept="image/jpeg,image/png,image/svg+xml"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <p class="text-gray-500 text-xs mt-1">Formats acceptés : JPG, PNG, SVG. Taille max : 2 Mo. Recommandé : 300x100px</p>
                    @error('logo')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="couleur_principale" class="block text-sm font-medium text-gray-700 mb-2">
                        Couleur principale (pour les PDF)
                    </label>
                    <div class="flex gap-3 items-center">
                        <input type="color" id="couleur_principale" name="couleur_principale" 
                               value="{{ old('couleur_principale', $settings['couleur_principale'] ?? '#2563eb') }}"
                               class="w-16 h-10 border border-gray-300 rounded cursor-pointer">
                        <input type="text" 
                               value="{{ old('couleur_principale', $settings['couleur_principale'] ?? '#2563eb') }}"
                               class="px-3 py-2 border border-gray-300 rounded-lg w-24 text-sm"
                               readonly>
                        <span class="text-sm text-gray-500">Utilisée pour les en-têtes des devis et factures</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Paramètres par défaut -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                Paramètres par défaut
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="taux_tva_defaut" class="block text-sm font-medium text-gray-700 mb-2">
                        Taux de TVA par défaut (%)
                    </label>
                    <input type="number" id="taux_tva_defaut" name="taux_tva_defaut" 
                           value="{{ old('taux_tva_defaut', $settings['taux_tva_defaut'] ?? '20') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           min="0" max="100" step="0.1">
                </div>

                <div>
                    <label for="delai_paiement_defaut" class="block text-sm font-medium text-gray-700 mb-2">
                        Délai de paiement par défaut (jours)
                    </label>
                    <input type="number" id="delai_paiement_defaut" name="delai_paiement_defaut" 
                           value="{{ old('delai_paiement_defaut', $settings['delai_paiement_defaut'] ?? '30') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           min="1" max="365">
                </div>

                <div class="md:col-span-2">
                    <label for="conditions_generales_defaut" class="block text-sm font-medium text-gray-700 mb-2">
                        Conditions générales par défaut
                    </label>
                    <textarea id="conditions_generales_defaut" name="conditions_generales_defaut" rows="4"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                              placeholder="Conditions générales à afficher sur les devis...">{{ old('conditions_generales_defaut', $settings['conditions_generales_defaut'] ?? '') }}</textarea>
                </div>

                <div class="md:col-span-2">
                    <label for="modalites_paiement_defaut" class="block text-sm font-medium text-gray-700 mb-2">
                        Modalités de paiement par défaut
                    </label>
                    <input type="text" id="modalites_paiement_defaut" name="modalites_paiement_defaut" 
                           value="{{ old('modalites_paiement_defaut', $settings['modalites_paiement_defaut'] ?? 'Paiement à 30 jours fin de mois') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>
        </div>

        <!-- Boutons d'action -->
        <div class="flex justify-between items-center pt-6">
            <a href="{{ route('admin.index') }}" 
               class="px-4 py-2 text-gray-700 hover:text-gray-900 transition duration-200">
                ← Retour à l'administration
            </a>
            
            <div class="flex gap-3">
                <button type="button" 
                        onclick="document.getElementById('preview-form').submit()"
                        class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg transition duration-200">
                    Aperçu
                </button>
                <button type="submit" 
                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition duration-200">
                    Enregistrer les paramètres
                </button>
            </div>
        </div>
    </form>

    <!-- Formulaire caché pour l'aperçu -->
    <form id="preview-form" action="{{ route('admin.entreprise.preview-pdf') }}" method="POST" target="_blank" style="display: none;">
        @csrf
        <input type="hidden" name="preview_data" id="preview-data">
    </form>
</div>

<script>
// Synchroniser le color picker avec l'input text
document.getElementById('couleur_principale').addEventListener('change', function() {
    document.querySelector('input[readonly]').value = this.value;
});

// Préparer les données pour l'aperçu
function preparePreviewData() {
    const formData = new FormData(document.querySelector('form'));
    const data = {};
    for (let [key, value] of formData.entries()) {
        data[key] = value;
    }
    document.getElementById('preview-data').value = JSON.stringify(data);
}

// Préparer les données avant soumission de l'aperçu
document.querySelector('button[onclick*="preview-form"]').addEventListener('click', function() {
    preparePreviewData();
});

// Validation du SIRET
document.getElementById('siret').addEventListener('input', function() {
    let value = this.value.replace(/\s/g, '');
    if (value.length > 14) {
        value = value.slice(0, 14);
    }
    this.value = value;
});

// Formatage automatique de l'IBAN
document.getElementById('iban').addEventListener('input', function() {
    let value = this.value.replace(/\s/g, '').toUpperCase();
    if (value.length > 4) {
        value = value.match(/.{1,4}/g).join(' ');
    }
    this.value = value;
});

// Preview de l'image logo
document.getElementById('logo').addEventListener('change', function() {
    const file = this.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            // Créer un aperçu si pas déjà présent
            let preview = document.getElementById('logo-preview');
            if (!preview) {
                preview = document.createElement('div');
                preview.id = 'logo-preview';
                preview.className = 'mt-4 p-4 bg-gray-50 rounded-lg';
                preview.innerHTML = '<p class="text-sm text-gray-600 mb-2">Aperçu du nouveau logo :</p>';
                document.getElementById('logo').parentNode.appendChild(preview);
            }
            
            const img = document.createElement('img');
            img.src = e.target.result;
            img.className = 'max-h-20 rounded border';
            img.alt = 'Aperçu du logo';
            
            // Remplacer l'ancien aperçu
            const oldImg = preview.querySelector('img');
            if (oldImg) {
                oldImg.remove();
            }
            preview.appendChild(img);
        };
        reader.readAsDataURL(file);
    }
});
</script>
@endsection