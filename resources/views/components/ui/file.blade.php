@props([
    'label' => '',
    'name',
    'multiple' => false,
    'accept' => null,
    'hint' => 'Drag &amp; drop files here, or click to browse',
])

@php
    $inputId = $attributes->get('id', $name);
    $fieldName = $multiple ? $name . '[]' : $name;
@endphp

<div {{ $attributes->only('class') }}>
    @if ($label)
        <label for="{{ $inputId }}" class="mb-1 block text-sm font-medium text-slate-700">{{ $label }}</label>
    @endif

    {{-- Drag & drop zone. JS keeps the hidden input's FileList in sync with the scrollable preview list. --}}
    <div
        data-file-dropzone
        class="rounded-md border-2 border-dashed border-slate-300 bg-slate-50 px-4 py-6 text-center transition hover:border-slate-400"
    >
        <input
            id="{{ $inputId }}"
            name="{{ $fieldName }}"
            type="file"
            class="hidden"
            data-file-input
            @if ($multiple) multiple @endif
            @if ($accept) accept="{{ $accept }}" @endif
            {{ $attributes->except(['id', 'class']) }}
        >

        <div data-file-prompt class="cursor-pointer">
            <svg class="mx-auto h-8 w-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.9A5 5 0 1115.9 6 4.5 4.5 0 0117 15h-1m-5-4v9m0-9l-3 3m3-3l3 3" />
            </svg>
            <p class="mt-2 text-sm text-slate-600">{!! $hint !!}</p>
            @if ($accept)
                <p class="mt-1 text-xs text-slate-400">Accepted: {{ $accept }}</p>
            @endif
        </div>

        {{-- Scrollable list of staged files. --}}
        <ul data-file-list class="mt-3 hidden max-h-48 space-y-2 overflow-y-auto text-left"></ul>
    </div>
</div>

@once
    @push('scripts')
        <script>
            (function () {
                function formatSize(bytes) {
                    if (!bytes) { return '0 B'; }
                    var units = ['B', 'KB', 'MB', 'GB'];
                    var i = Math.floor(Math.log(bytes) / Math.log(1024));
                    return (bytes / Math.pow(1024, i)).toFixed(i ? 1 : 0) + ' ' + units[i];
                }

                function fileKey(file) {
                    return file.name + '|' + file.size + '|' + file.lastModified;
                }

                function initDropzone(zone) {
                    var input = zone.querySelector('[data-file-input]');
                    var list = zone.querySelector('[data-file-list]');
                    var prompt = zone.querySelector('[data-file-prompt]');
                    var multiple = input.multiple;
                    var store = new DataTransfer();

                    function syncInput() {
                        input.files = store.files;
                    }

                    function removeAt(index) {
                        var next = new DataTransfer();
                        Array.prototype.forEach.call(store.files, function (file, i) {
                            if (i !== index) { next.items.add(file); }
                        });
                        store = next;
                        syncInput();
                        render();
                    }

                    function render() {
                        list.innerHTML = '';
                        if (!store.files.length) {
                            list.classList.add('hidden');
                            return;
                        }
                        list.classList.remove('hidden');

                        Array.prototype.forEach.call(store.files, function (file, index) {
                            var li = document.createElement('li');
                            li.className = 'flex items-center justify-between gap-3 rounded-md border border-slate-200 bg-white px-3 py-2 text-sm';

                            var info = document.createElement('div');
                            info.className = 'min-w-0';
                            var nameEl = document.createElement('p');
                            nameEl.className = 'truncate font-medium text-slate-700';
                            nameEl.textContent = file.name;
                            var sizeEl = document.createElement('p');
                            sizeEl.className = 'text-xs text-slate-400';
                            sizeEl.textContent = formatSize(file.size);
                            info.appendChild(nameEl);
                            info.appendChild(sizeEl);

                            var remove = document.createElement('button');
                            remove.type = 'button';
                            remove.className = 'shrink-0 rounded-md px-2 py-1 text-xs font-medium text-red-600 hover:bg-red-50';
                            remove.textContent = 'Remove';
                            remove.addEventListener('click', function () { removeAt(index); });

                            li.appendChild(info);
                            li.appendChild(remove);
                            list.appendChild(li);
                        });
                    }

                    function addFiles(files) {
                        if (!multiple && files.length) {
                            store = new DataTransfer();
                        }
                        var existing = {};
                        Array.prototype.forEach.call(store.files, function (file) {
                            existing[fileKey(file)] = true;
                        });

                        Array.prototype.forEach.call(files, function (file) {
                            if (!multiple) {
                                store = new DataTransfer();
                                store.items.add(file);
                                return;
                            }
                            if (!existing[fileKey(file)]) {
                                existing[fileKey(file)] = true;
                                store.items.add(file);
                            }
                        });

                        syncInput();
                        render();
                    }

                    prompt.addEventListener('click', function () { input.click(); });

                    input.addEventListener('change', function () {
                        // Snapshot the freshly picked files before syncInput() overwrites input.files.
                        var picked = Array.prototype.slice.call(input.files);
                        if (picked.length === 0) { return; }
                        addFiles(picked);
                    });

                    ['dragenter', 'dragover'].forEach(function (type) {
                        zone.addEventListener(type, function (e) {
                            e.preventDefault();
                            zone.classList.add('border-slate-500', 'bg-slate-100');
                        });
                    });

                    ['dragleave', 'drop'].forEach(function (type) {
                        zone.addEventListener(type, function (e) {
                            e.preventDefault();
                            zone.classList.remove('border-slate-500', 'bg-slate-100');
                        });
                    });

                    zone.addEventListener('drop', function (e) {
                        if (e.dataTransfer && e.dataTransfer.files.length) {
                            addFiles(e.dataTransfer.files);
                        }
                    });
                }

                function initAll() {
                    document.querySelectorAll('[data-file-dropzone]').forEach(function (zone) {
                        if (zone.dataset.fileInit) { return; }
                        zone.dataset.fileInit = '1';
                        initDropzone(zone);
                    });
                }

                if (document.readyState !== 'loading') {
                    initAll();
                } else {
                    document.addEventListener('DOMContentLoaded', initAll);
                }
            })();
        </script>
    @endpush
@endonce
