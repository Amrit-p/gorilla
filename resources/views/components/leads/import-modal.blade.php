<x-ui.modal id="lead-import-modal" title="Import Leads" maxWidth="max-w-screen-2xl">
    <form id="lead-import-form" enctype="multipart/form-data">
        @csrf

        <div class="flex gap-6 overflow-hidden" style="height:460px">

            {{-- LEFT PANE: upload --}}
            <div class="flex w-96 shrink-0 flex-col gap-4">

                <label id="import-drop-zone" class="group flex flex-1 cursor-pointer flex-col items-center justify-center gap-4 rounded-xl border-2 border-dashed border-slate-200 bg-gradient-to-b from-slate-50 to-white px-6 py-8 text-center transition-all duration-200 hover:border-indigo-300 hover:from-indigo-50/60 hover:to-white">
                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-white shadow-sm ring-1 ring-slate-200 transition-all duration-200 group-hover:shadow-md group-hover:ring-indigo-200">
                        <svg class="h-8 w-8 text-slate-400 transition-colors duration-200 group-hover:text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                    </div>
                    <div class="space-y-1">
                        <p id="import-file-label" class="text-sm font-semibold text-slate-700">Click to choose file</p>
                        <p class="text-xs text-slate-400">.xlsx files only · Drag & drop supported</p>
                    </div>
                    <input id="import-file-input" type="file" name="import_file" accept=".xlsx" class="hidden" required>
                </label>

                <div id="import-progress" class="hidden space-y-2 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                    <div class="flex items-center justify-between">
                        <span id="import-progress-label" class="text-xs font-medium text-slate-600">Uploading…</span>
                        <span id="import-progress-pct" class="text-xs font-semibold text-indigo-600">0%</span>
                    </div>
                    <div class="h-2 w-full overflow-hidden rounded-full bg-slate-200">
                        <div id="import-progress-bar" class="h-2 rounded-full bg-gradient-to-r from-indigo-500 to-indigo-400 transition-all duration-300" style="width:0%"></div>
                    </div>
                </div>

                <div class="flex flex-col gap-2.5">
                    <x-ui.button type="submit">Import Leads</x-ui.button>
                    <a href="{{ route('admin.leads.import.sample') }}" class="inline-flex items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-xs font-medium text-slate-600 shadow-sm transition-all hover:border-slate-300 hover:bg-slate-50 hover:shadow">
                        <svg class="h-3.5 w-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5m0 0l5-5m-5 5V4"/>
                        </svg>
                        Download Sample File
                    </a>
                </div>
            </div>

            {{-- DIVIDER --}}
            <div class="w-px self-stretch bg-slate-100"></div>

            {{-- RIGHT PANE: results --}}
            <div id="import-result" class="flex min-h-0 flex-1 flex-col">

                {{-- Empty state --}}
                <div id="import-result-empty" class="flex flex-1 flex-col items-center justify-center gap-3 text-center">
                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-50 ring-1 ring-slate-200">
                        <svg class="h-8 w-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div class="space-y-1">
                        <p class="text-sm font-medium text-slate-500">No results yet</p>
                        <p class="text-xs text-slate-400">Import results will appear here</p>
                    </div>
                </div>

                {{-- HTTP / validation error --}}
                <div id="import-result-error" class="hidden flex-1 flex-col items-center justify-center gap-4 text-center">
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-red-50 ring-1 ring-red-200">
                        <svg class="h-7 w-7 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </div>
                    <div class="space-y-1">
                        <p class="text-sm font-semibold text-slate-700">Import Failed</p>
                        <p id="import-error-text" class="max-w-xs text-xs text-slate-500"></p>
                    </div>
                </div>

                {{-- Tabbed results (shown after a successful import response) --}}
                <div id="import-result-tabs" class="hidden min-h-0 flex-1 flex-col">

                    {{-- Main tab bar --}}
                    <div class="mb-4 flex border-b border-slate-200">
                        <button type="button" id="tab-imported-btn"
                            onclick="switchImportTab('imported')"
                            class="relative -mb-px flex items-center gap-2 border-b-2 border-emerald-500 px-4 py-2.5 text-xs font-semibold text-emerald-600">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                            </svg>
                            Imported
                            <span id="tab-imported-count" class="rounded-full bg-emerald-100 px-2 py-0.5 text-emerald-700">0</span>
                        </button>
                        <button type="button" id="tab-errors-btn"
                            onclick="switchImportTab('errors')"
                            class="relative -mb-px flex items-center gap-2 border-b-2 border-transparent px-4 py-2.5 text-xs font-semibold text-slate-400 hover:text-slate-600">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            Errors
                            <span id="tab-errors-count" class="rounded-full bg-slate-100 px-2 py-0.5 text-slate-500">0</span>
                        </button>
                    </div>

                    {{-- Imported panel --}}
                    <div id="tab-imported-panel" class="min-h-0 flex-1 flex-col">
                        <div id="imported-empty" class="hidden flex-1 flex-col items-center justify-center gap-3 py-10 text-center">
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-slate-50 ring-1 ring-slate-200">
                                <svg class="h-6 w-6 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <p class="text-xs text-slate-400">No leads were imported</p>
                        </div>
                        <div id="imported-table" class="hidden min-h-0 flex-1 flex-col overflow-hidden rounded-xl border border-slate-200 shadow-sm">
                            <div class="flex-1 overflow-y-auto">
                                <table class="w-full">
                                    <thead class="sticky top-0 bg-slate-50 text-xs text-slate-500">
                                        <tr class="border-b border-slate-200">
                                            <th class="w-14 px-4 py-2.5 text-left font-semibold uppercase tracking-wide">Row</th>
                                            <th class="px-4 py-2.5 text-left font-semibold uppercase tracking-wide">Identifier</th>
                                        </tr>
                                    </thead>
                                    <tbody id="import-imported-body" class="import-tbody divide-y divide-slate-100 bg-white text-xs"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- Errors panel --}}
                    <div id="tab-errors-panel" class="hidden min-h-0 flex-1 flex-col gap-3">

                        {{-- Sub-tab bar --}}
                        <div class="flex gap-1.5">
                            <button type="button" id="subtab-failures-btn"
                                onclick="switchImportSubTab('failures')"
                                class="flex items-center gap-1.5 rounded-lg bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-700 ring-1 ring-red-200">
                                Failures
                                <span id="subtab-failures-count" class="rounded-full bg-red-100 px-1.5 py-0.5 text-red-600">0</span>
                            </button>
                            <button type="button" id="subtab-duplicates-btn"
                                onclick="switchImportSubTab('duplicates')"
                                class="flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold text-slate-500 hover:bg-slate-100">
                                Duplicates
                                <span id="subtab-duplicates-count" class="rounded-full bg-slate-100 px-1.5 py-0.5 text-slate-500">0</span>
                            </button>
                        </div>

                        {{-- Failures table --}}
                        <div id="subtab-failures-panel" class="min-h-0 flex-1 flex-col">
                            <div id="failures-empty" class="hidden flex-1 flex-col items-center justify-center gap-3 py-10 text-center">
                                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-50 ring-1 ring-emerald-200">
                                    <svg class="h-6 w-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                                <p class="text-xs text-slate-400">No failed rows</p>
                            </div>
                            <div id="failures-table" class="hidden min-h-0 flex-1 flex-col overflow-hidden rounded-xl border border-slate-200 shadow-sm">
                                <div class="flex-1 overflow-y-auto">
                                    <table class="w-full table-fixed">
                                        <thead class="sticky top-0 bg-slate-50 text-xs text-slate-500">
                                            <tr class="border-b border-slate-200">
                                                <th class="w-12 px-4 py-2.5 text-left font-semibold uppercase tracking-wide">Row</th>
                                                <th class="w-2/5 px-4 py-2.5 text-left font-semibold uppercase tracking-wide">Identifier</th>
                                                <th class="px-4 py-2.5 text-left font-semibold uppercase tracking-wide">Reason</th>
                                            </tr>
                                        </thead>
                                        <tbody id="import-failures-body" class="import-tbody divide-y divide-slate-100 bg-white text-xs"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        {{-- Duplicates table --}}
                        <div id="subtab-duplicates-panel" class="hidden min-h-0 flex-1 flex-col">
                            <div id="duplicates-empty" class="hidden flex-1 flex-col items-center justify-center gap-3 py-10 text-center">
                                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-50 ring-1 ring-emerald-200">
                                    <svg class="h-6 w-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                                <p class="text-xs text-slate-400">No duplicate rows</p>
                            </div>
                            <div id="duplicates-table" class="hidden min-h-0 flex-1 flex-col overflow-hidden rounded-xl border border-slate-200 shadow-sm">
                                <div class="flex-1 overflow-y-auto">
                                    <table class="w-full table-fixed">
                                        <thead class="sticky top-0 bg-slate-50 text-xs text-slate-500">
                                            <tr class="border-b border-slate-200">
                                                <th class="w-12 px-4 py-2.5 text-left font-semibold uppercase tracking-wide">Row</th>
                                                <th class="w-2/5 px-4 py-2.5 text-left font-semibold uppercase tracking-wide">Identifier</th>
                                                <th class="px-4 py-2.5 text-left font-semibold uppercase tracking-wide">Reason</th>
                                            </tr>
                                        </thead>
                                        <tbody id="import-duplicates-body" class="import-tbody divide-y divide-slate-100 bg-white text-xs"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                    </div>{{-- /tab-errors-panel --}}

                </div>{{-- /import-result-tabs --}}

            </div>
        </div>
    </form>
