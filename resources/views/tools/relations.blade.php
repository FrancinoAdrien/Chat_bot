@extends('layouts.app')

@section('title', 'Modélisation ERD')
@section('page-title', 'Modélisation des Données')
@section('page-subtitle', 'Liez vos outils API pour que l\'IA puisse croiser les données automatiquement.')

@section('header-actions')
    @if($connections->isNotEmpty())
    <div class="flex items-center gap-3">
        <div class="relative">
            <select id="connection-selector" onchange="window.location.href='?connection_id='+this.value"
                class="appearance-none bg-slate-900/50 border border-slate-700/50 text-slate-200 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-4 pr-10 py-2.5 transition-all hover:bg-slate-800/80 cursor-pointer">
                @foreach($connections as $conn)
                    <option value="{{ $conn->id }}" {{ $selectedConnection?->id === $conn->id ? 'selected' : '' }}>
                        {{ $conn->name }}
                    </option>
                @endforeach
            </select>
            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </div>
        </div>
    </div>
    @endif
@endsection

@section('content')
<div class="space-y-4">

    {{-- Info Bar --}}
    <div class="glass rounded-xl border border-slate-800/50 p-4 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center">
                <svg class="w-5 h-5 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.217 10.907a2.25 2.25 0 100 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186l9.566-5.314m-9.566 7.5l9.566 5.314m0-12.814a2.25 2.25 0 103.935 2.186 2.25 2.25 0 00-3.935-2.186zm0 12.814a2.25 2.25 0 103.933-2.185 2.25 2.25 0 00-3.933 2.185z" />
                </svg>
            </div>
            <div>
                <p class="text-sm text-slate-200 font-medium">Mode ERD — Cliquez sur une colonne (clé primaire), puis sur une autre (clé étrangère) pour créer un lien.</p>
                <p class="text-xs text-slate-500">Les fenêtres sont déplaçables. Les colonnes en surbrillance sont liées.</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <span id="relation-count" class="text-xs px-3 py-1 rounded-full bg-indigo-500/10 border border-indigo-500/20 text-indigo-300">
                {{ $relations->count() }} relation(s)
            </span>
        </div>
    </div>

    @if($tools->isEmpty())
        <div class="glass rounded-xl border border-slate-800/50 p-12 text-center">
            <div class="w-16 h-16 rounded-2xl bg-slate-800 border border-slate-700 flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.336l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085" />
                </svg>
            </div>
            <p class="text-slate-400 text-sm">Aucun outil API actif pour cette connexion.</p>
            <a href="{{ route('tools.create') }}" class="text-indigo-400 text-xs mt-2 inline-block hover:text-indigo-300">
                + Créer un outil API
            </a>
        </div>
    @else
        {{-- ERD Canvas --}}
        <div id="erd-canvas" class="relative glass rounded-xl border border-slate-800/50 overflow-hidden" style="height: calc(100vh - 280px); min-height: 500px;">

            {{-- SVG layer for lines --}}
            <svg id="erd-lines" class="absolute inset-0 w-full h-full pointer-events-none" style="z-index: 1;">
                <defs>
                    <marker id="arrowhead" markerWidth="10" markerHeight="7" refX="10" refY="3.5" orient="auto">
                        <polygon points="0 0, 10 3.5, 0 7" fill="#818cf8" />
                    </marker>
                </defs>
            </svg>

            {{-- Tool Entities (will be loaded via JS) --}}
            <div id="erd-entities" class="absolute inset-0" style="z-index: 2;"></div>

            {{-- Loading overlay --}}
            <div id="erd-loading" class="absolute inset-0 flex items-center justify-center bg-slate-950/50 z-50">
                <div class="flex flex-col items-center gap-3">
                    <div class="w-8 h-8 border-2 border-indigo-500 border-t-transparent rounded-full animate-spin"></div>
                    <p class="text-sm text-slate-400">Chargement des schémas API…</p>
                </div>
            </div>
        </div>

        {{-- Relations List --}}
        <div class="glass rounded-xl border border-slate-800/50 p-4">
            <h3 class="text-sm font-semibold text-slate-200 mb-3">Relations existantes</h3>
            <div id="relations-list" class="space-y-2">
                @forelse($relations as $rel)
                <div class="flex items-center justify-between glass rounded-lg p-3 border border-slate-800/50 group" data-relation-id="{{ $rel->id }}">
                    <div class="flex items-center gap-3 text-xs">
                        <span class="px-2 py-1 rounded bg-indigo-500/10 border border-indigo-500/20 text-indigo-300 font-mono">{{ $rel->primaryTool->label }}.{{ $rel->primary_field }}</span>
                        <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                        </svg>
                        <span class="px-2 py-1 rounded bg-purple-500/10 border border-purple-500/20 text-purple-300 font-mono">{{ $rel->foreignTool->label }}.{{ $rel->foreign_field }}</span>
                    </div>
                    <button onclick="deleteRelation({{ $rel->id }}, this)"
                        class="opacity-0 group-hover:opacity-100 p-1.5 rounded text-slate-500 hover:text-red-400 hover:bg-red-500/10 transition-all">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                        </svg>
                    </button>
                </div>
                @empty
                <p class="text-xs text-slate-600 italic">Aucune relation configurée. Cliquez sur les colonnes ci-dessus pour en créer.</p>
                @endforelse
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
const TOOLS = @json($tools);
const EXISTING_RELATIONS = @json($relations);
const CONNECTION_ID = {{ $selectedConnection?->id ?? 'null' }};
const CSRF = document.querySelector('meta[name=csrf-token]').content;

