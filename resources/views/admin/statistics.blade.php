@extends('layouts.app')

@section('title', 'Statistiques Avancées')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-blue-50">
    <!-- Header -->
    <div class="bg-gradient-to-r from-indigo-600 to-purple-700 shadow-xl">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="md:flex md:items-center md:justify-between">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="h-16 w-16 rounded-full bg-white/20 flex items-center justify-center">
                                <svg class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                                </svg>
                            </div>
                        </div>
                        <div class="ml-6">
                            <h1 class="text-3xl font-bold text-white sm:text-4xl">
                                Statistiques Avancées
                            </h1>
                            <p class="mt-2 text-indigo-100 text-lg">
                                Analyses détaillées et indicateurs de performance
                            </p>
                        </div>
                    </div>
                </div>
                <div class="mt-6 flex space-x-3 md:mt-0 md:ml-4">
                    <button onclick="exportStatistics()" 
                            class="inline-flex items-center px-6 py-3 border border-white/20 rounded-full shadow-sm text-sm font-medium text-white hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-white transition-all duration-200">
                        <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                        </svg>
                        Exporter
                    </button>
                    <a href="{{ route('admin.index') }}" 
                       class="inline-flex items-center px-6 py-3 border border-transparent rounded-full shadow-sm text-sm font-medium text-indigo-700 bg-white hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200">
                        <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3" />
                        </svg>
                        Retour Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Métriques principales -->
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4 mb-8">
            <!-- Total Chantiers -->
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-blue-500 to-blue-600 p-6 shadow-xl">
                <div class="absolute inset-0 bg-gradient-to-br from-white/20 to-transparent"></div>
                <div class="relative">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-blue-100 text-sm font-medium uppercase tracking-wide">Total Chantiers</p>
                            <p class="text-4xl font-bold text-white mt-2 counter" data-target="{{ $stats['total_chantiers'] ?? 0 }}">0</p>
                        </div>
                        <div class="h-16 w-16 rounded-full bg-white/20 flex items-center justify-center">
                            <svg class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5M6.75 3h12M6.75 9h12M6.75 15h12" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Chantiers Actifs -->
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-green-500 to-green-600 p-6 shadow-xl">
                <div class="absolute inset-0 bg-gradient-to-br from-white/20 to-transparent"></div>
                <div class="relative">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-green-100 text-sm font-medium uppercase tracking-wide">En Cours</p>
                            <p class="text-4xl font-bold text-white mt-2 counter" data-target="{{ $stats['chantiers_actifs'] ?? 0 }}">0</p>
                        </div>
                        <div class="h-16 w-16 rounded-full bg-white/20 flex items-center justify-center">
                            <svg class="h-8 w-8 text-white animate-spin" style="animation-duration:3s" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Chantiers Terminés -->
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-500 to-emerald-600 p-6 shadow-xl">
                <div class="absolute inset-0 bg-gradient-to-br from-white/20 to-transparent"></div>
                <div class="relative">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-emerald-100 text-sm font-medium uppercase tracking-wide">Terminés</p>
                            <p class="text-4xl font-bold text-white mt-2 counter" data-target="{{ $stats['chantiers_termines'] ?? 0 }}">0</p>
                        </div>
                        <div class="h-16 w-16 rounded-full bg-white/20 flex items-center justify-center">
                            <svg class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Avancement Moyen -->
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-purple-500 to-purple-600 p-6 shadow-xl">
                <div class="absolute inset-0 bg-gradient-to-br from-white/20 to-transparent"></div>
                <div class="relative">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-purple-100 text-sm font-medium uppercase tracking-wide">Avancement Moyen</p>
                            <p class="text-4xl font-bold text-white mt-2"><span class="counter" data-target="{{ round($stats['average_progress'] ?? 0,1) }}">0</span>%</p>
                            <div class="w-full bg-white/20 rounded-full h-2 mt-3">
                                <div class="bg-white h-2 rounded-full" style="width: {{ $stats['average_progress'] ?? 0 }}%"></div>
                            </div>
                        </div>
                        <div class="h-16 w-16 rounded-full bg-white/20 flex items-center justify-center">
                            <svg class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sections supplémentaires : Graphiques, alertes, etc. -->
        @includeIf('admin.statistics.partials._charts')
        @includeIf('admin.statistics.partials._alerts')
        @includeIf('admin.statistics.partials._performance')
        @includeIf('admin.statistics.partials._actions')
    </div>
</div>
@endsection

@push('scripts')
<script>
// animation des compteurs
function animateCounters(){
    document.querySelectorAll('.counter').forEach(el=>{
        const target=parseFloat(el.dataset.target)||0;
        let current=0;
        const inc=target/50;
        const t=setInterval(()=>{
            current+=inc;
            if(current>=target){el.textContent=Math.round(target);clearInterval(t);}else{el.textContent=Math.round(current);}
        },30);
    });
}
window.addEventListener('DOMContentLoaded',()=>{setTimeout(animateCounters,500);});

function exportStatistics(){
    const data={
        date_export:new Date().toISOString(),
        chantiers_by_status:@json($stats['chantiers_by_status'] ?? []),
        users_by_role:@json($stats['users_by_role'] ?? []),
        chantiers_by_month:@json($stats['chantiers_by_month'] ?? [])
    };
    const uri='data:application/json;charset=utf-8,'+encodeURIComponent(JSON.stringify(data,null,2));
    const link=document.createElement('a');
    link.setAttribute('href',uri);
    link.setAttribute('download','statistiques_'+(new Date().toISOString().split('T')[0])+'.json');
    link.click();
}
</script>
@endpush

@push('styles')
<style>
.rounded-2xl{transition:all .3s ease-in-out;}
.rounded-2xl:hover{transform:translateY(-2px);box-shadow:0 20px 25px -5px rgba(0,0,0,.1),0 10px 10px -5px rgba(0,0,0,.04);}
</style>
@endpush
