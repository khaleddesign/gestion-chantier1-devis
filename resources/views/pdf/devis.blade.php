<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Devis {{ $devis->numero }}</title>
    <style>
        /* ======= GÉNÉRAL ======= */
        @page { margin: 70px 32px 80px 32px; }
        *,*::before,*::after{box-sizing:border-box;}
        body{font-family:'DejaVu Sans',Arial,sans-serif;font-size:10pt;line-height:1.45;color:#1f2937;}

        /* ======= COULEURS ======= */
        :root{
            --c-primary:#0d6efd;
            --c-grey-50:#f9fafb; --c-grey-100:#f3f4f6; --c-grey-200:#e5e7eb; --c-grey-500:#6b7280;
        }

        /* ======= HEADER ======= */
        header{position:fixed;top:-60px;left:0;right:0;height:60px;display:flex;align-items:center;justify-content:space-between;padding:12px 32px;border-bottom:2px solid var(--c-grey-200);}  
        .header-left img{height:46px;}
        .header-right{text-align:right;}
        .header-right .title{font-size:16pt;font-weight:700;color:var(--c-primary);} 
        .header-right .meta{font-size:8pt;line-height:1.3;}

        /* ======= FOOTER ======= */
        footer{position:fixed;bottom:-70px;left:0;right:0;height:70px;padding:8px 32px;border-top:2px solid var(--c-grey-200);font-size:8pt;color:var(--c-grey-500);display:flex;align-items:center;justify-content:space-between;}
        .footer-center{text-align:center;flex:1;}
        .pagenum:before{content:counter(page);} .pagecount:before{content:counter(pages);} 

        /* ======= SECTIONS / LAYOUT ======= */
        .section{margin-bottom:26px;}
        h2{font-size:11pt;color:var(--c-primary);margin-bottom:6px;text-transform:uppercase;}
        .two-cols{display:table;width:100%;}
        .col{display:table-cell;width:50%;vertical-align:top;}
        .text-right{text-align:right;}

        /* ======= TABLE LIGNES ======= */
        table{width:100%;border-collapse:collapse;}
        thead{background:var(--c-primary);color:#fff;}
        th,td{padding:8px 10px;font-size:9.1pt;border:1px solid var(--c-grey-200);} 
        th{text-transform:uppercase;font-weight:600;letter-spacing:.3px;}
        tbody tr:nth-child(even){background:var(--c-grey-50);} 
        tr{page-break-inside:avoid;}
        .descr{font-style:italic;font-size:8pt;color:var(--c-grey-500);} 
        .nowrap{white-space:nowrap;}

        /* ======= TOTALS ======= */
        .totals-table{margin-left:auto;width:260px;border-collapse:collapse;margin-top:4px;}
        .totals-table td{padding:4px 8px;font-size:9pt;}
        .totals-table tr:last-child td{font-weight:700;color:var(--c-primary);border-top:2px solid var(--c-primary);} 

        /* ======= PAYMENT & SIGN ======= */
        .pay-info{margin-top:10px;font-size:9pt;}
        .pay-info span{font-weight:700;color:var(--c-primary);} 
        .signature-block{margin-top:24px;font-size:9pt;text-align:right;}
        .signature-line{margin-top:34px;border-bottom:2px solid var(--c-grey-200);width:240px;height:2px;}
        .legal{margin-top:22px;font-size:8pt;color:var(--c-grey-500);text-align:center;}

        /* ======= PAGE BREAK ======= */
        .page-break{page-break-before:always;}
    </style>
</head>
<body>
    <!-- ===== HEADER & FOOTER ===== -->
    <header>
        <div class="header-left">
            @if(!empty($entreprise['logo_path']))
                <img src="{{ public_path($entreprise['logo_path']) }}" alt="Logo">
            @endif
        </div>
        <div class="header-right">
            <div class="title">Devis N° {{ $devis->numero }}</div>
            <div class="meta">Créé le {{ $devis->date_emission->format('d/m/Y') }} · Valide jusqu'au {{ $devis->date_validite->format('d/m/Y') }}</div>
        </div>
    </header>
    <footer>
        <div>SIRET : {{ $entreprise['siret'] }}</div>
        <div class="footer-center">Page <span class="pagenum"></span>/<span class="pagecount"></span></div>
        <div>TVA : {{ $entreprise['tva_intracommunautaire'] }}</div>
    </footer>

    <main>
        <!-- ===== IDENTITÉS ===== -->
        <section class="section">
            <div class="two-cols">
                <div class="col">
                    <h2>Vendeur</h2>
                    {{ $entreprise['nom'] }}<br>
                    {{ $entreprise['forme_juridique'] ?? '' }} – Capital : {{ $entreprise['capital'] ?? '—' }} €<br>
                    {{ $entreprise['adresse'] }}<br>
                    RCS {{ $entreprise['ville_rcs'] ?? '' }} {{ $entreprise['rcs'] ?? '' }}<br>
                    Assurance : {{ $entreprise['assureur'] ?? '—' }} · Police {{ $entreprise['num_police'] ?? '—' }}<br>
                    {{ $entreprise['email'] }} – T : {{ $entreprise['telephone'] }}
                </div>
                <div class="col text-right">
                    <h2>Acheteur</h2>
                    {{ $devis->client_info['nom'] }}<br>
                    {{ $devis->client_info['adresse'] ?? '' }}
                </div>
            </div>
        </section>

        @if($devis->titre)
            <section class="section"><h2>{{ $devis->titre }}</h2></section>
        @endif

        <!-- ===== TABLE LIGNES ===== -->
        <section class="section">
            <table>
                <thead>
                    <tr>
                        <th style="width:4%">#</th><th style="width:34%">Désignation</th><th style="width:8%">Qté</th>
                        <th style="width:10%">PU HT</th><th style="width:10%">Total HT</th><th style="width:8%">TVA</th>
                        <th style="width:12%">TVA €</th><th style="width:14%">Total TTC</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($devis->lignes as $idx=>$l)
                        <tr>
                            <td class="text-center nowrap">{{ $idx+1 }}</td>
                            <td><strong>{{ $l->designation }}</strong>@if($l->description)<div class="descr">{{ $l->description }}</div>@endif</td>
                            <td class="text-center nowrap">{{ number_format($l->quantite,2,',',' ') }} {{ $l->unite }}</td>
                            <td class="text-right nowrap">{{ number_format($l->prix_unitaire_ht,2,',',' ') }} €</td>
                            <td class="text-right nowrap">{{ number_format($l->montant_ht,2,',',' ') }} €</td>
                            <td class="text-center nowrap">{{ number_format($l->taux_tva,0,',',' ') }} %</td>
                            <td class="text-right nowrap">{{ number_format($l->montant_tva,2,',',' ') }} €</td>
                            <td class="text-right nowrap">{{ number_format($l->montant_ttc,2,',',' ') }} €</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="background:var(--c-primary);color:#fff;font-weight:700;">
                        <td colspan="7" class="text-right">Total TTC :</td>
                        <td class="text-right nowrap">{{ number_format($devis->montant_ttc,2,',',' ') }} €</td>
                    </tr>
                </tfoot>
            </table>
        </section>

        <!-- ===== RÉCAP ===== -->
        <section class="section">
            <table class="totals-table">
                <tr><td>Total HT</td><td class="text-right">{{ number_format($devis->montant_ht,2,',',' ') }} €</td></tr>
                <tr><td>Montant TVA</td><td class="text-right">{{ number_format($devis->montant_tva,2,',',' ') }} €</td></tr>
                <tr><td>Total TTC</td><td class="text-right">{{ number_format($devis->montant_ttc,2,',',' ') }} €</td></tr>
            </table>
        </section>

        <!-- ===== PAIEMENT ===== -->
        <section class="section pay-info">
            À payer : <span>{{ number_format($devis->montant_ttc,2,',',' ') }} € TTC</span><br>
            @isset($devis->montant_ttc_lettres)En lettres : {{ Str::ucfirst($devis->montant_ttc_lettres) }}<br>@endisset
            Mode : {{ $devis->mode_reglement ?? 'Virement bancaire' }} · Pénalités : taux BCE + 10 pts · Escompte : aucun.
        </section>

        <!-- ===== SIGNATURE ===== -->
        <section class="signature-block">
            Bon pour accord le {{ $devis->date_emission->format('d/m/Y') }}<br>
            @if($devis->signature_client)
                <img src="{{ $devis->signature_client }}" alt="Signature" style="height:60px;margin-top:6px;">
            @else
                <div class="signature-line"></div>
            @endif
        </section>

        <!-- ===== LEGAL ===== -->
        <section class="legal">{{ $config['legal']['mentions_legales']['devis'] ?? 'Devis valable 30 jours – Clause de réserve de propriété.' }}</section>
    </main>

    <!-- ===== PAGE INFO ENTREPRISE (facultative) ===== -->
    @if($showInfoEntreprise ?? true)
    <div class="page-break"></div>
    <h2>Informations légales de l'entreprise</h2>
    <table class="info-table">
        <tr><th>Nom</th><td>{{ $entreprise['nom'] }}</td></tr>
        <tr><th>SIRET</th><td>{{ $entreprise['siret'] }}</td></tr>
        <tr><th>TVA intra.</th><td>{{ $entreprise['tva_intracommunautaire'] }}</td></tr>
        <tr><th>RCS</th><td>{{ $entreprise['ville_rcs'] ?? '' }} {{ $entreprise['rcs'] ?? '' }}</td></tr>
        <tr><th>Adresse</th><td>{{ $entreprise['adresse'] }}</td></tr>
        <tr><th>Téléphone</th><td>{{ $entreprise['telephone'] }}</td></tr>
        <tr><th>Email</th><td>{{ $entreprise['email'] }}</td></tr>
        <tr><th>Capital social</th><td>{{ $entreprise['capital'] ?? '—' }} €</td></tr>
        <tr><th>Assurance</th><td>{{ $entreprise['assureur'] ?? '—' }} – Police {{ $entreprise['num_police'] ?? '—' }}</td></tr>
    </table>
    @endif
</body>
</html>