// State
let toolSchemas = {};      // { toolId: { fields: [...], x, y } }
let selectedField = null;  // { toolId, fieldName, element }
let dragState = null;      // { toolId, startX, startY, origX, origY }

// Initialize
document.addEventListener('DOMContentLoaded', async () => {
    if (!TOOLS.length) return;
    await loadAllSchemas();
    renderEntities();
    renderLines();
    document.getElementById('erd-loading').classList.add('hidden');
});

async function loadAllSchemas() {
    const promises = TOOLS.map(async (tool, index) => {
        try {
            const res = await fetch(`/tool-relations/schema/${tool.id}`, {
                headers: { 'Accept': 'application/json' }
            });
            const json = await res.json();
            toolSchemas[tool.id] = {
                fields: json.success ? json.fields : [],
                label: tool.label || tool.name,
                x: 40 + (index % 3) * 320,
                y: 40 + Math.floor(index / 3) * 280,
                error: json.success ? null : json.message,
            };
        } catch (e) {
            toolSchemas[tool.id] = {
                fields: [],
                label: tool.label || tool.name,
                x: 40 + (index % 3) * 320,
                y: 40 + Math.floor(index / 3) * 280,
                error: 'Impossible de récupérer le schéma.',
            };
        }
    });
    await Promise.all(promises);
}

function renderEntities() {
    const container = document.getElementById('erd-entities');
    container.innerHTML = '';

    for (const [toolId, schema] of Object.entries(toolSchemas)) {
        const entity = document.createElement('div');
        entity.className = 'absolute select-none';
        entity.style.left = schema.x + 'px';
        entity.style.top = schema.y + 'px';
        entity.style.minWidth = '240px';
        entity.dataset.toolId = toolId;

        // Find which fields are involved in relations
        const relatedFields = getRelatedFields(parseInt(toolId));

        let fieldsHtml = '';
        if (schema.error) {
            fieldsHtml = `<div class="px-3 py-2 text-xs text-red-400 italic">${schema.error}</div>`;
        } else if (schema.fields.length === 0) {
            fieldsHtml = `<div class="px-3 py-2 text-xs text-slate-600 italic">Aucun champ détecté</div>`;
        } else {
            schema.fields.forEach(field => {
                const isPK = relatedFields.primary.includes(field);
                const isFK = relatedFields.foreign.includes(field);
                const highlight = isPK ? 'bg-indigo-500/15 border-l-2 border-l-indigo-400' :
                                  isFK ? 'bg-purple-500/15 border-l-2 border-l-purple-400' : 'border-l-2 border-l-transparent';
                const badge = isPK ? '<span class="text-[9px] px-1 py-0.5 rounded bg-indigo-500/20 text-indigo-300 font-bold">PK</span>' :
                              isFK ? '<span class="text-[9px] px-1 py-0.5 rounded bg-purple-500/20 text-purple-300 font-bold">FK</span>' : '';

                fieldsHtml += `
                    <div class="erd-field flex items-center justify-between px-3 py-1.5 cursor-pointer hover:bg-slate-700/50 transition-colors ${highlight}"
                         data-tool-id="${toolId}" data-field="${field}"
                         onclick="onFieldClick(this, ${toolId}, '${field}')">
                        <span class="text-xs text-slate-300 font-mono">${field}</span>
                        ${badge}
                    </div>`;
            });
        }

        entity.innerHTML = `
            <div class="rounded-lg overflow-hidden shadow-xl shadow-black/30 border border-slate-700/50 bg-slate-900/95 backdrop-blur-sm">
                <div class="erd-header flex items-center justify-between px-3 py-2.5 bg-gradient-to-r from-indigo-600/20 to-purple-600/20 border-b border-slate-700/50 cursor-move"
                     onmousedown="startDrag(event, ${toolId})">
                    <div class="flex items-center gap-2">
                        <svg class="w-3.5 h-3.5 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.375 19.5h17.25m-17.25 0a1.125 1.125 0 01-1.125-1.125M3.375 19.5h7.5c.621 0 1.125-.504 1.125-1.125m-9.75 0V5.625m0 12.75v-1.5c0-.621.504-1.125 1.125-1.125m18.375 2.625V5.625m0 12.75c0 .621-.504 1.125-1.125 1.125m1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125m0 3.75h-7.5A1.125 1.125 0 0112 18.375m9.75 0V5.625m0 0A1.125 1.125 0 0020.625 4.5h-1.5B18 4.5h-7.5A1.125 1.125 0 009.375 5.625m11.25 0v3.375" />
                        </svg>
                        <span class="text-xs font-bold text-slate-100 uppercase tracking-wider">${schema.label}</span>
                    </div>
                    <span class="text-[10px] text-slate-500">${schema.fields.length} col.</span>
                </div>
                <div class="max-h-[250px] overflow-y-auto divide-y divide-slate-800/50">
                    ${fieldsHtml}
                </div>
            </div>`;

        container.appendChild(entity);
    }
}

