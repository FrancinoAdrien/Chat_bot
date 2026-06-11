@extends('layouts.app')

@section('title', 'Modélisation ERD')
@section('page-title', 'Modélisation des Données')
@section('page-subtitle', 'Liez vos outils API pour que l\'IA puisse croiser les données automatiquement.')

@section('header-actions')
    <a href="{{ route('tools.create') }}" class="btn btn-primary btn-sm hover-lift">+ Nouvel outil</a>
@endsection

@section('content')
<div class="page erd-page">

    <div class="erd-toolbar">
        <div class="flex items-center gap-3">
            <div class="avatar-chip">
                <svg class="icon-sm text-accent" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.217 10.907a2.25 2.25 0 100 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186l9.566-5.314m-9.566 7.5l9.566 5.314m0-12.814a2.25 2.25 0 103.935 2.186 2.25 2.25 0 00-3.935-2.186zm0 12.814a2.25 2.25 0 103.933-2.185 2.25 2.25 0 00-3.933 2.185z" />
                </svg>
            </div>
            <div>
                <p class="text-sm font-medium">Mode ERD — Cliquez sur une colonne (clé primaire), puis sur une autre (clé étrangère) pour créer un lien.</p>
                <p class="form-hint">Les fenêtres sont déplaçables. Les colonnes en surbrillance sont liées.</p>
            </div>
        </div>
        <div class="flex items-center gap-3 flex-wrap">
            <input id="erd-search" type="search" placeholder="Rechercher table..." class="form-control form-control--sm" style="min-width:12rem;" />
            <div class="erd-controls-group">
                <button type="button" onclick="zoom(-0.1)" class="erd-control-btn">−</button>
                <button type="button" onclick="zoom(0.1)" class="erd-control-btn">+</button>
                <button type="button" onclick="resetZoom()" class="btn btn-primary btn-sm">Reset</button>
            </div>
            <span id="relation-count" class="badge badge--accent">{{ $relations->count() }} relation(s)</span>
        </div>
    </div>

    @if($tools->isEmpty())
        <div class="empty-state panel panel__body">
            <div class="empty-state__icon">
                <svg class="icon-lg text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.336l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085" />
                </svg>
            </div>
            <p class="empty-state__text">Aucun outil configuré.</p>
            <a href="{{ route('tools.create') }}" class="link-action">+ Créer un outil</a>
        </div>
    @else
        <div id="erd-canvas" class="erd-canvas">
            <div id="erd-stage" class="erd-stage-wrap" style="transform: scale(1);">
                <svg id="erd-lines" class="erd-connections" style="width:100%;height:100%;">
                    <defs>
                        <marker id="arrowhead" markerWidth="10" markerHeight="7" refX="10" refY="3.5" orient="auto">
                            <polygon points="0 0, 10 3.5, 0 7" fill="var(--color-accent-primary)" />
                        </marker>
                    </defs>
                </svg>
                <div id="erd-entities" style="position:absolute;inset:0;z-index:2;"></div>
            </div>
            <div id="erd-loading" class="erd-loading-overlay">
                <div class="flex flex-col items-center gap-3">
                    <svg class="loading-spinner icon-lg text-accent" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle style="opacity:0.25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path style="opacity:0.75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    <p class="text-sm text-muted">Chargement des schémas API…</p>
                </div>
            </div>
        </div>

        <div class="panel panel__body">
            <h3 class="form-section__title">Relations existantes</h3>
            <div id="relations-list" class="space-y-4" style="margin-top:0.75rem;">
                @forelse($relations as $rel)
                <div class="erd-relation-item" data-relation-id="{{ $rel->id }}">
                    <div class="flex items-center gap-3 text-xs">
                        <span class="badge badge--accent font-mono">{{ $rel->primaryTool->label }}.{{ $rel->primary_field }}</span>
                        <svg class="icon-sm text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                        </svg>
                        <span class="badge badge--purple font-mono">{{ $rel->foreignTool->label }}.{{ $rel->foreign_field }}</span>
                    </div>
                    <button type="button" onclick="deleteRelation({{ $rel->id }}, this)" class="erd-relation-delete" aria-label="Supprimer">
                        <svg class="icon-sm" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                        </svg>
                    </button>
                </div>
                @empty
                <p class="form-hint italic">Aucune relation configurée. Cliquez sur les colonnes ci-dessus pour en créer.</p>
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
const CSRF = document.querySelector('meta[name=csrf-token]').content;

