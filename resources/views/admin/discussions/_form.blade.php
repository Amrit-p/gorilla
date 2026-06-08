@php
    $isEdit    = isset($discussion) && $discussion->exists ?? false;
    $sections  = $isEdit ? $discussion->sections : collect();
@endphp

@once
@push('styles')
<style>
@keyframes errorFlash {
    0%,100% { background-color: #fff; }
    30%      { background-color: #fef2f2; }
}
.error-flash { animation: errorFlash 1.8s ease; }
</style>
@endpush
@endonce

@php
    $topLevelErrors = collect(['title', 'category', 'date', 'worker_id'])
        ->flatMap(fn($f) => $errors->get($f))
        ->filter()
        ->values();
    $sectionErrorCount = collect($errors->keys())
        ->filter(fn($k) => str_starts_with($k, 'sections.'))->count();

    $defaultSections = $defaultSections ?? [];
    $selectedCategory = old('category', $discussion?->category ?? 'worker');
    $defaultCategorySections = $selectedCategory === 'worker'
        ? ($defaultSections['worker'] ?? [])
        : [];
    $oldSections = old('sections', []);
@endphp

{{-- Top-level field errors + section error summary --}}
@if ($errors->any())
    <div class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
        <div class="flex items-start gap-2">
            <svg class="mt-0.5 h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>
                <p class="font-semibold">Please fix the following before saving:</p>
                <ul class="mt-1 list-inside list-disc space-y-0.5">
                    @foreach ($topLevelErrors as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                    @if ($sectionErrorCount > 0)
                        <li>
                            {{ $sectionErrorCount }} {{ Str::plural('section field', $sectionErrorCount) }}
                            {{ $sectionErrorCount === 1 ? 'has' : 'have' }} errors — see the highlighted
                            {{ Str::plural('section', $sectionErrorCount) }} below.
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </div>
@endif

{{-- Meta card --}}
<div class="rounded-2xl border border-slate-200 bg-white p-5">
    <h3 class="mb-4 text-sm font-semibold text-slate-700">Details</h3>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        {{-- Title --}}
        <div class="sm:col-span-2">
            <label class="mb-1 block text-xs font-medium text-slate-700" for="title">Title</label>
            <input type="text" id="title" name="title"
                   value="{{ old('title', $discussion?->title) }}"
                   placeholder="Discussion title"
                   class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none @error('title') border-red-400 @enderror">
        </div>

        {{-- Category --}}
        <div>
            <label class="mb-1 block text-xs font-medium text-slate-700" for="category">Category</label>
            <select id="category" name="category"
                    class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none @error('category') border-red-400 @enderror">
                @foreach ([
                    'worker'     => 'Worker',
                    'budget'     => 'Budget',
                    'expansion'  => 'Expansion',
                    'crm_update' => 'CRM Update',
                    'general'    => 'General',
                ] as $value => $label)
                    <option value="{{ $value }}" @selected(old('category', $discussion?->category) === $value)>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Date --}}
        <div>
            <label class="mb-1 block text-xs font-medium text-slate-700" for="date">Date</label>
            <input type="date" id="date" name="date"
                   value="{{ old('date', $isEdit ? $discussion->date->format('Y-m-d') : today()->format('Y-m-d')) }}"
                   class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none @error('date') border-red-400 @enderror">
        </div>

        {{-- Worker --}}
        <div class="sm:col-span-2">
            <label class="mb-1 block text-xs font-medium text-slate-700" for="worker_id">Worker (optional)</label>
            <select id="worker_id" name="worker_id"
                    class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none @error('worker_id') border-red-400 @enderror">
                <option value="">— none —</option>
                @foreach ($workers as $worker)
                    <option value="{{ $worker->id }}"
                            @selected(old('worker_id', $discussion?->worker_id) == $worker->id)>
                        {{ $worker->name }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
</div>

{{-- Sections --}}
<div class="space-y-4">
    <div class="flex items-center justify-between">
        <h3 class="text-sm font-semibold text-slate-700">Sections</h3>
    </div>

    <div id="sections-container" class="grid grid-cols-1 gap-4 lg:grid-cols-2">
        @php
            $sectionKeys = collect($errors->keys())->filter(fn($k) => str_starts_with($k, 'sections.'));
            $hasErrorFn  = fn(int $i) => $sectionKeys->contains(fn($k) => str_starts_with($k, "sections.{$i}."));
        @endphp

        @if (!empty($oldSections))
            @foreach ($oldSections as $index => $section)
                @php $sectionErr = $hasErrorFn($index); @endphp
                <div class="section-block rounded-2xl border bg-white p-5 {{ $sectionErr ? 'border-red-300 ring-1 ring-red-200 has-errors' : 'border-slate-200' }}" data-index="{{ $index }}">
                    @include('admin.discussions._section', ['index' => $index, 'section' => null, 'defaultHeading' => '', 'sectionHasErrors' => $sectionErr])
                </div>
            @endforeach
        @elseif ($sections->isNotEmpty())
            @foreach ($sections as $index => $section)
                @php $sectionErr = $hasErrorFn($index); @endphp
                <div class="section-block rounded-2xl border bg-white p-5 {{ $sectionErr ? 'border-red-300 ring-1 ring-red-200 has-errors' : 'border-slate-200' }}" data-index="{{ $index }}">
                    @include('admin.discussions._section', ['index' => $index, 'section' => $section, 'defaultHeading' => '', 'sectionHasErrors' => $sectionErr])
                </div>
            @endforeach
        @elseif (!empty($defaultCategorySections))
            @foreach ($defaultCategorySections as $index => $heading)
                @php $sectionErr = $hasErrorFn($index); @endphp
                <div class="section-block rounded-2xl border bg-white p-5 {{ $sectionErr ? 'border-red-300 ring-1 ring-red-200 has-errors' : 'border-slate-200' }}" data-index="{{ $index }}">
                    @include('admin.discussions._section', ['index' => $index, 'section' => null, 'defaultHeading' => $heading, 'sectionHasErrors' => $sectionErr])
                </div>
            @endforeach
        @else
            <div class="section-block rounded-2xl border border-slate-200 bg-white p-5" data-index="0">
                @include('admin.discussions._section', ['index' => 0, 'section' => null, 'defaultHeading' => '', 'sectionHasErrors' => false])
            </div>
        @endif
    </div>

    <button type="button" id="add-section-btn"
            class="flex items-center gap-2 rounded-md border border-dashed border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-600 hover:border-slate-400 hover:text-slate-800">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Add section
    </button>
</div>

{{-- Submit --}}
<div class="flex items-center gap-3 border-t border-slate-100 pt-4">
    <button type="submit"
            class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">
        Save &amp; store
    </button>
    <a href="{{ route('admin.discussions.index') }}"
       class="text-sm font-medium text-slate-600 hover:text-slate-900">
        Cancel
    </a>
</div>

@push('scripts')
<script>
    // Auto-grow textareas
    function autoGrow(el) {
        el.style.height = 'auto';
        el.style.height = el.scrollHeight + 'px';
    }

    $(document).on('input', 'textarea.auto-grow', function () {
        autoGrow(this);
    });

    // Evaluate remove-section button visibility
    function updateRemoveButtons() {
        const $sections = $('#sections-container .section-block');
        if ($sections.length <= 1) {
            $sections.find('.remove-section-btn').addClass('hidden');
        } else {
            $sections.find('.remove-section-btn').removeClass('hidden');
        }
    }

    // Re-index all section name/id attributes after add/remove
    function reindexSections() {
        $('#sections-container .section-block').each(function (i) {
            $(this).attr('data-index', i);
            $(this).find('[name]').each(function () {
                const newName = $(this).attr('name').replace(/sections\[\d+\]/, 'sections[' + i + ']');
                $(this).attr('name', newName);
            });
            $(this).find('[data-section-index]').attr('data-section-index', i);
            $(this).find('.section-index-label').text('Section ' + (i + 1));
        });
    }

    const MAX_FILE_BYTES = 5 * 1024 * 1024; // 5 MB
    const MAX_FILES      = 10;
    const ALLOWED_EXTS   = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx'];

    // Per-section accumulated DataTransfer (survives multiple file picks)
    const sectionDTs = new WeakMap();

    function getSectionDT(blockEl) {
        if (!sectionDTs.has(blockEl)) sectionDTs.set(blockEl, new DataTransfer());
        return sectionDTs.get(blockEl);
    }

    function formatSize(bytes) {
        if (bytes >= 1024 * 1024) return (bytes / 1024 / 1024).toFixed(1) + ' MB';
        return Math.round(bytes / 1024) + ' KB';
    }

    function updateFileCountBadge($block) {
        const dt    = getSectionDT($block[0]);
        const count = dt.items.length;
        const label = count + ' / ' + MAX_FILES + ' file' + (count !== 1 ? 's' : '');
        const $badge = $block.find('.file-count-badge');
        $badge.text(label)
              .toggleClass('text-red-500 font-semibold', count >= MAX_FILES)
              .toggleClass('text-slate-400', count < MAX_FILES);
    }

    function makePill(html) {
        return '<div class="flex items-center gap-2 rounded-md border px-3 py-1.5 text-xs">' + html + '</div>';
    }

    // File input → validate, accumulate valid files, update preview + badge
    $(document).on('change', '.file-input', function () {
        const input  = this;
        const $block = $(input).closest('.section-block');
        const $list  = $block.find('.file-preview-list');
        const dt     = getSectionDT($block[0]);

        Array.from(input.files).forEach(function (file) {
            const ext      = file.name.split('.').pop().toLowerCase();
            const tooLarge = file.size > MAX_FILE_BYTES;
            const badType  = !ALLOWED_EXTS.includes(ext);
            const tooMany  = dt.items.length >= MAX_FILES;

            if (tooLarge || badType || tooMany) {
                const reason = tooLarge ? 'exceeds 5 MB (' + formatSize(file.size) + ')'
                             : badType  ? 'type not allowed (.' + ext + ')'
                             :            'limit reached — max ' + MAX_FILES + ' files per section';
                const $err = $(
                    '<div class="flex items-start gap-2 rounded-md border border-red-200 bg-red-50 px-3 py-1.5 text-xs text-red-600">' +
                    '<svg class="mt-px h-3.5 w-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">' +
                    '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>' +
                    '</svg>' +
                    '<span><span class="font-medium">' + file.name + '</span> — ' + reason + '</span>' +
                    '</div>'
                );
                $list.append($err);
                setTimeout(function () { $err.fadeOut(300, function () { $(this).remove(); }); }, 4000);
            } else {
                dt.items.add(file);
                $list.append(
                    '<div class="new-file-pill flex items-center gap-2 rounded-md border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs text-slate-700"' +
                    ' data-file-name="' + file.name + '" data-file-size="' + file.size + '">' +
                    '<svg class="h-3.5 w-3.5 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">' +
                    '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>' +
                    '</svg>' +
                    '<span class="min-w-0 flex-1 truncate">' + file.name + '</span>' +
                    '<span class="shrink-0 text-slate-400">(' + formatSize(file.size) + ')</span>' +
                    '<button type="button" class="remove-new-file shrink-0 text-slate-400 hover:text-red-500" title="Remove file">&times;</button>' +
                    '</div>'
                );
            }
        });

        // Sync file input with accumulated valid files
        input.files = dt.files;
        updateFileCountBadge($block);
    });

    // File drop-zone click
    $(document).on('click', '.file-drop-zone', function () {
        $(this).closest('.section-block').find('.file-input').trigger('click');
    });

    // Remove a newly-selected file from the section's DataTransfer + preview
    $(document).on('click', '.remove-new-file', function () {
        const $pill  = $(this).closest('.new-file-pill');
        const name   = $pill.data('file-name');
        const size   = $pill.data('file-size');
        const $block = $pill.closest('.section-block');
        const input  = $block.find('.file-input')[0];

        // Rebuild the accumulated DataTransfer without the removed file
        const current = getSectionDT($block[0]);
        const fresh   = new DataTransfer();
        let removed   = false;
        Array.from(current.files).forEach(function (f) {
            // Remove only the first match so duplicate-named files behave correctly
            if (!removed && f.name === name && f.size === size) {
                removed = true;
            } else {
                fresh.items.add(f);
            }
        });
        sectionDTs.set($block[0], fresh);
        input.files = fresh.files;

        $pill.remove();
        updateFileCountBadge($block);
    });

    // Remove existing attachment
    $(document).on('click', '.remove-attachment-btn', function () {
        const id = $(this).data('id');
        $('<input type="hidden" name="deleted_attachments[]">').val(id).appendTo('form');
        $(this).closest('.attachment-pill').remove();
    });

    // Remove section
    $(document).on('click', '.remove-section-btn', function () {
        $(this).closest('.section-block').remove();
        reindexSections();
        updateRemoveButtons();
    });

    // Add section — clone last block, clear, re-index
    $('#add-section-btn').on('click', function () {
        const $container = $('#sections-container');
        const $last = $container.find('.section-block').last();
        const $clone = $last.clone();
        const newIndex = $container.find('.section-block').length;

        // Clear inputs and textareas
        $clone.find('input[type="text"], input[type="file"]').val('');
        $clone.find('textarea').val('').css('height', 'auto');
        $clone.find('.file-preview-list').empty();
        $clone.find('.attachment-pill').remove();
        $clone.find('.existing-attachments').remove();

        $container.append($clone);
        // New block gets its own fresh DataTransfer automatically via WeakMap
        sectionDTs.set($clone[0], new DataTransfer());
        updateFileCountBadge($clone);

        reindexSections();
        updateRemoveButtons();

        // Auto-grow newly added textareas
        $clone.find('textarea.auto-grow').each(function () { autoGrow(this); });
    });

    // Re-sync file inputs from DataTransfer just before submit (needed because val('') clears input.files)
    $('form').on('submit', function () {
        $('#sections-container .section-block').each(function () {
            const input = $(this).find('.file-input')[0];
            if (input) input.files = getSectionDT(this).files;
        });
    });

    const WORKER_DEFAULT_SECTION_HEADINGS = @json($defaultSections['worker'] ?? []);

    function sectionBlockIsBlank($block) {
        const heading = $block.find('input[type="text"]').val().trim();
        const body = $block.find('textarea').val().trim();
        const hasFiles = getSectionDT($block[0]).files.length > 0;
        const hasAttachments = $block.find('.attachment-pill, .existing-attachments').length > 0;
        return !heading && !body && !hasFiles && !hasAttachments;
    }

    function renderSectionBlocks(headings) {
        const $container = $('#sections-container');
        const $first = $container.find('.section-block').first();

        // Clear existing sections, keep only the first blank template
        resetSectionBlock($first);
        $container.find('.section-block').not($first).remove();

        headings.forEach(function (heading, index) {
            if (index === 0) {
                $first.find('input[type="text"]').val(heading);
            } else {
                const $clone = $first.clone();
                resetSectionBlock($clone);
                $clone.find('input[type="text"]').val(heading);
                $clone.appendTo($container);
                sectionDTs.set($clone[0], new DataTransfer());
            }
        });

        reindexSections();
        updateRemoveButtons();
    }

    function resetSectionBlock($block) {
        $block.find('input[type="text"]').val('');
        $block.find('textarea').val('').css('height', 'auto');
        $block.find('input[type="file"]').val('');
        $block.find('.file-preview-list').empty();
        $block.find('.attachment-pill').remove();
        $block.find('.existing-attachments').remove();
        sectionDTs.set($block[0], new DataTransfer());
        updateFileCountBadge($block);
    }

    function isShowingOnlyWorkerDefaults() {
        const $blocks = $('#sections-container .section-block');
        if ($blocks.length !== WORKER_DEFAULT_SECTION_HEADINGS.length) {
            return false;
        }

        return $blocks.toArray().every(function (block, index) {
            const $block = $(block);
            return $block.find('input[type="text"]').val().trim() === WORKER_DEFAULT_SECTION_HEADINGS[index]
                && $block.find('textarea').val().trim() === ''
                && getSectionDT(block).files.length === 0
                && $block.find('.attachment-pill, .existing-attachments').length === 0;
        });
    }

    $('#category').on('change', function () {
        const category = $(this).val();
        const $first = $('#sections-container .section-block').first();

        if (category === 'worker') {
            if ($('#sections-container .section-block').length === 1 && sectionBlockIsBlank($first)) {
                renderSectionBlocks(WORKER_DEFAULT_SECTION_HEADINGS);
            }
        } else if (isShowingOnlyWorkerDefaults()) {
            resetSectionBlock($first);
            $('#sections-container').find('.section-block').not($first).remove();
            reindexSections();
            updateRemoveButtons();
        }
    });

    // Init on page load
    updateRemoveButtons();
    $('textarea.auto-grow').each(function () { autoGrow(this); });

    // Scroll to + briefly flash the first section with server-side errors
    (function scrollToFirstError() {
        const $first = $('.section-block.has-errors').first();
        if (!$first.length) return;
        setTimeout(function () {
            $first[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
            $first.addClass('error-flash');
            setTimeout(function () { $first.removeClass('error-flash'); }, 1800);
        }, 150);
    }());
</script>
@endpush
