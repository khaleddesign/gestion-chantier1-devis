<?php
// app/Helpers/EntrepriseHelper.php

if (!function_exists('entreprise')) {
    /**
     * Récupérer un paramètre de l'entreprise
     *
     * @param string|null $key
     * @param mixed $default
     * @return mixed
     */
    function entreprise(?string $key = null, $default = null)
    {
        $settings = app('entreprise.settings');
        
        if ($key === null) {
            return $settings;
        }
        
        return data_get($settings, $key, $default);
    }
}

if (!function_exists('entreprise_logo_url')) {
    /**
     * Récupérer l'URL du logo de l'entreprise
     *
     * @return string|null
     */
    function entreprise_logo_url(): ?string
    {
        return entreprise('logo_url');
    }
}

if (!function_exists('entreprise_logo_path')) {
    /**
     * Récupérer le chemin absolu du logo pour les PDF
     *
     * @return string|null
     */
    function entreprise_logo_path(): ?string
    {
        return entreprise('logo_path');
    }
}

if (!function_exists('entreprise_configured')) {
    /**
     * Vérifier si l'entreprise est configurée
     *
     * @return bool
     */
    function entreprise_configured(): bool
    {
        return entreprise('configured', false);
    }
}

if (!function_exists('format_euro')) {
    /**
     * Formater un montant en euros
     *
     * @param float $montant
     * @param int $decimales
     * @return string
     */
    function format_euro(float $montant, int $decimales = 2): string
    {
        return number_format($montant, $decimales, ',', ' ') . ' €';
    }
}

if (!function_exists('format_pourcentage')) {
    /**
     * Formater un pourcentage
     *
     * @param float $pourcentage
     * @param int $decimales
     * @return string
     */
    function format_pourcentage(float $pourcentage, int $decimales = 1): string
    {
        return number_format($pourcentage, $decimales, ',', ' ') . '%';
    }
}

if (!function_exists('format_telephone')) {
    /**
     * Formater un numéro de téléphone français
     *
     * @param string $telephone
     * @return string
     */
    function format_telephone(string $telephone): string
    {
        $numero = preg_replace('/[^\d]/', '', $telephone);
        
        if (strlen($numero) === 10) {
            return substr($numero, 0, 2) . ' ' . 
                   substr($numero, 2, 2) . ' ' . 
                   substr($numero, 4, 2) . ' ' . 
                   substr($numero, 6, 2) . ' ' . 
                   substr($numero, 8, 2);
        }
        
        return $telephone;
    }
}

if (!function_exists('format_siret')) {
    /**
     * Formater un numéro SIRET
     *
     * @param string $siret
     * @return string
     */
    function format_siret(string $siret): string
    {
        $numero = preg_replace('/[^\d]/', '', $siret);
        
        if (strlen($numero) === 14) {
            return substr($numero, 0, 3) . ' ' . 
                   substr($numero, 3, 3) . ' ' . 
                   substr($numero, 6, 3) . ' ' . 
                   substr($numero, 9, 5);
        }
        
        return $siret;
    }
}

if (!function_exists('format_iban')) {
    /**
     * Formater un IBAN
     *
     * @param string $iban
     * @return string
     */
    function format_iban(string $iban): string
    {
        $numero = strtoupper(preg_replace('/[^\w]/', '', $iban));
        
        return chunk_split($numero, 4, ' ');
    }
}

if (!function_exists('generer_numero_devis')) {
    /**
     * Générer un numéro de devis selon le format configuré
     *
     * @return string
     */
    function generer_numero_devis(): string
    {
        $format = config('entreprise.numerotation.devis.format', 'DEV-{YYYY}-{NNN}');
        $prefixe = config('entreprise.numerotation.devis.prefixe', 'DEV');
        $longueur = config('entreprise.numerotation.devis.longueur_numero', 3);
        $resetAnnuel = config('entreprise.numerotation.devis.reset_annuel', true);
        
        $annee = date('Y');
        
        // Récupérer le dernier numéro
        $query = \App\Models\Devis::where('numero', 'like', "{$prefixe}-{$annee}-%");
        
        if (!$resetAnnuel) {
            $query = \App\Models\Devis::where('numero', 'like', "{$prefixe}-%");
        }
        
        $dernierNumero = $query->orderBy('numero', 'desc')->value('numero');
        
        if ($dernierNumero) {
            $parties = explode('-', $dernierNumero);
            $numero = (int) end($parties) + 1;
        } else {
            $numero = 1;
        }
        
        // Remplacer les placeholders
        $numeroFormate = str_replace([
            '{YYYY}',
            '{YY}',
            '{NNN}',
            '{NN}',
            '{N}'
        ], [
            $annee,
            substr($annee, -2),
            str_pad($numero, $longueur, '0', STR_PAD_LEFT),
            str_pad($numero, 2, '0', STR_PAD_LEFT),
            $numero
        ], $format);
        
        return $numeroFormate;
    }
}

