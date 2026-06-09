<x-layouts.dashboard :title="$contractor->name" subtitle="Contractor details and contracts.">
    <div class="space-y-6">

        <a href="{{ route('admin.contractors.index') }}" class="inline-flex items-center gap-1 text-sm text-slate-500 hover:text-slate-800 transition-colors">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back to Contractors
        </a>

        <div id="contractor-alert" class="hidden"></div>

        {{-- Contractor Info Card --}}
        <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-wrap items-start justify-between gap-4 px-6 py-5">
                <div class="space-y-3">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-widest text-slate-400">Contractor</p>
                        <h2 class="mt-0.5 text-xl font-bold text-slate-900">{{ $contractor->name }}</h2>
                    </div>
                    <div class="flex flex-wrap gap-5 text-sm">
                        <div class="flex items-center gap-1.5 text-slate-600">
                            <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            {{ $contractor->phone }}
                        </div>
                        @if ($contractor->email)
                            <div class="flex items-center gap-1.5 text-slate-600">
                                <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                {{ $contractor->email }}
                            </div>
                        @endif
                    </div>
                </div>
                <button type="button" id="edit-contractor-btn"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 transition-colors">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Edit
                </button>
            </div>
        </div>

        {{-- Tab Bar --}}
        <div class="border-b border-slate-200">
            <nav class="-mb-px flex gap-0" aria-label="Contractor tabs">
                <button type="button" data-tab="contracts"
                    class="contractor-tab whitespace-nowrap border-b-2 px-5 py-2.5 text-sm font-medium transition-colors border-slate-900 text-slate-900">
                    Contracts
                    @if ($contractor->contracts->isNotEmpty())
                        <span class="ml-1.5 rounded-full bg-slate-100 px-1.5 py-0.5 text-xs font-medium text-slate-600">{{ $contractor->contracts->count() }}</span>
                    @endif
                </button>
                <button type="button" data-tab="jobs"
                    class="contractor-tab whitespace-nowrap border-b-2 px-5 py-2.5 text-sm font-medium transition-colors border-transparent text-slate-500 hover:text-slate-700">
                    Jobs
                    @if ($jobs->total() > 0)
                        <span class="ml-1.5 rounded-full bg-slate-100 px-1.5 py-0.5 text-xs font-medium text-slate-600">{{ $jobs->total() }}</span>
                    @endif
                </button>
            </nav>
        </div>

        {{-- Contracts Tab --}}
        <div id="tab-pane-contracts" class="space-y-3">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h3 class="text-base font-semibold text-slate-900">Contracts</h3>
                    @if ($contractor->contracts->isNotEmpty())
                        <p class="text-xs text-slate-400 mt-0.5">{{ $contractor->contracts->count() }} contract(s) · {{ $contractor->contracts->where('status', \App\Enums\ContractStatus::Active)->count() }} active</p>
                    @endif
                </div>
                <button type="button" id="open-add-contract"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-slate-700 transition-colors">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Add Contract
                </button>
            </div>

            @forelse ($contractor->contracts as $loop_contract)
                @php
                    $isActive = $loop_contract->isActive();
                    $docCount = $loop_contract->documents->count();
                @endphp
                <div class="contract-card overflow-hidden rounded-xl border bg-white shadow-sm transition-all
                    {{ $isActive ? 'border-emerald-200' : 'border-slate-200' }}"
                    id="contract-{{ $loop_contract->id }}">

                    {{-- Card Header (clickable toggle) --}}
                    <div role="button"
                        class="contract-toggle w-full text-left px-5 py-4 flex items-center gap-4 hover:bg-slate-50/60 transition-colors cursor-pointer select-none"
                        data-target="body-{{ $loop_contract->id }}"
                        aria-expanded="{{ $loop->index === 0 ? 'true' : 'false' }}">

                        {{-- Status indicator dot --}}
                        <span class="shrink-0 h-2.5 w-2.5 rounded-full {{ $isActive ? 'bg-emerald-500' : 'bg-slate-300' }}"></span>

                        {{-- Main info --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="font-semibold text-slate-900">
                                    {{ $loop_contract->name ?: 'Contract #' . $loop_contract->id }}
                                </span>
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium
                                    {{ $isActive ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200' : 'bg-slate-100 text-slate-500 ring-1 ring-slate-200' }}">
                                    {{ $loop_contract->status?->label() }}
                                </span>
                            </div>
                            <div class="mt-0.5 flex flex-wrap items-center gap-3 text-xs text-slate-500">
                                <span>
                                    {{ $loop_contract->start_date->format('d M Y') }}
                                    &rarr;
                                    {{ $loop_contract->end_date ? $loop_contract->end_date->format('d M Y') : 'No end date' }}
                                </span>
                                @if ($docCount > 0)
                                    <span class="inline-flex items-center gap-1">
                                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        {{ $docCount }} {{ Str::plural('document', $docCount) }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- Action buttons (stop propagation via JS) --}}
                        <div class="flex items-center gap-2 shrink-0 contract-actions">
                            <button type="button"
                                class="edit-contract rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-medium text-slate-600 shadow-sm hover:bg-slate-50 transition-colors"
                                data-id="{{ $loop_contract->id }}">Edit</button>
                            <button type="button"
                                class="toggle-contract-status rounded-lg border px-2.5 py-1.5 text-xs font-medium shadow-sm transition-colors
                                    {{ $isActive ? 'border-rose-200 bg-rose-50 text-rose-600 hover:bg-rose-100' : 'border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100' }}"
                                data-id="{{ $loop_contract->id }}" data-status="{{ $loop_contract->status?->value }}">
                                {{ $isActive ? 'Deactivate' : 'Activate' }}
                            </button>
                        </div>

                        {{-- Chevron --}}
                        <svg class="contract-chevron h-4 w-4 shrink-0 text-slate-400 transition-transform duration-200
                            {{ $loop->index === 0 ? 'rotate-180' : '' }}"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>

                    {{-- Collapsible Body --}}
                    <div id="body-{{ $loop_contract->id }}"
                        class="contract-body border-t border-slate-100 {{ $loop->index === 0 ? '' : 'hidden' }}">

                        <div class="px-5 py-4 space-y-5">

                            {{-- Documents --}}
                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <h4 class="text-xs font-semibold uppercase tracking-widest text-slate-400">Documents</h4>
                                    @if ($docCount > 0)
                                        <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">{{ $docCount }}</span>
                                    @endif
                                </div>

                                @if ($loop_contract->documents->isNotEmpty())
                                    <ul class="divide-y divide-slate-100 rounded-lg border border-slate-100 overflow-hidden">
                                        @foreach ($loop_contract->documents as $doc)
                                            @php
                                                $docUrl = route('admin.contractors.contracts.documents.download', [$contractor, $loop_contract, $doc]);
                                                $isImage = $doc->file_type === 'image';
                                                $isPdf   = $doc->file_type === 'pdf';
                                                $canPreview = $isImage || $isPdf;
                                            @endphp
                                            <li class="flex items-center gap-3 bg-white px-4 py-3 text-sm hover:bg-slate-50/70 transition-colors">
                                                {{-- Icon --}}
                                                <span class="shrink-0 flex h-8 w-8 items-center justify-center rounded-lg
                                                    {{ $isImage ? 'bg-blue-50' : ($isPdf ? 'bg-red-50' : 'bg-slate-100') }}">
                                                    @if ($isImage)
                                                        <svg class="h-4 w-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                    @elseif ($isPdf)
                                                        <svg class="h-4 w-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                                    @else
                                                        <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                    @endif
                                                </span>

                                                {{-- Name + size --}}
                                                <div class="flex-1 min-w-0">
                                                    <p class="truncate font-medium text-slate-800">{{ $doc->original_name }}</p>
                                                    <p class="text-xs text-slate-400">{{ number_format($doc->file_size / 1024, 1) }} KB</p>
                                                </div>

                                                {{-- Actions --}}
                                                <div class="flex items-center gap-1 shrink-0">
                                                    @if ($canPreview)
                                                        <a href="{{ $docUrl }}?disposition=inline" target="_blank"
                                                            class="inline-flex items-center gap-1 rounded-md px-2 py-1 text-xs font-medium text-blue-600 hover:bg-blue-50 transition-colors">
                                                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                            View
                                                        </a>
                                                    @endif
                                                    <a href="{{ $docUrl }}?disposition=attachment"
                                                        class="inline-flex items-center gap-1 rounded-md px-2 py-1 text-xs font-medium text-slate-600 hover:bg-slate-100 transition-colors">
                                                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                                        Download
                                                    </a>
                                                    <button type="button"
                                                        class="delete-document inline-flex items-center gap-1 rounded-md px-2 py-1 text-xs font-medium text-red-600 hover:bg-red-50 transition-colors"
                                                        data-id="{{ $doc->id }}"
                                                        data-contract-id="{{ $loop_contract->id }}">
                                                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                        Delete
                                                    </button>
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <div class="flex flex-col items-center justify-center rounded-lg border border-dashed border-slate-200 py-6 text-center">
                                        <svg class="h-8 w-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        <p class="mt-2 text-sm font-medium text-slate-400">No documents yet</p>
                                        <p class="text-xs text-slate-300">Upload a file below to get started</p>
                                    </div>
                                @endif

                                {{-- Upload row --}}
                                <form class="upload-document-form flex items-center gap-2" data-contract-id="{{ $loop_contract->id }}">
                                    @csrf
                                    <label class="flex-1 flex items-center gap-2 cursor-pointer rounded-lg border border-dashed border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-500 hover:border-slate-400 hover:bg-white transition-colors">
                                        <svg class="h-4 w-4 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                                        <span class="upload-label">Choose a file to upload</span>
                                        <input type="file" name="document" class="sr-only"
                                            onchange="this.closest('label').querySelector('.upload-label').textContent = this.files[0]?.name || 'Choose a file to upload'" />
                                    </label>
                                    <button type="submit"
                                        class="shrink-0 rounded-lg bg-slate-800 px-4 py-2.5 text-xs font-medium text-white hover:bg-slate-700 transition-colors">
                                        Upload
                                    </button>
                                </form>
                            </div>

                            {{-- Status History --}}
                            @if ($loop_contract->statusHistories->isNotEmpty())
                                <div class="space-y-2 border-t border-slate-100 pt-4">
                                    <h4 class="text-xs font-semibold uppercase tracking-widest text-slate-400">Status History</h4>
                                    <ol class="relative border-l border-slate-200 ml-1 space-y-2">
                                        @foreach ($loop_contract->statusHistories as $history)
                                            <li class="ml-4 text-xs text-slate-600">
                                                <span class="absolute -left-1.5 mt-0.5 h-3 w-3 rounded-full border-2 border-white
                                                    {{ $history->status === \App\Enums\ContractStatus::Active ? 'bg-emerald-400' : 'bg-slate-300' }}"></span>
                                                <span class="font-semibold {{ $history->status === \App\Enums\ContractStatus::Active ? 'text-emerald-700' : 'text-slate-500' }}">
                                                    {{ $history->status?->label() }}
                                                </span>
                                                <span class="text-slate-400"> · {{ $history->changed_at->format('d M Y, H:i') }}</span>
                                                @if ($history->changedBy)
                                                    <span class="text-slate-400"> by {{ $history->changedBy->name }}</span>
                                                @endif
                                            </li>
                                        @endforeach
                                    </ol>
                                </div>
                            @endif

                        </div>
                    </div>
                </div>
            @empty
                <div class="flex flex-col items-center justify-center rounded-xl border border-dashed border-slate-200 bg-white py-14 text-center">
                    <svg class="h-10 w-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    <p class="mt-3 text-sm font-medium text-slate-500">No contracts yet</p>
                    <p class="mt-1 text-xs text-slate-400">Click "Add Contract" to create the first one.</p>
                </div>
            @endforelse
        </div>

        {{-- Jobs Tab --}}
        <div id="tab-pane-jobs" class="hidden space-y-3">
            @include('admin.jobs.partials.filter-bar', [
                'filters'        => $filters,
                'tableContainer' => 'contractor-jobs-container',
                'filterCallback' => 'loadContractorJobs',
                'filterUrl'      => route('admin.contractors.jobs', $contractor),
                'resetUrl'       => route('admin.contractors.detail', $contractor),
                'excelHref'      => route('admin.jobs.export.excel', ['contractor_id' => $contractor->id]),
                'pdfHref'        => route('admin.jobs.export.pdf', ['contractor_id' => $contractor->id]),
            ])
            <div id="contractor-jobs-container">
                @include('admin.jobs.partials.table', ['jobs' => $jobs])
            </div>
        </div>

    </div>

    {{-- Edit Contractor Modal --}}
    <x-ui.modal id="contractor-form-modal" title="Edit Contractor">
        <form id="contractor-form" class="space-y-3">
            @csrf
            <input type="hidden" name="_method" value="PATCH">
            <x-ui.input label="Name" name="name" :value="$contractor->name" />
            <x-ui.input label="Phone" name="phone" :value="$contractor->phone" />
            <x-ui.input label="Email" name="email" type="email" :value="$contractor->email" />
            <x-ui.button type="submit">Update</x-ui.button>
        </form>
    </x-ui.modal>

    {{-- Add / Edit Contract Modal --}}
    <x-ui.modal id="contract-form-modal" title="Add Contract">
        <form id="contract-form" class="space-y-3">
            @csrf
            <input type="hidden" name="contract_id" value="">
            <x-ui.input label="Contract Name" name="name" placeholder="e.g. Summer Maintenance 2026" />
            <x-ui.input label="Start Date" name="start_date" type="date" />
            <x-ui.input label="End Date" name="end_date" type="date" />

            <div id="contract-documents-section" class="space-y-2">
                <label class="block text-sm font-medium text-slate-700">
                    Documents <span class="text-slate-400 font-normal">(optional)</span>
                </label>
                <label for="contract-file-input"
                    class="flex cursor-pointer items-center gap-2 rounded-lg border border-dashed border-slate-300 bg-slate-50 px-4 py-3 text-sm text-slate-500 hover:border-slate-400 hover:bg-white transition-colors">
                    <svg class="h-4 w-4 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                    Choose files &mdash; <span class="text-xs text-slate-400">PDF, DOC, DOCX, JPG, PNG · max 10 MB each</span>
                </label>
                <input type="file" id="contract-file-input" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" class="sr-only" />
                <ul id="contract-file-list" class="space-y-1"></ul>
            </div>

            <x-ui.button type="submit" id="contract-form-submit">Save</x-ui.button>
        </form>
    </x-ui.modal>

    @include('admin.partials.job-modals')
    @include('admin.partials.dropdown-script')
    @include('admin.partials.job-actions-script', ['filterCallback' => 'loadContractorJobs'])

    <script>
        (function () {
            const contractorId = {{ $contractor->id }};
            const baseUrl = '{{ url('admin/contractors') }}/' + contractorId;
            const contractorUrl = baseUrl;
            const contractsBase = baseUrl + '/contracts';

            let selectedFiles = [];

            // ── Tabs ─────────────────────────────────────────────────────────
            const tabPanes = { contracts: $('#tab-pane-contracts'), jobs: $('#tab-pane-jobs') };
            const tabBtns = $('.contractor-tab');

            function activateTab(name) {
                Object.entries(tabPanes).forEach(([key, $pane]) => $pane.toggleClass('hidden', key !== name));
                tabBtns.each(function () {
                    const isActive = $(this).data('tab') === name;
                    $(this)
                        .toggleClass('border-slate-900 text-slate-900', isActive)
                        .toggleClass('border-transparent text-slate-500 hover:text-slate-700', !isActive);
                });
            }

            tabBtns.on('click', function () { activateTab($(this).data('tab')); });

            // ── Alert ────────────────────────────────────────────────────────
            function showAlert(message, isError = false) {
                const cls = isError
                    ? 'rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700'
                    : 'rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700';
                $('#contractor-alert').removeClass('hidden').attr('class', cls).text(message);
                setTimeout(() => $('#contractor-alert').addClass('hidden'), 4000);
            }

            // ── Modals ───────────────────────────────────────────────────────
            function openModal(id) { $('#' + id).removeClass('hidden').addClass('flex'); }
            function closeModal(id) { $('#' + id).addClass('hidden').removeClass('flex'); }
            $('[data-close-modal]').on('click', function () { closeModal($(this).data('close-modal')); });

            // ── Contract toggle ──────────────────────────────────────────────
            $(document).on('click', '.contract-toggle', function (e) {
                if ($(e.target).closest('.contract-actions').length) { return; }

                const targetId = $(this).data('target');
                const $body    = $('#' + targetId);
                const $chevron = $(this).find('.contract-chevron');
                const isOpen   = $(this).attr('aria-expanded') === 'true';

                $body.toggleClass('hidden', isOpen);
                $chevron.toggleClass('rotate-180', !isOpen);
                $(this).attr('aria-expanded', String(!isOpen));
            });

            // ── File picker (modal) ──────────────────────────────────────────
            function renderFileList() {
                const $list = $('#contract-file-list');
                $list.empty();
                selectedFiles.forEach(function (file, index) {
                    const sizeKb = (file.size / 1024).toFixed(1);
                    $list.append(
                        $('<li>').addClass('flex items-center justify-between rounded-lg border border-slate-100 bg-slate-50 px-3 py-2 text-xs')
                            .append($('<span>').addClass('truncate text-slate-700 max-w-[220px]').text(file.name))
                            .append($('<span>').addClass('ml-2 shrink-0 text-slate-400').text(sizeKb + ' KB'))
                            .append(
                                $('<button>').attr('type', 'button')
                                    .addClass('ml-3 shrink-0 text-red-400 hover:text-red-600 transition-colors leading-none')
                                    .attr('title', 'Remove')
                                    .html('&times;')
                                    .on('click', function () { selectedFiles.splice(index, 1); renderFileList(); })
                            )
                    );
                });
            }

            $('#contract-file-input').on('change', function () {
                Array.from(this.files).forEach(function (file) {
                    const isDupe = selectedFiles.some(f => f.name === file.name && f.size === file.size);
                    if (!isDupe) { selectedFiles.push(file); }
                });
                this.value = '';
                renderFileList();
            });

            // ── Contractor edit ──────────────────────────────────────────────
            $('#edit-contractor-btn').on('click', function () { openModal('contractor-form-modal'); });
            $('#contractor-form').on('submit', function (e) {
                e.preventDefault();
                $.ajax({
                    url: contractorUrl, method: 'POST', data: $(this).serialize(),
                    headers: { 'Accept': 'application/json' },
                    success: function (res) { closeModal('contractor-form-modal'); showAlert(res.message); setTimeout(() => location.reload(), 800); },
                    error: function (xhr) {
                        const errors = xhr.responseJSON?.errors || {};
                        showAlert(Object.values(errors)[0]?.[0] || 'Unable to update contractor.', true);
                    },
                });
            });

            // ── Add contract ─────────────────────────────────────────────────
            $('#open-add-contract').on('click', function () {
                $('#contract-form')[0].reset();
                $('#contract-form [name="contract_id"]').val('');
                $('#contract-form-submit').text('Save Contract');
                $('#contract-form-modal h3').text('Add Contract');
                selectedFiles = [];
                renderFileList();
                $('#contract-documents-section').show();
                openModal('contract-form-modal');
            });

            // ── Edit contract ────────────────────────────────────────────────
            $(document).on('click', '.edit-contract', function () {
                const id = $(this).data('id');
                $.get(contractsBase + '/' + id, function (data) {
                    $('#contract-form [name="contract_id"]').val(data.id);
                    $('#contract-form [name="name"]').val(data.name || '');
                    $('#contract-form [name="start_date"]').val(data.start_date);
                    $('#contract-form [name="end_date"]').val(data.end_date || '');
                    $('#contract-form-submit').text('Update Contract');
                    $('#contract-form-modal h3').text('Edit Contract');
                    $('#contract-documents-section').hide();
                    openModal('contract-form-modal');
                });
            });

            // ── Save / update contract ───────────────────────────────────────
            $('#contract-form').on('submit', function (e) {
                e.preventDefault();
                const contractId = $(this).find('[name="contract_id"]').val();
                const isEdit     = Boolean(contractId);
                const url        = isEdit ? contractsBase + '/' + contractId : contractsBase;

                $.ajax({
                    url: url, method: 'POST',
                    data: $(this).serialize() + (isEdit ? '&_method=PATCH' : ''),
                    headers: { 'Accept': 'application/json' },
                    success: function (res) {
                        if (!isEdit && selectedFiles.length > 0) {
                            const newId = res.contract_id;
                            const uploads = selectedFiles.map(function (file) {
                                const fd = new FormData();
                                fd.append('_token', '{{ csrf_token() }}');
                                fd.append('document', file);
                                return $.ajax({
                                    url: contractsBase + '/' + newId + '/documents',
                                    method: 'POST', data: fd,
                                    processData: false, contentType: false,
                                    headers: { 'Accept': 'application/json' },
                                });
                            });
                            $.when.apply($, uploads).always(function () {
                                closeModal('contract-form-modal');
                                showAlert(res.message);
                                setTimeout(() => location.reload(), 600);
                            });
                        } else {
                            closeModal('contract-form-modal');
                            showAlert(res.message);
                            setTimeout(() => location.reload(), 800);
                        }
                    },
                    error: function (xhr) {
                        const errors = xhr.responseJSON?.errors || {};
                        showAlert(Object.values(errors)[0]?.[0] || 'Unable to save contract.', true);
                    },
                });
            });

            // ── Toggle contract status ───────────────────────────────────────
            $(document).on('click', '.toggle-contract-status', function () {
                const contractId    = $(this).data('id');
                const currentStatus = $(this).data('status');
                const newStatus     = currentStatus === 'active' ? 'inactive' : 'active';
                $.ajax({
                    url: contractsBase + '/' + contractId + '/status', method: 'POST',
                    data: { _token: '{{ csrf_token() }}', _method: 'PATCH', status: newStatus },
                    headers: { 'Accept': 'application/json' },
                    success: function (res) { showAlert(res.message); setTimeout(() => location.reload(), 600); },
                    error: function () { showAlert('Unable to update status.', true); },
                });
            });

            // ── Upload document (inline per contract) ────────────────────────
            $(document).on('submit', '.upload-document-form', function (e) {
                e.preventDefault();
                const contractId = $(this).data('contract-id');
                const $btn = $(this).find('button[type="submit"]');
                $btn.text('Uploading…').prop('disabled', true);
                $.ajax({
                    url: contractsBase + '/' + contractId + '/documents', method: 'POST',
                    data: new FormData(this), processData: false, contentType: false,
                    headers: { 'Accept': 'application/json' },
                    success: function (res) { showAlert(res.message); setTimeout(() => location.reload(), 600); },
                    error: function (xhr) {
                        $btn.text('Upload').prop('disabled', false);
                        const errors = xhr.responseJSON?.errors || {};
                        showAlert(Object.values(errors)[0]?.[0] || 'Upload failed.', true);
                    },
                });
            });

            // ── Delete document ──────────────────────────────────────────────
            $(document).on('click', '.delete-document', function () {
                if (!confirm('Delete this document?')) { return; }
                const docId      = $(this).data('id');
                const contractId = $(this).data('contract-id');
                $.ajax({
                    url: contractsBase + '/' + contractId + '/documents/' + docId, method: 'POST',
                    data: { _token: '{{ csrf_token() }}', _method: 'DELETE' },
                    headers: { 'Accept': 'application/json' },
                    success: function (res) { showAlert(res.message); setTimeout(() => location.reload(), 600); },
                    error: function () { showAlert('Unable to delete document.', true); },
                });
            });
        })();
    </script>
</x-layouts.dashboard>