// State
let toolSchemas = {};      // { toolId: { fields: [...], x, y } }
let selectedField = null;  // { toolId, fieldName, element }
let dragState = null;      // { toolId, startX, startY, origX, origY }
let currentZoom = 1;
let erdSearchQuery = '';
const MIN_ZOOM = 0.5;
const MAX_ZOOM = 2;

// Initialize
document.addEventListener('DOMContentLoaded', async () => {
    if (!TOOLS.length) return;
    await loadAllSchemas();
    renderEntities();
    renderLines();
    document.getElementById('erd-loading').classList.add('hidden');

    const searchInput = document.getElementById('erd-search');
    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            erdSearchQuery = e.target.value.trim().toLowerCase();
            updateEntityHighlight(erdSearchQuery);
        });
    }
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
    const stage = document.getElementById('erd-stage');
    container.innerHTML = '';

    let maxRight = 0;
    let maxBottom = 0;
    const padding = 240;
    const minStageWidth = Math.max(1200, Object.keys(toolSchemas).length * 240);
    const minStageHeight = 800;

    for (const [toolId, schema] of Object.entries(toolSchemas)) {
        const entity = document.createElement('div');
        entity.className = 'erd-entity';
        entity.style.left = schema.x + 'px';
        entity.style.top = schema.y + 'px';
        entity.dataset.toolId = toolId;

        const match = erdSearchQuery !== '' && schema.label.toLowerCase().includes(erdSearchQuery);
        if (match) entity.classList.add('is-search-match');

        // Find which fields are involved in relations
        const relatedFields = getRelatedFields(parseInt(toolId));

        let fieldsHtml = '';
        if (schema.error) {
            fieldsHtml = `<div class="erd-field-item"><span class="text-xs text-danger italic">${schema.error}</span></div>`;
        } else if (schema.fields.length === 0) {
            fieldsHtml = `<div class="erd-field-item"><span class="text-xs text-muted italic">Aucun champ détecté</span></div>`;
        } else {
            schema.fields.forEach(field => {
                const isPK = relatedFields.primary.includes(field);
                const isFK = relatedFields.foreign.includes(field);
                const classes = ['erd-field-item'];
                if (isPK) classes.push('is-pk');
                if (isFK) classes.push('is-fk');
                const badge = isPK ? '<span class="erd-field-badge erd-field-badge--pk">PK</span>' :
                              isFK ? '<span class="erd-field-badge erd-field-badge--fk">FK</span>' : '';

                fieldsHtml += `
                    <div class="${classes.join(' ')} erd-field"
                         data-tool-id="${toolId}" data-field="${field}"
                         onclick="onFieldClick(this, ${toolId}, '${field}')">
                        <span class="text-xs font-mono">${field}</span>
                        ${badge}
                    </div>`;
            });
        }

        entity.innerHTML = `
            <div class="erd-entity-card">
                <div class="erd-entity-card__header" onmousedown="startDrag(event, ${toolId})">
                    <span class="erd-entity-card__title">${schema.label}</span>
                    <span class="erd-entity-card__meta">${schema.fields.length} col.</span>
                </div>
                <div class="erd-entity-card__body">${fieldsHtml}</div>
            </div>`;

        container.appendChild(entity);

        const entityRect = entity.getBoundingClientRect();
        const stageRect = stage.getBoundingClientRect();
        const right = schema.x + Math.max(entityRect.width, 280);
        const bottom = schema.y + entityRect.height;

        maxRight = Math.max(maxRight, right);
        maxBottom = Math.max(maxBottom, bottom);
    }

    stage.style.width = Math.max(minStageWidth, maxRight + padding) + 'px';
    stage.style.height = Math.max(minStageHeight, maxBottom + padding) + 'px';
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
        element.classList.add('is-selected');
    } else {
        // Second click — must be a different tool
        if (selectedField.toolId == toolId) {
            // Same tool — deselect
            selectedField.element.classList.remove('is-selected');
            selectedField = null;
            return;
        }

        createRelation(
            selectedField.toolId,
            selectedField.fieldName,
            toolId,
            fieldName
        );

        selectedField.element.classList.remove('is-selected');
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
                api_connection_id: null,
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
    svg.querySelectorAll('line, path').forEach(el => el.remove());

    const canvas = document.getElementById('erd-canvas');
    const canvasRect = canvas.getBoundingClientRect();
    const stage = document.getElementById('erd-stage');

    EXISTING_RELATIONS.forEach(rel => {
        const fromEl = document.querySelector(`[data-tool-id="${rel.primary_tool_id}"][data-field="${rel.primary_field}"]`);
        const toEl = document.querySelector(`[data-tool-id="${rel.foreign_tool_id}"][data-field="${rel.foreign_field}"]`);

        if (!fromEl || !toEl) return;

        const fromRect = fromEl.getBoundingClientRect();
        const toRect = toEl.getBoundingClientRect();

        const x1 = (fromRect.right - canvasRect.left) / currentZoom;
        const y1 = (fromRect.top + fromRect.height / 2 - canvasRect.top) / currentZoom;
        const x2 = (toRect.left - canvasRect.left) / currentZoom;
        const y2 = (toRect.top + toRect.height / 2 - canvasRect.top) / currentZoom;

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

function zoom(delta) {
    currentZoom = Math.min(MAX_ZOOM, Math.max(MIN_ZOOM, currentZoom + delta));
    const stage = document.getElementById('erd-stage');
    stage.style.transform = `scale(${currentZoom})`;
    renderLines();
}

function resetZoom() {
    currentZoom = 1;
    document.getElementById('erd-stage').style.transform = 'scale(1)';
    renderLines();
}

function updateEntityHighlight(query) {
    document.querySelectorAll('#erd-entities > .erd-entity').forEach(entityWrapper => {
        const toolId = entityWrapper.dataset.toolId;
        const label = toolSchemas[toolId]?.label?.toLowerCase() || '';
        const match = query !== '' && label.includes(query);
        if (match) {
            entityWrapper.classList.add('is-search-match');
            entityWrapper.scrollIntoView({ behavior: 'smooth', block: 'center', inline: 'center' });
        } else {
            entityWrapper.classList.remove('is-search-match');
        }
    });
}

// ─── Drag & Drop ───
function startDrag(event, toolId) {
    if (event.target.closest('.erd-field')) return;
    event.preventDefault();

    const entity = document.querySelector(`[data-tool-id="${toolId}"]`).closest('.erd-entity');
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
    <div class="erd-relation-item" data-relation-id="${rel.id}">
        <div class="flex items-center gap-3 text-xs">
            <span class="badge badge--accent font-mono">${primaryLabel}.${rel.primary_field}</span>
            <svg class="icon-sm text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
            </svg>
            <span class="badge badge--purple font-mono">${foreignLabel}.${rel.foreign_field}</span>
        </div>
        <button type="button" onclick="deleteRelation(${rel.id}, this)" class="erd-relation-delete" aria-label="Supprimer">
            <svg class="icon-sm" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
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
    toast.className = `toast toast--${type === 'success' ? 'success' : 'error'}`;
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}
</script>
@endpush