if (!function_exists('generer_numero_facture')) {
    /**
     * Générer un numéro de facture selon le format configuré
     *
     * @return string
     */
    function generer_numero_facture(): string
    {
        $format = config('entreprise.numerotation.factures.format', 'F-{YYYY}-{NNN}');
        $prefixe = config('entreprise.numerotation.factures.prefixe', 'F');
        $longueur = config('entreprise.numerotation.factures.longueur_numero', 3);
        $resetAnnuel = config('entreprise.numerotation.factures.reset_annuel', true);
        
        $annee = date('Y');
        
        // Récupérer le dernier numéro
        $query = \App\Models\Facture::where('numero', 'like', "{$prefixe}-{$annee}-%");
        
        if (!$resetAnnuel) {
            $query = \App\Models\Facture::where('numero', 'like', "{$prefixe}-%");
        }
        
        $dernierNumero = $query->orderBy('numero', 'desc')->value('numero');
        
        if ($dernierNumero) {
            $parties = explode('-', $dernierNumero);
            $numero = (int) end($parties) + 1;
        } else {
            $numero = 1;
        }
        
        // Remplacer les placeholders
        $numeroFormate = str_replace([
            '{YYYY}',
            '{YY}',
            '{NNN}',
            '{NN}',
            '{N}'
        ], [
            $annee,
            substr($annee, -2),
            str_pad($numero, $longueur, '0', STR_PAD_LEFT),
            str_pad($numero, 2, '0', STR_PAD_LEFT),
            $numero
        ], $format);
        
        return $numeroFormate;
    }
}

if (!function_exists('calculer_penalites_retard')) {
    /**
     * Calculer les pénalités de retard
     *
     * @param float $montant
     * @param \Carbon\Carbon $dateEcheance
     * @param \Carbon\Carbon|null $dateReference
     * @return array
     */
    function calculer_penalites_retard(float $montant, \Carbon\Carbon $dateEcheance, ?\Carbon\Carbon $dateReference = null): array
    {
        $dateReference = $dateReference ?? now();
        
        if ($dateReference <= $dateEcheance) {
            return [
                'jours_retard' => 0,
                'taux_annuel' => 0,
                'montant_penalites' => 0,
                'indemnite_recouvrement' => 0,
                'total' => 0
            ];
        }
        
        $joursRetard = $dateReference->diffInDays($dateEcheance);
        $tauxAnnuel = config('entreprise.facturation.taux_penalites_retard', 10.0);
        $indemniteForfaitaire = config('entreprise.facturation.indemnite_recouvrement', 40.0);
        
        $montantPenalites = $montant * ($tauxAnnuel / 100) * ($joursRetard / 365);
        
        return [
            'jours_retard' => $joursRetard,
            'taux_annuel' => $tauxAnnuel,
            'montant_penalites' => round($montantPenalites, 2),
            'indemnite_recouvrement' => $indemniteForfaitaire,
            'total' => round($montantPenalites + $indemniteForfaitaire, 2)
        ];
    }
}

if (!function_exists('determiner_niveau_relance')) {
    /**
     * Déterminer le niveau de relance selon les jours de retard
     *
     * @param int $joursRetard
     * @return array
     */
    function determiner_niveau_relance(int $joursRetard): array
    {
        $delaiRelance1 = config('entreprise.facturation.delai_relance_1', 15);
        $delaiRelance2 = config('entreprise.facturation.delai_relance_2', 30);
        $delaiMiseEnDemeure = config('entreprise.facturation.delai_mise_en_demeure', 60);
        
        if ($joursRetard < $delaiRelance1) {
            return [
                'niveau' => 0,
                'type' => 'aucune',
                'libelle' => 'Pas de relance nécessaire',
                'urgence' => 'normal'
            ];
        } elseif ($joursRetard < $delaiRelance2) {
            return [
                'niveau' => 1,
                'type' => 'rappel_aimable',
                'libelle' => 'Rappel aimable',
                'urgence' => 'low'
            ];
        } elseif ($joursRetard < $delaiMiseEnDemeure) {
            return [
                'niveau' => 2,
                'type' => 'relance_ferme',
                'libelle' => 'Relance ferme',
                'urgence' => 'medium'
            ];
        } else {
            return [
                'niveau' => 3,
                'type' => 'mise_en_demeure',
                'libelle' => 'Mise en demeure',
                'urgence' => 'high'
            ];
        }
    }
}