@extends('layouts.app')

@section('title', 'ChatBot IA')
@section('page-title', 'Assistant IA')
@section('page-subtitle', 'Posez vos questions, l\'IA interroge votre API en temps réel.')

@section('header-actions')
    @if($connections->isNotEmpty())
        <div class="select-wrapper">
            <select id="connection-selector" onchange="changeConnection(this.value)" class="form-control form-control--inline">
                @foreach($connections as $conn)
                    <option value="{{ $conn->id }}" {{ $selectedConnection?->id === $conn->id ? 'selected' : '' }}>
                        {{ $conn->name }}
                        @if($conn->is_authenticated) (Auth ✅) @endif
                    </option>
                @endforeach
            </select>
            <div class="select-wrapper__chevron">
                <svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </div>
        </div>
    @endif
@endsection

@section('sidebar-extra')
<div class="chat-history">
    <div class="chat-history__header">
        <p class="sidebar__section-label" style="margin:0">Historique</p>
        <a href="{{ route('chat.index') }}" id="new-chat-btn" class="chat-history__new">
            <svg class="icon-xs" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Nouveau
        </a>
    </div>

    <div class="overflow-y-auto chat-history__list" id="chat-history-list">
        @forelse($sessions as $session)
            <div class="chat-history__item {{ $currentSession?->id === $session->id ? 'is-active' : '' }}" data-session-item>
                <a href="{{ route('chat.index', ['session' => $session->id]) }}" class="chat-history__link">
                    <p class="chat-history__title">{{ $session->title }}</p>
                    <p class="chat-history__date">{{ $session->last_message_at?->diffForHumans() ?? $session->created_at->diffForHumans() }}</p>
                </a>
                <button onclick="deleteSession({{ $session->id }}, this)" class="chat-history__delete" title="Supprimer">
                    <svg class="icon-xs" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        @empty
            <p class="text-xs text-muted italic px-3 py-2">Aucune conversation.</p>
        @endforelse
    </div>
</div>
@endsection

@section('content')
<div class="chat-page">

    @if($connections->isNotEmpty())
    <div class="panel chat-window">

        <div id="chat-messages" class="chat-messages">
            @if($conversations->isEmpty())
            <div class="chat-welcome" id="chat-welcome">
                <div class="chat-welcome__icon">
                    <svg class="icon-lg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z" />
                    </svg>
                </div>
                <h3 class="chat-welcome__title">Comment puis-je vous aider ?</h3>
                <p class="chat-welcome__text">Posez une question sur vos données. L'IA interrogera automatiquement votre système.</p>
            </div>
            @else
            @foreach($conversations as $conv)
                <div class="chat-msg chat-msg--user">
                    <div class="chat-msg__body">
                        <div class="chat-bubble chat-bubble--user">{{ $conv->user_message }}</div>
                        <div class="chat-msg__meta">{{ $conv->created_at->format('H:i') }}</div>
                    </div>
                    <div class="avatar avatar--sm">{{ substr(auth()->user()->name, 0, 1) }}</div>
                </div>

                <div class="chat-msg chat-msg--ai">
                    <div class="chat-avatar-ai">
                        <svg class="icon-sm" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                        </svg>
                    </div>
                    <div class="chat-msg__body">
                        <div class="chat-bubble chat-bubble--ai">{!! nl2br(e($conv->ai_response)) !!}</div>
                        <div class="chat-msg__meta">
                            <span>{{ $conv->created_at->format('H:i') }}</span>
                            @if($conv->tool_used)
                            <span class="badge badge--purple">🔧 {{ $conv->tool_used }}</span>
                            @endif
                            @if($conv->response_time_ms)
                            <span>{{ $conv->response_time_ms }}ms</span>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
            @endif

            <div id="typing-indicator" class="chat-msg chat-msg--ai hidden">
                <div class="chat-avatar-ai">
                    <svg class="icon-sm" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                    </svg>
                </div>
                <div class="chat-bubble chat-bubble--ai chat-typing">
                    <div class="chat-typing__dot"></div>
                    <div class="chat-typing__dot"></div>
                    <div class="chat-typing__dot"></div>
                </div>
            </div>
        </div>

        <div class="chat-input-area">
            <form id="chat-form" class="chat-form">
                @csrf
                <input type="hidden" id="connection-id" value="{{ $selectedConnection?->id }}">
                <input type="hidden" id="session-id" value="{{ $currentSession?->id }}">

                <div class="chat-form__field">
                    <textarea id="chat-input" rows="1" placeholder="Posez une question à l'IA…" class="chat-textarea"></textarea>
                </div>

                <button type="submit" id="send-btn" class="btn btn-primary chat-send-btn">
                    <svg id="send-icon" class="icon-sm" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                    </svg>
                    <span id="send-text">Envoyer</span>
                </button>
            </form>
            <p class="chat-hint">Shift+Entrée pour nouvelle ligne · Entrée pour envoyer</p>
        </div>
    </div>

    @else
    <div class="chat-no-connection">
        <div class="empty-state">
            <div class="empty-state__icon">
                <svg class="icon-lg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244" />
                </svg>
            </div>
            <p class="empty-state__text">Sélectionnez ou créez une connexion pour démarrer</p>
            @if(auth()->user()->isAdmin())
            <a href="{{ route('connections.index') }}" class="link-action">+ Ajouter une connexion API</a>
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

