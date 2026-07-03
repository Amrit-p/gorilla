<x-layouts.dashboard title="Checklists">
    <div class="space-y-5">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Checklists</h2>
                <p class="text-sm text-slate-600">Manage checklists and their points.</p>
            </div>
            <button type="button" id="open-create-checklist" class="rounded-md bg-slate-900 px-3 py-2 text-sm font-medium text-white hover:bg-slate-700">
                Add Checklist
            </button>
        </div>

        <div id="checklist-alert" class="hidden"></div>

        <form id="checklist-filter-form" class="flex flex-wrap gap-2">
            <input type="text" name="search" value="{{ $filters['search'] }}"
                placeholder="Search by name…"
                class="filter rounded-md border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400" />
            <button type="submit" class="rounded-md bg-slate-800 px-3 py-2 text-sm font-medium text-white hover:bg-slate-700">
                Search
            </button>
        </form>

        <div id="checklist-table-container" class="relative">
            @include('admin.masters.checklists.partials.table', ['records' => $records])
        </div>
    </div>

    <style>
        #checklist-loading-overlay {
            position: absolute;
            inset: 0;
            z-index: 20;
            background: rgba(255, 255, 255, 0.72);
            backdrop-filter: blur(3px);
            -webkit-backdrop-filter: blur(3px);
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.5rem;
        }
        #checklist-loading-overlay .checklist-spinner {
            width: 28px;
            height: 28px;
            border: 2.5px solid #e0e7ff;
            border-top-color: #4f46e5;
            border-radius: 50%;
            animation: checklist-spin 0.7s linear infinite;
        }
        @keyframes checklist-spin {
            to { transform: rotate(360deg); }
        }
    </style>

    <x-ui.modal id="checklist-form-modal" title="Add Checklist" maxWidth="max-w-5xl">
        @include('admin.masters.checklists.partials.form')
    </x-ui.modal>

    <script>
        (function () {
            const routes    = @json($routes);
            const csrfToken = '{{ csrf_token() }}';

            // ── helpers ─────────────────────────────────────────────────────────────

            function recordUrl(template, id) {
                return template.replace('__ID__', id);
            }

            function showAlert(message, isError = false) {
                const cls = isError
                    ? 'rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700'
                    : 'rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700';
                $('#checklist-alert').removeClass('hidden').attr('class', cls).text(message);
            }

            function openModal() {
                $('#checklist-form-modal').removeClass('hidden').addClass('flex');
            }

            function closeModal() {
                $('#checklist-form-modal').addClass('hidden').removeClass('flex');
            }

            function showChecklistsLoading() {
                if ($('#checklist-loading-overlay').length) return;
                $('#checklist-table-container').append(
                    '<div id="checklist-loading-overlay">' +
                    '<div style="display:flex;flex-direction:column;align-items:center;gap:10px;">' +
                    '<div class="checklist-spinner"></div>' +
                    '<span style="font-size:0.7rem;font-weight:500;color:#64748b;letter-spacing:0.05em;">Loading…</span>' +
                    '</div>' +
                    '</div>'
                );
            }

            function fetchChecklists(url = routes.index) {
                showChecklistsLoading();
                $.get(url, $('#checklist-filter-form').serialize(), function (res) {
                    $('#checklist-table-container').html(res.html);
                }).fail(function () {
                    $('#checklist-loading-overlay').remove();
                    showAlert('Failed to load checklists. Please try again.', true);
                });
            }

            function escapeHtml(str) {
                return $('<div>').text(str || '').html();
            }

            // ── point rows ──────────────────────────────────────────────────────────

            const DRAG_HANDLE_SVG =
                '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="currentColor">' +
                    '<circle cx="9" cy="7" r="1.5"/><circle cx="15" cy="7" r="1.5"/>' +
                    '<circle cx="9" cy="12" r="1.5"/><circle cx="15" cy="12" r="1.5"/>' +
                    '<circle cx="9" cy="17" r="1.5"/><circle cx="15" cy="17" r="1.5"/>' +
                '</svg>';

            function createPointRow(heading, text, pointId) {
                const h = escapeHtml(heading);
                const t = escapeHtml(text);
                const idAttr = pointId ? ' data-point-id="' + pointId + '"' : '';
                return $(
                    '<div class="point-row flex items-start gap-2 rounded-lg border border-slate-200 bg-white px-2.5 py-2 shadow-sm"' + idAttr + '>' +
                        '<div class="drag-handle mt-2 shrink-0 cursor-grab select-none text-slate-300 hover:text-slate-500 active:cursor-grabbing">' +
                            DRAG_HANDLE_SVG +
                        '</div>' +
                        '<span class="point-number mt-1.5 w-6 shrink-0 text-xs font-bold text-slate-400"></span>' +
                        '<div class="min-w-0 flex-1 space-y-1.5">' +
                            '<input type="text" class="point-heading w-full rounded-md border border-slate-300 px-2 py-1.5 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-slate-400" placeholder="Heading…" value="' + h + '" />' +
                            '<textarea class="point-text w-full rounded-md border border-slate-300 px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400" rows="4" placeholder="Point text…">' + t + '</textarea>' +
                        '</div>' +
                        '<button type="button" class="remove-point mt-1.5 shrink-0 rounded border border-red-200 px-2 py-1 text-xs text-red-600 hover:border-red-300 hover:bg-red-50">' +
                            '<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>' +
                        '</button>' +
                    '</div>'
                );
            }

            function renumberPoints() {
                $('#points-container .point-row').each(function (i) {
                    $(this).find('.point-number').text((i + 1) + '.');
                });
                $('#points-empty-hint').toggle($('#points-container .point-row').length === 0);
            }

            // ── sortable ────────────────────────────────────────────────────────────

            Sortable.create(document.getElementById('points-container'), {
                handle:     '.drag-handle',
                animation:  150,
                ghostClass: 'opacity-40',
                dragClass:  'shadow-lg',
                onEnd: function () {
                    renumberPoints();
                },
            });

            // ── modal events ────────────────────────────────────────────────────────

            $('[data-close-modal]').on('click', closeModal);

            $('#open-create-checklist').on('click', function () {
                $('#checklist-form')[0].reset();
                $('#checklist-form [name="record_id"]').val('');
                $('#checklist-form-modal h3').text('Add Checklist');
                $('#checklist-form-submit').text('Create');
                $('#points-container').empty();
                renumberPoints();
                openModal();
            });

            $(document).on('click', '.edit-checklist', function () {
                const id = $(this).data('id');
                $.get(recordUrl(routes.show, id), function (data) {
                    $('#checklist-form [name="record_id"]').val(data.id);
                    $('#checklist-form [name="name"]').val(data.name);
                    $('#checklist-form-modal h3').text('Edit Checklist');
                    $('#checklist-form-submit').text('Update');
                    $('#points-container').empty();
                    (data.points || []).forEach(function (point) {
                        $('#points-container').append(createPointRow(point.heading, point.text, point.id));
                    });
                    renumberPoints();
                    openModal();
                });
            });

            // ── point add/remove ────────────────────────────────────────────────────

            $('#add-point-btn').on('click', function () {
                const $row = createPointRow('', '');
                $('#points-container').append($row);
                renumberPoints();
                $row.find('.point-heading').focus();
            });

            $(document).on('click', '.remove-point', function () {
                $(this).closest('.point-row').remove();
                renumberPoints();
            });

            // ── form submit ─────────────────────────────────────────────────────────

            $('#checklist-form').on('submit', function (e) {
                e.preventDefault();

                const id     = $(this).find('[name="record_id"]').val();
                const isEdit = Boolean(id);
                const name   = $(this).find('[name="name"]').val().trim();

                const points = [];
                $('#points-container .point-row').each(function (pi) {
                    points.push({
                        id:         $(this).data('point-id') || null,
                        heading:    $(this).find('.point-heading').val().trim(),
                        text:       $(this).find('.point-text').val().trim(),
                        sort_order: pi,
                    });
                });

                $.ajax({
                    url:         isEdit ? recordUrl(routes.update, id) : routes.store,
                    type:        isEdit ? 'PATCH' : 'POST',
                    contentType: 'application/json',
                    headers:     { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    data:        JSON.stringify({ name, points }),
                    success: function (res) {
                        closeModal();
                        showAlert(res.message);
                        fetchChecklists();
                    },
                    error: function (xhr) {
                        const errors = xhr.responseJSON?.errors || {};
                        showAlert(Object.values(errors)[0]?.[0] || 'Unable to save checklist.', true);
                    },
                });
            });

            // ── filter / pagination ─────────────────────────────────────────────────

            $('#checklist-filter-form').on('submit', function (e) {
                e.preventDefault();
                fetchChecklists();
            });

            let checklistSearchTimer;
            $('#checklist-filter-form').on('input', 'input[type="text"].filter', function () {
                clearTimeout(checklistSearchTimer);
                checklistSearchTimer = setTimeout(function () {
                    fetchChecklists();
                }, 400);
            });

            $(document).on('click', '#checklist-table-container .pagination a', function (e) {
                e.preventDefault();
                fetchChecklists($(this).attr('href'));
            });
        })();
    </script>
</x-layouts.dashboard>
