@extends('layouts.app')

@section('title', 'Gestion des Utilisateurs')
@section('page-title', 'Utilisateurs')
@section('page-subtitle', 'Gérez les accès et les permissions au ChatBot.')

@section('header-actions')
    <a href="{{ route('users.create') }}" class="px-4 py-2 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-700 text-white text-sm font-medium hover:from-indigo-500 hover:to-purple-600 transition-all shadow-lg shadow-indigo-500/20">
        + Nouvel Utilisateur
    </a>
@endsection

@section('content')
<div class="p-6">
    <div class="glass rounded-2xl border border-slate-800/50 overflow-hidden">
        <table class="w-full text-sm text-slate-300">
            <thead class="bg-slate-900/50 text-xs text-slate-500 uppercase">
                <tr>
                    <th class="px-6 py-4 text-left">Utilisateur</th>
                    <th class="px-6 py-4 text-left">Matricule</th>
                    <th class="px-6 py-4 text-left">Rôle</th>
                    <th class="px-6 py-4 text-left">Statut</th>
                    <th class="px-6 py-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/50">
                @foreach($users as $user)
                <tr class="hover:bg-slate-800/20 transition-colors">
                    <td class="px-6 py-4">
                        <div class="font-medium text-slate-200">{{ $user->name }} {{ $user->prenom }}</div>
                        <div class="text-xs text-slate-500">{{ $user->poste ?? 'Aucun poste' }}</div>
                    </td>
                    <td class="px-6 py-4 font-mono text-indigo-400">
                        {{ $user->matricule }}
                    </td>
                    <td class="px-6 py-4">
                        @if($user->isAdmin())
                            <span class="px-2 py-1 rounded-md bg-purple-500/10 text-purple-400 border border-purple-500/20 text-xs font-semibold">Admin</span>
                        @else
                            <span class="px-2 py-1 rounded-md bg-slate-500/10 text-slate-400 border border-slate-500/20 text-xs">Utilisateur</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        @if($user->is_active)
                            <span class="inline-flex items-center gap-1.5 text-xs text-emerald-400"><span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Actif</span>
                        @else
                            <span class="inline-flex items-center gap-1.5 text-xs text-red-400"><span class="w-1.5 h-1.5 rounded-full bg-red-400"></span> Inactif</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right space-x-3">
                        <a href="{{ route('users.edit', $user) }}" class="text-indigo-400 hover:text-indigo-300 text-xs font-medium">Modifier</a>
                        @if($user->id !== auth()->id())
                        <form action="{{ route('users.destroy', $user) }}" method="POST" class="inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')">
                            @csrf @method('DELETE')
                            <button class="text-red-400 hover:text-red-300 text-xs font-medium">Supprimer</button>
                        </form>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
