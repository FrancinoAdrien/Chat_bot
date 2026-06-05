<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'ChatBot IA') — ChatBot IA</title>
    <meta name="description" content="Assistant IA connecté à vos applications métier via Ollama/Llama">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body { font-family: 'Inter', sans-serif; }

        /* Custom scrollbar */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #0f172a; }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #475569; }

        /* Glassmorphism */
        .glass {
            background: rgba(15, 23, 42, 0.7);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(99, 102, 241, 0.15);
        }

        /* Gradient text */
        .gradient-text {
            background: linear-gradient(135deg, #818cf8 0%, #c084fc 50%, #f472b6 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Animated gradient border */
        .gradient-border {
            position: relative;
        }
        .gradient-border::before {
            content: '';
            position: absolute;
            inset: -1px;
            background: linear-gradient(135deg, #6366f1, #a855f7, #ec4899);
            border-radius: inherit;
            z-index: -1;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .gradient-border:hover::before { opacity: 1; }

        /* Pulse animation */
        @keyframes pulse-glow {
            0%, 100% { box-shadow: 0 0 5px rgba(99, 102, 241, 0.3); }
            50%       { box-shadow: 0 0 20px rgba(99, 102, 241, 0.6); }
        }
        .pulse-glow { animation: pulse-glow 2s ease-in-out infinite; }

        /* Sidebar active item */
        .nav-item-active {
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.2), rgba(168, 85, 247, 0.1));
            border-left: 3px solid #818cf8;
        }

        /* AI dot animation */
        @keyframes ai-typing {
            0%, 60%, 100% { transform: translateY(0); }
            30%           { transform: translateY(-6px); }
        }
        .typing-dot:nth-child(1) { animation: ai-typing 1s ease-in-out infinite; }
        .typing-dot:nth-child(2) { animation: ai-typing 1s ease-in-out 0.2s infinite; }
        .typing-dot:nth-child(3) { animation: ai-typing 1s ease-in-out 0.4s infinite; }
    </style>
</head>
<body class="h-full bg-slate-950 text-slate-100">

    <!-- Background Ambient -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none z-0">
        <div class="absolute -top-40 -right-40 w-96 h-96 bg-indigo-500/10 rounded-full blur-3xl"></div>
        <div class="absolute top-1/2 -left-40 w-96 h-96 bg-purple-500/8 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-40 right-1/3 w-80 h-80 bg-pink-500/8 rounded-full blur-3xl"></div>
    </div>

    <div class="relative z-10 flex h-full min-h-screen">

        <!-- Sidebar -->
        <aside class="w-64 shrink-0 glass border-r border-slate-800/50 flex flex-col">

            <!-- Logo -->
            <div class="p-6 border-b border-slate-800/50">
                <a href="{{ route('chat.index') }}" class="flex items-center gap-3 group">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-lg shadow-indigo-500/30 group-hover:scale-105 transition-transform">
                        <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z" />
                        </svg>
                    </div>
                    <div>
                        <div class="font-bold text-sm gradient-text">ChatBot IA</div>
                        <div class="text-xs text-slate-500">Powered by Llama</div>
                    </div>
                </a>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 p-4 space-y-1">
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider px-3 mb-3">Navigation</p>
                <a href="{{ route('tools.index') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 hover:bg-slate-800/50 {{ request()->routeIs('tools.*') ? 'nav-item-active text-indigo-300' : 'text-slate-400 hover:text-slate-100' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.336l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437l1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008z" />
                    </svg>
                    Outils API
                </a>

                <a href="{{ route('connections.index') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 hover:bg-slate-800/50 {{ request()->routeIs('connections.*') ? 'nav-item-active text-indigo-300' : 'text-slate-400 hover:text-slate-100' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244" />
                    </svg>
                    Connexions API
                </a>

                <a href="{{ route('ai-provider.index') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 hover:bg-slate-800/50 {{ request()->routeIs('ai-provider.*') ? 'nav-item-active text-indigo-300' : 'text-slate-400 hover:text-slate-100' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z" />
                    </svg>
                    Moteur IA
                    @php $activeAi = App\Models\AiProviderSetting::activeCloud(); @endphp
                    @if($activeAi)
                    <span class="ml-auto text-[10px] px-1.5 py-0.5 rounded-full bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">{{ ucfirst($activeAi->provider) }}</span>
                    @endif
                </a>
            </nav>

            <!-- Ollama Status -->
            <div class="p-4 border-t border-slate-800/50">
                @php $activeAiEngine = App\Models\AiProviderSetting::activeCloud(); @endphp
                @if($activeAiEngine)
                <div class="glass rounded-lg p-3 border border-emerald-500/20">
                    <div class="flex items-center gap-2">
                        <div class="w-2 h-2 rounded-full bg-emerald-400 pulse-glow"></div>
                        <span class="text-xs text-emerald-400">{{ ucfirst($activeAiEngine->provider) }} actif ✅</span>
                    </div>
                    <div class="text-xs text-slate-600 mt-1">Modèle: {{ $activeAiEngine->model }}</div>
                </div>
                @else
                <div class="glass rounded-lg p-3" id="ollama-status-box">
                    <div class="flex items-center gap-2">
                        <div class="w-2 h-2 rounded-full bg-slate-600 animate-pulse" id="ollama-status-dot"></div>
                        <span class="text-xs text-slate-400" id="ollama-status-text">Vérification Ollama…</span>
                    </div>
                    <div class="text-xs text-slate-600 mt-1" id="ollama-model-text">Modèle: {{ config('ollama.model') }}</div>
                </div>
                @endif
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 flex flex-col overflow-hidden">

            <!-- Top Bar -->
            <header class="glass border-b border-slate-800/50 px-6 py-4 flex items-center justify-between shrink-0">
                <div>
                    <h1 class="font-semibold text-slate-100">@yield('page-title', 'Dashboard')</h1>
                    <p class="text-xs text-slate-500 mt-0.5">@yield('page-subtitle', '')</p>
                </div>
                <div class="flex items-center gap-3">
                    @yield('header-actions')
                </div>
            </header>

            <!-- Flash Messages -->
            @if(session('success'))
            <div class="mx-6 mt-4 px-4 py-3 rounded-lg bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-sm flex items-center gap-2" x-data="{ show: true }" x-show="show">
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                {{ session('success') }}
                <button onclick="this.parentElement.remove()" class="ml-auto text-emerald-500 hover:text-emerald-300">✕</button>
            </div>
            @endif

            @if(session('error'))
            <div class="mx-6 mt-4 px-4 py-3 rounded-lg bg-red-500/10 border border-red-500/30 text-red-300 text-sm flex items-center gap-2">
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                {{ session('error') }}
                <button onclick="this.parentElement.remove()" class="ml-auto text-red-400 hover:text-red-200">✕</button>
            </div>
            @endif

            <!-- Page Content -->
            <div class="flex-1 overflow-auto">
                @yield('content')
            </div>

        </main>
    </div>

    <!-- Ollama Status Check -->
    <script>
    (async function checkOllama() {
        const dot  = document.getElementById('ollama-status-dot');
        const text = document.getElementById('ollama-status-text');
        if (!dot || !text) return;
        try {
            const res = await fetch('{{ rtrim(config("ollama.url"), "/") }}/api/tags', { signal: AbortSignal.timeout(4000) });
            if (res.ok) {
                dot.className  = 'w-2 h-2 rounded-full bg-emerald-400 pulse-glow';
                text.textContent = 'Ollama connecté ✅';
                text.className = 'text-xs text-emerald-400';
            } else { throw new Error(); }
        } catch {
            dot.className  = 'w-2 h-2 rounded-full bg-red-400';
            text.textContent = 'Ollama hors ligne ❌';
            text.className = 'text-xs text-red-400';
        }
    })();
    </script>

    @stack('scripts')
</body>
</html>
