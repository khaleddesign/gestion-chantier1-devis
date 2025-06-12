<?php
// config/devis.php

return [
    /*
    |--------------------------------------------------------------------------
    | Configuration du système de devis et factures
    |--------------------------------------------------------------------------
    */

    // Informations de l'entreprise pour les PDF
    'entreprise' => [
        'nom' => env('COMPANY_NAME', 'Votre Entreprise'),
        'adresse' => env('COMPANY_ADDRESS', '123 rue de la Construction, 75001 Paris'),
        'telephone' => env('COMPANY_PHONE', '+33 1 23 45 67 89'),
        'email' => env('COMPANY_EMAIL', 'contact@entreprise.com'),
        'site_web' => env('COMPANY_WEBSITE', 'https://www.entreprise.com'),
        'siret' => env('COMPANY_SIRET', '12345678901234'),
        'tva_intracommunautaire' => env('COMPANY_TVA_INTRA', 'FR12345678901'),
        'rib' => env('COMPANY_RIB', 'FR76 1234 5678 9012 3456 7890 123'),
        'logo_path' => env('COMPANY_LOGO_PATH', null), // Chemin vers le logo
    ],

    // Configuration des devis
    'devis' => [
        // Numérotation automatique
        'numerotation' => [
            'prefixe' => 'DEV',
            'format' => 'DEV-{YYYY}-{NNN}', // {YYYY} = année, {NNN} = numéro séquentiel
            'reset_annuel' => true, // Remet à zéro chaque année
        ],
        
        // Paramètres par défaut
        'defauts' => [
            'duree_validite_jours' => 30,
            'taux_tva' => 20.00,
            'delai_realisation_jours' => 30,
            'modalites_paiement' => 'Paiement à 30 jours fin de mois',
            'conditions_generales' => "Devis gratuit valable 30 jours.\nTravaux soumis aux conditions générales de vente.\nAcompte de 30% à la commande.",
        ],
        
        // Limites
        'max_lignes' => 50,
        'montant_max' => 999999.99,
        
        // Workflow
        'notifications' => [
            'envoie_auto_client' => true,
            'rappel_expiration_jours' => 7, // Rappel X jours avant expiration
            'auto_expire_jours' => 5, // Auto-expire X jours après la date de validité
        ],
        
        // Signature électronique
        'signature' => [
            'active' => true,
            'obligatoire' => false, // Signature obligatoire pour accepter
            'stockage_path' => 'signatures/devis',
        ],
    ],

    // Configuration des factures
    'factures' => [
        // Numérotation automatique
        'numerotation' => [
            'prefixe' => 'F',
            'format' => 'F-{YYYY}-{NNN}',
            'reset_annuel' => true,
        ],
        
        // Paramètres par défaut
        'defauts' => [
            'delai_paiement_jours' => 30,
            'taux_tva' => 20.00,
            'conditions_reglement' => 'Paiement à 30 jours fin de mois par virement bancaire.',
        ],
        
        // Relances automatiques
        'relances' => [
            'active' => true,
            'premiere_relance_jours' => 8, // Premier rappel 8 jours après échéance
            'deuxieme_relance_jours' => 15,
            'derniere_relance_jours' => 30,
            'max_relances' => 3,
            'email_auto' => false, // Envoi automatique des emails de relance
        ],
        
        // Modes de paiement acceptés
        'modes_paiement' => [
            'virement' => 'Virement bancaire',
            'cheque' => 'Chèque',
            'especes' => 'Espèces',
            'cb' => 'Carte bancaire',
            'prelevement' => 'Prélèvement',
            'autre' => 'Autre',
        ],
    ],

    // Configuration des lignes (produits/services)
    'lignes' => [
        // Unités disponibles
        'unites' => [
            'unité' => 'Unité',
            'm²' => 'Mètre carré',
            'ml' => 'Mètre linéaire',
            'm³' => 'Mètre cube',
            'heure' => 'Heure',
            'jour' => 'Jour',
            'forfait' => 'Forfait',
            'kg' => 'Kilogramme',
            'lot' => 'Lot',
            'pièce' => 'Pièce',
        ],
        
        // Catégories par défaut
        'categories' => [
            'materiaux' => 'Matériaux',
            'main_oeuvre' => 'Main d\'œuvre',
            'transport' => 'Transport',
            'location' => 'Location matériel',
            'sous_traitance' => 'Sous-traitance',
            'divers' => 'Divers',
        ],
        
        // Taux de TVA disponibles
        'taux_tva' => [
            0.00 => '0% (exonéré)',
            5.50 => '5,5% (taux réduit)',
            10.00 => '10% (taux intermédiaire)',
            20.00 => '20% (taux normal)',
        ],
    ],

    // Configuration PDF
    'pdf' => [
        'format' => 'A4',
        'orientation' => 'portrait',
        'marges' => [
            'top' => 15,
            'right' => 15,
            'bottom' => 15,
            'left' => 15,
        ],
        'police' => [
            'famille' => 'Arial',
            'taille' => 11,
            'taille_titre' => 16,
        ],
        'couleurs' => [
            'primaire' => '#2563EB',
            'secondaire' => '#6B7280',
            'texte' => '#111827',
        ],
        'options' => [
            'dpi' => 96,
            'enable_php' => false,
            'enable_javascript' => false,
            'enable_remote' => true,
        ],
    ],

    // Configuration des notifications
    'notifications' => [
        'emails' => [
            'devis_envoye' => [
                'subject' => 'Nouveau devis - {numero}',
                'template' => 'emails.devis.envoye',
            ],
            'devis_accepte' => [
                'subject' => 'Devis accepté - {numero}',
                'template' => 'emails.devis.accepte',
            ],
            'devis_refuse' => [
                'subject' => 'Devis refusé - {numero}',
                'template' => 'emails.devis.refuse',
            ],
            'facture_envoyee' => [
                'subject' => 'Nouvelle facture - {numero}',
                'template' => 'emails.facture.envoyee',
            ],
            'facture_relance' => [
                'subject' => 'Rappel facture - {numero}',
                'template' => 'emails.facture.relance',
            ],
            'paiement_recu' => [
                'subject' => 'Paiement reçu - {numero}',
                'template' => 'emails.facture.paiement_recu',
            ],
        ],
        
        // Notifications in-app
        'in_app' => [
            'retention_days' => 90,
            'auto_mark_read_days' => 30,
        ],
    ],

    // Configuration de sécurité
    'securite' => [
        // Tokens pour les liens publics
        'token_expiration_hours' => 72, // Expiration des liens de validation
        'token_algorithm' => 'sha256',
        
        // Permissions
        'client_peut_modifier_devis' => false,
        'client_peut_refuser_devis' => true,
        'commercial_peut_modifier_facture_envoyee' => false,
        
        // Validation
        'signature_ip_tracking' => true,
        'log_actions_importantes' => true,
    ],

    // Configuration des exports
    'exports' => [
        'formats' => [
            'excel' => [
                'extension' => 'xlsx',
                'mime_type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ],
            'csv' => [
                'extension' => 'csv',
                'mime_type' => 'text/csv',
                'delimiter' => ';',
                'encoding' => 'UTF-8',
            ],
            'pdf' => [
                'extension' => 'pdf',
                'mime_type' => 'application/pdf',
            ],
        ],
        
        'limites' => [
            'max_records' => 10000,
            'timeout_seconds' => 300,
        ],
    ],

    // Configuration de stockage
    'stockage' => [
        'documents' => [
            'disk' => 'public',
            'path' => 'documents',
            'max_size_mb' => 10,
            'allowed_types' => ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx', 'xls', 'xlsx'],
        ],
        
        'pdf_generes' => [
            'disk' => 'public',
            'path' => 'pdf',
            'cache_duration_minutes' => 60,
            'auto_cleanup_days' => 7,
        ],
        
        'signatures' => [
            'disk' => 'private',
            'path' => 'signatures',
            'format' => 'png',
            'max_size_kb' => 500,
            'retention_years' => 10, // Conservation légale
        ],
        
        'temp' => [
            'path' => 'temp',
            'cleanup_hours' => 24,
        ],
    ],

    // Configuration de performance
    'performance' => [
        'cache' => [
            'pdf_ttl_minutes' => 60,
            'statistiques_ttl_minutes' => 30,
            'totaux_chantier_ttl_minutes' => 15,
        ],
        
        'pagination' => [
            'devis_per_page' => 20,
            'factures_per_page' => 20,
            'lignes_per_page' => 50,
            'paiements_per_page' => 25,
        ],
        
        'limits' => [
            'max_lignes_par_devis' => 100,
            'max_paiements_par_facture' => 50,
            'max_upload_simultane' => 5,
        ],
    ],

    // Configuration de l'interface utilisateur
    'ui' => [
        // Thème des couleurs pour les statuts
        'statuts_couleurs' => [
            // Devis
            'devis' => [
                'brouillon' => 'gray',
                'envoye' => 'blue',
                'accepte' => 'green',
                'refuse' => 'red',
                'expire' => 'orange',
            ],
            
            // Factures
            'factures' => [
                'brouillon' => 'gray',
                'envoyee' => 'blue',
                'payee_partiel' => 'yellow',
                'payee' => 'green',
                'en_retard' => 'red',
                'annulee' => 'gray',
            ],
            
            // Paiements
            'paiements' => [
                'en_attente' => 'yellow',
                'valide' => 'green',
                'rejete' => 'red',
            ],
        ],
        
        // Icônes par défaut
        'icones' => [
            'devis' => 'document-text',
            'facture' => 'receipt-tax',
            'paiement' => 'banknotes',
            'signature' => 'pencil-square',
            'pdf' => 'document-arrow-down',
            'email' => 'envelope',
        ],
        
        // Messages d'aide
        'aide' => [
            'devis_expire_info' => 'Ce devis a dépassé sa date de validité. Le client ne peut plus l\'accepter.',
            'facture_en_retard_info' => 'Cette facture a dépassé sa date d\'échéance.',
            'signature_electronique_info' => 'La signature électronique a valeur légale et horodate l\'acceptation.',
            'conversion_devis_info' => 'La conversion créera automatiquement une facture avec les mêmes lignes.',
        ],
    ],

    // Configuration des rapports
    'rapports' => [
        'chiffre_affaires' => [
            'periodes' => ['mois', 'trimestre', 'semestre', 'annee'],
            'groupements' => ['commercial', 'client', 'statut', 'mois'],
            'metriques' => ['montant_ht', 'montant_ttc', 'nombre', 'taux_conversion'],
        ],
        
        'impayees' => [
            'seuils_alerte' => [
                'jaune' => 15, // jours de retard
                'orange' => 30,
                'rouge' => 60,
            ],
            'groupements' => ['commercial', 'client', 'anciennete'],
        ],
        
        'performance_commerciale' => [
            'kpi' => [
                'nombre_devis',
                'taux_conversion',
                'montant_moyen',
                'delai_moyen_acceptation',
                'ca_mensuel',
                'ca_annuel',
            ],
        ],
    ],

    // Configuration des intégrations
    'integrations' => [
        'comptabilite' => [
            'active' => false,
            'format_export' => 'csv', // csv, xml, api
            'mapping_comptes' => [
                'ventes' => '701000',
                'tva_collectee' => '445571',
                'clients' => '411000',
            ],
        ],
        
        'banque' => [
            'active' => false,
            'rapprochement_auto' => false,
            'tolerance_montant' => 0.01,
        ],
        
        'erp' => [
            'active' => false,
            'sync_produits' => false,
            'sync_clients' => false,
        ],
    ],

    // Configuration de sauvegarde
    'sauvegarde' => [
        'auto_backup' => [
            'active' => false,
            'frequence' => 'daily', // daily, weekly, monthly
            'retention_days' => 30,
            'inclure_documents' => true,
        ],
        
        'export_donnees' => [
            'formats' => ['json', 'xml', 'csv'],
            'compression' => true,
            'chiffrement' => false,
        ],
    ],

    // Configuration de développement et debug
    'debug' => [
        'log_queries' => env('DEVIS_LOG_QUERIES', false),
        'log_pdf_generation' => env('DEVIS_LOG_PDF', false),
        'cache_pdf_debug' => env('DEVIS_CACHE_PDF_DEBUG', false),
        'show_sql_time' => env('DEVIS_SHOW_SQL_TIME', false),
    ],

    // Configuration de conformité légale
    'legal' => [
        'rgpd' => [
            'active' => true,
            'retention_data_years' => 10,
            'anonymisation_auto' => false,
            'consent_tracking' => true,
        ],
        
        'mentions_legales' => [
            'factures' => 'Mentions légales : TVA non applicable, art. 293 B du CGI. En cas de retard de paiement, indemnité forfaitaire de 40€ pour frais de recouvrement (art. L441-6 du code de commerce).',
            'devis' => 'Devis gratuit valable 30 jours. Travaux non commencés.',
        ],
        
        'archivage' => [
            'duree_legale_factures' => 10, // années
            'duree_legale_devis' => 5,
            'format_archivage' => 'pdf',
        ],
    ],

    // Messages personnalisables
    'messages' => [
        'devis' => [
            'email_envoi_client' => 'Nous avons le plaisir de vous adresser notre devis pour votre projet.',
            'email_acceptation_commercial' => 'Excellente nouvelle ! Le client a accepté votre devis.',
            'email_refus_commercial' => 'Le client a décliné le devis. N\'hésitez pas à reprendre contact.',
        ],
        
        'factures' => [
            'email_envoi_client' => 'Veuillez trouver ci-joint votre facture.',
            'email_relance_1' => 'Nous vous rappelons qu\'une facture est en attente de règlement.',
            'email_relance_2' => 'Malgré notre précédent courrier, votre facture reste impayée.',
            'email_relance_3' => 'Dernier rappel avant mise en demeure. Merci de régulariser votre situation.',
        ],
        
        'paiements' => [
            'email_confirmation' => 'Nous accusons réception de votre paiement. Merci.',
        ],
    ],

    // Configuration des webhooks (pour intégrations futures)
    'webhooks' => [
        'active' => false,
        'endpoints' => [
            'devis_accepte' => env('WEBHOOK_DEVIS_ACCEPTE'),
            'facture_payee' => env('WEBHOOK_FACTURE_PAYEE'),
            'paiement_recu' => env('WEBHOOK_PAIEMENT_RECU'),
        ],
        'security' => [
            'secret_key' => env('WEBHOOK_SECRET_KEY'),
            'timeout_seconds' => 30,
            'max_retries' => 3,
        ],
    ],

    // Paramètres avancés
    'avance' => [
        'multi_devise' => [
            'active' => false,
            'devise_defaut' => 'EUR',
            'devises_acceptees' => ['EUR', 'USD', 'GBP'],
            'taux_change_api' => env('CURRENCY_API_KEY'),
        ],
        
        'multi_tva' => [
            'active' => false,
            'gestion_tva_par_ligne' => true,
            'calcul_tva_encaissement' => false,
        ],
        
        'workflow_approbation' => [
            'active' => false,
            'seuil_montant' => 10000,
            'approbateurs' => ['admin'],
            'notifications_approbation' => true,
        ],
        
        'numerotation_personnalisee' => [
            'active' => false,
            'pattern_devis' => 'DEV-{YYYY}{MM}-{NNNN}',
            'pattern_factures' => 'FACT-{YYYY}{MM}-{NNNN}',
            'variables_disponibles' => ['{YYYY}', '{MM}', '{DD}', '{NNNN}', '{COMMERCIAL}'],
        ],
    ],
];