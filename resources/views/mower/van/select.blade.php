<x-layouts.mower :title="'Select Van'">
    <div class="space-y-4">
        <div class="rounded-2xl bg-white p-4 shadow-sm">
            <p class="text-sm font-semibold text-slate-800">Choose your van for today</p>
            <p class="mt-1 text-xs text-slate-500">Pick the van you'll be using. Vans taken by another mower today can't be selected.</p>
        </div>

        @if ($errors->has('van_id'))
            <div class="rounded-xl bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first('van_id') }}</div>
        @endif

        @if ($vans->isEmpty())
            <div class="rounded-xl bg-white px-4 py-8 text-center shadow-sm">
                <p class="text-sm text-slate-500">No vans are available. Please contact the office.</p>
            </div>
        @else
            <form action="{{ route('mower.van.store') }}" method="POST" class="space-y-3">
                @csrf

                <div class="space-y-2">
                    @foreach ($vans as $van)
                        @php
                            $assignment = $assignments->get($van->id);
                            $takenByOther = $assignment && $assignment->user_id !== auth()->id();
                            $isMine = $currentVanId === $van->id;
                        @endphp
                        <label
                            class="flex items-center justify-between gap-3 rounded-2xl border bg-white p-4 shadow-sm {{ $takenByOther ? 'cursor-not-allowed opacity-60' : 'cursor-pointer' }} {{ $isMine ? 'border-emerald-500 ring-2 ring-emerald-500' : 'border-slate-200' }}"
                        >
                            <div class="min-w-0">
                                <p class="text-base font-semibold text-slate-800">{{ $van->name }}</p>
                                @if ($takenByOther)
                                    <p class="text-xs text-red-600">Taken by {{ $assignment->user?->name ?? 'another mower' }}</p>
                                @elseif ($isMine)
                                    <p class="text-xs text-emerald-700">Your current van</p>
                                @else
                                    <p class="text-xs text-slate-500">Available</p>
                                @endif
                            </div>
                            <input
                                type="radio"
                                name="van_id"
                                value="{{ $van->id }}"
                                @checked($isMine)
                                @disabled($takenByOther)
                                class="h-5 w-5 shrink-0 cursor-pointer border-slate-300 text-emerald-600 focus:ring-emerald-500"
                            >
                        </label>
                    @endforeach
                </div>

                <button type="submit"
                    class="mower-touch w-full rounded-2xl bg-emerald-700 px-4 py-4 text-base font-semibold text-white shadow-sm active:bg-emerald-800">
                    Confirm van &amp; continue
                </button>
            </form>
        @endif
    </div>
</x-layouts.mower>
