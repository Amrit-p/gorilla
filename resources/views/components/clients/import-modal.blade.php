<x-ui.modal id="client-import-modal" title="Import Customers" maxWidth="max-w-screen-2xl">
    <form id="client-import-form" enctype="multipart/form-data">
        @csrf

        <div class="flex gap-6 overflow-hidden" style="height:460px">

            {{-- LEFT PANE: upload --}}
            <div class="flex w-96 shrink-0 flex-col gap-4">

                <label id="client-import-drop-zone" class="group flex flex-1 cursor-pointer flex-col items-center justify-center gap-4 rounded-xl border-2 border-dashed border-slate-200 bg-gradient-to-b from-slate-50 to-white px-6 py-8 text-center transition-all duration-200 hover:border-indigo-300 hover:from-indigo-50/60 hover:to-white">
                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-white shadow-sm ring-1 ring-slate-200 transition-all duration-200 group-hover:shadow-md group-hover:ring-indigo-200">
                        <svg class="h-8 w-8 text-slate-400 transition-colors duration-200 group-hover:text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                    </div>
                    <div class="space-y-1">
                        <p id="client-import-file-label" class="text-sm font-semibold text-slate-700">Click to choose file</p>
                        <p class="text-xs text-slate-400">.xlsx files only · Drag & drop supported</p>
                    </div>
                    <input id="client-import-file-input" type="file" name="import_file" accept=".xlsx" class="hidden" required>
                </label>

                <div id="client-import-progress" class="hidden space-y-2 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                    <div class="flex items-center justify-between">
                        <span id="client-import-progress-label" class="text-xs font-medium text-slate-600">Uploading…</span>
                        <span id="client-import-progress-pct" class="text-xs font-semibold text-indigo-600">0%</span>
                    </div>
                    <div class="h-2 w-full overflow-hidden rounded-full bg-slate-200">
                        <div id="client-import-progress-bar" class="h-2 rounded-full bg-gradient-to-r from-indigo-500 to-indigo-400 transition-all duration-300" style="width:0%"></div>
                    </div>
                </div>

                <label class="flex cursor-pointer items-center gap-2.5 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs font-medium text-slate-600 hover:bg-slate-100">
                    <input type="checkbox" name="create_jobs" value="1" class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    <span>Create initial job for each customer</span>
                </label>

                <div class="flex flex-col gap-2.5">
                    <x-ui.button type="submit">Import Customers</x-ui.button>
                    <a href="{{ route('admin.clients.import.sample') }}" class="inline-flex items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-xs font-medium text-slate-600 shadow-sm transition-all hover:border-slate-300 hover:bg-slate-50 hover:shadow">
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
            <div id="client-import-result" class="flex min-h-0 flex-1 flex-col">

                {{-- Empty state --}}
                <div id="client-import-result-empty" class="flex flex-1 flex-col items-center justify-center gap-3 text-center">
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
                <div id="client-import-result-error" class="hidden flex-1 flex-col items-center justify-center gap-4 text-center">
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-red-50 ring-1 ring-red-200">
                        <svg class="h-7 w-7 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </div>
                    <div class="space-y-1">
                        <p class="text-sm font-semibold text-slate-700">Import Failed</p>
                        <p id="client-import-error-text" class="max-w-xs text-xs text-slate-500"></p>
                    </div>
                </div>

                {{-- Tabbed results (shown after a successful import response) --}}
                <div id="client-import-result-tabs" class="hidden min-h-0 flex-1 flex-col">

                    {{-- Main tab bar --}}
                    <div class="mb-4 flex border-b border-slate-200">
                        <button type="button" id="client-tab-imported-btn"
                            onclick="switchClientImportTab('imported')"
                            class="relative -mb-px flex items-center gap-2 border-b-2 border-emerald-500 px-4 py-2.5 text-xs font-semibold text-emerald-600">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                            </svg>
                            Imported
                            <span id="client-tab-imported-count" class="rounded-full bg-emerald-100 px-2 py-0.5 text-emerald-700">0</span>
                        </button>
                        <button type="button" id="client-tab-errors-btn"
                            onclick="switchClientImportTab('errors')"
                            class="relative -mb-px flex items-center gap-2 border-b-2 border-transparent px-4 py-2.5 text-xs font-semibold text-slate-400 hover:text-slate-600">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            Errors
                            <span id="client-tab-errors-count" class="rounded-full bg-slate-100 px-2 py-0.5 text-slate-500">0</span>
                        </button>
                    </div>

                    {{-- Imported panel --}}
                    <div id="client-tab-imported-panel" class="min-h-0 flex-1 flex-col">
                        <div id="client-imported-empty" class="hidden flex-1 flex-col items-center justify-center gap-3 py-10 text-center">
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-slate-50 ring-1 ring-slate-200">
                                <svg class="h-6 w-6 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <p class="text-xs text-slate-400">No customers were imported</p>
                        </div>
                        <div id="client-imported-table" class="hidden min-h-0 flex-1 flex-col overflow-hidden rounded-xl border border-slate-200 shadow-sm">
                            <div class="flex-1 overflow-y-auto">
                                <table class="w-full">
                                    <thead class="sticky top-0 bg-slate-50 text-xs text-slate-500">
                                        <tr class="border-b border-slate-200">
                                            <th class="w-14 px-4 py-2.5 text-left font-semibold uppercase tracking-wide">Row</th>
                                            <th class="px-4 py-2.5 text-left font-semibold uppercase tracking-wide">Identifier</th>
                                        </tr>
                                    </thead>
                                    <tbody id="client-import-imported-body" class="client-import-tbody divide-y divide-slate-100 bg-white text-xs"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- Errors panel --}}
                    <div id="client-tab-errors-panel" class="hidden min-h-0 flex-1 flex-col gap-3">

                        {{-- Sub-tab bar --}}
                        <div class="flex gap-1.5">
                            <button type="button" id="client-subtab-failures-btn"
                                onclick="switchClientImportSubTab('failures')"
                                class="flex items-center gap-1.5 rounded-lg bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-700 ring-1 ring-red-200">
                                Failures
                                <span id="client-subtab-failures-count" class="rounded-full bg-red-100 px-1.5 py-0.5 text-red-600">0</span>
                            </button>
                            <button type="button" id="client-subtab-duplicates-btn"
                                onclick="switchClientImportSubTab('duplicates')"
                                class="flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold text-slate-500 hover:bg-slate-100">
                                Duplicates
                                <span id="client-subtab-duplicates-count" class="rounded-full bg-slate-100 px-1.5 py-0.5 text-slate-500">0</span>
                            </button>
                        </div>

                        {{-- Failures table --}}
                        <div id="client-subtab-failures-panel" class="min-h-0 flex-1 flex-col">
                            <div id="client-failures-empty" class="hidden flex-1 flex-col items-center justify-center gap-3 py-10 text-center">
                                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-50 ring-1 ring-emerald-200">
                                    <svg class="h-6 w-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                                <p class="text-xs text-slate-400">No failed rows</p>
                            </div>
                            <div id="client-failures-table" class="hidden min-h-0 flex-1 flex-col overflow-hidden rounded-xl border border-slate-200 shadow-sm">
                                <div class="flex-1 overflow-y-auto">
                                    <table class="w-full table-fixed">
                                        <thead class="sticky top-0 bg-slate-50 text-xs text-slate-500">
                                            <tr class="border-b border-slate-200">
                                                <th class="w-12 px-4 py-2.5 text-left font-semibold uppercase tracking-wide">Row</th>
                                                <th class="w-2/5 px-4 py-2.5 text-left font-semibold uppercase tracking-wide">Identifier</th>
                                                <th class="px-4 py-2.5 text-left font-semibold uppercase tracking-wide">Reason</th>
                                            </tr>
                                        </thead>
                                        <tbody id="client-import-failures-body" class="client-import-tbody divide-y divide-slate-100 bg-white text-xs"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        {{-- Duplicates table --}}
                        <div id="client-subtab-duplicates-panel" class="hidden min-h-0 flex-1 flex-col">
                            <div id="client-duplicates-empty" class="hidden flex-1 flex-col items-center justify-center gap-3 py-10 text-center">
                                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-50 ring-1 ring-emerald-200">
                                    <svg class="h-6 w-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                                <p class="text-xs text-slate-400">No duplicate rows</p>
                            </div>
                            <div id="client-duplicates-table" class="hidden min-h-0 flex-1 flex-col overflow-hidden rounded-xl border border-slate-200 shadow-sm">
                                <div class="flex-1 overflow-y-auto">
                                    <table class="w-full table-fixed">
                                        <thead class="sticky top-0 bg-slate-50 text-xs text-slate-500">
                                            <tr class="border-b border-slate-200">
                                                <th class="w-12 px-4 py-2.5 text-left font-semibold uppercase tracking-wide">Row</th>
                                                <th class="w-2/5 px-4 py-2.5 text-left font-semibold uppercase tracking-wide">Identifier</th>
                                                <th class="px-4 py-2.5 text-left font-semibold uppercase tracking-wide">Reason</th>
                                            </tr>
                                        </thead>
                                        <tbody id="client-import-duplicates-body" class="client-import-tbody divide-y divide-slate-100 bg-white text-xs"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                    </div>{{-- /client-tab-errors-panel --}}

                </div>{{-- /client-import-result-tabs --}}

            </div>
        </div>
    </form>
