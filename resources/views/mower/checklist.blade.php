<x-layouts.mower :title="'Daily Checklist'">
    <div class="space-y-4">
        <div class="rounded-2xl bg-white p-4 shadow-sm">
            <p class="text-sm font-semibold text-slate-800">Before you start today's work</p>
            <p class="mt-1 text-xs text-slate-500">Check every point below, then tap <strong>Confirm &amp; Continue</strong>.</p>
        </div>

        @if ($errors->has('point_ids') || $errors->has('acknowledged'))
            <div class="rounded-xl bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ $errors->first('point_ids') ?: $errors->first('acknowledged') }}
            </div>
        @endif

        @if ($checklists->isEmpty())
            <div class="rounded-xl bg-white px-4 py-8 text-center shadow-sm">
                <p class="text-sm text-slate-500">No checklist items configured yet.</p>
                <a href="{{ route('mower.index') }}" class="mower-touch mt-4 inline-block rounded-xl bg-emerald-700 px-6 py-3 text-sm font-semibold text-white">Continue to Dashboard</a>
            </div>
        @else
            <form id="checklist-form" action="{{ route('mower.checklist.submit') }}" method="POST" class="space-y-4">
                @csrf

                @foreach ($checklists as $checklist)
                    @if ($checklist->points->isNotEmpty())
                        <div class="rounded-2xl bg-white shadow-sm overflow-hidden">
                            <div class="border-b border-slate-100 bg-emerald-50 px-4 py-3">
                                <p class="text-sm font-semibold text-emerald-800">{{ $checklist->name }}</p>
                            </div>
                            <ul class="divide-y divide-slate-100">
                                @foreach ($checklist->points as $point)
                                    <li class="flex items-start gap-3 px-4 py-3">
                                        <input
                                            type="checkbox"
                                            name="point_ids[]"
                                            value="{{ $point->id }}"
                                            id="point-{{ $point->id }}"
                                            class="checklist-point mt-0.5 h-5 w-5 shrink-0 cursor-pointer rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
                                        >
                                        <label for="point-{{ $point->id }}" class="cursor-pointer space-y-0.5">
                                            @if ($point->heading)
                                                <span class="flex items-center gap-1.5 text-sm font-medium text-slate-800">
                                                    {{ $point->heading }}
                                                    @if ($point->text)
                                                        <button type="button"
                                                            class="more-btn shrink-0 rounded px-1.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-emerald-700 hover:bg-emerald-50"
                                                            aria-expanded="true">
                                                            Less
                                                        </button>
                                                    @endif
                                                </span>
                                            @endif
                                            @if ($point->text)
                                                <span class="point-text text-xs text-slate-500">{{ $point->text }}</span>
                                            @endif
                                        </label>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                @endforeach

                <div class="rounded-2xl border-2 border-emerald-600 bg-emerald-50 p-4 shadow-sm">
                    <label for="acknowledged" class="flex cursor-pointer items-start gap-3">
                        <input
                            type="checkbox"
                            name="acknowledged"
                            id="acknowledged"
                            value="1"
                            class="checklist-point mt-0.5 h-5 w-5 shrink-0 cursor-pointer rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
                        >
                        <span class="space-y-1">
                            <span class="block text-sm font-semibold text-emerald-900">Declaration of Understanding</span>
                            <span class="block text-xs leading-relaxed text-emerald-800">
                                I confirm that I have carefully read and fully understood all items listed in this checklist.
                                I acknowledge my responsibility to comply with each point and understand that this declaration
                                is recorded as part of my daily safety and operational sign-off.
                            </span>
                        </span>
                    </label>
                </div>

                <div class="pb-4">
                    <button
                        type="submit"
                        id="checklist-submit"
                        disabled
                        class="mower-touch w-full rounded-2xl bg-emerald-700 py-4 text-sm font-semibold text-white shadow-sm transition-opacity disabled:cursor-not-allowed disabled:opacity-40"
                    >
                        Confirm &amp; Continue
                    </button>
                    <p id="checklist-hint" class="mt-2 text-center text-xs text-slate-400">Check all items to continue</p>
                </div>
            </form>
        @endif
    </div>

    @push('scripts')
        <script>
            document.querySelectorAll('.more-btn').forEach(function (btn) {
                btn.addEventListener('click', function (e) {
                    e.preventDefault();
                    var text = btn.closest('label').querySelector('.point-text');
                    var expanded = btn.getAttribute('aria-expanded') === 'true';
                    text.classList.toggle('hidden', expanded);
                    btn.setAttribute('aria-expanded', String(!expanded));
                    btn.textContent = expanded ? 'More' : 'Less';
                });
            });

            (function () {
                const form      = document.getElementById('checklist-form');
                const submitBtn = document.getElementById('checklist-submit');
                const hint      = document.getElementById('checklist-hint');

                if (!form) return;

                const points    = Array.from(form.querySelectorAll('.checklist-point:not(#acknowledged)'));
                const ack       = form.querySelector('#acknowledged');
                const totalPts  = points.length;

                function update() {
                    const checkedPts = points.filter(cb => cb.checked).length;
                    const ackDone    = ack ? ack.checked : true;
                    const allDone    = checkedPts === totalPts && ackDone;
                    submitBtn.disabled = !allDone;
                    if (allDone) {
                        hint.textContent = 'All items confirmed — tap to continue';
                    } else if (checkedPts < totalPts) {
                        hint.textContent = `${checkedPts} of ${totalPts} points checked`;
                    } else {
                        hint.textContent = 'Please confirm the declaration to continue';
                    }
                }

                const checkboxes = [...points, ...(ack ? [ack] : [])];

                checkboxes.forEach(cb => cb.addEventListener('change', update));
                update();
            })();
        </script>
    @endpush
</x-layouts.mower>
