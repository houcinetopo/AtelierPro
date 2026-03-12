<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Connexion — {{ config('app.name', 'Atelier Pro') }}</title>
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
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
</head>
<body class="bg-gradient-to-br from-primary-900 via-primary-800 to-slate-900 min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-md">
        {{-- Logo & Title --}}
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-white/10 backdrop-blur-sm mb-4">
                <i data-lucide="wrench" class="w-8 h-8 text-white"></i>
            </div>
            <h1 class="text-2xl font-bold text-white">Atelier Pro</h1>
            <p class="text-primary-200 text-sm mt-1">Système de gestion d'atelier automobile</p>
        </div>

        {{-- Login Card --}}
        <div class="bg-white rounded-2xl shadow-2xl p-8">
            <h2 class="text-xl font-semibold text-gray-800 mb-1">Connexion</h2>
            <p class="text-sm text-gray-500 mb-6">Entrez vos identifiants pour accéder au système</p>

            {{-- Status message (password reset, etc.) --}}
            @if (session('status'))
                <div class="mb-4 bg-green-50 border border-green-200 rounded-lg p-3 flex items-center gap-2">
                    <i data-lucide="check-circle" class="w-4 h-4 text-green-500 flex-shrink-0"></i>
                    <p class="text-sm text-green-700">{{ session('status') }}</p>
                </div>
            @endif

            {{-- Error messages --}}
            @if ($errors->any())
                <div class="mb-4 bg-red-50 border border-red-200 rounded-lg p-3">
                    @foreach ($errors->all() as $error)
                        <p class="text-sm text-red-600 flex items-center gap-2">
                            <i data-lucide="alert-circle" class="w-4 h-4 flex-shrink-0"></i>
                            {{ $error }}
                        </p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf

                {{-- Email --}}
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">Adresse email</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i data-lucide="mail" class="w-5 h-5 text-gray-400"></i>
                        </div>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                               class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg text-sm
                                      focus:ring-2 focus:ring-primary-500 focus:border-primary-500
                                      placeholder-gray-400 transition-colors"
                               placeholder="votre@email.ma">
                    </div>
                </div>

                {{-- Password --}}
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1.5">Mot de passe</label>
                    <div class="relative" x-data="{ showPassword: false }">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i data-lucide="lock" class="w-5 h-5 text-gray-400"></i>
                        </div>
                        <input :type="showPassword ? 'text' : 'password'" id="password" name="password" required
                               class="w-full pl-10 pr-10 py-2.5 border border-gray-300 rounded-lg text-sm
                                      focus:ring-2 focus:ring-primary-500 focus:border-primary-500
                                      placeholder-gray-400 transition-colors"
                               placeholder="••••••••">
                        <button type="button" @click="showPassword = !showPassword"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                            <i :data-lucide="showPassword ? 'eye-off' : 'eye'" class="w-5 h-5"></i>
                        </button>
                    </div>
                </div>

                {{-- Remember + Forgot --}}
                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                        <span class="text-sm text-gray-600">Se souvenir de moi</span>
                    </label>
                    <a href="{{ route('password.request') }}" class="text-sm text-primary-600 hover:text-primary-700 font-medium">
                        Mot de passe oublié ?
                    </a>
                </div>

                {{-- Submit --}}
                <button type="submit"
                        class="w-full bg-primary-600 hover:bg-primary-700 text-white font-medium py-2.5 px-4 rounded-lg
                               transition-colors duration-200 flex items-center justify-center gap-2 text-sm">
                    <i data-lucide="log-in" class="w-4 h-4"></i>
                    Se connecter
                </button>
            </form>
        </div>

        {{-- Footer --}}
        <p class="text-center text-primary-300 text-xs mt-6">
            &copy; {{ date('Y') }} Atelier Pro — Tous droits réservés
        </p>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => lucide.createIcons());
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>
