@extends('layouts.app')

@section('title', 'Créer un Outil')
@section('page-title', 'Nouvel Outil')
@section('page-subtitle', 'Ajoutez une nouvelle capacité à l\'IA.')

@section('header-actions')
    <a href="{{ route('tools.index') }}" class="px-4 py-2 bg-slate-800 text-slate-300 rounded-lg text-sm hover:bg-slate-700 hover:text-white transition-all">
        &larr; Retour
    </a>
@endsection

@section('content')
<div class="max-w-4xl mx-auto p-6">
    <div class="glass rounded-2xl p-8 border border-slate-800/50">

        @if($connections->isEmpty())
            <div class="text-center py-10">
                <p class="text-slate-400 mb-4">Vous devez d'abord configurer une connexion API avant d'ajouter des outils.</p>
                <a href="{{ route('connections.index') }}" class="text-indigo-400 hover:underline">+ Créer une connexion</a>
            </div>
        @else

        <form action="{{ route('tools.store') }}" method="POST">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Connection --}}
                <div class="col-span-1 md:col-span-2 space-y-4">
                    <label class="block text-sm font-medium text-slate-300">Connexion API *</label>
                    <select name="api_connection_id" required class="w-full bg-slate-900/50 border border-slate-700 text-slate-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all shadow-inner">
                        <option value="">Sélectionner une connexion</option>
                        @foreach($connections as $c)
                            <option value="{{ $c->id }}" {{ old('api_connection_id') == $c->id ? 'selected' : '' }}>
                                {{ $c->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('api_connection_id')
                        <p class="text-sm text-red-400 mt-1.5 flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Internal Name --}}
                <div class="space-y-4">
                    <label class="block text-sm font-medium text-slate-300">Nom interne * (ex: getSales)</label>
                    <input type="text" name="name" value="{{ old('name') }}" required class="w-full bg-slate-900/50 border border-slate-700 text-slate-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
                    @error('name') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Label --}}
                <div class="space-y-4">
                    <label class="block text-sm font-medium text-slate-300">Libellé * (ex: Ventes du jour)</label>
                    <input type="text" name="label" value="{{ old('label') }}" required class="w-full bg-slate-900/50 border border-slate-700 text-slate-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
                    @error('label') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Endpoint --}}
                <div class="col-span-1 md:col-span-2 space-y-4">
                    <label class="block text-sm font-medium text-slate-300">Endpoint API *</label>
                    <div class="flex">
                        <span class="inline-flex items-center px-4 rounded-l-xl border border-r-0 border-slate-700 bg-slate-800 text-slate-400 text-sm">
                            GET/POST
                        </span>
                        <input type="text" name="endpoint" value="{{ old('endpoint') }}" placeholder="/api/endpoint" required class="flex-1 min-w-0 block w-full px-4 py-3 rounded-none rounded-r-xl bg-slate-900/50 border border-slate-700 text-slate-200 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                    </div>
                    @error('endpoint') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Method --}}
                <div class="space-y-4">
                    <label class="block text-sm font-medium text-slate-300">Méthode HTTP *</label>
                    <select name="method" required class="w-full bg-slate-900/50 border border-slate-700 text-slate-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
                        <option value="GET" {{ old('method') == 'GET' ? 'selected' : '' }}>GET</option>
                        <option value="POST" {{ old('method') == 'POST' ? 'selected' : '' }}>POST</option>
                    </select>
                </div>

                {{-- Keywords --}}
                <div class="col-span-1 md:col-span-2 space-y-4">
                    <label class="block text-sm font-medium text-slate-300">Mots-clés déclencheurs * (séparés par virgule)</label>
                    <input type="text" name="keywords" value="{{ old('keywords') }}" placeholder="ventes, ca, aujourd'hui" required class="w-full bg-slate-900/50 border border-slate-700 text-slate-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
                    <p class="text-xs text-slate-500 mt-1">Si le message contient l'un de ces mots, l'outil sera déclenché.</p>
                </div>

                {{-- Description --}}
                <div class="col-span-1 md:col-span-2 space-y-4">
                    <label class="block text-sm font-medium text-slate-300">Description (optionnelle)</label>
                    <textarea name="description" rows="2" placeholder="Que fait cet outil ?" class="w-full bg-slate-900/50 border border-slate-700 text-slate-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all">{{ old('description') }}</textarea>
                </div>

                {{-- Active --}}
                <div class="col-span-1 md:col-span-2 flex items-center gap-3 pt-4 border-t border-slate-800/50 mt-4">
                    <input type="checkbox" name="active" id="active" value="1" {{ old('active', true) ? 'checked' : '' }} class="w-5 h-5 rounded border-slate-600 bg-slate-800 text-indigo-500 focus:ring-indigo-500 focus:ring-offset-slate-900">
                    <label for="active" class="text-sm font-medium text-slate-300 cursor-pointer">Activer cet outil</label>
                </div>
            </div>

            <div class="mt-8 flex justify-end">
                <button type="submit" class="px-6 py-3 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-700 text-white font-medium hover:from-indigo-500 hover:to-purple-600 transition-all shadow-lg shadow-indigo-500/30">
                    Créer l'outil
                </button>
            </div>
        </form>
        @endif
    </div>
</div>
@endsection
