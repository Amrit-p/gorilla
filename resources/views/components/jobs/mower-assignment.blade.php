@props([
    'employees' => [],
    'primaryMowerId' => null,
    'assignedIds' => [],
    'idPrefix' => 'job',
])

@php
    $initPrimaryId = (string) old('done_by_user_id', $primaryMowerId ?? '');
    $initHelperIds = array_values(array_filter(array_map('strval', (array) old('employee_ids', $assignedIds))));
    $employeesData = collect($employees)
        ->map(
            fn($e) => [
                'id' => (string) $e->id,
                'name' => $e->name,
                'efficiency' => $e->efficiency ?? 'Average',
                'initials' => mb_strtoupper(mb_substr($e->name, 0, 1)),
            ],
        )
        ->values()
        ->all();
    $mcaInitData = [
        'primaryId' => $initPrimaryId,
        'helperIds' => $initHelperIds,
        'employees' => $employeesData,
    ];
@endphp

{{-- Hidden inputs owned by JS; server seeds initial state for pre-JS safety --}}
<input type="hidden" name="done_by_user_id" id="{{ $idPrefix }}-primary-hidden" value="{{ $initPrimaryId }}">
<div id="{{ $idPrefix }}-helper-hiddens">
    @foreach ($initHelperIds as $hid)
        <input type="hidden" name="employee_ids[]" value="{{ $hid }}">
    @endforeach
</div>

<div class="sm:col-span-2">
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm"
        id="{{ $idPrefix }}-mower-widget">
        <div class="grid grid-cols-1 sm:grid-cols-2 divide-y divide-slate-100 sm:divide-x sm:divide-y-0">

            {{-- Left: Available Pool --}}
            <div class="flex flex-col p-3">
                <div class="mb-3 flex items-center justify-between">
                    <div class="flex items-center gap-1.5">
                        <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span class="text-sm font-semibold text-slate-600">Available</span>
                    </div>
                    <span id="{{ $idPrefix }}-pool-count"
                        class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-500">0</span>
                </div>
                <div id="{{ $idPrefix }}-pool"
                    class="mca-zone flex-1 overflow-y-auto rounded-lg border-2 border-dashed border-slate-200 bg-slate-50 p-1.5"
                    data-zone="pool" style="min-height:200px;max-height:300px;"></div>
            </div>

            {{-- Right: Primary + Helpers --}}
            <div class="flex flex-col gap-3 p-3">

                {{-- Primary Mower --}}
                <div>
                    <div class="mb-2 flex items-center gap-1.5">
                        <span
                            class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-amber-500 text-white"
                            style="font-size:11px;">★</span>
                        <span class="text-sm font-semibold text-slate-600">Primary Mower</span>
                        <span class="ml-auto text-xs text-slate-400">one slot</span>
                    </div>
                    <div id="{{ $idPrefix }}-primary-zone"
                        class="mca-zone rounded-lg border-2 border-dashed border-amber-200 p-1.5"
                        style="background:rgba(255,251,235,.5);min-height:68px;" data-zone="primary"></div>
                </div>

                {{-- Helper Mowers --}}
                <div class="flex flex-col flex-1">
                    <div class="mb-2 flex items-center gap-1.5">
                        <span
                            class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-sky-500 font-bold text-white"
                            style="font-size:13px;">+</span>
                        <span class="text-sm font-semibold text-slate-600">Helper Mowers</span>
                        <span id="{{ $idPrefix }}-helper-count"
                            class="ml-auto rounded-full bg-sky-100 px-2 py-0.5 text-xs font-medium text-sky-600">0</span>
                    </div>
                    <div id="{{ $idPrefix }}-helper-zone"
                        class="mca-zone flex-1 overflow-y-auto rounded-lg border-2 border-dashed border-sky-200 p-1.5"
                        style="background:rgba(240,249,255,.5);min-height:100px;max-height:220px;" data-zone="helpers">
                    </div>
                </div>

            </div>
        </div>

        <div class="border-t border-slate-100 bg-slate-50/80 px-4 py-2 text-center text-xs text-slate-400">
            Drag employees to assign &middot; Click an assigned card to remove
        </div>
    </div>
</div>

