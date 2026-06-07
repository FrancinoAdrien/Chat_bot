<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion — ChatBot IA</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Inter', sans-serif; }
        .glass {
            background: rgba(15, 23, 42, 0.7);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(99, 102, 241, 0.15);
        }
        .gradient-text {
            background: linear-gradient(135deg, #818cf8 0%, #c084fc 50%, #f472b6 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
    </style>
</head>
<body class="h-full bg-slate-950 text-slate-100 flex items-center justify-center relative">

    <!-- Background Ambient -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none z-0">
        <div class="absolute -top-40 -right-40 w-96 h-96 bg-indigo-500/10 rounded-full blur-3xl"></div>
        <div class="absolute top-1/2 -left-40 w-96 h-96 bg-purple-500/8 rounded-full blur-3xl"></div>
    </div>

    <div class="z-10 w-full max-w-md p-6">
        <div class="text-center mb-8">
            <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-lg shadow-indigo-500/30 mx-auto mb-4">
                <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z" />
                </svg>
            </div>
            <h1 class="text-2xl font-bold gradient-text">ChatBot IA</h1>
            <p class="text-slate-500 text-sm mt-2">Connectez-vous à votre espace métier</p>
        </div>

        <div class="glass rounded-2xl p-8 border border-slate-800/50 shadow-2xl">
            @if ($errors->any())
                <div class="mb-6 p-4 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 text-sm">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-6">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Matricule</label>
                    <input type="text" name="matricule" required autofocus value="{{ old('matricule') }}"
                        class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-sm text-slate-100 placeholder-slate-600 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500/50 transition-all"
                        placeholder="Ex: ADMIN001">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Mot de passe</label>
                    <input type="password" name="password" required
                        class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-sm text-slate-100 placeholder-slate-600 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500/50 transition-all"
                        placeholder="••••••••">
                </div>

                <div class="flex items-center">
                    <input id="remember" type="checkbox" name="remember" class="w-4 h-4 rounded border-slate-700 bg-slate-900/50 text-indigo-500 focus:ring-indigo-500/50">
                    <label for="remember" class="ml-2 block text-sm text-slate-400">
                        Se souvenir de moi
                    </label>
                </div>

                <button type="submit" class="w-full py-3 px-4 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-700 text-white font-medium hover:from-indigo-500 hover:to-purple-600 transition-all shadow-lg shadow-indigo-500/20">
                    Se connecter
                </button>
            </form>
        </div>
    </div>
</body>
</html>
