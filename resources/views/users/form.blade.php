@extends('layouts.app')

@section('title', $user->exists ? 'Modifier l\'utilisateur' : 'Nouvel utilisateur')
@section('page-title', $user->exists ? 'Modifier l\'utilisateur' : 'Nouvel utilisateur')

@section('content')
<div class="p-6 max-w-2xl">
    <div class="glass rounded-2xl border border-slate-800/50 p-6">
        <form action="{{ $user->exists ? route('users.update', $user) : route('users.store') }}" method="POST" class="space-y-6">
            @csrf
            @if($user->exists)
                @method('PUT')
            @endif

            <div class="grid grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-slate-400 mb-2">Nom <span class="text-red-400">*</span></label>
                    <input type="text" name="name" required value="{{ old('name', $user->name) }}"
                        class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-slate-100 placeholder-slate-600 focus:outline-none focus:ring-2 focus:ring-indigo-500/50">
                    @error('name') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-400 mb-2">Prénom</label>
                    <input type="text" name="prenom" value="{{ old('prenom', $user->prenom) }}"
                        class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-slate-100 placeholder-slate-600 focus:outline-none focus:ring-2 focus:ring-indigo-500/50">
                    @error('prenom') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-slate-400 mb-2">Matricule <span class="text-red-400">*</span></label>
                    <input type="text" name="matricule" required value="{{ old('matricule', $user->matricule) }}"
                        class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-slate-100 placeholder-slate-600 uppercase focus:outline-none focus:ring-2 focus:ring-indigo-500/50">
                    @error('matricule') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-400 mb-2">Poste</label>
                    <input type="text" name="poste" value="{{ old('poste', $user->poste) }}"
                        class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-slate-100 placeholder-slate-600 focus:outline-none focus:ring-2 focus:ring-indigo-500/50">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-slate-400 mb-2">Rôle <span class="text-red-400">*</span></label>
                    <select name="role" required class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/50">
                        <option value="user" {{ old('role', $user->role) === 'user' ? 'selected' : '' }}>Utilisateur</option>
                        <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Administrateur</option>
                    </select>
                </div>
                <div class="flex items-center pt-8">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $user->exists ? $user->is_active : true) ? 'checked' : '' }}
                            class="w-5 h-5 rounded bg-slate-900 border-slate-700 text-emerald-500 focus:ring-emerald-500/50">
                        <span class="text-sm font-medium text-slate-300">Compte Actif</span>
                    </label>
                </div>
            </div>

            <div class="border-t border-slate-800/50 pt-6 mt-6">
                <h3 class="text-sm font-semibold text-slate-300 mb-4">Mot de passe</h3>
                
                @if($user->exists)
                    <div class="mb-4">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" name="reset_password" id="reset_password" value="1" onchange="togglePasswordField()"
                                class="w-4 h-4 rounded bg-slate-900 border-slate-700 text-indigo-500 focus:ring-indigo-500/50">
                            <span class="text-sm text-slate-400">Générer un nouveau mot de passe</span>
                        </label>
                    </div>
                @else
                    <p class="text-xs text-slate-500 mb-4">Si vous laissez le champ vide, un mot de passe sécurisé sera généré automatiquement.</p>
                @endif

                <div id="password_field" class="{{ $user->exists ? 'hidden' : '' }}">
                    <input type="text" name="custom_password" placeholder="Mot de passe personnalisé (optionnel, min 8 caractères)"
                        class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-slate-100 placeholder-slate-600 focus:outline-none focus:ring-2 focus:ring-indigo-500/50">
                    @error('custom_password') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-6">
                <a href="{{ route('users.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-700 text-slate-300 hover:bg-slate-800 transition-colors text-sm font-medium">Annuler</a>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-indigo-600 text-white hover:bg-indigo-500 transition-colors text-sm font-medium shadow-lg shadow-indigo-500/20">
                    {{ $user->exists ? 'Mettre à jour' : 'Créer l\'utilisateur' }}
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function togglePasswordField() {
        const checkbox = document.getElementById('reset_password');
        const field = document.getElementById('password_field');
        if (checkbox && checkbox.checked) {
            field.classList.remove('hidden');
        } else {
            field.classList.add('hidden');
        }
    }
</script>
@endsection