function getRelatedFields(toolId) {
    const primary = [];
    const foreign = [];
    EXISTING_RELATIONS.forEach(rel => {
        if (rel.primary_tool_id === toolId) primary.push(rel.primary_field);
        if (rel.foreign_tool_id === toolId) foreign.push(rel.foreign_field);
    });
    return { primary, foreign };
}

// ─── Field Click (Select PK then FK) ───
function onFieldClick(element, toolId, fieldName) {
    if (!selectedField) {
        // First click — mark as PK
        selectedField = { toolId, fieldName, element };
        element.classList.add('ring-2', 'ring-indigo-400', 'bg-indigo-500/20');
        element.style.outline = '2px solid #818cf8';
    } else {
        // Second click — must be a different tool
        if (selectedField.toolId == toolId) {
            // Same tool — deselect
            selectedField.element.classList.remove('ring-2', 'ring-indigo-400', 'bg-indigo-500/20');
            selectedField.element.style.outline = '';
            selectedField = null;
            return;
        }

        // Create the relation: selectedField = PK, this = FK
        createRelation(
            selectedField.toolId,
            selectedField.fieldName,
            toolId,
            fieldName
        );

        selectedField.element.classList.remove('ring-2', 'ring-indigo-400', 'bg-indigo-500/20');
        selectedField.element.style.outline = '';
        selectedField = null;
    }
}

async function createRelation(primaryToolId, primaryField, foreignToolId, foreignField) {
    try {
        const res = await fetch('/tool-relations', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF,
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                api_connection_id: CONNECTION_ID,
                primary_tool_id: primaryToolId,
                primary_field: primaryField,
                foreign_tool_id: foreignToolId,
                foreign_field: foreignField,
            }),
        });

        const json = await res.json();
        if (!res.ok) {
            showToast(json.message || 'Erreur lors de la création.', 'error');
            return;
        }

        // Add to state
        EXISTING_RELATIONS.push(json.relation);
        renderEntities();
        renderLines();
        addRelationToList(json.relation);
        updateRelationCount();
        showToast('Relation créée avec succès !', 'success');
    } catch (e) {
        showToast('Erreur réseau.', 'error');
    }
}

