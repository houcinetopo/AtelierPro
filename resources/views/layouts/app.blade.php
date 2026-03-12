<!DOCTYPE html>
<html lang="fr" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Atelier Pro') — {{ config('app.name') }}</title>

    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#eff6ff', 100: '#dbeafe', 200: '#bfdbfe', 300: '#93c5fd',
                            400: '#60a5fa', 500: '#3b82f6', 600: '#2563eb', 700: '#1d4ed8',
                            800: '#1e40af', 900: '#1e3a8a', 950: '#172554',
                        }
                    }
                }
            }
        }
    </script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Chart.js (pour le dashboard) -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        .sidebar-link.active { background-color: rgba(37, 99, 235, 0.1); color: #2563eb; border-right: 3px solid #2563eb; }
        .sidebar-link:hover { background-color: rgba(37, 99, 235, 0.05); }
    </style>

    @stack('styles')
</head>
<body class="bg-gray-50 font-sans antialiased" x-data="{ sidebarOpen: true, mobileSidebar: false }">

    <div class="min-h-screen flex">

        {{-- ═══════════ SIDEBAR ═══════════ --}}
        @include('components.sidebar')

        {{-- ═══════════ MAIN CONTENT ═══════════ --}}
        <div class="flex-1 flex flex-col min-h-screen transition-all duration-300"
             :class="sidebarOpen ? 'lg:ml-64' : 'lg:ml-20'">

            {{-- ─── HEADER ─── --}}
            <header class="bg-white border-b border-gray-200 sticky top-0 z-30">
                <div class="flex items-center justify-between h-16 px-4 sm:px-6">
                    <div class="flex items-center gap-3">
                        <!-- Toggle sidebar (desktop) -->
                        <button @click="sidebarOpen = !sidebarOpen"
                                class="hidden lg:flex items-center justify-center w-9 h-9 rounded-lg hover:bg-gray-100 text-gray-500">
                            <i data-lucide="panel-left" class="w-5 h-5"></i>
                        </button>
                        <!-- Toggle sidebar (mobile) -->
                        <button @click="mobileSidebar = true"
                                class="lg:hidden flex items-center justify-center w-9 h-9 rounded-lg hover:bg-gray-100 text-gray-500">
                            <i data-lucide="menu" class="w-5 h-5"></i>
                        </button>
                        <!-- Breadcrumb -->
                        <nav class="hidden sm:flex items-center text-sm text-gray-500">
                            <a href="{{ route('dashboard') }}" class="hover:text-primary-600">Accueil</a>
                            @hasSection('breadcrumb')
                                <i data-lucide="chevron-right" class="w-4 h-4 mx-1"></i>
                                @yield('breadcrumb')
                            @endif
                        </nav>
                    </div>

                    <div class="flex items-center gap-3">
                        {{-- Notifications (placeholder) --}}
                        <button class="relative w-9 h-9 flex items-center justify-center rounded-lg hover:bg-gray-100 text-gray-500">
                            <i data-lucide="bell" class="w-5 h-5"></i>
                            <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full" id="notif-badge" style="display:none;"></span>
                        </button>

                        {{-- User dropdown --}}
                        <div x-data="{ open: false }" class="relative">
                            <button @click="open = !open" class="flex items-center gap-2 px-3 py-1.5 rounded-lg hover:bg-gray-100">
                                <img src="{{ auth()->user()->avatar_url }}" alt="" class="w-8 h-8 rounded-full object-cover">
                                <div class="hidden sm:block text-left">
                                    <p class="text-sm font-medium text-gray-700">{{ auth()->user()->name }}</p>
                                    <p class="text-xs text-gray-500">{{ auth()->user()->role_label }}</p>
                                </div>
                                <i data-lucide="chevron-down" class="w-4 h-4 text-gray-400"></i>
                            </button>

                            <div x-show="open" @click.away="open = false" x-cloak
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="opacity-0 scale-95"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-50">
                                <div class="px-4 py-2 border-b border-gray-100">
                                    <p class="text-sm font-medium text-gray-700">{{ auth()->user()->name }}</p>
                                    <p class="text-xs text-gray-500">{{ auth()->user()->email }}</p>
                                </div>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 flex items-center gap-2">
                                        <i data-lucide="log-out" class="w-4 h-4"></i> Déconnexion
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            {{-- ─── PAGE CONTENT ─── --}}
            <main class="flex-1 p-4 sm:p-6">
                {{-- Toast notifications --}}
                @include('components.toast')

                @yield('content')
            </main>

            {{-- ─── FOOTER ─── --}}
            <footer class="bg-white border-t border-gray-200 px-6 py-3">
                <p class="text-xs text-center text-gray-400">
                    &copy; {{ date('Y') }} Atelier Pro — Système de gestion d'atelier de réparation automobile
                </p>
            </footer>
        </div>
    </div>

    {{-- Mobile sidebar overlay --}}
    <div x-show="mobileSidebar" @click="mobileSidebar = false" x-cloak
         class="fixed inset-0 bg-black/50 z-40 lg:hidden"></div>

    <script>
        // Initialiser les icônes Lucide
        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();
        });

        // Réinitialiser après les transitions Alpine
        document.addEventListener('alpine:initialized', () => {
            setTimeout(() => lucide.createIcons(), 100);
        });
    </script>

    @stack('scripts')
</body>
</html>
