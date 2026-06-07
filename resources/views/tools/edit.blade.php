@extends('layouts.app')

@section('title', 'Modifier l\'Outil')
@section('page-title', 'Modifier : ' . $tool->label)
@section('page-subtitle', $tool->type === 'excel' ? '📊 Outil Excel — Feuille : ' . $tool->sheet_name : '🌐 Outil API')

@section('header-actions')
    <a href="{{ route('tools.index') }}" class="px-4 py-2 bg-slate-800 text-slate-300 rounded-lg text-sm hover:bg-slate-700 hover:text-white transition-all">
        &larr; Retour
    </a>
@endsection

@section('content')
<div class="p-6 max-w-4xl mx-auto">
    <div class="glass rounded-2xl p-8 border border-slate-800/50">

        @if($tool->type === 'excel')
        {{-- EXCEL EDIT FORM --}}
        <div class="mb-6 p-4 bg-emerald-500/10 border border-emerald-500/20 rounded-xl flex items-center gap-3">
            <svg class="w-5 h-5 text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0H3"/>
            </svg>
            <div>
                <p class="text-sm text-emerald-300 font-medium">Fichier : {{ basename($tool->file_path) }}</p>
                <p class="text-xs text-slate-400 mt-0.5">Feuille Excel active : <span class="text-emerald-400">{{ $tool->sheet_name }}</span>
                — Pour remplacer entièrement un fichier, supprimez cet outil et recréez-le.</p>
            </div>
        </div>

        <form action="{{ route('tools.update', $tool) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-slate-300 mb-1.5">Libellé *</label>
                    <input type="text" name="label" value="{{ old('label', $tool->label) }}" required
                           class="w-full bg-slate-900/50 border border-slate-700 text-slate-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500">
                    @error('label')<p class="text-xs text-red-400 mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="col-span-2">
                    <label class="block text-sm font-medium text-slate-300 mb-1.5">Mots-clés déclencheurs * <span class="text-slate-500 text-xs">(séparés par virgule)</span></label>
                    <input type="text" name="keywords" value="{{ old('keywords', implode(', ', $tool->keywords ?? [])) }}" required
                           class="w-full bg-slate-900/50 border border-slate-700 text-slate-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500">
                </div>

                <div class="col-span-2">
                    <label class="block text-sm font-medium text-slate-300 mb-1.5">Remplacer le fichier <span class="text-slate-500 text-xs">(optionnel, .xlsx, .xls, .csv)</span></label>
                    <input type="file" name="excel_file" accept=".xlsx,.xls,.csv"
                           class="w-full text-slate-400 text-sm file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-indigo-600/80 file:text-white hover:file:bg-indigo-600 file:cursor-pointer">
                </div>

                <div class="col-span-2">
                    <label class="block text-sm font-medium text-slate-300 mb-1.5">Description <span class="text-slate-500 text-xs">(optionnelle)</span></label>
                    <textarea name="description" rows="2" class="w-full bg-slate-900/50 border border-slate-700 text-slate-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500">{{ old('description', $tool->description) }}</textarea>
                </div>

                <div class="col-span-2 flex items-center gap-3 pt-4 border-t border-slate-800/50">
                    <input type="checkbox" name="active" id="active" value="1" {{ old('active', $tool->active) ? 'checked' : '' }}
                           class="w-5 h-5 rounded border-slate-600 bg-slate-800 text-indigo-500">
                    <label for="active" class="text-sm font-medium text-slate-300 cursor-pointer">Activer cet outil</label>
                </div>
            </div>

            <div class="mt-8 flex justify-end">
                <button type="submit" class="px-6 py-3 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 text-white font-medium hover:from-emerald-500 hover:to-teal-500 transition-all shadow-lg shadow-emerald-500/30">
                    Mettre à jour l'outil Excel
                </button>
            </div>
        </form>

        @else
        {{-- API EDIT FORM --}}
        <form action="{{ route('tools.update', $tool) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-slate-300 mb-1.5">Connexion API *</label>
                    <select name="api_connection_id" required class="w-full bg-slate-900/50 border border-slate-700 text-slate-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500">
                        <option value="">Sélectionner une connexion</option>
                        @foreach($connections as $c)
                            <option value="{{ $c->id }}" {{ old('api_connection_id', $tool->api_connection_id) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                    @error('api_connection_id')<p class="text-xs text-red-400 mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1.5">Nom interne *</label>
                    <input type="text" name="name" value="{{ old('name', $tool->name) }}" required
                           class="w-full bg-slate-900/50 border border-slate-700 text-slate-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500">
                    @error('name')<p class="text-xs text-red-400 mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1.5">Libellé *</label>
                    <input type="text" name="label" value="{{ old('label', $tool->label) }}" required
                           class="w-full bg-slate-900/50 border border-slate-700 text-slate-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500">
                    @error('label')<p class="text-xs text-red-400 mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="col-span-2">
                    <label class="block text-sm font-medium text-slate-300 mb-1.5">Endpoint API *</label>
                    <div class="flex">
                        <span class="inline-flex items-center px-4 rounded-l-xl border border-r-0 border-slate-700 bg-slate-800 text-slate-400 text-sm">GET/POST</span>
                        <input type="text" name="endpoint" value="{{ old('endpoint', $tool->endpoint) }}" placeholder="/api/endpoint" required
                               class="flex-1 min-w-0 px-4 py-3 rounded-r-xl bg-slate-900/50 border border-slate-700 text-slate-200 focus:ring-indigo-500 text-sm">
                    </div>
                    @error('endpoint')<p class="text-xs text-red-400 mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1.5">Méthode HTTP *</label>
                    <select name="method" required class="w-full bg-slate-900/50 border border-slate-700 text-slate-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500">
                        <option value="GET" {{ old('method', $tool->method) == 'GET' ? 'selected' : '' }}>GET</option>
                        <option value="POST" {{ old('method', $tool->method) == 'POST' ? 'selected' : '' }}>POST</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1.5">Mots-clés * <span class="text-slate-500 text-xs">(virgule)</span></label>
                    <input type="text" name="keywords" value="{{ old('keywords', implode(', ', $tool->keywords ?? [])) }}" required
                           class="w-full bg-slate-900/50 border border-slate-700 text-slate-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500">
                </div>

                <div class="col-span-2">
                    <label class="block text-sm font-medium text-slate-300 mb-1.5">Description <span class="text-slate-500 text-xs">(optionnelle)</span></label>
                    <textarea name="description" rows="2" class="w-full bg-slate-900/50 border border-slate-700 text-slate-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500">{{ old('description', $tool->description) }}</textarea>
                </div>

                <div class="col-span-2 flex items-center gap-3 pt-4 border-t border-slate-800/50">
                    <input type="checkbox" name="active" id="active" value="1" {{ old('active', $tool->active) ? 'checked' : '' }}
                           class="w-5 h-5 rounded border-slate-600 bg-slate-800 text-indigo-500">
                    <label for="active" class="text-sm font-medium text-slate-300 cursor-pointer">Activer cet outil</label>
                </div>
            </div>

            <div class="mt-8 flex justify-end">
                <button type="submit" class="px-6 py-3 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-700 text-white font-medium hover:from-indigo-500 hover:to-purple-600 transition-all shadow-lg shadow-indigo-500/30">
                    Mettre à jour l'outil API
                </button>
            </div>
        </form>
        @endif

    </div>
</div>
@endsection
