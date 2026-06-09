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
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <h3 class="mb-4 text-sm font-semibold text-slate-700">Target</h3>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Model type</label>
                    <select name="followable_type"
                            class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm @error('followable_type') border-red-400 @enderror">
                        <option value="">Select type…</option>
                        @foreach ($options['followableTypes'] as $type)
                            <option value="{{ $type }}"
                                    @selected(old('followable_type', $followup?->followable_type ?? '') === $type)>
                                {{ class_basename($type) }}
                            </option>
                        @endforeach
                    </select>
                    @error('followable_type')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Record ID</label>
                    <input type="number" name="followable_id" min="1"
                           value="{{ old('followable_id', $followup?->followable_id ?? '') }}"
                           placeholder="e.g. 42"
                           class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm @error('followable_id') border-red-400 @enderror">
                    @error('followable_id')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>
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
