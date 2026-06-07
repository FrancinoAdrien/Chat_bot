@extends('layouts.app')

@section('title', 'ChatBot IA')
@section('page-title', 'Assistant IA')
@section('page-subtitle', 'Posez vos questions, l\'IA interroge votre API en temps réel.')

@section('header-actions')
    @if($connections->isNotEmpty())
        <div class="relative">
            <select id="connection-selector" onchange="changeConnection(this.value)" class="appearance-none bg-slate-900/50 border border-slate-700/50 text-slate-200 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-4 pr-10 py-2.5 transition-all hover:bg-slate-800/80 cursor-pointer">
                @foreach($connections as $conn)
                    <option value="{{ $conn->id }}" {{ $selectedConnection?->id === $conn->id ? 'selected' : '' }}>
                        {{ $conn->name }}
                        @if($conn->is_authenticated) (Auth ✅) @endif
                    </option>
                @endforeach
            </select>
            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </div>
        </div>
    @endif
@endsection

@section('sidebar-extra')
<!-- History Panel (injected into sidebar via section) -->
<div class="border-t border-slate-800/50 mt-2 pt-2">
    <div class="px-4 mb-2 flex items-center justify-between">
        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Historique</p>
        <a href="{{ route('chat.index') }}" id="new-chat-btn"
           class="flex items-center gap-1 text-xs text-indigo-400 hover:text-indigo-300 transition-colors px-2 py-1 rounded-lg hover:bg-indigo-500/10">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Nouveau
        </a>
    </div>

    <div class="px-2 space-y-0.5 overflow-y-auto" style="max-height: 300px;">
        @forelse($sessions as $session)
            <div class="group flex items-center gap-1 rounded-lg hover:bg-slate-800/50 transition-colors {{ $currentSession?->id === $session->id ? 'bg-indigo-500/10 border border-indigo-500/20' : '' }}">
                <a href="{{ route('chat.index', ['session' => $session->id]) }}"
                   class="flex-1 min-w-0 px-2 py-2">
                    <p class="text-xs text-slate-300 truncate {{ $currentSession?->id === $session->id ? 'text-indigo-300' : '' }}">
                        {{ $session->title }}
                    </p>
                    <p class="text-[10px] text-slate-600 mt-0.5">
                        {{ $session->last_message_at?->diffForHumans() ?? $session->created_at->diffForHumans() }}
                    </p>
                </a>
                <button onclick="deleteSession({{ $session->id }}, this)"
                    class="opacity-0 group-hover:opacity-100 mr-1.5 p-1 rounded text-slate-600 hover:text-red-400 hover:bg-red-500/10 transition-all">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        @empty
            <p class="text-xs text-slate-600 px-3 py-2 italic">Aucune conversation.</p>
        @endforelse
    </div>
</div>
@endsection

