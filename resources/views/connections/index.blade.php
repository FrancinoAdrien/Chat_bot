@extends('layouts.app')

@section('title', 'Connexions API')
@section('page-title', 'Connexions API')
@section('page-subtitle', 'Gérez les APIs externes auxquelles le chatbot a accès')

@section('header-actions')
<button onclick="openAddModal()" class="btn btn-primary btn-sm hover-lift">
    <svg class="icon-sm" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
    </svg>
    Nouvelle Connexion
</button>
@endsection

@section('content')
<div class="page">

    <form method="GET" class="filter-bar panel panel__body">
        <div>
            <label class="form-label">Recherche de connexion</label>
            <input type="search" name="search" value="{{ $search ?? '' }}" placeholder="Nom de la connexion..." class="form-control form-control--search" />
        </div>
        <p class="filter-bar__hint">Filtrer par nom pour retrouver rapidement une connexion API.</p>
    </form>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-card__value text-accent">{{ $connections->count() }}</div>
            <div class="stat-card__label">Connexions totales</div>
        </div>
        <div class="stat-card">
            <div class="stat-card__value text-success">{{ $connections->where('is_authenticated', true)->count() }}</div>
            <div class="stat-card__label">Authentifiées</div>
        </div>
        <div class="stat-card">
            <div class="stat-card__value text-danger">{{ $connections->where('is_authenticated', false)->count() }}</div>
            <div class="stat-card__label">Non authentifiées</div>
        </div>
    </div>

    <div class="panel data-table-wrap">

        @if($connections->isEmpty())
        <div class="empty-state">
            <div class="empty-state__icon">
                <svg class="icon-lg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244" />
                </svg>
            </div>
            <p class="empty-state__text">Aucune connexion API configurée.</p>
            <button onclick="openAddModal()" class="link-action">+ Ajouter la première connexion</button>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Connexion</th>
                        <th>URL de base</th>
                        <th>Authentification</th>
                        <th>Token</th>
                        <th class="data-table__actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($connections as $conn)
                    <tr id="row-{{ $conn->id }}">
                        <td>
                            <div class="flex items-center gap-3">
                                <div class="avatar-chip">{{ strtoupper(substr($conn->name, 0, 2)) }}</div>
                                <div>
                                    <div class="data-table__primary">{{ $conn->name }}</div>
                                    @if($conn->description)
                                    <div class="data-table__secondary">{{ Str::limit($conn->description, 40) }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>

                        <td>
                            <div class="font-mono text-xs text-secondary">{{ $conn->base_url }}</div>
                            @if($conn->login_url)
                            <div class="data-table__secondary">Login: {{ $conn->login_url }}</div>
                            @endif
                        </td>

                        <td>
                            @if($conn->is_authenticated)
                            <div class="flex flex-col gap-1">
                                <span class="badge badge--success"><span class="badge__dot"></span> Authentifié</span>
                                @if($conn->authenticated_at)
                                <span class="data-table__secondary">{{ $conn->authenticated_at->diffForHumans() }}</span>
                                @endif
                            </div>
                            @else
                            <span class="badge badge--danger"><span class="badge__dot"></span> Non authentifié</span>
                            @endif
                        </td>

                        <td>
                            <span class="font-mono text-xs text-muted" id="token-display-{{ $conn->id }}">
                                {{ $conn->masked_token ?? '—' }}
                            </span>
                        </td>

                        {{-- Actions --}}
                        <td class="data-table__actions">
                            <div class="btn-action-group">
                                <button onclick="pingConnection({{ $conn->id }}, this)"
                                    title="Tester la connexion"
                                    class="btn-action btn-action--success">
                                    <svg class="icon-sm" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.288 15.038a5.25 5.25 0 017.424 0M5.106 11.856c3.807-3.808 9.98-3.808 13.788 0M1.924 8.674c5.565-5.565 14.587-5.565 20.152 0M12.53 18.22l-.53.53-.53-.53a.75.75 0 011.06 0z" />
                                    </svg>
                                </button>

                                {{-- Auth button --}}
                                @if(!$conn->is_authenticated)
                                <button onclick="openAuthModal({{ $conn->id }}, '{{ addslashes($conn->name) }}', {{ $conn->hasLoginUrl() ? 'true' : 'false' }})"
                                    class="btn-tag btn-tag--accent">
                                    🔑 S'authentifier
                                </button>
                                @else
                                <button onclick="disconnectConnection({{ $conn->id }}, '{{ addslashes($conn->name) }}')"
                                    class="btn-tag btn-tag--danger">
                                    🔌 Déconnecter
                                </button>
                                @endif

                                <button onclick="openEditModal({{ $conn->id }}, '{{ addslashes($conn->name) }}', '{{ addslashes($conn->base_url) }}', '{{ addslashes($conn->login_url ?? '') }}', '{{ addslashes($conn->description ?? '') }}')"
                                    class="btn-action">
                                    <svg class="icon-sm" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                                    </svg>
                                </button>

                                {{-- Delete --}}
                                <form action="{{ route('connections.destroy', $conn) }}" method="POST" class="inline-form" onsubmit="return confirm('Supprimer cette connexion ?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-action btn-action--danger">
                                        <svg class="icon-sm" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>

<div id="modal-add" class="modal-overlay hidden">
    <div class="modal">
        <div class="modal__body">
            <h2 class="modal__title">Nouvelle Connexion API</h2>
            <form action="{{ route('connections.store') }}" method="POST">
                @csrf
                <div class="form-group"><label class="form-label">Nom</label><input type="text" name="name" required placeholder="Caisse Principale" class="form-control form-control--sm"></div>
                <div class="form-group"><label class="form-label">URL de base</label><input type="url" name="base_url" required placeholder="http://localhost:8000" class="form-control form-control--sm form-control--mono"></div>
                <div class="form-group"><label class="form-label">URL de login <span class="text-muted">(optionnel)</span></label><input type="text" name="login_url" placeholder="/api/login" class="form-control form-control--sm form-control--mono"></div>
                <div class="form-group"><label class="form-label">Description <span class="text-muted">(optionnel)</span></label><input type="text" name="description" placeholder="ERP de gestion commerciale..." class="form-control form-control--sm"></div>
                <div class="modal__footer">
                    <button type="button" onclick="closeModal('modal-add')" class="btn btn-ghost btn-sm">Annuler</button>
                    <button type="submit" class="btn btn-primary btn-sm">Ajouter</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="modal-edit" class="modal-overlay hidden">
    <div class="modal">
        <div class="modal__body">
            <h2 class="modal__title">Modifier la Connexion</h2>
            <form id="edit-form" method="POST">
                @csrf @method('PUT')
                <div class="form-group"><label class="form-label">Nom</label><input type="text" name="name" id="edit-name" required class="form-control form-control--sm"></div>
                <div class="form-group"><label class="form-label">URL de base</label><input type="url" name="base_url" id="edit-url" required class="form-control form-control--sm form-control--mono"></div>
                <div class="form-group"><label class="form-label">URL de login <span class="text-muted">(optionnel)</span></label><input type="text" name="login_url" id="edit-login-url" class="form-control form-control--sm form-control--mono"></div>
                <div class="form-group"><label class="form-label">Description</label><input type="text" name="description" id="edit-desc" class="form-control form-control--sm"></div>
                <div class="modal__footer">
                    <button type="button" onclick="closeModal('modal-edit')" class="btn btn-ghost btn-sm">Annuler</button>
                    <button type="submit" class="btn btn-primary btn-sm">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="modal-auth" class="modal-overlay hidden">
    <div class="modal">
        <div class="modal__body">
            <h2 class="modal__title">S'authentifier</h2>
            <p class="form-hint mb-4" id="auth-modal-subtitle">Connexion à l'API</p>
            <div class="tabs">
                <button type="button" id="tab-credentials" onclick="switchAuthTab('credentials')" class="tab is-active">Email / Mot de passe</button>
                <button type="button" id="tab-token" onclick="switchAuthTab('token')" class="tab">Token direct</button>
            </div>
            <div id="form-credentials">
                <div class="form-group"><label class="form-label">Email</label><input type="email" id="auth-email" placeholder="admin@example.com" class="form-control form-control--sm"></div>
                <div class="form-group"><label class="form-label">Mot de passe</label><input type="password" id="auth-password" placeholder="••••••••" class="form-control form-control--sm"></div>
                <div id="auth-error" class="flash-message flash-message--error hidden" style="margin:0 0 1rem;"></div>
                <div class="modal__footer">
                    <button type="button" onclick="closeModal('modal-auth')" class="btn btn-ghost btn-sm">Annuler</button>
                    <button type="button" onclick="submitAuth()" id="auth-submit-btn" class="btn btn-primary btn-sm">S'authentifier</button>
                </div>
            </div>
            <div id="form-token" class="hidden">
                <div class="form-group"><label class="form-label">Bearer Token</label><input type="text" id="direct-token" placeholder="1|xxxxxxxxxxxxxxxxxx..." class="form-control form-control--sm form-control--mono"><p class="form-hint">Collez ici votre token Bearer généré depuis votre application.</p></div>
                <div id="token-error" class="flash-message flash-message--error hidden" style="margin:0 0 1rem;"></div>
                <div class="modal__footer">
                    <button type="button" onclick="closeModal('modal-auth')" class="btn btn-ghost btn-sm">Annuler</button>
                    <button type="button" onclick="submitToken()" id="token-submit-btn" class="btn btn-primary btn-sm">Enregistrer le Token</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="toast" class="toast hidden"></div>
@endsection

@push('scripts')
<script>
const csrf = document.querySelector('meta[name=csrf-token]').content;
let currentAuthConnectionId = null;
let hasLoginUrl = false;

// ========================
// MODAL HELPERS
// ========================
function openModal(id) {
    document.getElementById(id)?.classList.remove('hidden');
}
function closeModal(id) {
    document.getElementById(id)?.classList.add('hidden');
}

// ========================
// ADD MODAL
// ========================
function openAddModal() { openModal('modal-add'); }

// ========================
// EDIT MODAL
// ========================
function openEditModal(id, name, url, loginUrl, desc) {
    document.getElementById('edit-form').action = `/connections/${id}`;
    document.getElementById('edit-name').value = name;
    document.getElementById('edit-url').value = url;
    document.getElementById('edit-login-url').value = loginUrl;
    document.getElementById('edit-desc').value = desc;
    openModal('modal-edit');
}

// ========================
// AUTH MODAL
// ========================
function openAuthModal(id, name, loginUrlAvailable) {
    currentAuthConnectionId = id;
    hasLoginUrl = loginUrlAvailable;
    document.getElementById('auth-modal-subtitle').textContent = `Authentification sur « ${name} »`;
    document.getElementById('auth-email').value = '';
    document.getElementById('auth-password').value = '';
    document.getElementById('direct-token').value = '';
    document.getElementById('auth-error').classList.add('hidden');
    document.getElementById('token-error').classList.add('hidden');

    // Default to credentials tab if login url exists, else token tab
    switchAuthTab(loginUrlAvailable ? 'credentials' : 'token');

    openModal('modal-auth');
}

function switchAuthTab(tab) {
    const isCredentials = tab === 'credentials';
    document.getElementById('form-credentials').classList.toggle('hidden', !isCredentials);
    document.getElementById('form-token').classList.toggle('hidden', isCredentials);
    document.getElementById('tab-credentials').classList.toggle('is-active', isCredentials);
    document.getElementById('tab-token').classList.toggle('is-active', !isCredentials);
}

async function submitAuth() {
    const email    = document.getElementById('auth-email').value.trim();
    const password = document.getElementById('auth-password').value;
    const errEl    = document.getElementById('auth-error');
    const btn      = document.getElementById('auth-submit-btn');

    if (!email || !password) { showInlineError(errEl, 'Email et mot de passe requis.'); return; }
    errEl.classList.add('hidden');
    setButtonLoading(btn, true);

    try {
        const res  = await fetch(`/connections/${currentAuthConnectionId}/authenticate`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            body: JSON.stringify({ email, password }),
        });
        const json = await res.json();

        if (json.success) {
            closeModal('modal-auth');
            showToast(json.message, 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showInlineError(errEl, json.message || 'Erreur d\'authentification.');
        }
    } catch { showInlineError(errEl, 'Erreur réseau. Réessayez.'); }
    finally  { setButtonLoading(btn, false); }
}

async function submitToken() {
    const token = document.getElementById('direct-token').value.trim();
    const errEl = document.getElementById('token-error');
    const btn   = document.getElementById('token-submit-btn');

    if (!token || token.length < 8) { showInlineError(errEl, 'Token invalide (min 8 caractères).'); return; }
    errEl.classList.add('hidden');
    setButtonLoading(btn, true);

    try {
        const res  = await fetch(`/connections/${currentAuthConnectionId}/store-token`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            body: JSON.stringify({ token }),
        });
        const json = await res.json();

        if (json.success) {
            closeModal('modal-auth');
            showToast(json.message, 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showInlineError(errEl, json.message || 'Erreur.');
        }
    } catch { showInlineError(errEl, 'Erreur réseau. Réessayez.'); }
    finally  { setButtonLoading(btn, false); }
}

// ========================
// DISCONNECT
// ========================
async function disconnectConnection(id, name) {
    if (!confirm(`Déconnecter « ${name} » ? Le token sera effacé.`)) return;

    const res  = await fetch(`/connections/${id}/disconnect`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
    });
    const json = await res.json();
    showToast(json.message, json.success ? 'warning' : 'error');
    if (json.success) setTimeout(() => location.reload(), 1200);
}