@once
    <style>
        .mca-card {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-radius: .5rem;
            border: 1px solid;
            padding: .45rem .75rem;
            margin-bottom: .3rem;
            cursor: grab;
            user-select: none;
            background: #fff;
            transition: box-shadow .15s, border-color .15s;
            box-shadow: 0 1px 2px rgba(0, 0, 0, .05);
        }

        .mca-card:last-child {
            margin-bottom: 0;
        }

        .mca-card:active {
            cursor: grabbing;
        }

        .mca-card:hover {
            box-shadow: 0 2px 8px rgba(0, 0, 0, .1);
        }

        .sortable-ghost {
            opacity: .3;
        }

        .mca-card.zone-pool {
            border-color: #e2e8f0;
        }

        .mca-card.zone-pool:hover {
            border-color: #cbd5e1;
        }

        .mca-card.zone-primary {
            border-color: #fcd34d;
        }

        .mca-card.zone-primary:hover {
            border-color: #f59e0b;
        }

        .mca-card.zone-helpers {
            border-color: #bae6fd;
        }

        .mca-card.zone-helpers:hover {
            border-color: #38bdf8;
        }

        .mca-avatar {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            font-size: 11px;
            font-weight: 700;
            flex-shrink: 0;
        }

        .zone-pool .mca-avatar {
            background: #e2e8f0;
            color: #475569;
        }

        .zone-primary .mca-avatar {
            background: #f59e0b;
            color: #fff;
        }

        .zone-helpers .mca-avatar {
            background: #38bdf8;
            color: #fff;
        }

        .mca-badge {
            border-radius: 9999px;
            padding: 1px 7px;
            font-size: .69rem;
        }

        .zone-pool .mca-badge {
            background: #f1f5f9;
            color: #64748b;
        }

        .zone-primary .mca-badge {
            background: #fef3c7;
            color: #92400e;
        }

        .zone-helpers .mca-badge {
            background: #e0f2fe;
            color: #0369a1;
        }

        .mca-lead {
            background: #f59e0b;
            color: #fff;
            border-radius: 9999px;
            padding: 1px 7px;
            font-size: .69rem;
            font-weight: 600;
        }

        .mca-drag-hint {
            opacity: 0;
            transition: opacity .15s;
            color: #cbd5e1;
        }

        .mca-card:hover .mca-drag-hint {
            opacity: 1;
        }

        .mca-zone {
            transition: border-color .15s, background-color .15s;
        }

        .mca-empty {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 1.1rem 0;
            font-size: .75rem;
            gap: 4px;
        }
    </style>
@endonce

