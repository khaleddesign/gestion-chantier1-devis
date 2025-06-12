<?php
// app/Providers/AuthServiceProvider.php
namespace App\Providers;

use App\Models\Chantier;
use App\Models\Devis;
use App\Models\Facture;
use App\Policies\ChantierPolicy;
use App\Policies\DevisPolicy;
use App\Policies\FacturePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Chantier::class => ChantierPolicy::class,
        Devis::class => DevisPolicy::class,
        Facture::class => FacturePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies(); // 🔥 LIGNE CRITIQUE AJOUTÉE

        // Gates personnalisés
        Gate::define('admin-only', function ($user) {
            return $user->isAdmin();
        });

        Gate::define('commercial-or-admin', function ($user) {
            return $user->isCommercial() || $user->isAdmin();
        });

        Gate::define('manage-users', function ($user) {
            return $user->isAdmin();
        });

        // Gates pour les devis/factures
        Gate::define('manage-devis', function ($user) {
            return $user->isAdmin() || $user->isCommercial();
        });

        Gate::define('manage-factures', function ($user) {
            return $user->isAdmin() || $user->isCommercial();
        });

        Gate::define('view-financial-data', function ($user) {
            return $user->isAdmin() || $user->isCommercial();
        });
    }
}