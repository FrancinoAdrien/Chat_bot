<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <script>
    (function () {
        var key = 'app-theme-preference';
        var saved = localStorage.getItem(key);
        var theme = saved || (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
        document.documentElement.setAttribute('data-theme', theme);
    })();
    </script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion — ChatBot IA</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full">

    <div class="ambient-bg">
        <div class="ambient-orb ambient-orb--tr" style="background: color-mix(in srgb, var(--color-accent-primary) 18%, transparent);"></div>
        <div class="ambient-orb ambient-orb--cl" style="background: color-mix(in srgb, var(--palette-purple-500) 15%, transparent);"></div>
        <div class="ambient-orb ambient-orb--br" style="background: color-mix(in srgb, var(--color-accent-secondary) 12%, transparent);"></div>
    </div>

    <div class="auth-page">
        <div class="auth-card">
            <div class="auth-card__header">
                <div class="auth-card__logo">
                    <svg class="icon-lg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z" />
                    </svg>
                </div>
                <h1 class="auth-card__title gradient-text">ChatBot IA</h1>
                <p class="auth-card__subtitle">Connectez-vous à votre espace métier</p>
            </div>

            <div class="panel auth-form">
                @if ($errors->any())
                    <div class="flash-message flash-message--error" style="margin: 0 0 1.5rem;">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    <div class="form-group">
                        <label class="form-label" for="matricule">Matricule</label>
                        <input type="text" id="matricule" name="matricule" required autofocus value="{{ old('matricule') }}"
                            class="form-control" placeholder="Ex: ADMIN001">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="password">Mot de passe</label>
                        <input type="password" id="password" name="password" required
                            class="form-control" placeholder="••••••••">
                    </div>

                    <div class="flex items-center gap-2 mb-4">
                        <input id="remember" type="checkbox" name="remember" style="width:auto;">
                        <label for="remember" class="text-sm text-muted" style="cursor:pointer;">Se souvenir de moi</label>
                    </div>

                    <button type="submit" class="btn btn-primary hover-lift">Se connecter</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
