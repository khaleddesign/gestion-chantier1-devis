<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Devis {{ $devis->numero }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }

        .container {
            max-width: 100%;
            margin: 0 auto;
            padding: 20px;
        }

        /* En-tête */
        .header {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }

        .header-left, .header-right {
            display: table-cell;
            vertical-align: top;
            width: 50%;
        }

        .logo {
            margin-bottom: 20px;
        }

        .company-info {
            font-size: 11px;
            line-height: 1.3;
        }

        .company-info strong {
            display: block;
            font-size: 14px;
            color: #2563eb;
            margin-bottom: 5px;
        }

        .document-title {
            text-align: right;
            font-size: 24px;
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 10px;
        }

        .document-number {
            text-align: right;
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .document-date {
            text-align: right;
            font-size: 11px;
            color: #666;
        }

        /* Informations client */
        .client-section {
            margin: 30px 0;
            display: table;
            width: 100%;
        }

        .client-left, .client-right {
            display: table-cell;
            vertical-align: top;
            width: 50%;
        }

        .client-info {
            background: #f8fafc;
            padding: 15px;
            border-left: 4px solid #2563eb;
        }

        .client-info h3 {
            font-size: 13px;
            color: #2563eb;
            margin-bottom: 8px;
            text-transform: uppercase;
        }

        .client-info p {
            margin: 3px 0;
            font-size: 11px;
        }

        .project-info {
            padding: 15px 0;
        }

        .project-info h3 {
            font-size: 13px;
            color: #2563eb;
            margin-bottom: 8px;
            text-transform: uppercase;
        }

        /* Tableau des lignes */
        .lines-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 11px;
        }

        .lines-table th {
            background: #2563eb;
            color: white;
            padding: 10px 5px;
            text-align: left;
            font-weight: bold;
            font-size: 10px;
            text-transform: uppercase;
        }

        .lines-table td {
            padding: 8px 5px;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: top;
        }

        .lines-table tr:nth-child(even) {
            background: #f9fafb;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        /* Totaux */
        .totals {
            width: 100%;
            margin-top: 20px;
        }

        .totals-table {
            width: 300px;
            margin-left: auto;
            font-size: 12px;
        }

        .totals-table td {
            padding: 5px 10px;
            border-bottom: 1px solid #e5e7eb;
        }

        .totals-table .total-final {
            background: #2563eb;
            color: white;
            font-weight: bold;
            font-size: 14px;
        }

        /* Conditions */
        .conditions {
            margin-top: 30px;
            page-break-inside: avoid;
        }

        .conditions h3 {
            font-size: 13px;
            color: #2563eb;
            margin-bottom: 10px;
            text-transform: uppercase;
        }

        .conditions-grid {
            display: table;
            width: 100%;
        }

        .condition-item {
            display: table-cell;
            width: 33.33%;
            padding-right: 15px;
            vertical-align: top;
        }

        .condition-item h4 {
            font-size: 11px;
            font-weight: bold;
            margin-bottom: 5px;
            color: #374151;
        }

        .condition-item p {
            font-size: 10px;
            color: #6b7280;
            line-height: 1.3;
        }

        /* Signature */
        .signature-section {
            margin-top: 40px;
            display: table;
            width: 100%;
        }

        .signature-left, .signature-right {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }

        .signature-box {
            border: 1px solid #d1d5db;
            padding: 20px;
            margin: 10px;
            min-height: 80px;
            text-align: center;
        }

        .signature-box h4 {
            font-size: 11px;
            margin-bottom: 10px;
            color: #374151;
        }

        .signature-box p {
            font-size: 9px;
            color: #6b7280;
            margin-top: 10px;
        }

        /* Pied de page */
        .footer {
            position: fixed;
            bottom: 20px;
            left: 20px;
            right: 20px;
            text-align: center;
            font-size: 9px;
            color: #6b7280;
            border-top: 1px solid #e5e7eb;
            padding-top: 10px;
        }

        /* Statut du devis */
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-accepte {
            background: #dcfce7;
            color: #166534;
        }

        .status-envoye {
            background: #dbeafe;
            color: #1e40af;
        }

        .status-brouillon {
            background: #f3f4f6;
            color: #374151;
        }

        .status-refuse {
            background: #fee2e2;
            color: #991b1b;
        }

        /* Responsive pour PDF */
        @media print {
            .container {
                padding: 0;
            }
            
            .page-break {
                page-break-before: always;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- En-tête -->
        <div class="header">
            <div class="header-left">
                @if(isset($entreprise['logo']) && $entreprise['logo'])
                    <div class="logo">
                        <img src="{{ public_path($entreprise['logo']) }}" alt="Logo" style="max-height: 60px;">
                    </div>
                @endif
                
                <div class="company-info">
                    <strong>{{ $entreprise['nom'] ?? 'Votre Entreprise' }}</strong>
                    @if(isset($entreprise['adresse']))
                        <div>{{ $entreprise['adresse'] }}</div>
                    @endif
                    @if(isset($entreprise['code_postal']) && isset($entreprise['ville']))
                        <div>{{ $entreprise['code_postal'] }} {{ $entreprise['ville'] }}</div>
                    @endif
                    @if(isset($entreprise['telephone']))
                        <div>Tél : {{ $entreprise['telephone'] }}</div>
                    @endif
                    @if(isset($entreprise['email']))
                        <div>Email : {{ $entreprise['email'] }}</div>
                    @endif
                    @if(isset($entreprise['siret']))
                        <div>SIRET : {{ $entreprise['siret'] }}</div>
                    @endif
                </div>
            </div>
            
            <div class="header-right">
                <div class="document-title">DEVIS</div>
                <div class="document-number">N° {{ $devis->numero }}</div>
                <div class="document-date">
                    Date d'émission : {{ $devis->date_emission->format('d/m/Y') }}<br>
                    Valable jusqu'au : {{ $devis->date_validite->format('d/m/Y') }}
                </div>
                
                @if($devis->statut !== 'brouillon')
                    <div style="margin-top: 10px;">
                        <span class="status-badge status-{{ $devis->statut }}">
                            {{ $devis->statut_texte }}
                        </span>
                    </div>
                @endif
            </div>
        </div>

        <!-- Informations client et projet -->
        <div class="client-section">
            <div class="client-left">
                <div class="client-info">
                    <h3>Client</h3>
                    <p><strong>{{ $devis->client_nom }}</strong></p>
                    @if(isset($devis->client_info['adresse']) && $devis->client_info['adresse'])
                        <p>{{ $devis->client_info['adresse'] }}</p>
                    @endif
                    @if(isset($devis->client_info['email']) && $devis->client_info['email'])
                        <p>Email : {{ $devis->client_info['email'] }}</p>
                    @endif
                    @if(isset($devis->client_info['telephone']) && $devis->client_info['telephone'])
                        <p>Tél : {{ $devis->client_info['telephone'] }}</p>
                    @endif
                </div>
            </div>
            
            <div class="client-right">
                <div class="project-info">
                    <h3>Projet</h3>
                    <p><strong>{{ $devis->titre }}</strong></p>
                    @if($devis->description)
                        <p>{{ $devis->description }}</p>
                    @endif
                    @if($devis->chantier)
                        <p>Chantier : {{ $devis->chantier->titre }}</p>
                    @endif
                    @if($devis->commercial)
                        <p>Commercial : {{ $devis->commercial->name }}</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Tableau des lignes -->
        @if($devis->lignes->count() > 0)
            <table class="lines-table">
                <thead>
                    <tr>
                        <th style="width: 5%;">#</th>
                        <th style="width: 35%;">Désignation</th>
                        <th style="width: 8%;">Unité</th>
                        <th style="width: 8%;">Qté</th>
                        <th style="width: 12%;">Prix unit. HT</th>
                        @if($devis->lignes->where('remise_pourcentage', '>', 0)->count() > 0)
                            <th style="width: 8%;">Remise</th>
                        @endif
                        <th style="width: 8%;">TVA</th>
                        <th style="width: 12%;">Montant HT</th>
                        <th style="width: 12%;">Montant TTC</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($devis->lignes as $ligne)
                        <tr>
                            <td class="text-center">{{ $ligne->ordre }}</td>
                            <td>
                                <strong>{{ $ligne->designation }}</strong>
                                @if($ligne->description)
                                    <br><small style="color: #6b7280;">{{ $ligne->description }}</small>
                                @endif
                            </td>
                            <td class="text-center">{{ $ligne->unite }}</td>
                            <td class="text-center">{{ number_format($ligne->quantite, 2, ',', ' ') }}</td>
                            <td class="text-right">{{ number_format($ligne->prix_unitaire_ht, 2, ',', ' ') }} €</td>
                            @if($devis->lignes->where('remise_pourcentage', '>', 0)->count() > 0)
                                <td class="text-center">
                                    @if($ligne->remise_pourcentage > 0)
                                        {{ number_format($ligne->remise_pourcentage, 1, ',', ' ') }}%
                                    @else
                                        -
                                    @endif
                                </td>
                            @endif
                            <td class="text-center">{{ number_format($ligne->taux_tva, 1, ',', ' ') }}%</td>
                            <td class="text-right">{{ number_format($ligne->montant_ht, 2, ',', ' ') }} €</td>
                            <td class="text-right">{{ number_format($ligne->montant_ttc, 2, ',', ' ') }} €</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        <!-- Totaux -->
        <div class="totals">
            <table class="totals-table">
                <tr>
                    <td><strong>Total HT :</strong></td>
                    <td class="text-right">{{ number_format($devis->montant_ht, 2, ',', ' ') }} €</td>
                </tr>
                <tr>
                    <td><strong>Total TVA :</strong></td>
                    <td class="text-right">{{ number_format($devis->montant_tva, 2, ',', ' ') }} €</td>
                </tr>
                <tr class="total-final">
                    <td><strong>Total TTC :</strong></td>
                    <td class="text-right"><strong>{{ number_format($devis->montant_ttc, 2, ',', ' ') }} €</strong></td>
                </tr>
            </table>
        </div>

        <!-- Conditions -->
        <div class="conditions">
            <h3>Conditions</h3>
            <div class="conditions-grid">
                <div class="condition-item">
                    <h4>Délai de réalisation</h4>
                    <p>{{ $devis->delai_realisation ? $devis->delai_realisation . ' jours' : 'À définir' }}</p>
                </div>
                <div class="condition-item">
                    <h4>Modalités de paiement</h4>
                    <p>{{ $devis->modalites_paiement ?: 'Selon conditions générales' }}</p>
                </div>
                <div class="condition-item">
                    <h4>Validité du devis</h4>
                    <p>{{ $devis->date_validite->format('d/m/Y') }}</p>
                </div>
            </div>
        </div>

        <!-- Conditions générales -->
        @if($devis->conditions_generales)
            <div class="conditions" style="margin-top: 20px;">
                <h3>Conditions générales</h3>
                <div style="font-size: 10px; line-height: 1.4; text-align: justify;">
                    {!! nl2br(e($devis->conditions_generales)) !!}
                </div>
            </div>
        @endif

        <!-- Signatures -->
        @if($devis->statut === 'envoye' || $devis->statut === 'accepte')
            <div class="signature-section">
                <div class="signature-left">
                    <div class="signature-box">
                        <h4>Signature du client</h4>
                        <div style="height: 50px;">
                            @if($devis->signature_client && $devis->statut === 'accepte')
                                <div style="font-size: 10px; color: #16a34a;">
                                    ✓ Devis accepté le {{ $devis->date_reponse->format('d/m/Y à H:i') }}
                                </div>
                            @endif
                        </div>
                        <p>Date et signature<br>Mention "Bon pour accord"</p>
                    </div>
                </div>
                
                <div class="signature-right">
                    <div class="signature-box">
                        <h4>{{ $entreprise['nom'] ?? 'L\'entreprise' }}</h4>
                        <div style="height: 50px;"></div>
                        <p>Date et signature</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Pied de page -->
        <div class="footer">
            {{ $entreprise['nom'] ?? 'Votre Entreprise' }} - 
            @if(isset($entreprise['siret']))
                SIRET : {{ $entreprise['siret'] }} - 
            @endif
            @if(isset($entreprise['email']))
                {{ $entreprise['email'] }} - 
            @endif
            @if(isset($entreprise['telephone']))
                {{ $entreprise['telephone'] }}
            @endif
        </div>
    </div>
</body>
</html>