if (chatMessages) scrollToBottom();

chatInput?.addEventListener('input', function () {
    this.style.height = 'auto';
    this.style.height = Math.min(this.scrollHeight, 160) + 'px';
});

chatInput?.addEventListener('keydown', function (e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        chatForm?.dispatchEvent(new Event('submit'));
    }
});

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

            if (json.session_id && !sessionIdEl.value) {
                sessionIdEl.value = json.session_id;
                const url = new URL(window.location);
                url.searchParams.set('session', json.session_id);
                window.history.replaceState({}, '', url);
                addSessionToSidebar(json.session_id, message);
            }
        }
    } catch (err) {
        appendAiMessage('Impossible de contacter le serveur. Vérifiez votre connexion.', null, null);
    } finally {
        setLoading(false);
    }
});

function setLoading(isLoading) {
    if (!chatInput || !sendBtn) return;
    chatInput.disabled = isLoading;
    sendBtn.disabled = isLoading;
    sendText.textContent = isLoading ? 'Réflexion...' : 'Envoyer';
    sendIcon.classList.toggle('hidden', isLoading);
    typingIndicator.classList.toggle('hidden', !isLoading);
    scrollToBottom();
}

function scrollToBottom() {
    if (chatMessages) chatMessages.scrollTop = chatMessages.scrollHeight;
}

function appendUserMessage(text) {
    const time = new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
    const html = `
    <div class="chat-msg chat-msg--user">
        <div class="chat-msg__body">
            <div class="chat-bubble chat-bubble--user">${escapeHtml(text)}</div>
            <div class="chat-msg__meta">${time}</div>
        </div>
        <div class="avatar avatar--sm">${userInitial}</div>
    </div>`;
    typingIndicator.insertAdjacentHTML('beforebegin', html);
    document.getElementById('chat-welcome')?.remove();
    scrollToBottom();
}

function appendAiMessage(text, tool, timeMs) {
    const time = new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
    let metaHtml = `<span>${time}</span>`;
    if (tool) metaHtml += `<span class="badge badge--purple">🔧 ${tool}</span>`;
    if (timeMs) metaHtml += `<span>${timeMs}ms</span>`;

    const html = `
    <div class="chat-msg chat-msg--ai">
        <div class="chat-avatar-ai">
            <svg class="icon-sm" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
            </svg>
        </div>
        <div class="chat-msg__body">
            <div class="chat-bubble chat-bubble--ai">${escapeHtml(text).replace(/\n/g, '<br>')}</div>
            <div class="chat-msg__meta">${metaHtml}</div>
        </div>
    </div>`;
    typingIndicator.insertAdjacentHTML('beforebegin', html);
    scrollToBottom();
}

function addSessionToSidebar(sessionId, title) {
    const container = document.getElementById('chat-history-list');
    if (!container) return;
    const shortTitle = title.length > 47 ? title.substring(0, 47) + '...' : title;
    const html = `
    <div class="chat-history__item is-active" data-session-item>
        <a href="?session=${sessionId}" class="chat-history__link">
            <p class="chat-history__title">${escapeHtml(shortTitle)}</p>
            <p class="chat-history__date">À l'instant</p>
        </a>
        <button onclick="deleteSession(${sessionId}, this)" class="chat-history__delete" title="Supprimer">
            <svg class="icon-xs" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
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
            btn.closest('[data-session-item]')?.remove();
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