// ========================
// PING
// ========================
async function pingConnection(id, btn) {
    btn.disabled = true;
    const orig = btn.innerHTML;
    btn.innerHTML = '<svg class="icon-sm loading-spinner" fill="none" viewBox="0 0 24 24"><circle style="opacity:0.25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path style="opacity:0.75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>';

    try {
        const res  = await fetch(`/connections/${id}/ping`);
        const json = await res.json();
        showToast(json.message, json.online ? 'success' : 'error');
    } catch { showToast('❌ Erreur lors du test.', 'error'); }
    finally  { btn.disabled = false; btn.innerHTML = orig; }
}

// ========================
// UI HELPERS
// ========================
function showInlineError(el, msg) {
    el.textContent = msg;
    el.classList.remove('hidden');
}

function setButtonLoading(btn, loading) {
    btn.disabled = loading;
    if (loading) { btn.dataset.orig = btn.textContent; btn.textContent = 'Chargement…'; }
    else         { btn.textContent = btn.dataset.orig || 'OK'; }
}

function showToast(msg, type = 'success') {
    const toast = document.getElementById('toast');
    toast.className = `toast toast--${type}`;
    toast.textContent = msg;
    toast.classList.remove('hidden');
    setTimeout(() => toast.classList.add('hidden'), 4000);
}

// Close modals on backdrop click
['modal-add','modal-edit','modal-auth'].forEach(id => {
    document.getElementById(id).addEventListener('click', function(e) {
        if (e.target === this) closeModal(id);
    });
});
</script>
@endpush
