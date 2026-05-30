<x-ui.modal id="lead-import-modal" title="Import Leads" maxWidth="max-w-5xl">
    <form id="lead-import-form" enctype="multipart/form-data">
        @csrf

        <div class="flex gap-5 overflow-hidden" style="height:460px">

            {{-- LEFT PANE: upload --}}
            <div class="flex w-64 shrink-0 flex-col gap-4">

                <label id="import-drop-zone" class="flex flex-1 cursor-pointer flex-col items-center justify-center gap-3 rounded-lg border-2 border-dashed border-slate-300 bg-slate-50 px-4 py-6 text-center transition hover:border-slate-400 hover:bg-slate-100">
                    <svg class="h-9 w-9 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>
                    <div>
                        <p id="import-file-label" class="text-sm font-medium text-slate-700">Click to choose file</p>
                        <p class="mt-0.5 text-xs text-slate-400">.xlsx files only</p>
                    </div>
                    <input id="import-file-input" type="file" name="import_file" accept=".xlsx" class="hidden" required>
                </label>

                <div id="import-progress" class="hidden space-y-1.5 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5">
                    <div class="flex items-center justify-between text-xs font-medium text-slate-600">
                        <span id="import-progress-label">Uploading…</span>
                        <span id="import-progress-pct">0%</span>
                    </div>
                    <div class="h-1.5 w-full overflow-hidden rounded-full bg-slate-200">
                        <div id="import-progress-bar" class="h-1.5 rounded-full bg-emerald-500 transition-all duration-200" style="width:0%"></div>
                    </div>
                </div>

                <div class="flex flex-col gap-2">
                    <x-ui.button type="submit">Import</x-ui.button>
                    <a href="{{ route('admin.leads.import.sample') }}" class="inline-flex items-center justify-center gap-1.5 rounded-md border border-slate-300 bg-white px-3 py-2 text-xs font-medium text-slate-600 transition hover:bg-slate-50">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5m0 0l5-5m-5 5V4"/>
                        </svg>
                        Download Sample
                    </a>
                </div>
            </div>

            {{-- DIVIDER --}}
            <div class="w-px self-stretch bg-slate-200"></div>

            {{-- RIGHT PANE: results --}}
            <div id="import-result" class="flex min-h-0 flex-1 flex-col">

                {{-- Empty state --}}
                <div id="import-result-empty" class="flex flex-1 flex-col items-center justify-center gap-2 text-center">
                    <svg class="h-10 w-10 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p class="text-xs text-slate-400">Import results will appear here</p>
                </div>

                {{-- Success --}}
                <div id="import-result-success" class="hidden flex-1 flex-col items-center justify-center gap-3 text-center">
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-emerald-100">
                        <svg class="h-6 w-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <p id="import-success-text" class="text-sm font-medium text-slate-700"></p>
                </div>

                {{-- Failures --}}
                <div id="import-result-partial" class="hidden min-h-0 flex-1 flex-col gap-3">
                    <div class="flex items-center gap-2.5 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2.5">
                        <svg class="h-4 w-4 shrink-0 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <p id="import-failures-summary" class="text-xs font-medium text-amber-700"></p>
                    </div>

                    <div class="flex min-h-0 flex-1 flex-col overflow-hidden rounded-lg border border-slate-200">
                        <div class="border-b border-slate-200 bg-slate-50 px-3 py-2 text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Failed rows — fix in your Excel file and re-import
                        </div>
                        <div class="flex-1 overflow-y-auto">
                            <table class="w-full">
                                <thead class="sticky top-0 bg-slate-50 text-xs text-slate-500">
                                    <tr class="border-b border-slate-100">
                                        <th class="w-12 px-3 py-2 text-left font-medium">Row</th>
                                        <th class="w-36 px-3 py-2 text-left font-medium">Identifier</th>
                                        <th class="px-3 py-2 text-left font-medium">Reason</th>
                                    </tr>
                                </thead>
                                <tbody id="import-failures-body" class="divide-y divide-slate-100 bg-white text-xs"></tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </form>
</x-ui.modal>

<script>
    function resetImportModal() {
        $('#lead-import-form')[0].reset();
        $('#import-file-label').text('Click to choose file');
        $('#import-drop-zone').removeClass('border-emerald-400 bg-emerald-50').addClass('border-slate-300 bg-slate-50');
        $('#import-progress').addClass('hidden');
        $('#import-result-empty').removeClass('hidden');
        $('#import-result-success').addClass('hidden').removeClass('flex');
        $('#import-result-partial').addClass('hidden').removeClass('flex');
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
                $('#import-result-success').addClass('hidden').removeClass('flex');
                $('#import-result-partial').addClass('hidden').removeClass('flex');

                if (res.failures && res.failures.length > 0) {
                    const rows = res.failures.map(f => `<tr>
                        <td class="px-3 py-2 font-mono font-semibold text-slate-700">${f.row}</td>
                        <td class="max-w-[140px] truncate px-3 py-2 text-slate-600" title="${$('<span>').text(f.identifier).html()}">${$('<span>').text(f.identifier).html()}</td>
                        <td class="px-3 py-2 text-red-600">${$('<span>').text(f.reason).html()}</td>
                    </tr>`).join('');
                    $('#import-failures-summary').text(
                        res.imported + (res.imported === 1 ? ' row imported' : ' rows imported') + ', ' +
                        res.failed   + (res.failed   === 1 ? ' row failed'   : ' rows failed')
                    );
                    $('#import-failures-body').html(rows);
                    $('#import-result-partial').removeClass('hidden').addClass('flex');
                } else {
                    $('#import-success-text').text(res.message);
                    $('#import-result-success').removeClass('hidden').addClass('flex');
                }
            },
            error: function (xhr) {
                $('#import-progress').addClass('hidden');
                $btn.prop('disabled', false);
                showLeadAlert(Object.values(xhr.responseJSON?.errors || {})[0]?.[0] || 'Import failed.', true);
            }
        });
    });
</script>