@section('content')
<div class="flex flex-col h-[calc(100vh-140px)]">

    @if($connections->isNotEmpty())
    <div class="flex-1 flex flex-col glass rounded-2xl border border-slate-800/50 overflow-hidden relative">

        {{-- Chat Messages Area --}}
        <div id="chat-messages" class="flex-1 overflow-y-auto p-6 space-y-6 scroll-smooth">
            @if($conversations->isEmpty())
            <div class="h-full flex flex-col items-center justify-center text-center space-y-4">
                <div class="w-16 h-16 rounded-2xl bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center">
                    <svg class="w-8 h-8 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.428-1.428L13.5 18.75l1.183-.394a2.25 2.25 0 001.428-1.428l.394-1.183.394 1.183a2.25 2.25 0 001.428 1.428l1.183.394-1.183.394a2.25 2.25 0 00-1.428 1.428z" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-slate-200">Comment puis-je vous aider ?</h3>
                <p class="text-slate-500 text-sm max-w-sm">Posez une question sur vos données. L'IA interrogera automatiquement votre système.</p>
                <div class="flex flex-wrap justify-center gap-2 pt-4">
                    <button onclick="sendSuggestion('Combien de ventes aujourd\'hui ?')"
                        class="px-4 py-2 rounded-full text-xs bg-slate-800 border border-slate-700 text-slate-300 hover:border-indigo-500/50 hover:text-indigo-300 transition-all cursor-pointer">
                        🛒 Ventes du jour
                    </button>
                    <button onclick="sendSuggestion('Montre les ventes du mois')"
                        class="px-4 py-2 rounded-full text-xs bg-slate-800 border border-slate-700 text-slate-300 hover:border-indigo-500/50 hover:text-indigo-300 transition-all cursor-pointer">
                        📈 Ventes du mois
                    </button>
                    <button onclick="sendSuggestion('Quels sont les meilleurs produits ?')"
                        class="px-4 py-2 rounded-full text-xs bg-slate-800 border border-slate-700 text-slate-300 hover:border-indigo-500/50 hover:text-indigo-300 transition-all cursor-pointer">
                        🏆 Top produits
                    </button>
                    <button onclick="sendSuggestion('Quels produits sont en rupture de stock ?')"
                        class="px-4 py-2 rounded-full text-xs bg-slate-800 border border-slate-700 text-slate-300 hover:border-indigo-500/50 hover:text-indigo-300 transition-all cursor-pointer">
                        ⚠️ Stock faible
                    </button>
                </div>
            </div>
            @else
            @foreach($conversations as $conv)
                {{-- User message --}}
                <div class="flex justify-end gap-3">
                    <div class="max-w-[70%]">
                        <div class="bg-gradient-to-br from-indigo-600 to-purple-700 rounded-2xl rounded-tr-sm px-4 py-3 text-sm text-white shadow-lg shadow-indigo-500/20">
                            {{ $conv->user_message }}
                        </div>
                        <div class="text-xs text-slate-600 text-right mt-1">
                            {{ $conv->created_at->format('H:i') }}
                        </div>
                    </div>
                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-xs font-bold text-white shrink-0 mt-1">
                        {{ substr(auth()->user()->name, 0, 1) }}
                    </div>
                </div>

                {{-- AI response --}}
                <div class="flex gap-3">
                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-slate-700 to-slate-800 border border-indigo-500/30 flex items-center justify-center shrink-0 mt-1">
                        <svg class="w-4 h-4 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                        </svg>
                    </div>
                    <div class="max-w-[80%]">
                        <div class="glass rounded-2xl rounded-tl-sm px-4 py-3 text-sm text-slate-200 leading-relaxed">
                            {!! nl2br(e($conv->ai_response)) !!}
                        </div>
                        <div class="flex items-center gap-3 mt-1">
                            <span class="text-xs text-slate-600">{{ $conv->created_at->format('H:i') }}</span>
                            @if($conv->tool_used)
                            <span class="text-xs px-2 py-0.5 rounded-full bg-purple-500/10 border border-purple-500/30 text-purple-400">
                                🔧 {{ $conv->tool_used }}
                            </span>
                            @endif
                            @if($conv->response_time_ms)
                            <span class="text-xs text-slate-600">{{ $conv->response_time_ms }}ms</span>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
            @endif

            {{-- AI Typing Indicator (hidden by default) --}}
            <div id="typing-indicator" class="flex gap-3 hidden">
                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-slate-700 to-slate-800 border border-indigo-500/30 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                    </svg>
                </div>
                <div class="glass rounded-2xl rounded-tl-sm px-4 py-4 flex items-center gap-1.5">
                    <div class="w-2 h-2 bg-indigo-400 rounded-full animate-bounce"></div>
                    <div class="w-2 h-2 bg-indigo-400 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                    <div class="w-2 h-2 bg-indigo-400 rounded-full animate-bounce" style="animation-delay: 0.4s"></div>
                </div>
            </div>
        </div>

        {{-- Input Area --}}
        <div class="border-t border-slate-800/50 p-4">
            <form id="chat-form" class="flex gap-3">
                @csrf
                <input type="hidden" id="connection-id" value="{{ $selectedConnection?->id }}">
                <input type="hidden" id="session-id" value="{{ $currentSession?->id }}">

                <div class="flex-1 relative">
                    <textarea
                        id="chat-input"
                        rows="1"
                        placeholder="Posez une question à l'IA… (ex: Combien de ventes aujourd'hui ?)"
                        class="w-full glass rounded-xl px-4 py-3 pr-12 text-sm text-slate-100 placeholder-slate-500 resize-none focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500/50 transition-all"
                        style="min-height: 48px; max-height: 160px;"
                    ></textarea>
                </div>

                <button type="submit" id="send-btn" class="h-12 px-6 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-700 text-white font-medium hover:from-indigo-500 hover:to-purple-600 transition-all shadow-lg shadow-indigo-500/30 flex items-center justify-center shrink-0">
                    <svg id="send-icon" class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                    </svg>
                    <span id="send-text">Envoyer</span>
                </button>
            </form>
            <p class="text-xs text-slate-600 mt-2 text-center">
                Shift+Entrée pour nouvelle ligne · Entrée pour envoyer
            </p>
        </div>
    </div>

    @else
    {{-- No connection --}}
    <div class="flex-1 flex items-center justify-center">
        <div class="text-center">
            <div class="w-16 h-16 rounded-2xl bg-slate-800 border border-slate-700 flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244" />
                </svg>
            </div>
            <p class="text-slate-400 text-sm">Sélectionnez ou créez une connexion pour démarrer</p>
            @if(auth()->user()->isAdmin())
            <a href="{{ route('connections.index') }}" class="text-indigo-400 text-xs mt-2 inline-block hover:text-indigo-300">
                + Ajouter une connexion API
            </a>
            @endif
        </div>
    </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
