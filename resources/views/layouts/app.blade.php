<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Dashboard') - {{ config('app.name', 'Gestion Chantiers') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    
    <!-- Font Awesome pour les icônes -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    <!-- Styles compilés avec Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Styles supplémentaires de la page -->
    @stack('styles')
    
    <style>
        /* Styles pour assurer que les modales fonctionnent */
        .modal-open {
            overflow: hidden;
        }
        
        /* Amélioration des transitions */
        .transition-all {
            transition-property: all;
            transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
            transition-duration: 150ms;
        }
        
        /* Style pour les étoiles de notation */
        .star-rating i {
            transition: color 0.2s ease-in-out;
        }
        
        .star-rating i:hover {
            transform: scale(1.1);
        }
    </style>
</head>
<body class="font-sans antialiased bg-gray-50">
    <div id="app">
        <!-- Navigation -->
        @auth
        <nav class="bg-white shadow-sm border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <!-- Logo -->
                        <div class="shrink-0 flex items-center">
                            <a href="{{ route('dashboard') }}" class="text-xl font-bold text-gray-900">
                                <i class="fas fa-hard-hat mr-2 text-blue-600"></i>{{ config('app.name', 'Chantiers') }}
                            </a>
                        </div>

                        <!-- Navigation Links -->
                        <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                            <a href="{{ route('dashboard') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('dashboard') ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} text-sm font-medium transition-colors duration-150">
                                <i class="fas fa-home mr-2"></i>Dashboard
                            </a>
                            <a href="{{ route('chantiers.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('chantiers.*') ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} text-sm font-medium transition-colors duration-150">
                                <i class="fas fa-building mr-2"></i>Chantiers
                            </a>
                            @if(Auth::user()->isAdmin())
                            <a href="{{ route('admin.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('admin.*') ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} text-sm font-medium transition-colors duration-150">
                                <i class="fas fa-cog mr-2"></i>Administration
                            </a>
                            @endif
                        </div>
                    </div>

                    <!-- Right Side -->
                    <div class="hidden sm:flex sm:items-center sm:ml-6">
                        <!-- Notifications -->
                        <div class="mr-3 relative">
                            <a href="{{ route('notifications.index') }}" class="relative p-2 text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-150">
                                <i class="fas fa-bell text-lg"></i>
                                @if(Auth::user()->getNotificationsNonLues() > 0)
                                <span class="absolute top-0 right-0 block h-5 w-5 rounded-full bg-red-400 text-white text-xs font-medium flex items-center justify-center">
                                    {{ Auth::user()->getNotificationsNonLues() }}
                                </span>
                                @endif
                            </a>
                        </div>

                        <!-- User Dropdown -->
                        <div class="ml-3 relative">
                            <div class="flex items-center space-x-3">
                                <div class="flex flex-col items-end">
                                    <div class="text-sm font-medium text-gray-900">{{ Auth::user()->name }}</div>
                                    <div class="text-xs text-gray-500 capitalize">{{ Auth::user()->role }}</div>
                                </div>
                                <form method="POST" action="{{ route('logout') }}" class="inline">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-150">
                                        <i class="fas fa-sign-out-alt mr-1"></i>Déconnexion
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Hamburger (mobile) -->
                    <div class="-mr-2 flex items-center sm:hidden">
                        <button type="button" class="bg-white inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-500" onclick="toggleMobileMenu()">
                            <i class="fas fa-bars"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Mobile menu -->
            <div class="sm:hidden hidden" id="mobile-menu">
                <div class="pt-2 pb-3 space-y-1">
                    <a href="{{ route('dashboard') }}" class="block pl-3 pr-4 py-2 border-l-4 {{ request()->routeIs('dashboard') ? 'border-blue-500 text-blue-700 bg-blue-50' : 'border-transparent text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300' }} text-base font-medium transition-colors duration-150">
                        <i class="fas fa-home mr-2"></i>Dashboard
                    </a>
                    <a href="{{ route('chantiers.index') }}" class="block pl-3 pr-4 py-2 border-l-4 {{ request()->routeIs('chantiers.*') ? 'border-blue-500 text-blue-700 bg-blue-50' : 'border-transparent text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300' }} text-base font-medium transition-colors duration-150">
                        <i class="fas fa-building mr-2"></i>Chantiers
                    </a>
                    @if(Auth::user()->isAdmin())
                    <a href="{{ route('admin.index') }}" class="block pl-3 pr-4 py-2 border-l-4 {{ request()->routeIs('admin.*') ? 'border-blue-500 text-blue-700 bg-blue-50' : 'border-transparent text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300' }} text-base font-medium transition-colors duration-150">
                        <i class="fas fa-cog mr-2"></i>Administration
                    </a>
                    @endif
                    <a href="{{ route('notifications.index') }}" class="block pl-3 pr-4 py-2 border-l-4 border-transparent text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300 text-base font-medium transition-colors duration-150">
                        <i class="fas fa-bell mr-2"></i>Notifications 
                        @if(Auth::user()->getNotificationsNonLues() > 0)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 ml-2">
                            {{ Auth::user()->getNotificationsNonLues() }}
                        </span>
                        @endif
                    </a>
                </div>
                <div class="pt-4 pb-3 border-t border-gray-200">
                    <div class="px-4">
                        <div class="text-base font-medium text-gray-800">{{ Auth::user()->name }}</div>
                        <div class="text-sm text-gray-500 capitalize">{{ Auth::user()->role }}</div>
                    </div>
                    <div class="mt-3 space-y-1">
                        <form method="POST" action="{{ route('logout') }}" class="block">
                            @csrf
                            <button type="submit" class="block w-full text-left pl-3 pr-4 py-2 border-l-4 border-transparent text-base font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300 transition-colors duration-150">
                                <i class="fas fa-sign-out-alt mr-2"></i>Déconnexion
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </nav>
        @endauth

        <!-- Alertes flash -->
        @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 mx-4 mt-4 rounded-md" role="alert">
            <div class="flex items-center">
                <i class="fas fa-check-circle mr-2"></i>
                <span>{{ session('success') }}</span>
                <button onclick="this.parentElement.parentElement.remove()" class="ml-auto text-green-600 hover:text-green-800">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        @endif

        @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 mx-4 mt-4 rounded-md" role="alert">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <span>{{ session('error') }}</span>
                <button onclick="this.parentElement.parentElement.remove()" class="ml-auto text-red-600 hover:text-red-800">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        @endif

        @if(session('warning'))
        <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 mx-4 mt-4 rounded-md" role="alert">
            <div class="flex items-center">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                <span>{{ session('warning') }}</span>
                <button onclick="this.parentElement.parentElement.remove()" class="ml-auto text-yellow-600 hover:text-yellow-800">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        @endif

        @if(session('info'))
        <div class="bg-blue-50 border border-blue-200 text-blue-800 px-4 py-3 mx-4 mt-4 rounded-md" role="alert">
            <div class="flex items-center">
                <i class="fas fa-info-circle mr-2"></i>
                <span>{{ session('info') }}</span>
                <button onclick="this.parentElement.parentElement.remove()" class="ml-auto text-blue-600 hover:text-blue-800">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        @endif

        <!-- Erreurs de validation -->
        @if ($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 mx-4 mt-4 rounded-md" role="alert">
            <div class="flex items-start">
                <i class="fas fa-exclamation-circle mr-2 mt-0.5"></i>
                <div class="flex-1">
                    <div class="font-medium">Erreurs de validation :</div>
                    <ul class="mt-1 list-disc list-inside text-sm">
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                <button onclick="this.parentElement.parentElement.remove()" class="ml-2 text-red-600 hover:text-red-800">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        @endif

        <!-- Contenu principal -->
        <main class="min-h-screen">
            @yield('content')
        </main>

        <!-- Footer (optionnel) -->
        <footer class="bg-white border-t border-gray-200 mt-auto">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center">
                    <div class="text-gray-500 text-sm">
                        © {{ date('Y') }} {{ config('app.name') }}. Tous droits réservés.
                    </div>
                    <div class="flex space-x-6 text-sm text-gray-500">
                        <a href="#" class="hover:text-gray-700 transition-colors duration-150">Support</a>
                        <a href="#" class="hover:text-gray-700 transition-colors duration-150">Documentation</a>
                        <a href="#" class="hover:text-gray-700 transition-colors duration-150">Contact</a>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    <!-- Scripts globaux -->
    <script>
        // Configuration globale
        window.App = {
            csrfToken: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            user: @auth @json(auth()->user()) @else null @endauth,
            routes: {
                dashboard: "{{ route('dashboard') }}",
                logout: "{{ route('logout') }}",
                @auth
                notifications: "{{ route('notifications.index') }}",
                @endauth
            }
        };

        // Fonction utilitaire pour les requêtes AJAX avec CSRF
        window.fetchWithCSRF = function(url, options = {}) {
            const defaultOptions = {
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': window.App.csrfToken,
                    'Accept': 'application/json',
                    ...options.headers
                }
            };
            
            return fetch(url, { ...defaultOptions, ...options });
        };

        // Toggle menu mobile
        function toggleMobileMenu() {
            const menu = document.getElementById('mobile-menu');
            menu.classList.toggle('hidden');
        }

        // Auto-hide alerts après 5 secondes
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('[role="alert"]');
            alerts.forEach(alert => {
                setTimeout(() => {
                    if (alert.parentElement) {
                        alert.style.transition = 'opacity 0.5s ease-out';
                        alert.style.opacity = '0';
                        setTimeout(() => alert.remove(), 500);
                    }
                }, 5000);
            });

            // Fermeture du menu mobile en cliquant ailleurs
            document.addEventListener('click', function(e) {
                const mobileMenu = document.getElementById('mobile-menu');
                const mobileMenuButton = document.querySelector('[onclick="toggleMobileMenu()"]');
                
                if (mobileMenu && !mobileMenu.classList.contains('hidden') && 
                    !mobileMenu.contains(e.target) && !mobileMenuButton.contains(e.target)) {
                    mobileMenu.classList.add('hidden');
                }
            });
        });

        // Gestion globale des erreurs AJAX
        window.addEventListener('unhandledrejection', function(event) {
            console.error('Erreur non gérée:', event.reason);
            
            // Afficher un message d'erreur à l'utilisateur
            if (event.reason && event.reason.message) {
                showNotification('Une erreur est survenue: ' + event.reason.message, 'error');
            }
        });

        // Fonction utilitaire pour afficher des notifications
        window.showNotification = function(message, type = 'info', duration = 5000) {
            const alertClasses = {
                success: 'bg-green-50 border-green-200 text-green-800',
                error: 'bg-red-50 border-red-200 text-red-800',
                warning: 'bg-yellow-50 border-yellow-200 text-yellow-800',
                info: 'bg-blue-50 border-blue-200 text-blue-800'
            };

            const iconClasses = {
                success: 'fas fa-check-circle',
                error: 'fas fa-exclamation-circle',
                warning: 'fas fa-exclamation-triangle',
                info: 'fas fa-info-circle'
            };

            const alertDiv = document.createElement('div');
            alertDiv.className = `fixed top-4 right-4 z-50 max-w-sm w-full ${alertClasses[type]} px-4 py-3 rounded-md border shadow-lg`;
            alertDiv.innerHTML = `
                <div class="flex items-center">
                    <i class="${iconClasses[type]} mr-2"></i>
                    <span class="flex-1">${message}</span>
                    <button onclick="this.parentElement.parentElement.remove()" class="ml-2 text-current opacity-75 hover:opacity-100 transition-opacity duration-150">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;

            document.body.appendChild(alertDiv);

            // Auto-suppression
            if (duration > 0) {
                setTimeout(() => {
                    if (alertDiv.parentElement) {
                        alertDiv.style.transition = 'opacity 0.5s ease-out, transform 0.5s ease-out';
                        alertDiv.style.opacity = '0';
                        alertDiv.style.transform = 'translateX(100%)';
                        setTimeout(() => alertDiv.remove(), 500);
                    }
                }, duration);
            }
        };

        // Fonction pour confirmer les actions destructrices
        window.confirmAction = function(message = 'Êtes-vous sûr de vouloir effectuer cette action ?') {
            return confirm(message);
        };

        // Fonction pour formater les nombres
        window.formatNumber = function(number, decimals = 0) {
            return new Intl.NumberFormat('fr-FR', {
                minimumFractionDigits: decimals,
                maximumFractionDigits: decimals
            }).format(number);
        };

        // Fonction pour formater les dates
        window.formatDate = function(date, options = {}) {
            const defaultOptions = {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit'
            };
            
            return new Intl.DateTimeFormat('fr-FR', { ...defaultOptions, ...options }).format(new Date(date));
        };

        // Debug info en mode local
        @if(app()->environment('local'))
        console.log('🚀 Application Laravel chargée');
        console.log('👤 Utilisateur connecté:', window.App.user);
        console.log('🔗 Routes disponibles:', window.App.routes);
        @endif
    </script>

    <!-- Scripts supplémentaires de la page -->
    @stack('scripts')
    @yield('scripts')
</body>
</html>