async function deleteRelation(relationId, btn) {
    if (!confirm('Supprimer cette relation ?')) return;
    try {
        const res = await fetch(`/tool-relations/${relationId}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        });

        if (res.ok) {
            const idx = EXISTING_RELATIONS.findIndex(r => r.id === relationId);
            if (idx !== -1) EXISTING_RELATIONS.splice(idx, 1);
            btn.closest('[data-relation-id]').remove();
            renderEntities();
            renderLines();
            updateRelationCount();
            showToast('Relation supprimée.', 'success');
        }
    } catch (e) {
        showToast('Erreur lors de la suppression.', 'error');
    }
}

// ─── SVG Lines ───
function renderLines() {
    const svg = document.getElementById('erd-lines');
    // Remove all existing lines
    svg.querySelectorAll('line, path').forEach(el => el.remove());

    EXISTING_RELATIONS.forEach(rel => {
        const fromEl = document.querySelector(`[data-tool-id="${rel.primary_tool_id}"][data-field="${rel.primary_field}"]`);
        const toEl = document.querySelector(`[data-tool-id="${rel.foreign_tool_id}"][data-field="${rel.foreign_field}"]`);

        if (!fromEl || !toEl) return;

        const canvas = document.getElementById('erd-canvas');
        const canvasRect = canvas.getBoundingClientRect();
        const fromRect = fromEl.getBoundingClientRect();
        const toRect = toEl.getBoundingClientRect();

        const x1 = fromRect.right - canvasRect.left;
        const y1 = fromRect.top + fromRect.height / 2 - canvasRect.top;
        const x2 = toRect.left - canvasRect.left;
        const y2 = toRect.top + toRect.height / 2 - canvasRect.top;

        // Bezier curve for smooth line
        const midX = (x1 + x2) / 2;
        const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
        path.setAttribute('d', `M ${x1} ${y1} C ${midX} ${y1}, ${midX} ${y2}, ${x2} ${y2}`);
        path.setAttribute('stroke', '#818cf8');
        path.setAttribute('stroke-width', '2');
        path.setAttribute('fill', 'none');
        path.setAttribute('stroke-dasharray', '6,3');
        path.setAttribute('marker-end', 'url(#arrowhead)');
        path.style.pointerEvents = 'none';

        svg.appendChild(path);
    });
}

// ─── Drag & Drop ───
function startDrag(event, toolId) {
    if (event.target.closest('.erd-field')) return;
    event.preventDefault();

    const entity = document.querySelector(`[data-tool-id="${toolId}"]`).closest('.absolute');
    const canvas = document.getElementById('erd-canvas');
    const canvasRect = canvas.getBoundingClientRect();

    dragState = {
        toolId,
        entity,
        startX: event.clientX,
        startY: event.clientY,
        origX: parseInt(entity.style.left) || 0,
        origY: parseInt(entity.style.top) || 0,
    };

    document.addEventListener('mousemove', onDrag);
    document.addEventListener('mouseup', stopDrag);
}

function onDrag(event) {
    if (!dragState) return;
    const dx = event.clientX - dragState.startX;
    const dy = event.clientY - dragState.startY;

    dragState.entity.style.left = Math.max(0, dragState.origX + dx) + 'px';
    dragState.entity.style.top = Math.max(0, dragState.origY + dy) + 'px';

    // Update stored position
    toolSchemas[dragState.toolId].x = Math.max(0, dragState.origX + dx);
    toolSchemas[dragState.toolId].y = Math.max(0, dragState.origY + dy);

    renderLines();
}

function stopDrag() {
    dragState = null;
    document.removeEventListener('mousemove', onDrag);
    document.removeEventListener('mouseup', stopDrag);
}

// ─── UI Helpers ───
function addRelationToList(rel) {
    const container = document.getElementById('relations-list');
    const empty = container.querySelector('.italic');
    if (empty) empty.remove();

    const primaryLabel = toolSchemas[rel.primary_tool_id]?.label || 'Tool';
    const foreignLabel = toolSchemas[rel.foreign_tool_id]?.label || 'Tool';

    const html = `
    <div class="flex items-center justify-between glass rounded-lg p-3 border border-slate-800/50 group" data-relation-id="${rel.id}">
        <div class="flex items-center gap-3 text-xs">
            <span class="px-2 py-1 rounded bg-indigo-500/10 border border-indigo-500/20 text-indigo-300 font-mono">${primaryLabel}.${rel.primary_field}</span>
            <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
            </svg>
            <span class="px-2 py-1 rounded bg-purple-500/10 border border-purple-500/20 text-purple-300 font-mono">${foreignLabel}.${rel.foreign_field}</span>
        </div>
        <button onclick="deleteRelation(${rel.id}, this)"
            class="opacity-0 group-hover:opacity-100 p-1.5 rounded text-slate-500 hover:text-red-400 hover:bg-red-500/10 transition-all">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
            </svg>
        </button>
    </div>`;
    container.insertAdjacentHTML('beforeend', html);
}

function updateRelationCount() {
    document.getElementById('relation-count').textContent = EXISTING_RELATIONS.length + ' relation(s)';
}

function showToast(message, type) {
    const toast = document.createElement('div');
    toast.className = `fixed bottom-6 right-6 z-[999] px-4 py-3 rounded-lg text-sm font-medium shadow-xl transition-all ${
        type === 'success' ? 'bg-emerald-600 text-white' : 'bg-red-600 text-white'
    }`;
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}
</script>
@endpush
