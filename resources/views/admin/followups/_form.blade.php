{{--
  Reusable follow-up form.
  Usage:
    @include('admin.followups._form', ['followup' => null, 'options' => $options])
    @include('admin.followups._form', ['followup' => $followup, 'options' => $options])
  Pre-fill polymorphic context:
    @include('admin.followups._form', [..., 'followableType' => get_class($job), 'followableId' => $job->id])
--}}

<div class="space-y-6">

    {{-- Errors --}}
    @if ($errors->any())
        <div class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <ul class="list-disc space-y-1 pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Polymorphic target (hidden when pre-filled, visible for standalone creation) --}}
    @if (isset($followableType) && isset($followableId))
        <input type="hidden" name="followable_type" value="{{ $followableType }}">
        <input type="hidden" name="followable_id" value="{{ $followableId }}">
    @else
        @php
            $initialFollowableId    = old('followable_id', $followup?->followable_id ?? '');
            $initialFollowableType  = old('followable_type', $followup?->followable_type ?? '');
            $initialFollowableLabel = $followup ? $followup->followableLabel() : (string) old('followable_id', '');
        @endphp

        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <h3 class="mb-4 text-sm font-semibold text-slate-700">Target</h3>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Model type</label>
                    <select name="followable_type" id="followable_type"
                            class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm @error('followable_type') border-red-400 @enderror">
                        <option value="">Select type…</option>
                        @foreach ($options['followableTypes'] as $type)
                            <option value="{{ $type }}"
                                    @selected($initialFollowableType === $type)>
                                {{ class_basename($type) }}
                            </option>
                        @endforeach
                    </select>
                    @error('followable_type')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Record</label>
                    <div class="relative" id="followable-search-wrapper">
                        <input type="hidden" name="followable_id" id="followable_id"
                               value="{{ $initialFollowableId }}">
                        <input type="text" id="followable_search" autocomplete="off"
                               value="{{ $initialFollowableLabel }}"
                               placeholder="{{ $initialFollowableType ? 'Search by name, ID…' : 'Select a type first…' }}"
                               class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm @error('followable_id') border-red-400 @enderror">
                        <ul id="followable_dropdown"
                            class="absolute z-50 mt-1 max-h-60 w-full overflow-auto rounded-md border border-slate-200 bg-white text-sm shadow-lg hidden">
                        </ul>
                    </div>
                    @error('followable_id')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        @once
        @push('scripts')
        <script>
        (function () {
            var searchTimer;
            var searchUrl = '{{ route('admin.followups.search-followable') }}';

            function renderDropdown(results) {
                var $d = $('#followable_dropdown').empty();
                if (!results.length) {
                    $d.append('<li class="px-3 py-2 text-slate-400 cursor-default">No results found.</li>');
                } else {
                    $.each(results, function (_, item) {
                        $('<li>')
                            .addClass('cursor-pointer px-3 py-2 hover:bg-slate-50')
                            .text(item.label)
                            .attr('data-id', item.id)
                            .attr('data-label', item.label)
                            .appendTo($d);
                    });
                }
                $d.removeClass('hidden');
            }

            $('#followable_type').on('change', function () {
                var type = $(this).val();
                $('#followable_id').val('');
                $('#followable_search').val('').attr(
                    'placeholder', type ? 'Search by name, ID…' : 'Select a type first…'
                );
                $('#followable_dropdown').addClass('hidden').empty();
            });

            $('#followable_search').on('input', function () {
                clearTimeout(searchTimer);
                var val = $(this).val().trim();
                var type = $('#followable_type').val();

                if (!type) {
                    $('#followable_dropdown').addClass('hidden');
                    return;
                }

                if (!val) {
                    $('#followable_id').val('');
                    $('#followable_dropdown').addClass('hidden');
                    return;
                }

                searchTimer = setTimeout(function () {
                    $.ajax({
                        url: searchUrl,
                        data: { type: type, q: val },
                        success: function (res) { renderDropdown(res.results || []); }
                    });
                }, 300);
            });

            $('#followable_search').on('blur', function () {
                var val = $(this).val().trim();
                if (val && !$('#followable_id').val() && /^\d+$/.test(val)) {
                    $('#followable_id').val(val);
                }
            });

            $(document).on('click', '#followable_dropdown li[data-id]', function () {
                $('#followable_id').val($(this).data('id'));
                $('#followable_search').val($(this).data('label'));
                $('#followable_dropdown').addClass('hidden');
            });

            $(document).on('click', function (e) {
                if (!$(e.target).closest('#followable-search-wrapper').length) {
                    $('#followable_dropdown').addClass('hidden');
                }
            });
        }());
        </script>
        @endpush
        @endonce
    @endif

    {{-- Core details --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-5">
        <h3 class="mb-4 text-sm font-semibold text-slate-700">Details</h3>
        <div class="space-y-4">

            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">Outcome <span class="text-red-500">*</span></label>
                <textarea name="outcome" rows="4"
                          class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm @error('outcome') border-red-400 @enderror"
                          placeholder="What happened in this follow-up…">{{ old('outcome', $followup?->outcome ?? '') }}</textarea>
                @error('outcome')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">Notes</label>
                <textarea name="notes" rows="3"
                          class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm @error('notes') border-red-400 @enderror"
                          placeholder="Additional remarks (optional)…">{{ old('notes', $followup?->notes ?? '') }}</textarea>
                @error('notes')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Status <span class="text-red-500">*</span></label>
                    <select name="status"
                            class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm @error('status') border-red-400 @enderror">
                        @foreach ($options['statuses'] as $status)
                            <option value="{{ $status->value }}"
                                    @selected(old('status', $followup?->status?->value ?? \App\Enums\FollowupStatus::Pending->value) === $status->value)>
                                {{ $status->label() }}
                            </option>
                        @endforeach
                    </select>
                    @error('status')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Next follow-up</label>
                    <input type="datetime-local" name="next_followup_at"
                           value="{{ old('next_followup_at', $followup?->next_followup_at?->format('Y-m-d\TH:i') ?? '') }}"
                           class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm @error('next_followup_at') border-red-400 @enderror">
                    @error('next_followup_at')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

        </div>
    </div>

</div>
