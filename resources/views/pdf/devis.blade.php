<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Devis {{ $devis->numero }}</title>
    <style>
        /* ---------- GÉNÉRAL ---------- */
        @page { margin: 110px 32px 100px 32px; }
        *,*::before,*::after{box-sizing:border-box;}
        body{font-family:'DejaVu Sans',Arial,sans-serif;font-size:10pt;line-height:1.45;color:#1f2937;}

        /* ---------- COULEURS ---------- */
        :root{
            --c-primary:#0d6efd; /* bleu fideli */
            --c-primary-dark:#084298;
            --c-grey-50:#f9fafb;
            --c-grey-100:#f3f4f6;
            --c-grey-200:#e5e7eb;
            --c-grey-500:#6b7280;
        }

        /* ---------- HEADER ---------- */
        header{position:fixed;top:-90px;left:0;right:0;height:90px;display:flex;align-items:center;justify-content:space-between;padding:18px 32px;border-bottom:2px solid var(--c-grey-200);}        
        .header-left img{height:52px;}
        .header-right{text-align:right;}
        .header-right .title{font-size:18pt;font-weight:700;color:var(--c-primary);margin-bottom:2px;}
        .header-right .meta{font-size:8.5pt;line-height:1.3;}

        /* ---------- FOOTER ---------- */
        footer{position:fixed;bottom:-80px;left:0;right:0;height:80px;border-top:2px solid var(--c-grey-200);display:flex;align-items:center;justify-content:space-between;padding:8px 32px;font-size:8pt;color:var(--c-grey-500);}        
        .footer-center{text-align:center;flex:1;}
        .pagenum:before{content: counter(page);} 
        .pagecount:before{content: counter(pages);} 

        /* ---------- SECTIONS ---------- */
        .section{margin-bottom:28px;}
        h2{font-size:11pt;color:var(--c-primary);margin-bottom:6px;text-transform:uppercase;}
        .two-cols{display:table;width:100%;}
        .col{display:table-cell;width:50%;vertical-align:top;}
        .text-right{text-align:right;}

        /* ---------- TABLE PRODUITS ---------- */
        table{width:100%;border-collapse:collapse;}
        thead{background:var(--c-primary);color:#fff;}
        th,td{padding:8px 10px;font-size:9.2pt;border:1px solid var(--c-grey-200);}        
        th{text-transform:uppercase;font-weight:600;letter-spacing:.3px;}
        tbody tr:nth-child(even){background:var(--c-grey-50);} 
        .nowrap{white-space:nowrap;}
        .descr{font-style:italic;font-size:8pt;color:var(--c-grey-500);}        
        tfoot td{border:1px solid var(--c-grey-200);}        

        /* ---------- TOTALS ---------- */
        .totals-table{margin-left:auto;width:260px;border-collapse:collapse;margin-top:6px;}
        .totals-table td{padding:4px 8px;font-size:9pt;}
        .totals-table tr:last-child td{font-weight:700;color:var(--c-primary);border-top:2px solid var(--c-primary);}        

        /* ---------- AUTRES ---------- */
        .pay-info{margin-top:12px;}
        .pay-info span{font-weight:700;color:var(--c-primary);}        
        .signature-block{margin-top:24px;text-align:right;}
        .signature-line{margin-top:32px;border-bottom:2px solid var(--c-grey-200);width:240px;height:2px;}
        .legal{margin-top:24px;font-size:8pt;color:var(--c-grey-500);}        
    </style>
</head>
<body>
    <!-- HEADER -->
    <header>
        <div class="header-left">
            @if($entreprise['logo_path'])
                <img src="{{ public_path($entreprise['logo_path']) }}" alt="Logo">
            @else
                <img src="https://via.placeholder.com/140x40?text=LOGO" alt="Logo">
            @endif
        </div>
        <div class="header-right">
            <div class="title">Devis N° {{ $devis->numero }}</div>
            <div class="meta">
                Date de création&nbsp;: {{ $devis->date_emission->format('d/m/Y') }}<br>
                Date limite de validité&nbsp;: {{ $devis->date_validite->format('d/m/Y') }}
            </div>
        </div>
    </header>

    <!-- FOOTER -->
    <footer>
        <div>SIRET : {{ $entreprise['siret'] }}</div>
        <div class="footer-center">Page <span class="pagenum"></span> / <span class="pagecount"></span></div>
        <div>TVA Intra : {{ $entreprise['tva_intracommunautaire'] }}</div>
    </footer>

    <main>
        <!-- VENDEUR / ACHETEUR -->
        <section class="section">
            <div class="two-cols">
                <div class="col">
                    <h2>Vendeur :</h2>
                    {{ $entreprise['nom'] }}<br>
                    {{ $entreprise['forme_juridique'] ?? '' }} au capital de {{ $entreprise['capital'] ?? '' }} €<br>
                    {{ $entreprise['adresse'] }}<br>
                    RCS {{ $entreprise['ville_rcs'] ?? '' }} {{ $entreprise['rcs'] ?? '' }} – TVA {{ $entreprise['tva_intracommunautaire'] }}<br>
                    Assurance : {{ $entreprise['assureur'] ?? '' }} – Police n° {{ $entreprise['num_police'] ?? '' }}<br>
                    {{ $entreprise['email'] }} – T : {{ $entreprise['telephone'] }}
                </div>
                <div class="col text-right">
                    <h2>Acheteur :</h2>
                    {{ $devis->client_info['nom'] }}<br>
                    {{ $devis->client_info['adresse'] }}
                </div>
            </div>
        </section>

        <!-- TITRE CHANTIER / OBJET -->
        @if($devis->titre)
            <section class="section">
                <h2>{{ $devis->titre }}</h2>
            </section>
        @endif

        <!-- TABLE LIGNES -->
        <section class="section">
            <table>
                <thead>
                    <tr>
                        <th style="width:4%">N°</th>
                        <th style="width:34%">Désignation</th>
                        <th style="width:8%">Qté</th>
                        <th style="width:10%">PU HT</th>
                        <th style="width:10%">Total HT</th>
                        <th style="width:8%">TVA</th>
                        <th style="width:12%">Montant TVA</th>
                        <th style="width:14%">Total TTC</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($devis->lignes as $index=>$ligne)
                        <tr>
                            <td class="text-center nowrap">{{ $index+1 }}</td>
                            <td>
                                <strong>{{ $ligne->designation }}</strong>
                                @if($ligne->description)
                                    <div class="descr">{{ $ligne->description }}</div>
                                @endif
                            </td>
                            <td class="text-center nowrap">{{ number_format($ligne->quantite,2,',',' ') }} {{ $ligne->unite }}</td>
                            <td class="text-right nowrap">{{ number_format($ligne->prix_unitaire_ht,2,',',' ') }} €</td>
                            <td class="text-right nowrap">{{ number_format($ligne->montant_ht,2,',',' ') }} €</td>
                            <td class="text-center nowrap">{{ number_format($ligne->taux_tva,0,',',' ') }}%</td>
                            <td class="text-right nowrap">{{ number_format($ligne->montant_tva,2,',',' ') }} €</td>
                            <td class="text-right nowrap">{{ number_format($ligne->montant_ttc,2,',',' ') }} €</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="background:var(--c-primary);color:#fff;font-weight:700;">
                        <td colspan="7" class="text-right">Total :</td>
                        <td class="text-right nowrap">{{ number_format($devis->montant_ttc,2,',',' ') }} €</td>
                    </tr>
                </tfoot>
            </table>
        </section>

        <!-- TOTALS RÉCAP -->
        <section class="section" style="margin-top:4px;">
            <table class="totals
