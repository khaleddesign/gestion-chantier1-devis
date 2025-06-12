<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Devis {{ $devis->numero }} - {{ $devis->chantier->client->name }}</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    
    <!-- Styles -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }
        
        .signature-pad {
            border: 2px dashed #d1d5db;
            cursor: crosshair;
        }
        
        .signature-pad:hover {
            border-color: #6366f1;
        }
        
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; }
        }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen">
    <!-- Header -->
    <div class="bg-white shadow-lg border-b-4 border-blue-500">
        <div class="max-w-4xl mx-auto px-4 py-8">
            <div class="text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-100 rounded-full mb-4">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">
                    Devis {{ $devis->numero }}
                </h1>
                <p class="text-lg text-gray-600 mb-4">{{ $devis->titre }}</p>
                
                @php
                    $badgeClass = match($devis->statut) {
                        'envoye' => 'bg-blue-100 text-blue-800',
                        'accepte' => 'bg-green-100 text-green-800',
                        'refuse' => 'bg-red-100 text-red-800',
                        'expire' => 'bg-yellow-100 text-yellow-800',
                        default => 'bg-gray-100 text-gray-800'
                    };
                    $statutTexte = match($devis->statut) {
                        'envoye' => 'En attente de votre réponse',
                        'accepte' => 'Accepté - Merci !',
                        'refuse' => 'Refusé',
                        'expire' => 'Expiré',
                        default => 'Statut inconnu'
                    };
                @endphp
                
                <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium {{ $badgeClass }}">
                    {{ $statutTexte }}
                </span>
                
                @if($devis->date_validite && $devis->statut === 'envoye')
                    <p class="text-sm text-gray-500 mt-2">
                        Valable jusqu'au {{ $devis->date_validite->format('d/m/Y') }}
                        @if($devis->date_validite->isPast())
                            <span class="text-red-600 font-medium">(Expiré)</span>
                        @elseif($devis->date_validite->diffInDays(now()) <= 3)
                            <span class="text-orange-600 font-medium">({{ $devis->date_validite->diffInDays(now()) }} jour(s) restant(s))</span>
                        @endif
                    </p>
                @endif
            </div>
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 py-8">
        <!-- Informations du devis -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden mb-8">
            <div class="px-6 py-4 bg-gradient-to-r from-blue-50 to-indigo-50 border-b">
                <h2 class="text-xl font-semibold text-gray-900">Détails du devis</h2>
            </div>
            
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wide">Informations client</h3>
                        <div class="mt-2 space-y-1">
                            <p class="text-gray-900 font-medium">{{ $devis->client_info['nom'] ?? 'N/A' }}</p>
                            <p class="text-gray-600">{{ $devis->client_info['email'] ?? 'N/A' }}</p>
                            @if($devis->client_info['telephone'] ?? null)
                                <p class="text-gray-600">{{ $devis->client_info['telephone'] }}</p>
                            @endif
                            @if($devis->client_info['adresse'] ?? null)
                                <p class="text-gray-600">{{ $devis->client_info['adresse'] }}</p>
                            @endif
                        </div>
                    </div>
                    
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wide">Informations du devis</h3>
                        <div class="mt-2 space-y-1">
                            <p class="text-gray-900"><span class="font-medium">Numéro :</span> {{ $devis->numero }}</p>
                            <p class="text-gray-900"><span class="font-medium">Date :</span> {{ $devis->created_at->format('d/m/Y') }}</p>
                            @if($devis->date_validite)
                                <p class="text-gray-900"><span class="font-medium">Valable jusqu'au :</span> {{ $devis->date_validite->format('d/m/Y') }}</p>
                            @endif
                            @if($devis->delai_realisation)
                                <p class="text-gray-900"><span class="font-medium">Délai :</span> {{ $devis->delai_realisation }} jours</p>
                            @endif
                            <p class="text-gray-900"><span class="font-medium">Commercial :</span> {{ $devis->commercial->name }}</p>
                        </div>
                    </div>
                </div>
                
                @if($devis->description)
                    <div class="border-t pt-6">
                        <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wide mb-2">Description</h3>
                        <p class="text-gray-900">{{ $devis->description }}</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Détail des prestations -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden mb-8">
            <div class="px-6 py-4 bg-gradient-to-r from-green-50 to-emerald-50 border-b">
                <h2 class="text-xl font-semibold text-gray-900">Détail des prestations</h2>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Désignation
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Quantité
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Prix unitaire HT
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
                                            <div class="text-sm text-gray-500 mt-1">{{ $ligne->description }}</div>
                                        @endif
                                        @if($ligne->categorie)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 mt-1">
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
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ number_format($ligne->montant_ht, 2) }}€
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50">
                        <tr>
                            <td colspan="3" class="px-6 py-4 text-right text-sm font-medium text-gray-900">
                                Total HT :
                            </td>
                            <td class="px-6 py-4 text-sm font-bold text-gray-900">
                                {{ number_format($devis->montant_ht, 2) }}€
                            </td>
                        </tr>
                        <tr>
                            <td colspan="3" class="px-6 py-3 text-right text-sm font-medium text-gray-900">
                                TVA ({{ $devis->taux_tva }}%) :
                            </td>
                            <td class="px-6 py-3 text-sm font-medium text-gray-900">
                                {{ number_format($devis->montant_tva, 2) }}€
                            </td>
                        </tr>
                        <tr class="border-t-2 border-gray-300">
                            <td colspan="3" class="px-6 py-4 text-right text-lg font-bold text-gray-900">
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

        <!-- Actions du client -->
        @if($devis->statut === 'envoye' && $devis->peutEtreAccepte())
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden mb-8">
                <div class="px-6 py-4 bg-gradient-to-r from-green-50 to-emerald-50 border-b">
                    <h2 class="text-xl font-semibold text-gray-900">Votre réponse</h2>
                    <p class="text-sm text-gray-600 mt-1">Merci de nous indiquer si vous acceptez ou refusez ce devis</p>
                </div>
                
                <div class="p-6" x-data="devisResponse()">
                    <form action="{{ route('devis.public.reponse', [$devis, $token]) }}" method="POST">
                        @csrf
                        
                        <!-- Choix de la réponse -->
                        <div class="mb-6">
                            <fieldset>
                                <legend class="text-base font-medium text-gray-900 mb-4">Votre décision :</legend>
                                <div class="space-y-4">
                                    <label class="relative flex items-center cursor-pointer">
                                        <input type="radio" 
                                               name="action" 
                                               value="accepter" 
                                               x-model="action"
                                               class="sr-only">
                                        <div class="flex items-center">
                                            <div class="flex items-center justify-center w-6 h-6 rounded-full border-2 transition-all duration-200"
                                                 :class="action === 'accepter' ? 'border-green-500 bg-green-500' : 'border-gray-300'">
                                                <div class="w-2 h-2 rounded-full bg-white" 
                                                     x-show="action === 'accepter'"></div>
                                            </div>
                                            <div class="ml-3">
                                                <span class="text-lg font-medium text-green-700">J'accepte ce devis</span>
                                                <p class="text-sm text-gray-500">En acceptant, vous confirmez votre commande selon les conditions indiquées</p>
                                            </div>
                                        </div>
                                        <div class="absolute inset-0 rounded-lg"
                                             :class="action === 'accepter' ? 'bg-green-50 border-2 border-green-200' : 'hover:bg-gray-50'"
                                             style="z-index: -1;"></div>
                                    </label>
                                    
                                    <label class="relative flex items-center cursor-pointer">
                                        <input type="radio" 
                                               name="action" 
                                               value="refuser" 
                                               x-model="action"
                                               class="sr-only">
                                        <div class="flex items-center">
                                            <div class="flex items-center justify-center w-6 h-6 rounded-full border-2 transition-all duration-200"
                                                 :class="action === 'refuser' ? 'border-red-500 bg-red-500' : 'border-gray-300'">
                                                <div class="w-2 h-2 rounded-full bg-white" 
                                                     x-show="action === 'refuser'"></div>
                                            </div>
                                            <div class="ml-3">
                                                <span class="text-lg font-medium text-red-700">Je refuse ce devis</span>
                                                <p class="text-sm text-gray-500">Vous pouvez nous indiquer la raison de votre refus</p>
                                            </div>
                                        </div>
                                        <div class="absolute inset-0 rounded-lg"
                                             :class="action === 'refuser' ? 'bg-red-50 border-2 border-red-200' : 'hover:bg-gray-50'"
                                             style="z-index: -1;"></div>
                                    </label>
                                </div>
                            </fieldset>
                        </div>

                        <!-- Commentaire -->
                        <div class="mb-6">
                            <label for="commentaire" class="block text-sm font-medium text-gray-700 mb-2">
                                <span x-text="action === 'accepter' ? 'Commentaire (optionnel)' : 'Raison du refus (optionnel)'"></span>
                            </label>
                            <textarea name="commentaire" 
                                      id="commentaire" 
                                      rows="3" 
                                      class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                      :placeholder="action === 'accepter' ? 'Votre commentaire...' : 'Expliquez-nous pourquoi vous refusez ce devis...'"></textarea>
                        </div>

                        <!-- Signature électronique (pour acceptation) -->
                        <div x-show="action === 'accepter'" class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Signature électronique (optionnel)
                            </label>
                            <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center">
                                <canvas id="signaturePad" 
                                        width="400" 
                                        height="150" 
                                        class="signature-pad mx-auto bg-white rounded"></canvas>
                                <input type="hidden" name="signature" id="signatureData">
                                <div class="mt-2 space-x-2">
                                    <button type="button" 
                                            onclick="clearSignature()"
                                            class="text-sm text-gray-600 hover:text-gray-800">
                                        Effacer
                                    </button>
                                </div>
                                <p class="text-xs text-gray-500 mt-2">
                                    Dessinez votre signature ci-dessus avec votre souris ou votre doigt
                                </p>
                            </div>
                        </div>

                        <!-- Confirmation -->
                        <div x-show="action === 'accepter'" class="mb-6">
                            <label class="flex items-start">
                                <input type="checkbox" 
                                       x-model="confirmed"
                                       class="mt-1 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" 
                                       required>
                                <span class="ml-2 text-sm text-gray-700">
                                    Je confirme avoir lu et accepté les conditions de ce devis. 
                                    En acceptant, je m'engage à honorer cette commande selon les termes indiqués.
                                </span>
                            </label>
                        </div>

                        <!-- Boutons de soumission -->
                        <div class="flex justify-center space-x-4">
                            <button type="submit" 
                                    x-show="action === 'accepter'"
                                    :disabled="!confirmed"
                                    class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Confirmer l'acceptation
                            </button>
                            
                            <button type="submit" 
                                    x-show="action === 'refuser'"
                                    class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                Confirmer le refus
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        <!-- Conditions générales -->
        @if($devis->conditions_generales)
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden mb-8">
                <div class="px-6 py-4 bg-gradient-to-r from-purple-50 to-pink-50 border-b">
                    <h2 class="text-xl font-semibold text-gray-900">Conditions générales</h2>
                </div>
                <div class="p-6">
                    <div class="prose prose-sm max-w-none text-gray-700">
                        {!! nl2br(e($devis->conditions_generales)) !!}
                    </div>
                </div>
            </div>
        @endif

        <!-- Informations de contact -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-blue-50 to-indigo-50 border-b">
                <h2 class="text-xl font-semibold text-gray-900">Besoin d'aide ?</h2>
            </div>
            <div class="p-6">
                <p class="text-gray-600 mb-4">
                    Si vous avez des questions concernant ce devis, n'hésitez pas à contacter votre commercial :
                </p>
                <div class="flex items-center space-x-4">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                    </div>
                    <div>
                        <p class="font-medium text-gray-900">{{ $devis->commercial->name }}</p>
                        <p class="text-gray-600">{{ $devis->commercial->email }}</p>
                        @if($devis->commercial->telephone)
                            <p class="text-gray-600">{{ $devis->commercial->telephone }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="bg-gray-800 text-white py-8 mt-12 no-print">
        <div class="max-w-4xl mx-auto px-4 text-center">
            <p class="text-gray-300">
                &copy; {{ date('Y') }} - Devis généré automatiquement
            </p>
        </div>
    </div>

    <script>
    function devisResponse() {
        return {
            action: '',
            confirmed: false
        }
    }

    // Signature pad functionality
    let canvas, ctx, isDrawing = false;

    document.addEventListener('DOMContentLoaded', function() {
        canvas = document.getElementById('signaturePad');
        if (canvas) {
            ctx = canvas.getContext('2d');
            
            // Mouse events
            canvas.addEventListener('mousedown', startDrawing);
            canvas.addEventListener('mousemove', draw);
            canvas.addEventListener('mouseup', stopDrawing);
            canvas.addEventListener('mouseout', stopDrawing);
            
            // Touch events
            canvas.addEventListener('touchstart', handleTouch);
            canvas.addEventListener('touchmove', handleTouch);
            canvas.addEventListener('touchend', stopDrawing);
        }
    });

    function startDrawing(e) {
        isDrawing = true;
        const rect = canvas.getBoundingClientRect();
        ctx.beginPath();
        ctx.moveTo(e.clientX - rect.left, e.clientY - rect.top);
    }

    function draw(e) {
        if (!isDrawing) return;
        const rect = canvas.getBoundingClientRect();
        ctx.lineTo(e.clientX - rect.left, e.clientY - rect.top);
        ctx.stroke();
    }

    function stopDrawing() {
        if (isDrawing) {
            isDrawing = false;
            // Save signature data
            document.getElementById('signatureData').value = canvas.toDataURL();
        }
    }

    function handleTouch(e) {
        e.preventDefault();
        const touch = e.touches[0];
        const mouseEvent = new MouseEvent(e.type === 'touchstart' ? 'mousedown' : 
                                        e.type === 'touchmove' ? 'mousemove' : 'mouseup', {
            clientX: touch.clientX,
            clientY: touch.clientY
        });
        canvas.dispatchEvent(mouseEvent);
    }

    function clearSignature() {
        if (ctx) {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            document.getElementById('signatureData').value = '';
        }
    }
    </script>
</body>
</html>