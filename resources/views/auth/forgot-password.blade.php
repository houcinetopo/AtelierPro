<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mot de passe oublié — {{ config('app.name', 'Atelier Pro') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { theme: { extend: { colors: { primary: { 600: '#2563eb', 700: '#1d4ed8', 800: '#1e40af', 900: '#1e3a8a' } } } } }
    </script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
</head>
<body class="bg-gradient-to-br from-primary-900 via-primary-800 to-slate-900 min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-white/10 backdrop-blur-sm mb-4">
                <i data-lucide="key-round" class="w-8 h-8 text-white"></i>
            </div>
            <h1 class="text-2xl font-bold text-white">Mot de passe oublié</h1>
            <p class="text-blue-200 text-sm mt-1">Entrez votre email pour recevoir un lien de réinitialisation</p>
        </div>

        <div class="bg-white rounded-2xl shadow-2xl p-8">
            @if (session('status'))
                <div class="mb-4 bg-green-50 border border-green-200 rounded-lg p-3 flex items-center gap-2">
                    <i data-lucide="check-circle" class="w-4 h-4 text-green-500 flex-shrink-0"></i>
                    <p class="text-sm text-green-700">{{ session('status') }}</p>
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 bg-red-50 border border-red-200 rounded-lg p-3">
                    @foreach ($errors->all() as $error)
                        <p class="text-sm text-red-600">{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">Adresse email</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i data-lucide="mail" class="w-5 h-5 text-gray-400"></i>
                        </div>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                               class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg text-sm
                                      focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder-gray-400"
                               placeholder="votre@email.ma">
                    </div>
                </div>

                <button type="submit"
                        class="w-full bg-primary-600 hover:bg-primary-700 text-white font-medium py-2.5 px-4 rounded-lg
                               transition-colors text-sm flex items-center justify-center gap-2">
                    <i data-lucide="send" class="w-4 h-4"></i>
                    Envoyer le lien
                </button>
            </form>

            <div class="mt-4 text-center">
                <a href="{{ route('login') }}" class="text-sm text-primary-600 hover:text-primary-700 font-medium inline-flex items-center gap-1">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    Retour à la connexion
                </a>
            </div>
        </div>
    </div>

    <script>document.addEventListener('DOMContentLoaded', () => lucide.createIcons());</script>
</body>
</html>