const chatMessages    = document.getElementById('chat-messages');
const chatForm        = document.getElementById('chat-form');
const chatInput       = document.getElementById('chat-input');
const sendBtn         = document.getElementById('send-btn');
const sendText        = document.getElementById('send-text');
const sendIcon        = document.getElementById('send-icon');
const typingIndicator = document.getElementById('typing-indicator');
const connectionIdEl  = document.getElementById('connection-id');
const sessionIdEl     = document.getElementById('session-id');
const userInitial     = '{{ substr(auth()->user()->name, 0, 1) }}';

function changeConnection(id) {
    window.location.href = `?connection_id=${id}`;
}

scrollToBottom();

// Auto-resize textarea
chatInput?.addEventListener('input', function () {
    this.style.height = 'auto';
    this.style.height = Math.min(this.scrollHeight, 160) + 'px';
});

// Enter to send, Shift+Enter for newline
chatInput?.addEventListener('keydown', function (e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        chatForm.dispatchEvent(new Event('submit'));
    }
});

// Form submit
chatForm?.addEventListener('submit', async function (e) {
    e.preventDefault();
    const message      = chatInput.value.trim();
    const connectionId = connectionIdEl?.value;
    if (!message || !connectionId) return;

    appendUserMessage(message);
    chatInput.value = '';
    chatInput.style.height = 'auto';
    setLoading(true);

    try {
        const res = await fetch('/chat/send', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                connection_id: connectionId,
                message,
                session_id: sessionIdEl?.value || null,
            }),
        });

        const json = await res.json();

        if (!res.ok) {
            appendAiMessage(json.message || json.error || 'Une erreur est survenue.', null, null);
        } else {
            const d = json.data;
            appendAiMessage(d.ai_response, d.tool_used, d.response_time_ms);

            // If new session was created, update URL & hidden field silently
            if (json.session_id && !sessionIdEl.value) {
                sessionIdEl.value = json.session_id;
                const url = new URL(window.location);
                url.searchParams.set('session', json.session_id);
                window.history.replaceState({}, '', url);

                // Add to sidebar dynamically
                addSessionToSidebar(json.session_id, message);
            }
        }
    } catch (err) {
        appendAiMessage('❌ Impossible de contacter le serveur. Vérifiez votre connexion.', null, null);
    } finally {
        setLoading(false);
    }
});

function sendSuggestion(text) {
    if (!chatInput) return;
    chatInput.value = text;
    chatForm.dispatchEvent(new Event('submit'));
}

