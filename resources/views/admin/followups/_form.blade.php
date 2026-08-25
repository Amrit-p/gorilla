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

    {{-- Target: general by default, optionally pinned to a record --}}
    @if (isset($followableType) && isset($followableId))
        <input type="hidden" name="followable_type" value="{{ $followableType }}">
        <input type="hidden" name="followable_id" value="{{ $followableId }}">
    @else
        @php
            $isEditing              = $followup !== null;
            $initialFollowableType  = old('followable_type', $followup?->followable_type ?? '');
            $initialFollowableId    = old('followable_id', $followup?->followable_id ?? '');
            $initialFollowableLabel = $isEditing && ! $followup->isGeneral()
                ? $followup->followableLabel()
                : (string) old('followable_id', '');
            $initialTitle           = old('title', $followup?->title ?? '');
        @endphp

        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <h3 class="mb-4 text-sm font-semibold text-slate-700">Type</h3>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Follow-up type</label>
                    <select name="followable_type" id="followable_type"
                            @disabled($isEditing)
                            class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm disabled:bg-slate-50 disabled:text-slate-500 @error('followable_type') border-red-400 @enderror">
                        <option value="" @selected($initialFollowableType === '')>
                            {{ \App\Models\Followup::GENERAL_LABEL }}
                        </option>
                        @foreach ($options['followableTypes'] as $type)
                            <option value="{{ $type }}" @selected($initialFollowableType === $type)>
                                {{ class_basename($type) }}
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-slate-400">
                        General follow-ups only need a title. Pick a type to link this to a record instead.
                    </p>
                    @error('followable_type')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- General follow-up: plain title --}}
                <div id="followable-title-field" class="{{ $initialFollowableType ? 'hidden' : '' }}">
                    <label class="mb-1 block text-xs font-medium text-slate-600">
                        Title <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="title" id="followup_title" value="{{ $initialTitle }}"
                           placeholder="e.g. Call supplier about invoice"
                           class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm @error('title') border-red-400 @enderror">
                    @error('title')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Linked follow-up: record picker --}}
                <div id="followable-record-field" class="{{ $initialFollowableType ? '' : 'hidden' }}">
                    <label class="mb-1 block text-xs font-medium text-slate-600">
                        Record <span class="text-red-500">*</span>
                    </label>
                    <div class="relative" id="followable-search-wrapper">
                        <input type="hidden" name="followable_id" id="followable_id"
                               value="{{ $initialFollowableId }}">
                        <input type="text" id="followable_search" autocomplete="off"
                               value="{{ $initialFollowableLabel }}"
                               @disabled($isEditing)
                               placeholder="{{ $initialFollowableType ? 'Search by name, ID…' : 'Select a type first…' }}"
                               class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm disabled:bg-slate-50 disabled:text-slate-500 @error('followable_id') border-red-400 @enderror">
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

            function toggleTargetFields(type) {
                $('#followable-title-field').toggleClass('hidden', !!type);
                $('#followable-record-field').toggleClass('hidden', !type);
            }

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
                toggleTargetFields(type);
                $('#followable_id').val('');
                $('#followable_search').val('').attr(
                    'placeholder', type ? 'Search by name, ID…' : 'Select a type first…'
                );
                $('#followable_dropdown').addClass('hidden').empty();
                if (type) {
                    $('#followup_title').val('');
                }
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

            toggleTargetFields($('#followable_type').val());
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
                <label class="mb-1 block text-xs font-medium text-slate-600">Notes <span class="text-red-500">*</span></label>
                <textarea name="notes" rows="4"
                          class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm @error('notes') border-red-400 @enderror"
                          placeholder="What needs to happen, or what was discussed…">{{ old('notes', $followup?->notes ?? '') }}</textarea>
                @error('notes')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">Outcome</label>
                <textarea name="outcome" rows="3"
                          class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm @error('outcome') border-red-400 @enderror"
                          placeholder="Result of this follow-up (optional)…">{{ old('outcome', $followup?->outcome ?? '') }}</textarea>
                @error('outcome')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Assigned to</label>
                    <select name="assigned_to"
                            class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm @error('assigned_to') border-red-400 @enderror">
                        <option value="">Unassigned</option>
                        @foreach ($options['assignableUsers'] as $assignableUser)
                            <option value="{{ $assignableUser->id }}"
                                    @selected((int) old('assigned_to', $followup?->assigned_to ?? 0) === $assignableUser->id)>
                                {{ $assignableUser->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('assigned_to')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

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
            </div>

            <div class="sm:w-1/2 sm:pr-2">
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