<script>
    (function() {
        const PREFIX = '{{ $idPrefix }}';
        const INIT = @json($mcaInitData);

        let $pool, $primary, $helpers, $primaryHidden, $helperHiddens;

        const ICONS = {
            users: `<svg style="width:24px;height:24px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>`,
            user: `<svg style="width:20px;height:20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>`,
            plus: `<svg style="width:20px;height:20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>`,
            drag: `<svg class="mca-drag-hint" style="width:13px;height:13px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/></svg>`,
        };

        const EMPTY_CFG = {
            pool: {
                color: '#94a3b8',
                icon: ICONS.users,
                text: 'All employees assigned'
            },
            primary: {
                color: '#f59e0b',
                icon: ICONS.user,
                text: 'Drag or click to assign lead'
            },
            helpers: {
                color: '#38bdf8',
                icon: ICONS.plus,
                text: 'Drag helpers here'
            },
        };

        function makeCard(emp, zone) {
            const el = document.createElement('div');
            el.className = `mca-card zone-${zone}`;
            el.dataset.id = emp.id;
            el.innerHTML = `
            <div style="display:flex;align-items:center;gap:8px;min-width:0;">
                <div class="mca-avatar">${emp.initials}</div>
                <span style="font-size:.875rem;font-weight:500;color:#1e293b;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${emp.name}</span>
            </div>
            <div class="mca-badges" style="display:flex;align-items:center;gap:5px;flex-shrink:0;">
                <span class="mca-badge">${emp.efficiency}</span>
                ${zone === 'primary' ? '<span class="mca-lead">Lead</span>' : ''}
                ${ICONS.drag}
            </div>`;
            return el;
        }

        function makeEmpty(zone) {
            const cfg = EMPTY_CFG[zone];
            const el = document.createElement('div');
            el.className = 'mca-empty';
            el.style.color = cfg.color;
            el.innerHTML = cfg.icon + `<span>${cfg.text}</span>`;
            return el;
        }

        /* Update zone-specific classes and Lead badge when a card moves zones */
        function applyZone(card, zone) {
            card.classList.remove('zone-pool', 'zone-primary', 'zone-helpers');
            card.classList.add(`zone-${zone}`);
            const lead = card.querySelector('.mca-lead');
            if (zone === 'primary' && !lead) {
                card.querySelector('.mca-badges').insertAdjacentHTML('beforeend',
                    '<span class="mca-lead">Lead</span>');
            } else if (zone !== 'primary' && lead) {
                lead.remove();
            }
        }

        function syncAll() {
            /* Empty-state placeholders */
            [$pool, $primary, $helpers].forEach(el => {
                const zone = el.dataset.zone;
                const hasCards = !!el.querySelector('.mca-card');
                const empty = el.querySelector('.mca-empty');
                if (hasCards && empty) empty.remove();
                if (!hasCards && !empty) el.appendChild(makeEmpty(zone));
            });

            /* Counts */
            document.getElementById(`${PREFIX}-pool-count`).textContent = $pool.querySelectorAll('.mca-card')
            .length;
            document.getElementById(`${PREFIX}-helper-count`).textContent = $helpers.querySelectorAll('.mca-card')
                .length;

            /* Hidden inputs */
            const pc = $primary.querySelector('.mca-card');
            $primaryHidden.value = pc ? pc.dataset.id : '';
            $helperHiddens.innerHTML = '';
            $helpers.querySelectorAll('.mca-card').forEach(c => {
                const inp = document.createElement('input');
                inp.type = 'hidden';
                inp.name = 'employee_ids[]';
                inp.value = c.dataset.id;
                $helperHiddens.appendChild(inp);
            });
        }

        /* Move a card to a target zone element and sync */
        function moveCard(card, targetEl) {
            targetEl.appendChild(card);
            applyZone(card, targetEl.dataset.zone);
            syncAll();
        }

        function init() {
            $pool = document.getElementById(`${PREFIX}-pool`);
            $primary = document.getElementById(`${PREFIX}-primary-zone`);
            $helpers = document.getElementById(`${PREFIX}-helper-zone`);
            $primaryHidden = document.getElementById(`${PREFIX}-primary-hidden`);
            $helperHiddens = document.getElementById(`${PREFIX}-helper-hiddens`);
            if (!$pool) return;

            /* Render initial cards into their starting zones */
            const zoneEls = {
                pool: $pool,
                primary: $primary,
                helpers: $helpers
            };
            for (const emp of (INIT.employees || [])) {
                let zone = 'pool';
                if (emp.id === INIT.primaryId) zone = 'primary';
                else if ((INIT.helperIds || []).includes(emp.id)) zone = 'helpers';
                zoneEls[zone].appendChild(makeCard(emp, zone));
            }
            syncAll();

            /* SortableJS — shared onAdd handler applies zone styling after every drop */
            const group = `mca-${PREFIX}`;
            const onAdd = function(evt) {
                applyZone(evt.item, evt.to.dataset.zone);
                syncAll();
            };

            Sortable.create($pool, {
                group,
                animation: 150,
                onAdd
            });
            Sortable.create($helpers, {
                group,
                animation: 150,
                onAdd
            });
            Sortable.create($primary, {
                animation: 150,
                group: {
                    name: group,
                    pull: true,
                    /* Only accept a drop when the primary slot is empty */
                    put: to => !to.el.querySelector('.mca-card:not(.sortable-ghost)'),
                },
                onAdd,
            });

            /* Click to assign (pool → primary/helpers) or unassign (primary/helpers → pool) */
            [$pool, $primary, $helpers].forEach(zone => {
                zone.addEventListener('click', e => {
                    const card = e.target.closest('.mca-card');
                    if (!card) return;
                    if (card.closest('.mca-zone') === $pool) {
                        moveCard(card, !$primary.querySelector('.mca-card') ? $primary : $helpers);
                    } else {
                        moveCard(card, $pool);
                    }
                });
            });

            /* External reset API used by modal open handlers */
            window[`mcaReset_${PREFIX}`] = (newPrimaryId, newHelperIds) => {
                [...$primary.querySelectorAll('.mca-card'), ...$helpers.querySelectorAll('.mca-card')].forEach(
                    c => {
                        applyZone(c, 'pool');
                        $pool.appendChild(c);
                    });
                if (newPrimaryId) {
                    const c = $pool.querySelector(`.mca-card[data-id="${newPrimaryId}"]`);
                    if (c) {
                        $primary.appendChild(c);
                        applyZone(c, 'primary');
                    }
                }
                (newHelperIds || []).forEach(id => {
                    const c = $pool.querySelector(`.mca-card[data-id="${String(id)}"]`);
                    if (c) {
                        $helpers.appendChild(c);
                        applyZone(c, 'helpers');
                    }
                });
                syncAll();
            };
        }

        if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
        else init();
    })();
</script>
