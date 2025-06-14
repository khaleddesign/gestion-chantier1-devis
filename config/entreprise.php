<?php
// config/entreprise.php

return [
    /*
    |--------------------------------------------------------------------------
    | Configuration par défaut de l'entreprise
    |--------------------------------------------------------------------------
    |
    | Ces valeurs sont utilisées par défaut pour les devis et factures
    | si aucune configuration n'est définie dans la base de données.
    |
    */

    'defaut' => [
        'nom' => env('ENTREPRISE_NOM', 'Votre Entreprise'),
        'forme_juridique' => env('ENTREPRISE_FORME_JURIDIQUE', 'SARL'),
        'adresse' => env('ENTREPRISE_ADRESSE', ''),
        'code_postal' => env('ENTREPRISE_CODE_POSTAL', ''),
        'ville' => env('ENTREPRISE_VILLE', ''),
        'telephone' => env('ENTREPRISE_TELEPHONE', ''),
        'telephone_mobile' => env('ENTREPRISE_MOBILE', ''),
        'email' => env('ENTREPRISE_EMAIL', ''),
        'site_web' => env('ENTREPRISE_SITE_WEB', ''),
        'siret' => env('ENTREPRISE_SIRET', ''),
        'tva_intracommunautaire' => env('ENTREPRISE_TVA_INTRA', ''),
        'capital' => env('ENTREPRISE_CAPITAL', null),
        'code_ape' => env('ENTREPRISE_CODE_APE', ''),
        'banque' => env('ENTREPRISE_BANQUE', ''),
        'iban' => env('ENTREPRISE_IBAN', ''),
        'bic' => env('ENTREPRISE_BIC', ''),
        'couleur_principale' => env('ENTREPRISE_COULEUR', '#2563eb'),
        'taux_tva_defaut' => env('ENTREPRISE_TVA_DEFAUT', 20.00),
        'delai_paiement_defaut' => env('ENTREPRISE_DELAI_PAIEMENT', 30),
        'conditions_generales_defaut' => env('ENTREPRISE_CONDITIONS_GENERALES', ''),
        'modalites_paiement_defaut' => env('ENTREPRISE_MODALITES_PAIEMENT', 'Paiement à 30 jours fin de mois'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuration PDF
    |--------------------------------------------------------------------------
    |
    | Paramètres pour la génération des PDF de devis et factures
    |
    */

    'pdf' => [
        'format' => 'A4',
        'orientation' => 'portrait',
        'margin' => [
            'top' => 20,
            'right' => 20,
            'bottom' => 20,
            'left' => 20,
        ],
        'options' => [
            'dpi' => 96,
            'enable_remote' => true,
            'enable_php' => false,
            'enable_javascript' => false,
            'default_font' => 'Arial',
        ],
        'police' => [
            'famille' => 'Arial',
            'taille_titre' => 24,
            'taille_sous_titre' => 16,
            'taille_texte' => 12,
            'taille_petit' => 10,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Numérotation automatique
    |--------------------------------------------------------------------------
    |
    | Configuration pour la numérotation des devis et factures
    |
    */

    'numerotation' => [
        'devis' => [
            'prefixe' => 'DEV',
            'format' => 'DEV-{YYYY}-{NNN}',
            'reset_annuel' => true,
            'longueur_numero' => 3,
        ],
        'factures' => [
            'prefixe' => 'F',
            'format' => 'F-{YYYY}-{NNN}',
            'reset_annuel' => true,
            'longueur_numero' => 3,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Paramètres de facturation
    |--------------------------------------------------------------------------
    |
    | Configuration par défaut pour la facturation
    |
    */

    'facturation' => [
        'taux_penalites_retard' => 10.0, // % par an
        'indemnite_recouvrement' => 40.0, // € forfaitaire
        'delai_relance_1' => 15, // jours après échéance
        'delai_relance_2' => 30, // jours après échéance
        'delai_mise_en_demeure' => 60, // jours après échéance
        'unites_courantes' => [
            'h' => 'heure',
            'j' => 'jour',
            'unité' => 'unité',
            'm²' => 'mètre carré',
            'ml' => 'mètre linéaire',
            'forfait' => 'forfait',
        ],
        'modes_paiement' => [
            'virement' => 'Virement bancaire',
            'cheque' => 'Chèque',
            'especes' => 'Espèces',
            'cb' => 'Carte bancaire',
            'prelevement' => 'Prélèvement automatique',
            'autre' => 'Autre',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Catégories de prestations
    |--------------------------------------------------------------------------
    |
    | Catégories prédéfinies pour organiser les lignes de devis/factures
    |
    */

    'categories_prestations' => [
        'main_oeuvre' => 'Main d\'œuvre',
        'materiaux' => 'Matériaux',
        'equipements' => 'Équipements',
        'transport' => 'Transport',
        'etudes' => 'Études et conception',
        'formation' => 'Formation',
        'maintenance' => 'Maintenance',
        'autre' => 'Autre',
    ],

    /*
    |--------------------------------------------------------------------------
    | Validation et contraintes
    |--------------------------------------------------------------------------
    |
    | Règles de validation pour les documents
    |
    */

    'validation' => [
        'logo' => [
            'max_size' => 2048, // Ko
            'formats' => ['jpeg', 'png', 'svg'],
            'dimensions' => [
                'max_width' => 800,
                'max_height' => 300,
                'recommandee_width' => 300,
                'recommandee_height' => 100,
            ],
        ],
        'devis' => [
            'validite_min' => 7, // jours minimum
            'validite_max' => 90, // jours maximum
            'validite_defaut' => 30, // jours par défaut
        ],
        'factures' => [
            'delai_paiement_min' => 1, // jour minimum
            'delai_paiement_max' => 120, // jours maximum
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Messages et textes par défaut
    |--------------------------------------------------------------------------
    |
    | Textes utilisés dans les documents et l'interface
    |
    */

    'messages' => [
        'devis' => [
            'en_tete' => 'Nous avons le plaisir de vous proposer le devis suivant :',
            'pied_page' => 'Ce devis est valable {validite} jours à compter de sa date d\'émission.',
            'signature_client' => 'Bon pour accord, signature du client précédée de la mention "Bon pour accord"',
            'conditions_acceptation' => 'Devis accepté le {date} par signature électronique.',
        ],
        'factures' => [
            'en_tete' => 'Nous vous prions de bien vouloir trouver ci-joint notre facture :',
            'pied_page' => 'TVA non applicable, art. 293 B du CGI',
            'echeance' => 'Date limite de règlement : {date}',
            'penalites' => 'En cas de retard de paiement, pénalités de {taux}% par an et indemnité forfaitaire de {montant}€.',
        ],
        'relances' => [
            'rappel_aimable' => 'Nous vous rappelons aimablement que votre facture est échue.',
            'relance_ferme' => 'Malgré notre précédent rappel, votre facture demeure impayée.',
            'mise_en_demeure' => 'Nous vous mettons en demeure de régler le montant dû.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache et performance
    |--------------------------------------------------------------------------
    |
    | Configuration du cache pour les paramètres entreprise
    |
    */

    'cache' => [
        'enabled' => env('ENTREPRISE_CACHE_ENABLED', true),
        'ttl' => env('ENTREPRISE_CACHE_TTL', 3600), // 1 heure en secondes
        'key' => 'entreprise_settings',
        'tags' => ['entreprise', 'settings'],
    ],
];