function setLoading(isLoading) {
    chatInput.disabled = isLoading;
    sendBtn.disabled = isLoading;

    if (isLoading) {
        sendText.textContent = 'Réflexion...';
        sendIcon.classList.add('hidden');
        typingIndicator.classList.remove('hidden');
    } else {
        sendText.textContent = 'Envoyer';
        sendIcon.classList.remove('hidden');
        typingIndicator.classList.add('hidden');
        chatInput.focus();
    }
    scrollToBottom();
}

function scrollToBottom() {
    if (chatMessages) chatMessages.scrollTop = chatMessages.scrollHeight;
}

function appendUserMessage(text) {
    const time = new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
    const html = `
    <div class="flex justify-end gap-3">
        <div class="max-w-[70%]">
            <div class="bg-gradient-to-br from-indigo-600 to-purple-700 rounded-2xl rounded-tr-sm px-4 py-3 text-sm text-white shadow-lg shadow-indigo-500/20">
                ${escapeHtml(text)}
            </div>
            <div class="text-xs text-slate-600 text-right mt-1">${time}</div>
        </div>
        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-xs font-bold text-white shrink-0 mt-1">${userInitial}</div>
    </div>`;
    typingIndicator.insertAdjacentHTML('beforebegin', html);

    // Hide the welcome screen
    const welcome = chatMessages.querySelector('.h-full.flex.flex-col.items-center');
    if (welcome) welcome.remove();

    scrollToBottom();
}

function appendAiMessage(text, tool, timeMs) {
    const time = new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
    let metaHtml = `<span class="text-xs text-slate-600">${time}</span>`;
    if (tool) metaHtml += `<span class="text-xs px-2 py-0.5 rounded-full bg-purple-500/10 border border-purple-500/30 text-purple-400">🔧 ${tool}</span>`;
    if (timeMs) metaHtml += `<span class="text-xs text-slate-600">${timeMs}ms</span>`;

    const html = `
    <div class="flex gap-3">
        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-slate-700 to-slate-800 border border-indigo-500/30 flex items-center justify-center shrink-0 mt-1">
            <svg class="w-4 h-4 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
            </svg>
        </div>
        <div class="max-w-[80%]">
            <div class="glass rounded-2xl rounded-tl-sm px-4 py-3 text-sm text-slate-200 leading-relaxed">
                ${escapeHtml(text).replace(/\n/g, '<br>')}
            </div>
            <div class="flex items-center gap-3 mt-1">${metaHtml}</div>
        </div>
    </div>`;
    typingIndicator.insertAdjacentHTML('beforebegin', html);
    scrollToBottom();
}

function addSessionToSidebar(sessionId, title) {
    const container = document.querySelector('.px-2.space-y-0\\.5');
    if (!container) return;
    const shortTitle = title.length > 47 ? title.substring(0, 47) + '...' : title;
    const html = `
    <div class="group flex items-center gap-1 rounded-lg hover:bg-slate-800/50 transition-colors bg-indigo-500/10 border border-indigo-500/20">
        <a href="?session=${sessionId}" class="flex-1 min-w-0 px-2 py-2">
            <p class="text-xs text-indigo-300 truncate">${escapeHtml(shortTitle)}</p>
            <p class="text-[10px] text-slate-600 mt-0.5">À l'instant</p>
        </a>
        <button onclick="deleteSession(${sessionId}, this)"
            class="opacity-0 group-hover:opacity-100 mr-1.5 p-1 rounded text-slate-600 hover:text-red-400 hover:bg-red-500/10 transition-all">
            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>`;
    container.insertAdjacentHTML('afterbegin', html);
}

async function deleteSession(sessionId, btn) {
    if (!confirm('Supprimer cette conversation ?')) return;
    try {
        const res = await fetch(`/chat/sessions/${sessionId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                'Accept': 'application/json',
            },
        });
        if (res.ok) {
            btn.closest('.group').remove();
            // If deleting current session, go to new chat
            if (sessionIdEl.value == sessionId) {
                window.location.href = '{{ route('chat.index') }}';
            }
        }
    } catch (e) {
        alert('Erreur lors de la suppression.');
    }
}

function escapeHtml(unsafe) {
    return unsafe
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}
</script>
@endpush
