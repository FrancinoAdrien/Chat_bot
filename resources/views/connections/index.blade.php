@extends('layouts.app')

@section('title', 'Connexions API')
@section('page-title', 'Connexions API')
@section('page-subtitle', 'Gérez les APIs externes auxquelles le chatbot a accès')

@section('header-actions')
<button onclick="openAddModal()"
   class="flex items-center gap-2 px-4 py-2 rounded-lg bg-gradient-to-r from-indigo-600 to-purple-700 text-white text-sm font-medium hover:from-indigo-500 hover:to-purple-600 transition-all shadow-lg shadow-indigo-500/30 hover:scale-105 active:scale-95">
    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
    </svg>
    Nouvelle Connexion
</button>
@endsection

@section('content')
<div class="p-6 space-y-6">

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="glass rounded-xl p-4 border border-slate-800/50">
            <div class="text-2xl font-bold text-indigo-400">{{ $connections->count() }}</div>
            <div class="text-xs text-slate-500 mt-1">Connexions totales</div>
        </div>
        <div class="glass rounded-xl p-4 border border-slate-800/50">
            <div class="text-2xl font-bold text-emerald-400">{{ $connections->where('is_authenticated', true)->count() }}</div>
            <div class="text-xs text-slate-500 mt-1">Authentifiées</div>
        </div>
        <div class="glass rounded-xl p-4 border border-slate-800/50">
            <div class="text-2xl font-bold text-red-400">{{ $connections->where('is_authenticated', false)->count() }}</div>
            <div class="text-xs text-slate-500 mt-1">Non authentifiées</div>
        </div>
    </div>

    {{-- Connections Table --}}
    <div class="glass rounded-2xl overflow-hidden border border-slate-800/50">

        @if($connections->isEmpty())
        <div class="flex flex-col items-center justify-center py-16 text-center">
            <div class="w-16 h-16 rounded-2xl bg-slate-800/50 border border-slate-700 flex items-center justify-center mb-4">
                <svg class="w-8 h-8 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244" />
                </svg>
            </div>
            <p class="text-slate-400 mb-2">Aucune connexion API configurée.</p>
            <button onclick="openAddModal()" class="text-indigo-400 text-sm hover:text-indigo-300">+ Ajouter la première connexion</button>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-300">
                <thead class="bg-slate-900/50 text-slate-400 text-xs uppercase font-semibold">
                    <tr>
                        <th class="px-6 py-4">Connexion</th>
                        <th class="px-6 py-4">URL de base</th>
                        <th class="px-6 py-4">Authentification</th>
                        <th class="px-6 py-4">Token</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/50">
                    @foreach($connections as $conn)
                    <tr class="hover:bg-slate-800/20 transition-colors" id="row-{{ $conn->id }}">
                        {{-- Name --}}
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-indigo-600/30 to-purple-700/30 border border-indigo-500/20 flex items-center justify-center text-xs font-bold text-indigo-300">
                                    {{ strtoupper(substr($conn->name, 0, 2)) }}
                                </div>
                                <div>
                                    <div class="font-medium text-slate-200">{{ $conn->name }}</div>
                                    @if($conn->description)
                                    <div class="text-xs text-slate-500">{{ Str::limit($conn->description, 40) }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>

                        {{-- URL --}}
                        <td class="px-6 py-4">
                            <div class="font-mono text-xs text-slate-400">{{ $conn->base_url }}</div>
                            @if($conn->login_url)
                            <div class="text-[10px] text-slate-600 mt-0.5">Login: {{ $conn->login_url }}</div>
                            @endif
                        </td>

                        {{-- Auth Status --}}
                        <td class="px-6 py-4">
                            @if($conn->is_authenticated)
                            <div class="flex flex-col gap-1">
                                <span class="inline-flex items-center gap-1.5 text-xs text-emerald-400">
                                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                                    Authentifié
                                </span>
                                @if($conn->authenticated_at)
                                <span class="text-[10px] text-slate-600">{{ $conn->authenticated_at->diffForHumans() }}</span>
                                @endif
                            </div>
                            @else
                            <span class="inline-flex items-center gap-1.5 text-xs text-red-400">
                                <span class="w-2 h-2 rounded-full bg-red-400"></span>
                                Non authentifié
                            </span>
                            @endif
                        </td>

                        {{-- Token --}}
                        <td class="px-6 py-4">
                            <span class="font-mono text-xs text-slate-500" id="token-display-{{ $conn->id }}">
                                {{ $conn->masked_token ?? '—' }}
                            </span>
                        </td>

                        {{-- Actions --}}
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-end gap-2">
                                {{-- Ping --}}
                                <button onclick="pingConnection({{ $conn->id }}, this)"
                                    title="Tester la connexion"
                                    class="p-2 rounded-lg bg-slate-800 border border-slate-700 text-slate-400 hover:text-emerald-400 hover:border-emerald-500/40 transition-all">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.288 15.038a5.25 5.25 0 017.424 0M5.106 11.856c3.807-3.808 9.98-3.808 13.788 0M1.924 8.674c5.565-5.565 14.587-5.565 20.152 0M12.53 18.22l-.53.53-.53-.53a.75.75 0 011.06 0z" />
                                    </svg>
                                </button>

                                {{-- Auth button --}}
                                @if(!$conn->is_authenticated)
                                <button onclick="openAuthModal({{ $conn->id }}, '{{ addslashes($conn->name) }}', {{ $conn->hasLoginUrl() ? 'true' : 'false' }})"
                                    class="px-3 py-1.5 rounded-lg bg-indigo-500/10 border border-indigo-500/30 text-indigo-300 text-xs hover:bg-indigo-500/20 transition-all">
                                    🔑 S'authentifier
                                </button>
                                @else
                                <button onclick="disconnectConnection({{ $conn->id }}, '{{ addslashes($conn->name) }}')"
                                    class="px-3 py-1.5 rounded-lg bg-red-500/10 border border-red-500/30 text-red-400 text-xs hover:bg-red-500/20 transition-all">
                                    🔌 Déconnecter
                                </button>
                                @endif

                                {{-- Edit --}}
                                <button onclick="openEditModal({{ $conn->id }}, '{{ addslashes($conn->name) }}', '{{ addslashes($conn->base_url) }}', '{{ addslashes($conn->login_url ?? '') }}', '{{ addslashes($conn->description ?? '') }}')"
                                    class="p-2 rounded-lg bg-slate-800 border border-slate-700 text-slate-400 hover:text-indigo-300 hover:border-indigo-500/40 transition-all">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                                    </svg>
                                </button>

                                {{-- Delete --}}
                                <form action="{{ route('connections.destroy', $conn) }}" method="POST" class="inline" onsubmit="return confirm('Supprimer cette connexion ?')">
                                    @csrf @method('DELETE')
                                    <button class="p-2 rounded-lg bg-slate-800 border border-slate-700 text-slate-500 hover:text-red-400 hover:border-red-500/40 transition-all">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
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

{{-- ===== MODAL: ADD CONNECTION ===== --}}
<div id="modal-add" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/80 backdrop-blur-sm">
    <div class="glass rounded-2xl border border-slate-700 w-full max-w-md p-6 mx-4">
        <h2 class="text-lg font-semibold text-slate-100 mb-5">Nouvelle Connexion API</h2>
        <form action="{{ route('connections.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1">Nom</label>
                <input type="text" name="name" required placeholder="Caisse Principale"
                    class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2.5 text-sm text-slate-100 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1">URL de base</label>
                <input type="url" name="base_url" required placeholder="http://localhost:8000"
                    class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2.5 text-sm font-mono text-slate-100 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1">URL de login <span class="text-slate-600">(optionnel, ex: /api/login)</span></label>
                <input type="text" name="login_url" placeholder="/api/login"
                    class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2.5 text-sm font-mono text-slate-100 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1">Description <span class="text-slate-600">(optionnel)</span></label>
                <input type="text" name="description" placeholder="ERP de gestion commerciale..."
                    class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2.5 text-sm text-slate-100 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="closeModal('modal-add')" class="px-4 py-2 text-sm text-slate-400 hover:text-slate-200">Annuler</button>
                <button type="submit" class="px-5 py-2 rounded-lg bg-gradient-to-r from-indigo-600 to-purple-700 text-white text-sm hover:from-indigo-500 hover:to-purple-600 transition-all">Ajouter</button>
            </div>
        </form>
    </div>
</div>

{{-- ===== MODAL: EDIT CONNECTION ===== --}}
<div id="modal-edit" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/80 backdrop-blur-sm">
    <div class="glass rounded-2xl border border-slate-700 w-full max-w-md p-6 mx-4">
        <h2 class="text-lg font-semibold text-slate-100 mb-5">Modifier la Connexion</h2>
        <form id="edit-form" method="POST" class="space-y-4">
            @csrf @method('PUT')
            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1">Nom</label>
                <input type="text" name="name" id="edit-name" required class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2.5 text-sm text-slate-100 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1">URL de base</label>
                <input type="url" name="base_url" id="edit-url" required class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2.5 text-sm font-mono text-slate-100 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1">URL de login <span class="text-slate-600">(optionnel)</span></label>
                <input type="text" name="login_url" id="edit-login-url" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2.5 text-sm font-mono text-slate-100 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1">Description</label>
                <input type="text" name="description" id="edit-desc" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2.5 text-sm text-slate-100 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="closeModal('modal-edit')" class="px-4 py-2 text-sm text-slate-400 hover:text-slate-200">Annuler</button>
                <button type="submit" class="px-5 py-2 rounded-lg bg-gradient-to-r from-indigo-600 to-purple-700 text-white text-sm hover:from-indigo-500 hover:to-purple-600 transition-all">Enregistrer</button>
            </div>
        </form>
    </div>
</div>

{{-- ===== MODAL: AUTHENTICATE ===== --}}
<div id="modal-auth" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/80 backdrop-blur-sm">
    <div class="glass rounded-2xl border border-slate-700 w-full max-w-md p-6 mx-4">
        <h2 class="text-lg font-semibold text-slate-100 mb-1">S'authentifier</h2>
        <p class="text-xs text-slate-500 mb-5" id="auth-modal-subtitle">Connexion à l'API</p>

        {{-- Tab switcher --}}
        <div class="flex gap-2 mb-5 p-1 bg-slate-900 rounded-lg">
            <button id="tab-credentials" onclick="switchAuthTab('credentials')"
                class="flex-1 py-1.5 rounded-md text-xs font-medium transition-all bg-indigo-600 text-white">
                Email / Mot de passe
            </button>
            <button id="tab-token" onclick="switchAuthTab('token')"
                class="flex-1 py-1.5 rounded-md text-xs font-medium transition-all text-slate-400 hover:text-slate-200">
                Token direct
            </button>
        </div>

        {{-- Credentials form --}}
        <div id="form-credentials" class="space-y-4">
            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1">Email</label>
                <input type="email" id="auth-email" placeholder="admin@example.com"
                    class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2.5 text-sm text-slate-100 focus:ring-1 focus:ring-indigo-500 outline-none">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1">Mot de passe</label>
                <input type="password" id="auth-password" placeholder="••••••••"
                    class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2.5 text-sm text-slate-100 focus:ring-1 focus:ring-indigo-500 outline-none">
            </div>
            <div id="auth-error" class="hidden px-3 py-2 rounded-lg bg-red-500/10 border border-red-500/30 text-red-300 text-xs"></div>
            <div class="flex justify-end gap-3">
                <button type="button" onclick="closeModal('modal-auth')" class="px-4 py-2 text-sm text-slate-400 hover:text-slate-200">Annuler</button>
                <button onclick="submitAuth()" id="auth-submit-btn"
                    class="px-5 py-2 rounded-lg bg-gradient-to-r from-indigo-600 to-purple-700 text-white text-sm hover:from-indigo-500 hover:to-purple-600 transition-all">
                    S'authentifier
                </button>
            </div>
        </div>

        {{-- Token form --}}
        <div id="form-token" class="space-y-4 hidden">
            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1">Bearer Token</label>
                <input type="text" id="direct-token" placeholder="1|xxxxxxxxxxxxxxxxxx..."
                    class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2.5 text-sm font-mono text-slate-100 focus:ring-1 focus:ring-indigo-500 outline-none">
                <p class="text-[10px] text-slate-600 mt-1">Collez ici votre token Bearer généré depuis votre application.</p>
            </div>
            <div id="token-error" class="hidden px-3 py-2 rounded-lg bg-red-500/10 border border-red-500/30 text-red-300 text-xs"></div>
            <div class="flex justify-end gap-3">
                <button type="button" onclick="closeModal('modal-auth')" class="px-4 py-2 text-sm text-slate-400 hover:text-slate-200">Annuler</button>
                <button onclick="submitToken()" id="token-submit-btn"
                    class="px-5 py-2 rounded-lg bg-gradient-to-r from-indigo-600 to-purple-700 text-white text-sm hover:from-indigo-500 hover:to-purple-600 transition-all">
                    Enregistrer le Token
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Toast --}}
<div id="toast" class="fixed bottom-6 right-6 z-50 hidden"></div>
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
    const el = document.getElementById(id);
    el.classList.remove('hidden');
    el.classList.add('flex');
}
function closeModal(id) {
    const el = document.getElementById(id);
    el.classList.add('hidden');
    el.classList.remove('flex');
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
    document.getElementById('tab-credentials').className = `flex-1 py-1.5 rounded-md text-xs font-medium transition-all ${isCredentials ? 'bg-indigo-600 text-white' : 'text-slate-400 hover:text-slate-200'}`;
    document.getElementById('tab-token').className = `flex-1 py-1.5 rounded-md text-xs font-medium transition-all ${!isCredentials ? 'bg-indigo-600 text-white' : 'text-slate-400 hover:text-slate-200'}`;
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
    btn.innerHTML = '<svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>';

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
    const colors = {
        success: 'bg-emerald-500/20 border-emerald-500/40 text-emerald-300',
        error:   'bg-red-500/20 border-red-500/40 text-red-300',
        warning: 'bg-amber-500/20 border-amber-500/40 text-amber-300',
    };
    const toast = document.getElementById('toast');
    toast.className = `fixed bottom-6 right-6 z-50 px-5 py-3 rounded-xl border text-sm backdrop-blur-md shadow-xl ${colors[type] || colors.success}`;
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