</x-ui.modal>

<style>
    .client-import-tbody tr:hover { background-color: rgb(248 250 252); }
</style>

<script>
    function clientFormatReason(reasons) {
        if (!Array.isArray(reasons)) reasons = [reasons];
        if (reasons.length === 1) return $('<span>').text(reasons[0]).html();
        return '<ul class="list-disc space-y-0.5 pl-3.5">'
            + reasons.map(r => `<li>${$('<span>').text(r).html()}</li>`).join('')
            + '</ul>';
    }

    function switchClientImportTab(tab) {
        const isImported = tab === 'imported';

        $('#client-tab-imported-btn')
            .toggleClass('border-emerald-500 text-emerald-600', isImported)
            .toggleClass('border-transparent text-slate-400', !isImported);
        $('#client-tab-errors-btn')
            .toggleClass('border-amber-500 text-amber-600', !isImported)
            .toggleClass('border-transparent text-slate-400', isImported);

        $('#client-tab-imported-panel').toggleClass('hidden', !isImported).toggleClass('flex', isImported);
        $('#client-tab-errors-panel').toggleClass('hidden', isImported).toggleClass('flex', !isImported);
    }

    function switchClientImportSubTab(sub) {
        const isFailures = sub === 'failures';

        $('#client-subtab-failures-btn')
            .toggleClass('bg-red-50 text-red-700 ring-1 ring-red-200', isFailures)
            .toggleClass('text-slate-500 hover:bg-slate-100', !isFailures);
        $('#client-subtab-duplicates-btn')
            .toggleClass('bg-amber-50 text-amber-700 ring-1 ring-amber-200', !isFailures)
            .toggleClass('text-slate-500 hover:bg-slate-100', isFailures);

        $('#client-subtab-failures-panel').toggleClass('hidden', !isFailures).toggleClass('flex', isFailures);
        $('#client-subtab-duplicates-panel').toggleClass('hidden', isFailures).toggleClass('flex', !isFailures);
    }

    function resetClientImportModal() {
        $('#client-import-form')[0].reset();
        $('#client-import-file-label').text('Click to choose file');
        $('#client-import-drop-zone').removeClass('border-emerald-400 bg-emerald-50').addClass('border-slate-300 bg-slate-50');
        $('#client-import-progress').addClass('hidden');
        $('#client-import-result-empty').removeClass('hidden');
        $('#client-import-result-error').addClass('hidden').removeClass('flex');
        $('#client-import-result-tabs').addClass('hidden').removeClass('flex');
    }

    $('#open-client-import-modal').on('click', function () {
        resetClientImportModal();
        openModal('client-import-modal');
    });

    $('#client-import-file-input').on('change', function () {
        const name = this.files[0]?.name;
        if (name) {
            $('#client-import-file-label').text(name);
            $('#client-import-drop-zone').removeClass('border-slate-300 bg-slate-50').addClass('border-emerald-400 bg-emerald-50');
        } else {
            $('#client-import-file-label').text('Click to choose file');
            $('#client-import-drop-zone').removeClass('border-emerald-400 bg-emerald-50').addClass('border-slate-300 bg-slate-50');
        }
    });

    $('#client-import-form').on('submit', function (event) {
        event.preventDefault();
        const $form = $(this);
        const $btn  = $form.find('[type="submit"]');

        $('#client-import-progress-bar').css('width', '0%');
        $('#client-import-progress-pct').text('0%');
        $('#client-import-progress-label').text('Uploading…');
        $('#client-import-progress').removeClass('hidden');
        $btn.prop('disabled', true);

        $.ajax({
            url: "{{ route('admin.clients.import') }}",
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
                    $('#client-import-progress-bar').css('width', pct + '%');
                    $('#client-import-progress-pct').text(pct + '%');
                    if (pct === 100) $('#client-import-progress-label').text('Processing…');
                });
                return xhr;
            },
            success: function (res) {
                $('#client-import-progress').addClass('hidden');
                $btn.prop('disabled', false);
                refreshClients();

                $('#client-import-result-empty').addClass('hidden');
                $('#client-import-result-error').addClass('hidden').removeClass('flex');

                // Populate imported tab
                const importedCount = res.imported || 0;
                $('#client-tab-imported-count').text(importedCount);
                if (importedCount > 0) {
                    const importedRows = (res.imported_rows || []).map(r => `<tr>
                        <td class="px-3 py-2 font-mono font-semibold text-slate-700">${r.row}</td>
                        <td class="px-3 py-2 text-emerald-700">${$('<span>').text(r.identifier).html()}</td>
                    </tr>`).join('');
                    $('#client-import-imported-body').html(importedRows);
                    $('#client-imported-empty').addClass('hidden').removeClass('flex');
                    $('#client-imported-table').removeClass('hidden').addClass('flex');
                } else {
                    $('#client-imported-table').addClass('hidden').removeClass('flex');
                    $('#client-imported-empty').removeClass('hidden').addClass('flex');
                }

                // Populate errors tab
                const failedCount     = res.failed || 0;
                const duplicatedCount = res.duplicated || 0;
                const totalErrors     = failedCount + duplicatedCount;
                $('#client-tab-errors-count').text(totalErrors);

                if (failedCount > 0) {
                    const failureRows = (res.failures || []).map(f => `<tr>
                        <td class="px-3 py-2 font-mono font-semibold text-slate-700">${f.row}</td>
                        <td class="truncate px-3 py-2 text-slate-600" title="${$('<span>').text(f.identifier).html()}">${$('<span>').text(f.identifier).html()}</td>
                        <td class="px-3 py-2 text-red-600">${clientFormatReason(f.reason)}</td>
                    </tr>`).join('');
                    $('#client-import-failures-body').html(failureRows);
                    $('#client-failures-empty').addClass('hidden').removeClass('flex');
                    $('#client-failures-table').removeClass('hidden').addClass('flex');
                } else {
                    $('#client-failures-table').addClass('hidden').removeClass('flex');
                    $('#client-failures-empty').removeClass('hidden').addClass('flex');
                }
                $('#client-subtab-failures-count').text(failedCount);

                if (duplicatedCount > 0) {
                    const duplicateRows = (res.duplicates || []).map(d => `<tr>
                        <td class="px-3 py-2 font-mono font-semibold text-slate-700">${d.row}</td>
                        <td class="truncate px-3 py-2 text-slate-600" title="${$('<span>').text(d.identifier).html()}">${$('<span>').text(d.identifier).html()}</td>
                        <td class="px-3 py-2 text-amber-600">${clientFormatReason(d.reason)}</td>
                    </tr>`).join('');
                    $('#client-import-duplicates-body').html(duplicateRows);
                    $('#client-duplicates-empty').addClass('hidden').removeClass('flex');
                    $('#client-duplicates-table').removeClass('hidden').addClass('flex');
                } else {
                    $('#client-duplicates-table').addClass('hidden').removeClass('flex');
                    $('#client-duplicates-empty').removeClass('hidden').addClass('flex');
                }
                $('#client-subtab-duplicates-count').text(duplicatedCount);

                if (totalErrors > 0) {
                    $('#client-tab-errors-count').removeClass('bg-slate-100 text-slate-500').addClass('bg-amber-100 text-amber-700');
                } else {
                    $('#client-tab-errors-count').removeClass('bg-amber-100 text-amber-700').addClass('bg-slate-100 text-slate-500');
                }

                switchClientImportSubTab('failures');
                $('#client-import-result-tabs').removeClass('hidden').addClass('flex');
                switchClientImportTab('imported');
            },
            error: function (xhr) {
                $('#client-import-progress').addClass('hidden');
                $btn.prop('disabled', false);

                const msg = xhr.responseJSON?.message
                    || Object.values(xhr.responseJSON?.errors || {})[0]?.[0]
                    || 'Import failed.';

                $('#client-import-result-empty').addClass('hidden');
                $('#client-import-result-tabs').addClass('hidden').removeClass('flex');
                $('#client-import-error-text').text(msg);
                $('#client-import-result-error').removeClass('hidden').addClass('flex');
            }
        });
    });
</script>
