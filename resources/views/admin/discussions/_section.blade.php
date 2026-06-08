{{-- Section header --}}
<div class="mb-3 flex items-center gap-2">
    <svg class="h-4 w-4 shrink-0 cursor-grab text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/>
    </svg>
    <span class="section-index-label text-xs font-semibold uppercase tracking-wide {{ ($sectionHasErrors ?? false) ? 'text-red-500' : 'text-slate-400' }}">
        Section {{ $index + 1 }}
        @if ($sectionHasErrors ?? false)
            &mdash; <span class="normal-case font-medium">fix errors below</span>
        @endif
    </span>
    <button type="button"
            class="remove-section-btn ml-auto hidden rounded-md px-2 py-1 text-xs font-medium text-red-500 hover:bg-red-50">
        Remove
    </button>
</div>

{{-- Heading --}}
@php $headingError = $errors->first("sections.{$index}.heading"); @endphp
<div class="mb-3">
    <input type="text" name="sections[{{ $index }}][heading]"
           value="{{ old("sections.{$index}.heading", $section?->heading ?? $defaultHeading ?? '') }}"
           placeholder="Section heading"
           class="w-full rounded-md border px-3 py-2 text-sm font-semibold text-slate-900 placeholder-slate-400 focus:outline-none
                  {{ $headingError ? 'border-red-400 bg-red-50 focus:border-red-500' : 'border-slate-300 focus:border-slate-500' }}">
    @if ($headingError)
        <p class="mt-1 flex items-center gap-1 text-xs text-red-600">
            <svg class="h-3.5 w-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ $headingError }}
        </p>
    @endif
</div>

{{-- Body --}}
@php $bodyError = $errors->first("sections.{$index}.body"); @endphp
<div class="mb-4">
    <textarea name="sections[{{ $index }}][body]"
              rows="5"
              placeholder="Write notes, decisions, context…"
              class="auto-grow w-full resize-none rounded-md border px-3 py-2 text-sm text-slate-800 placeholder-slate-400 focus:outline-none
                     {{ $bodyError ? 'border-red-400 bg-red-50 focus:border-red-500' : 'border-slate-300 focus:border-slate-500' }}">{{ old("sections.{$index}.body", $section?->body) }}</textarea>
    @if ($bodyError)
        <p class="mt-1 flex items-center gap-1 text-xs text-red-600">
            <svg class="h-3.5 w-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ $bodyError }}
        </p>
    @endif
</div>

@if(config('app.discussion.attachments_enabled', false))
    {{-- Existing attachments (edit only) --}}
    @if ($section && $section->attachments->isNotEmpty())
        <div class="existing-attachments mb-3 flex flex-wrap gap-2">
            @foreach ($section->attachments as $attachment)
                <div class="attachment-pill flex items-center gap-1.5 rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs text-slate-700"
                    data-id="{{ $attachment->id }}">
                    <a href="{{ Storage::url($attachment->file_path) }}"
                    target="_blank"
                    rel="noopener"
                    title="View {{ $attachment->original_name }}"
                    class="flex min-w-0 items-center gap-1.5 hover:underline">
                        @if ($attachment->file_type === 'image')
                            <svg class="h-3.5 w-3.5 shrink-0 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        @elseif ($attachment->file_type === 'pdf')
                            <svg class="h-3.5 w-3.5 shrink-0 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                        @else
                            <svg class="h-3.5 w-3.5 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        @endif
                        <span class="max-w-[140px] truncate">{{ $attachment->original_name }}</span>
                    </a>
                    <button type="button"
                            class="remove-attachment-btn ml-1 text-slate-400 hover:text-red-500"
                            data-id="{{ $attachment->id }}"
                            title="Remove">
                        &times;
                    </button>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Server-side file errors --}}
    @php
        $fileErrors = collect($errors->keys())
            ->filter(fn($k) => str_starts_with($k, "sections.{$index}.files"))
            ->map(fn($k) => $errors->first($k))
            ->unique()->values();
    @endphp
    @if ($fileErrors->isNotEmpty())
        <div class="mb-2 space-y-1">
            @foreach ($fileErrors as $fe)
                <p class="flex items-center gap-1 text-xs text-red-600">
                    <svg class="h-3.5 w-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ $fe }}
                </p>
            @endforeach
        </div>
    @endif

    {{-- File count badge + drop zone --}}
    <div class="mb-2 flex items-center justify-between">
        <span class="text-xs text-slate-400">Attachments</span>
        <span class="file-count-badge text-xs text-slate-400">0 / 10 files</span>
    </div>

    <div class="file-drop-zone mb-2 flex cursor-pointer flex-col items-center justify-center gap-1 rounded-lg border border-dashed border-slate-300 bg-slate-50 py-4 text-center text-xs text-slate-500 hover:border-slate-400 hover:bg-slate-100"
        data-section-index="{{ $index }}">
        <svg class="h-5 w-5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
        </svg>
        <span>Click to attach files</span>
        <span class="text-slate-400">JPG, PNG, PDF, DOC · max 5 MB · up to 10 files</span>
    </div>

    <input type="file"
        name="sections[{{ $index }}][files][]"
        class="file-input hidden"
        multiple
        accept="image/*,.pdf,.doc,.docx">

    <div class="file-preview-list flex flex-col gap-1.5"></div>
@endif
