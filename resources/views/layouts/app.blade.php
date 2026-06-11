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
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'ChatBot IA') — ChatBot IA</title>
    <meta name="description" content="Assistant IA connecté à vos applications métier via Ollama/Llama">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="h-full">

    <div class="ambient-bg">
        <div class="ambient-orb ambient-orb--tr animate-float"></div>
        <div class="ambient-orb ambient-orb--cl" style="animation: float 4s ease-in-out infinite 0.5s"></div>
        <div class="ambient-orb ambient-orb--br" style="animation: float 5s ease-in-out infinite 1s"></div>
    </div>

    <div class="app-shell">

        <aside class="sidebar">
            <div class="sidebar__brand">
                <a href="{{ route('chat.index') }}" class="sidebar__brand-link">
                    <div class="sidebar__logo">
                        <svg class="icon-md" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z" />
                        </svg>
                    </div>
                    <div>
                        <div class="sidebar__brand-title gradient-text">ChatBot Entreprise</div>
                        <div class="sidebar__brand-sub">Assistant IA métier</div>
                    </div>
                </a>
            </div>

            <nav class="sidebar__nav">
                <p class="sidebar__section-label">Menu</p>

                <a href="{{ route('chat.index') }}" class="nav-link {{ request()->routeIs('chat.*') ? 'is-active' : '' }}">
                    <svg class="nav-link__icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z" />
                    </svg>
                    Assistant IA
                </a>

                @if(auth()->user()->isAdmin())
                <p class="sidebar__section-label sidebar__section-label--spaced">Administration</p>

                <a href="{{ route('tools.index') }}" class="nav-link {{ request()->routeIs('tools.*') && !request()->routeIs('tool-relations.*') ? 'is-active' : '' }}">
                    <svg class="nav-link__icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.336l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437l1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008z" />
                    </svg>
                    Outils API
                </a>

                <a href="{{ route('tool-relations.index') }}" class="nav-link {{ request()->routeIs('tool-relations.*') ? 'is-active' : '' }}">
                    <svg class="nav-link__icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7.217 10.907a2.25 2.25 0 100 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186l9.566-5.314m-9.566 7.5l9.566 5.314m0-12.814a2.25 2.25 0 103.935 2.186 2.25 2.25 0 00-3.935-2.186zm0 12.814a2.25 2.25 0 103.933-2.185 2.25 2.25 0 00-3.933 2.185z" />
                    </svg>
                    Modélisation ERD
                </a>

                <a href="{{ route('connections.index') }}" class="nav-link {{ request()->routeIs('connections.*') ? 'is-active' : '' }}">
                    <svg class="nav-link__icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244" />
                    </svg>
                    Connexions API
                </a>

                <a href="{{ route('ai-provider.index') }}" class="nav-link {{ request()->routeIs('ai-provider.*') ? 'is-active' : '' }}">
                    <svg class="nav-link__icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z" />
                    </svg>
                    Moteur IA
                    @php $activeAi = App\Models\AiProviderSetting::activeCloud(); @endphp
                    @if($activeAi)
                    <span class="nav-link__badge">{{ ucfirst($activeAi->provider) }}</span>
                    @endif
                </a>

                <a href="{{ route('ai-rules.index') }}" class="nav-link {{ request()->routeIs('ai-rules.*') ? 'is-active' : '' }}">
                    <svg class="nav-link__icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                    </svg>
                    Directives IA
                </a>

                <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.*') ? 'is-active' : '' }}">
                    <svg class="nav-link__icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                    </svg>
                    Utilisateurs
                </a>
                @endif
            </nav>

            @hasSection('sidebar-extra')
            <div class="sidebar__extra">
                @yield('sidebar-extra')
            </div>
            @endif

            <div class="sidebar__footer">
                @php $activeAiEngine = App\Models\AiProviderSetting::activeCloud(); @endphp
                @if($activeAiEngine)
                <div class="status-card status-card--online">
                    <div class="status-row">
                        <div class="status-card__dot status-card__dot--success animate-pulse-glow"></div>
                        <span class="status-card__label text-success">{{ ucfirst($activeAiEngine->provider) }} actif</span>
                    </div>
                    <div class="status-card__meta">Modèle : {{ $activeAiEngine->model }}</div>
                </div>
                @else
                <div class="status-card" id="ollama-status-box">
                    <div class="status-row">
                        <div class="status-card__dot status-card__dot--muted animate-pulse" id="ollama-status-dot"></div>
                        <span class="status-card__label text-muted" id="ollama-status-text">Vérification Ollama…</span>
                    </div>
                    <div class="status-card__meta" id="ollama-model-text">Modèle : {{ config('ollama.model') }}</div>
                </div>
                @endif
            </div>
        </aside>

        <div class="app-main">
            <header class="topbar">
                <div>
                    <h1 class="topbar__title">@yield('page-title', 'Dashboard')</h1>
                    @hasSection('page-subtitle')
                    <p class="topbar__subtitle">@yield('page-subtitle')</p>
                    @endif
                </div>
                <div class="topbar__actions">
                    @yield('header-actions')

                    <div class="theme-toggle">
                        <button data-toggle-theme="dark" title="Mode sombre" class="theme-toggle-btn" aria-pressed="true">
                            <svg class="icon-sm" fill="currentColor" viewBox="0 0 20 20"><path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path></svg>
                        </button>
                        <button data-toggle-theme="light" title="Mode clair" class="theme-toggle-btn" aria-pressed="false">
                            <svg class="icon-sm" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4.293 1.707a1 1 0 011.414-1.414l.707.707a1 1 0 11-1.414 1.414l-.707-.707zm2 2.414a1 1 0 011.414-1.414l.707.707a1 1 0 11-1.414 1.414l-.707-.707zm2.414 2a1 1 0 011-1h1a1 1 0 110 2h-1a1 1 0 01-1-1zm1.414 4.293a1 1 0 111.414-1.414l.707.707a1 1 0 11-1.414 1.414l-.707-.707zM17 14a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zm-2.414 2a1 1 0 111.414-1.414l.707.707a1 1 0 11-1.414 1.414l-.707-.707zm2.414 2a1 1 0 011-1h1a1 1 0 110 2h-1a1 1 0 01-1-1zm-9-18a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zM9.464 4.464a1 1 0 011.414-1.414l.707.707a1 1 0 11-1.414 1.414l-.707-.707zm1.414 8.486a1 1 0 01-1.414 0l-.707-.707a1 1 0 011.414-1.414l.707.707zm-4.486 1.414a1 1 0 111.414-1.414l.707.707a1 1 0 11-1.414 1.414l-.707-.707zM3 10a1 1 0 011-1h1a1 1 0 110 2H4a1 1 0 01-1-1zm9 9a1 1 0 01-1-1v-1a1 1 0 112 0v1a1 1 0 01-1 1zM4.464 4.464a1 1 0 111.414-1.414l.707.707a1 1 0 11-1.414 1.414l-.707-.707zM2.707 9.293a1 1 0 000 1.414l.707.707a1 1 0 101.414-1.414l-.707-.707z" clip-rule="evenodd"></path></svg>
                        </button>
                    </div>

                    <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                        <button @click="open = !open" class="user-menu-btn">
                            <div class="user-avatar">{{ substr(auth()->user()->name, 0, 1) }}</div>
                            <div class="user-menu__info hidden-mobile">
                                <p class="user-menu__name">{{ auth()->user()->prenom }} {{ auth()->user()->name }}</p>
                                <p class="user-menu__meta">{{ auth()->user()->matricule }}</p>
                            </div>
                            <svg class="icon-sm text-muted hidden-mobile" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="open" x-transition class="profile-dropdown" style="display: none;">
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit">
                                    <svg class="icon-sm" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                    Se déconnecter
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            @if(session('success'))
            <div class="flash-message flash-message--success" x-data="{ show: true }" x-show="show">
                <svg class="icon-sm shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                {{ session('success') }}
                <button onclick="this.parentElement.remove()" class="flash-message__close">✕</button>
            </div>
            @endif

            @if(session('error'))
            <div class="flash-message flash-message--error">
                <svg class="icon-sm shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                {{ session('error') }}
                <button onclick="this.parentElement.remove()" class="flash-message__close">✕</button>
            </div>
            @endif

            <div class="page-content">
                @yield('content')
            </div>
        </div>
    </div>

    <script>
    (async function checkOllama() {
        const dot  = document.getElementById('ollama-status-dot');
        const text = document.getElementById('ollama-status-text');
        if (!dot || !text) return;
        try {
            const res = await fetch('{{ rtrim(config("ollama.url"), "/") }}/api/tags', { signal: AbortSignal.timeout(4000) });
            if (res.ok) {
                dot.className  = 'status-card__dot status-card__dot--success animate-pulse-glow';
                text.textContent = 'Ollama connecté';
                text.className = 'status-card__label text-success';
            } else { throw new Error(); }
        } catch {
            dot.className  = 'status-card__dot status-card__dot--danger';
            text.textContent = 'Ollama hors ligne';
            text.className = 'status-card__label text-danger';
        }
    })();
    </script>

    @stack('scripts')
</body>
</html>
