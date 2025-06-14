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

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Compiled styles & scripts via Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')

    <style>
        /* Modal scroll lock */
        .modal-open{overflow:hidden;}
        /* Hover micro‑interaction */
        .star-rating i{transition:color .2s ease-in-out;}
        .star-rating i:hover{transform:scale(1.1);}
    </style>
</head>
<body class="font-sans antialiased bg-gray-50">
<div id="app" class="min-h-screen flex flex-col">
    @auth
    <!-- ========= NAVBAR ========= -->
    <nav class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <!-- Left -->
                <div class="flex items-center">
                    <!-- Logo -->
                    <a href="{{ route('dashboard') }}" class="text-xl font-bold text-gray-900 flex items-center">
                        <i class="fas fa-hard-hat mr-2 text-blue-600"></i>{{ config('app.name', 'Chantiers') }}
                    </a>

                    <!-- Desktop links -->
                    <div class="hidden sm:flex sm:space-x-8 sm:ml-10">
                        <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                            <i class="fas fa-home mr-2"></i>Dashboard
                        </x-nav-link>
                        <x-nav-link :href="route('chantiers.index')" :active="request()->routeIs('chantiers.*')">
                            <i class="fas fa-building mr-2"></i>Chantiers
                        </x-nav-link>
                        @if(Auth::user()->isAdmin())
                        <!-- Dropdown Admin -->
                        <div x-data="{open:false}" class="relative">
                            <button @click="open=!open" class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition-colors duration-150"
                                    :class="open || '{{ request()->routeIs('admin.*') ? 'border-blue-500 text-gray-900' : '' }}'">
                                <i class="fas fa-cog mr-2"></i>Administration
                                <i class="fas fa-chevron-down ml-1 text-xs transform transition-transform" :class="{'rotate-180':open}"></i>
                            </button>

                            <!-- Menu -->
                            <div x-show="open" @click.away="open=false" x-transition
                                 class="absolute left-0 mt-2 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50">
                                <a href="{{ route('admin.index') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-tachometer-alt mr-3 text-gray-400"></i>Tableau de bord
                                </a>
                                <a href="{{ route('admin.users') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-users mr-3 text-gray-400"></i>Utilisateurs
                                </a>
                                <a href="{{ route('admin.entreprise.settings') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-building mr-3 text-gray-400"></i>Paramètres entreprise
                                </a>
                                <a href="{{ route('admin.statistics') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-chart-bar mr-3 text-gray-400"></i>Statistiques
                                </a>
                                <div class="border-t border-gray-100"></div>
                                <a href="{{ route('admin.entreprise.export') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-download mr-3 text-gray-400"></i>Exporter paramètres
                                </a>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Right -->
                <div class="hidden sm:flex sm:items-center sm:space-x-6">
                    <!-- Notifications -->
                    <div class="relative">
                        <a href="{{ route('notifications.index') }}" class="p-2 text-gray-400 hover:text-gray-500 focus:outline-none transition-colors duration-150">
                            <i class="fas fa-bell text-lg"></i>
                            @if(Auth::user()->getNotificationsNonLues() > 0)
                            <span class="absolute -top-1 -right-1 h-5 w-5 rounded-full bg-red-400 text-white text-xs flex items-center justify-center">
                                {{ Auth::user()->getNotificationsNonLues() }}
                            </span>
                            @endif
                        </a>
                    </div>

                    <!-- User -->
                    <div class="relative">
                        <div class="flex items-center space-x-3">
                            <div class="flex flex-col items-end">
                                <span class="text-sm font-medium text-gray-900">{{ Auth::user()->name }}</span>
                                <span class="text-xs text-gray-500 capitalize">{{ Auth::user()->role }}</span>
                            </div>
                            <form method="POST" action="{{ route('logout') }}" class="inline">
                                @csrf
                                <button type="submit" class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-transparent rounded-md hover:text-gray-700 transition-colors duration-150">
                                    <i class="fas fa-sign-out-alt mr-1"></i>Déconnexion
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Mobile button -->
                <div class="flex items-center sm:hidden">
                    <button type="button" class="p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none" onclick="toggleMobileMenu()">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile menu -->
        <div id="mobile-menu" class="sm:hidden hidden">
            <div class="pt-2 pb-3 space-y-1">
                <x-mobile-link route="dashboard" icon="fas fa-home">Dashboard</x-mobile-link>
                <x-mobile-link route="chantiers.index" icon="fas fa-building">Chantiers</x-mobile-link>
                @if(Auth::user()->isAdmin())
                    <x-mobile-link route="admin.index" icon="fas fa-tachometer-alt">Admin – Tableau de bord</x-mobile-link>
                    <x-mobile-link route="admin.users" icon="fas fa-users">Utilisateurs</x-mobile-link>
                    <x-mobile-link route="admin.entreprise.settings" icon="fas fa-building">Paramètres entreprise</x-mobile-link>
                    <x-mobile-link route="admin.statistics" icon="fas fa-chart-bar">Statistiques</x-mobile-link>
                    <x-mobile-link route="admin.entreprise.export" icon="fas fa-download">Exporter paramètres</x-mobile-link>
                @endif
                <x-mobile-link route="notifications.index" icon="fas fa-bell">Notifications</x-mobile-link>
            </div>
        </div>
    </nav>
    @endauth

    <!-- Flash / validation -->
    @include('partials.alerts')

    <!-- Main -->
    <main class="flex-1">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 flex justify-between items-center">
            <span class="text-gray-500 text-sm">© {{ date('Y') }} {{ config('app.name') }}. Tous droits réservés.</span>
            <nav class="flex space-x-6 text-sm text-gray-500">
                <a href="#" class="hover:text-gray-700">Support</a>
                <a href="#" class="hover:text-gray-700">Documentation</a>
                <a href="#" class="hover:text-gray-700">Contact</a>
            </nav>
        </div>
    </footer>
</div>

<!-- Global helpers & notifications already in original layout -->
@stack('scripts')
@yield('scripts')
</body>
</html>
