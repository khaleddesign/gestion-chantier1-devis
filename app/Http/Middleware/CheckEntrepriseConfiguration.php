<?php
// app/Http/Middleware/CheckEntrepriseConfiguration.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\EntrepriseSettings;

class CheckEntrepriseConfiguration
{
    /**
     * Routes exclues de la vérification
     */
    protected $excludedRoutes = [
        'admin.entreprise.*',
        'logout',
        'login',
        'register',
        'password.*',
        'verification.*',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Ignorer les requêtes AJAX et API
        if ($request->ajax() || $request->expectsJson()) {
            return $next($request);
        }

        // Ignorer si l'utilisateur n'est pas admin
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            return $next($request);
        }

        // Ignorer certaines routes
        foreach ($this->excludedRoutes as $pattern) {
            if ($request->routeIs($pattern)) {
                return $next($request);
            }
        }

        // Vérifier si l'entreprise est configurée
        if (!$this->isEntrepriseConfigured()) {
            // Rediriger vers la page de configuration avec un message
            return redirect()
                ->route('admin.entreprise.settings')
                ->with('warning', 
                    'Veuillez configurer les paramètres de votre entreprise avant de continuer. ' .
                    'Ces informations sont nécessaires pour générer les devis et factures.'
                );
        }

        return $next($request);
    }

    /**
     * Vérifier si l'entreprise est configurée
     */
    private function isEntrepriseConfigured(): bool
    {
        try {
            return EntrepriseSettings::isConfigured();
        } catch (\Exception $e) {
            // En cas d'erreur (table pas encore créée par exemple), 
            // ne pas bloquer l'accès
            return true;
        }
    }
}