</x-ui.modal>

<style>
    .import-tbody tr:hover { background-color: rgb(248 250 252); }
</style>

<script>
    function formatReason(reasons) {
        if (!Array.isArray(reasons)) reasons = [reasons];
        if (reasons.length === 1) return $('<span>').text(reasons[0]).html();
        return '<ul class="list-disc space-y-0.5 pl-3.5">'
            + reasons.map(r => `<li>${$('<span>').text(r).html()}</li>`).join('')
            + '</ul>';
    }

    function switchImportTab(tab) {
        const isImported = tab === 'imported';

        $('#tab-imported-btn')
            .toggleClass('border-emerald-500 text-emerald-600', isImported)
            .toggleClass('border-transparent text-slate-400', !isImported);
        $('#tab-errors-btn')
            .toggleClass('border-amber-500 text-amber-600', !isImported)
            .toggleClass('border-transparent text-slate-400', isImported);

        $('#tab-imported-panel').toggleClass('hidden', !isImported).toggleClass('flex', isImported);
        $('#tab-errors-panel').toggleClass('hidden', isImported).toggleClass('flex', !isImported);
    }

    function switchImportSubTab(sub) {
        const isFailures = sub === 'failures';

        $('#subtab-failures-btn')
            .toggleClass('bg-red-50 text-red-700 ring-1 ring-red-200', isFailures)
            .toggleClass('text-slate-500 hover:bg-slate-100', !isFailures);
        $('#subtab-duplicates-btn')
            .toggleClass('bg-amber-50 text-amber-700 ring-1 ring-amber-200', !isFailures)
            .toggleClass('text-slate-500 hover:bg-slate-100', isFailures);

        $('#subtab-failures-panel').toggleClass('hidden', !isFailures).toggleClass('flex', isFailures);
        $('#subtab-duplicates-panel').toggleClass('hidden', isFailures).toggleClass('flex', !isFailures);
    }

    function resetImportModal() {
        $('#lead-import-form')[0].reset();
        $('#import-file-label').text('Click to choose file');
        $('#import-drop-zone').removeClass('border-emerald-400 bg-emerald-50').addClass('border-slate-300 bg-slate-50');
        $('#import-progress').addClass('hidden');
        $('#import-result-empty').removeClass('hidden');
        $('#import-result-error').addClass('hidden').removeClass('flex');
        $('#import-result-tabs').addClass('hidden').removeClass('flex');
    }

    $('#open-import-modal').on('click', function () {
        resetImportModal();
        openModal('lead-import-modal');
    });

    $('#import-file-input').on('change', function () {
        const name = this.files[0]?.name;
        if (name) {
            $('#import-file-label').text(name);
            $('#import-drop-zone').removeClass('border-slate-300 bg-slate-50').addClass('border-emerald-400 bg-emerald-50');
        } else {
            $('#import-file-label').text('Click to choose file');
            $('#import-drop-zone').removeClass('border-emerald-400 bg-emerald-50').addClass('border-slate-300 bg-slate-50');
        }
    });

    $('#lead-import-form').on('submit', function (event) {
        event.preventDefault();
        const $form = $(this);
        const $btn  = $form.find('[type="submit"]');

        $('#import-progress-bar').css('width', '0%');
        $('#import-progress-pct').text('0%');
        $('#import-progress-label').text('Uploading…');
        $('#import-progress').removeClass('hidden');
        $btn.prop('disabled', true);

        $.ajax({
            url: "{{ route('admin.leads.import') }}",
            method: 'POST',
            data: new FormData($form[0]),
            processData: false,
            contentType: false,
            headers: { 'Accept': 'application/json' },
            xhr: function () {
                const xhr = new XMLHttpRequest();
                xhr.upload.addEventListener('progress', function (e) {
                    if (!e.lengthComputable) return;
                    const pct = Math.round((e.loaded / e.total) * 100);
                    $('#import-progress-bar').css('width', pct + '%');
                    $('#import-progress-pct').text(pct + '%');
                    if (pct === 100) $('#import-progress-label').text('Processing…');
                });
                return xhr;
            },
            success: function (res) {
                $('#import-progress').addClass('hidden');
                $btn.prop('disabled', false);
                refreshLeads();

                $('#import-result-empty').addClass('hidden');
                $('#import-result-error').addClass('hidden').removeClass('flex');

                // Populate imported tab
                const importedCount = res.imported || 0;
                $('#tab-imported-count').text(importedCount);
                if (importedCount > 0) {
                    const importedRows = (res.imported_rows || []).map(r => `<tr>
                        <td class="px-3 py-2 font-mono font-semibold text-slate-700">${r.row}</td>
                        <td class="px-3 py-2 text-emerald-700">${$('<span>').text(r.identifier).html()}</td>
                    </tr>`).join('');
                    $('#import-imported-body').html(importedRows);
                    $('#imported-empty').addClass('hidden').removeClass('flex');
                    $('#imported-table').removeClass('hidden').addClass('flex');
                } else {
                    $('#imported-table').addClass('hidden').removeClass('flex');
                    $('#imported-empty').removeClass('hidden').addClass('flex');
                }

                // Populate errors tab
                const failedCount     = res.failed || 0;
                const duplicatedCount = res.duplicated || 0;
                const totalErrors     = failedCount + duplicatedCount;
                $('#tab-errors-count').text(totalErrors);

                if (failedCount > 0) {
                    const failureRows = (res.failures || []).map(f => `<tr>
                        <td class="px-3 py-2 font-mono font-semibold text-slate-700">${f.row}</td>
                        <td class="truncate px-3 py-2 text-slate-600" title="${$('<span>').text(f.identifier).html()}">${$('<span>').text(f.identifier).html()}</td>
                        <td class="px-3 py-2 text-red-600">${formatReason(f.reason)}</td>
                    </tr>`).join('');
                    $('#import-failures-body').html(failureRows);
                    $('#failures-empty').addClass('hidden').removeClass('flex');
                    $('#failures-table').removeClass('hidden').addClass('flex');
                } else {
                    $('#failures-table').addClass('hidden').removeClass('flex');
                    $('#failures-empty').removeClass('hidden').addClass('flex');
                }
                $('#subtab-failures-count').text(failedCount);

                if (duplicatedCount > 0) {
                    const duplicateRows = (res.duplicates || []).map(d => `<tr>
                        <td class="px-3 py-2 font-mono font-semibold text-slate-700">${d.row}</td>
                        <td class="truncate px-3 py-2 text-slate-600" title="${$('<span>').text(d.identifier).html()}">${$('<span>').text(d.identifier).html()}</td>
                        <td class="px-3 py-2 text-amber-600">${formatReason(d.reason)}</td>
                    </tr>`).join('');
                    $('#import-duplicates-body').html(duplicateRows);
                    $('#duplicates-empty').addClass('hidden').removeClass('flex');
                    $('#duplicates-table').removeClass('hidden').addClass('flex');
                } else {
                    $('#duplicates-table').addClass('hidden').removeClass('flex');
                    $('#duplicates-empty').removeClass('hidden').addClass('flex');
                }
                $('#subtab-duplicates-count').text(duplicatedCount);

                // Style errors tab badge based on whether there are errors
                if (totalErrors > 0) {
                    $('#tab-errors-count').removeClass('bg-slate-100 text-slate-500').addClass('bg-amber-100 text-amber-700');
                } else {
                    $('#tab-errors-count').removeClass('bg-amber-100 text-amber-700').addClass('bg-slate-100 text-slate-500');
                }

                // Reset sub-tab to failures
                switchImportSubTab('failures');

                // Show tabs, default to imported tab
                $('#import-result-tabs').removeClass('hidden').addClass('flex');
                switchImportTab('imported');
            },
            error: function (xhr) {
                $('#import-progress').addClass('hidden');
                $btn.prop('disabled', false);

                const msg = xhr.responseJSON?.message
                    || Object.values(xhr.responseJSON?.errors || {})[0]?.[0]
                    || 'Import failed.';

                $('#import-result-empty').addClass('hidden');
                $('#import-result-tabs').addClass('hidden').removeClass('flex');
                $('#import-error-text').text(msg);
                $('#import-result-error').removeClass('hidden').addClass('flex');
            }
        });
    });
</script>
