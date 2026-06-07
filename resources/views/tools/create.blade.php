@extends('layouts.app')

@section('title', 'Créer un Outil')
@section('page-title', 'Nouvel Outil')
@section('page-subtitle', 'Ajoutez une source de données à l\'IA : API distante ou fichier Excel.')

@section('header-actions')
    <a href="{{ route('tools.index') }}" class="px-4 py-2 bg-slate-800 text-slate-300 rounded-lg text-sm hover:bg-slate-700 hover:text-white transition-all">
        &larr; Retour
    </a>
@endsection

@section('content')
<div class="max-w-4xl mx-auto p-6">
    <div class="glass rounded-2xl p-8 border border-slate-800/50">

        {{-- Source Type Toggle --}}
        <div class="mb-8">
            <p class="text-sm font-medium text-slate-400 mb-3">Type de source de données</p>
            <div class="inline-flex bg-slate-900 rounded-xl p-1 border border-slate-800 gap-1">
                <button type="button" id="btn-api" onclick="switchType('api')"
                    class="type-btn px-5 py-2.5 rounded-lg text-sm font-medium transition-all bg-indigo-600 text-white shadow-lg shadow-indigo-500/20">
                    🌐 API Distante
                </button>
                <button type="button" id="btn-excel" onclick="switchType('excel')"
                    class="type-btn px-5 py-2.5 rounded-lg text-sm font-medium transition-all text-slate-400 hover:text-slate-200">
                    📊 Fichier Excel
                </button>
            </div>
        </div>

        {{-- API FORM --}}
        <form id="form-api" action="{{ route('tools.store') }}" method="POST">
            @csrf
            <input type="hidden" name="type" value="api">
            @if($connections->isEmpty())
                <div class="text-center py-10">
                    <p class="text-slate-400 mb-4">Vous devez d'abord configurer une connexion API.</p>
                    <a href="{{ route('connections.index') }}" class="text-indigo-400 hover:underline">+ Créer une connexion</a>
                </div>
            @else
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-slate-300 mb-1.5">Connexion API *</label>
                    <select name="api_connection_id" required class="w-full bg-slate-900/50 border border-slate-700 text-slate-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500">
                        <option value="">Sélectionner une connexion</option>
                        @foreach($connections as $c)
                            <option value="{{ $c->id }}" {{ old('api_connection_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                    @error('api_connection_id')<p class="text-xs text-red-400 mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1.5">Nom interne * <span class="text-slate-500 text-xs">(ex: getSales)</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required class="w-full bg-slate-900/50 border border-slate-700 text-slate-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500">
                    @error('name')<p class="text-xs text-red-400 mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1.5">Libellé * <span class="text-slate-500 text-xs">(ex: Ventes du jour)</span></label>
                    <input type="text" name="label" value="{{ old('label') }}" required class="w-full bg-slate-900/50 border border-slate-700 text-slate-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500">
                    @error('label')<p class="text-xs text-red-400 mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="col-span-2">
                    <label class="block text-sm font-medium text-slate-300 mb-1.5">Endpoint API *</label>
                    <div class="flex">
                        <span class="inline-flex items-center px-4 rounded-l-xl border border-r-0 border-slate-700 bg-slate-800 text-slate-400 text-sm">GET/POST</span>
                        <input type="text" name="endpoint" value="{{ old('endpoint') }}" placeholder="/api/endpoint" required class="flex-1 min-w-0 px-4 py-3 rounded-r-xl bg-slate-900/50 border border-slate-700 text-slate-200 focus:ring-indigo-500 text-sm">
                    </div>
                    @error('endpoint')<p class="text-xs text-red-400 mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1.5">Méthode HTTP *</label>
                    <select name="method" required class="w-full bg-slate-900/50 border border-slate-700 text-slate-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500">
                        <option value="GET">GET</option>
                        <option value="POST">POST</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1.5">Mots-clés déclencheurs * <span class="text-slate-500 text-xs">(séparés par virgule)</span></label>
                    <input type="text" name="keywords" value="{{ old('keywords') }}" placeholder="ventes, ca, chiffre" required class="w-full bg-slate-900/50 border border-slate-700 text-slate-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500">
                </div>

                <div class="col-span-2">
                    <label class="block text-sm font-medium text-slate-300 mb-1.5">Description <span class="text-slate-500 text-xs">(optionnelle)</span></label>
                    <textarea name="description" rows="2" class="w-full bg-slate-900/50 border border-slate-700 text-slate-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500">{{ old('description') }}</textarea>
                </div>

                <div class="col-span-2 flex items-center gap-3 pt-4 border-t border-slate-800/50">
                    <input type="checkbox" name="active" id="active-api" value="1" {{ old('active', true) ? 'checked' : '' }} class="w-5 h-5 rounded border-slate-600 bg-slate-800 text-indigo-500">
                    <label for="active-api" class="text-sm text-slate-300 cursor-pointer">Activer cet outil</label>
                </div>
            </div>

            <div class="mt-8 flex justify-end">
                <button type="submit" class="px-6 py-3 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-700 text-white font-medium hover:from-indigo-500 hover:to-purple-600 transition-all shadow-lg shadow-indigo-500/30">
                    Créer l'outil API
                </button>
            </div>
            @endif
        </form>

        {{-- EXCEL FORM --}}
        <form id="form-excel" action="{{ route('tools.store') }}" method="POST" enctype="multipart/form-data" class="hidden">
            @csrf
            <input type="hidden" name="type" value="excel">

            <div class="rounded-xl border-2 border-dashed border-indigo-500/30 bg-indigo-500/5 p-8 text-center mb-6" id="drop-zone">
                <svg class="w-12 h-12 text-indigo-400 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0H3" />
                </svg>
                <p class="text-slate-300 font-medium mb-1">Glissez votre fichier ici</p>
                <p class="text-slate-500 text-sm mb-4">Formats acceptés : .xlsx, .xls, .csv — Max 10 Mo</p>
                <label class="cursor-pointer px-4 py-2 bg-indigo-600/80 hover:bg-indigo-600 text-white text-sm rounded-lg transition-colors">
                    Parcourir…
                    <input type="file" name="excel_file" id="excel-file" accept=".xlsx,.xls,.csv" required class="hidden" onchange="onFileSelected(this)">
                </label>
            </div>

            <div id="file-info" class="hidden mb-6 p-4 bg-emerald-500/10 border border-emerald-500/20 rounded-xl flex items-center gap-3">
                <svg class="w-5 h-5 text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div>
                    <p class="text-sm text-emerald-300" id="file-name">—</p>
                    <p class="text-xs text-slate-400 mt-0.5">Chaque feuille du fichier sera créée comme un outil indépendant.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-slate-300 mb-1.5">Mots-clés déclencheurs * <span class="text-slate-500 text-xs">(séparés par virgule)</span></label>
                    <input type="text" name="keywords" value="{{ old('keywords') }}" placeholder="ventes excel, rapport, liste" class="w-full bg-slate-900/50 border border-slate-700 text-slate-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500">
                    <p class="text-xs text-slate-500 mt-1">Ces mots-clés s'appliqueront à tous les onglets du fichier. Vous pourrez les personnaliser après la création.</p>
                </div>

                <div class="col-span-2">
                    <label class="block text-sm font-medium text-slate-300 mb-1.5">Description <span class="text-slate-500 text-xs">(optionnelle)</span></label>
                    <textarea name="description" rows="2" class="w-full bg-slate-900/50 border border-slate-700 text-slate-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500"></textarea>
                </div>

                <div class="col-span-2 flex items-center gap-3 pt-4 border-t border-slate-800/50">
                    <input type="checkbox" name="active" id="active-excel" value="1" checked class="w-5 h-5 rounded border-slate-600 bg-slate-800 text-indigo-500">
                    <label for="active-excel" class="text-sm text-slate-300 cursor-pointer">Activer cet outil</label>
                </div>
            </div>

            <div class="mt-8 flex justify-end">
                <button type="submit" class="px-6 py-3 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 text-white font-medium hover:from-emerald-500 hover:to-teal-500 transition-all shadow-lg shadow-emerald-500/30">
                    Importer le fichier Excel
                </button>
            </div>
        </form>

    </div>
</div>
@endsection

@push('scripts')
<script>
    function switchType(type) {
        const formApi   = document.getElementById('form-api');
        const formExcel = document.getElementById('form-excel');
        const btnApi    = document.getElementById('btn-api');
        const btnExcel  = document.getElementById('btn-excel');

        if (type === 'api') {
            formApi.classList.remove('hidden');
            formExcel.classList.add('hidden');
            btnApi.classList.add('bg-indigo-600', 'text-white', 'shadow-lg', 'shadow-indigo-500/20');
            btnApi.classList.remove('text-slate-400');
            btnExcel.classList.remove('bg-emerald-600', 'text-white', 'shadow-lg', 'shadow-emerald-500/20');
            btnExcel.classList.add('text-slate-400');
        } else {
            formExcel.classList.remove('hidden');
            formApi.classList.add('hidden');
            btnExcel.classList.add('bg-emerald-600', 'text-white', 'shadow-lg', 'shadow-emerald-500/20');
            btnExcel.classList.remove('text-slate-400');
            btnApi.classList.remove('bg-indigo-600', 'text-white', 'shadow-lg', 'shadow-indigo-500/20');
            btnApi.classList.add('text-slate-400');
        }
    }

    function onFileSelected(input) {
        const file = input.files[0];
        if (!file) return;
        document.getElementById('file-name').textContent = file.name + ' (' + (file.size / 1024).toFixed(0) + ' Ko)';
        document.getElementById('file-info').classList.remove('hidden');
        document.getElementById('drop-zone').classList.add('border-emerald-500/50', 'bg-emerald-500/5');
        document.getElementById('drop-zone').classList.remove('border-indigo-500/30', 'bg-indigo-500/5');
    }

    // Drag and drop
    const dropZone = document.getElementById('drop-zone');
    dropZone.addEventListener('dragover', (e) => { e.preventDefault(); dropZone.classList.add('border-indigo-400'); });
    dropZone.addEventListener('dragleave', () => { dropZone.classList.remove('border-indigo-400'); });
    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        const fileInput = document.getElementById('excel-file');
        fileInput.files = e.dataTransfer.files;
        onFileSelected(fileInput);
    });
</script>
@endpush
