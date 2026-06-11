@php
    $client = $job->client;
    $mapsUrl = $job->latitude && $job->longitude
        ? 'https://www.google.com/maps/dir/?api=1&destination='.$job->latitude.','.$job->longitude
        : ($job->client_address ? 'https://www.google.com/maps/search/?api=1&query='.urlencode($job->client_address) : null);
@endphp

<x-layouts.mower
    :title="$client?->name ?: 'Job details'"
    :show-back="true"
    :back-url="route('mower.index')"
>
    <div id="mower-alert" class="hidden rounded-xl px-4 py-3 text-sm" style="position:fixed;top:1rem;left:50%;transform:translateX(-50%);z-index:9999;min-width:280px;max-width:90vw;"></div>

    <div class="space-y-4">
        <section class="rounded-2xl bg-white p-4 shadow-sm">
            <div class="flex items-start justify-between gap-2">
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Job status</p>
                    <div class="mt-1"><x-jobs.status-badge :status="$job->status" /></div>
                </div>
                <p class="text-right text-xs text-slate-500">
                    {{ optional($job->scheduled_date)->format('M j, Y') }}
                    @if ($job->scheduled_time)
                        <br>{{ \Illuminate\Support\Carbon::parse($job->scheduled_time)->format('g:i A') }}
                    @endif
                </p>
            </div>
            <div class="mt-4">
                <label for="mower-status" class="mb-1 block text-sm font-medium text-slate-700">Update status</label>
                <select id="mower-status" class="mower-touch w-full rounded-xl border border-slate-300 px-3 py-3 text-base">
                    @foreach ($workflowStatuses as $status)
                        <option value="{{ $status }}" @selected($job->status === $status)>{{ $status }}</option>
                    @endforeach
                </select>
                <button type="button" id="mower-save-status" class="mower-touch mt-2 w-full rounded-xl bg-emerald-700 px-4 py-3 text-sm font-semibold text-white hidden">
                    Save status
                </button>
            </div>
        </section>

        <section class="rounded-2xl bg-white p-4 shadow-sm">
            <h2 class="text-sm font-semibold text-slate-900">Customer</h2>
            <p class="mt-1 text-xs text-slate-500">Read-only — contact office to change.</p>
            <dl class="mt-3 space-y-2 text-sm">
                @if ($client?->customer_unique_id)
                    <div class="flex justify-between gap-4">
                        <dt class="text-slate-500">ID</dt>
                        <dd class="font-medium text-slate-900">#{{ $client->customer_unique_id }}</dd>
                    </div>
                @endif
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-500">Name</dt>
                    <dd class="text-right font-medium text-slate-900">{{ $client?->name ?: '—' }}</dd>
                </div>
                @if ($client?->phone)
                    <div class="flex justify-between gap-4">
                        <dt class="text-slate-500">Phone</dt>
                        <dd><a href="tel:{{ preg_replace('/\D+/', '', $client->phone) }}" class="font-medium text-emerald-700">{{ $client->phone }}</a></dd>
                    </div>
                @endif
                @if ($client?->email)
                    <div class="flex justify-between gap-4">
                        <dt class="text-slate-500">Email</dt>
                        <dd class="text-right text-slate-900">{{ $client->email }}</dd>
                    </div>
                @endif
                @if ($client?->customer_type)
                    <div class="flex justify-between gap-4">
                        <dt class="text-slate-500">Type</dt>
                        <dd class="text-right">{{ $client->customer_type }}</dd>
                    </div>
                @endif
                @if ($client?->additional_site_instructions)
                    <div>
                        <dt class="text-slate-500">Site instructions</dt>
                        <dd class="mt-1 rounded-lg bg-slate-50 px-3 py-2 text-slate-800">{{ $client->additional_site_instructions }}</dd>
                    </div>
                @endif
            </dl>
        </section>
        @if ($job->special_remarks)
            <section class="rounded-2xl bg-white p-4 shadow-sm">
                <h2 class="text-sm font-semibold text-slate-900">Job remarks</h2>
                <div class="mt-3">
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Special remarks</p>
                    <p class="mt-1 rounded-lg bg-amber-50 px-3 py-2 text-sm text-slate-800 whitespace-pre-wrap">{{ $job->special_remarks }}</p>
                </div>
            </section>
        @endif

        <section class="rounded-2xl bg-white p-4 shadow-sm">
            <h2 class="text-sm font-semibold text-slate-900">Last visit instructions</h2>
            @if ($lastRemark)
                <p class="mt-2 text-sm text-slate-800 whitespace-pre-wrap">{{ $lastRemark->description }}</p>
                <p class="mt-2 text-xs text-slate-400">
                    Left by {{ $lastRemark->user?->name ?: 'Unknown' }}
                    &mdash; {{ $lastRemark->created_at->format('M j, Y g:i A') }}
                </p>
            @else
                <p class="mt-2 text-sm text-slate-400">No instructions from previous visit.</p>
            @endif
        </section>
        <section class="rounded-2xl bg-white p-4 shadow-sm">
            <h2 class="text-sm font-semibold text-slate-900">Location</h2>
            <p class="mt-2 text-sm text-slate-800">{{ $job->client_address ?: $client?->address }}</p>
            @if ($mapsUrl)
                <a
                    href="{{ $mapsUrl }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="mower-touch mt-3 flex w-full items-center justify-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800"
                >
                    Open in Maps
                </a>
            @endif
            @if ($job->latitude && $job->longitude)
                <p class="mt-2 text-xs text-slate-500">{{ $job->latitude }}, {{ $job->longitude }}</p>
            @endif
        </section>

        <section class="rounded-2xl bg-white p-4 shadow-sm">
            <h2 class="text-sm font-semibold text-slate-900">Payment</h2>
            <div class="mt-3 space-y-3">
                <select id="mower-payment-status" class="mower-touch w-full rounded-xl border border-slate-300 px-3 py-3 text-base">
                    @foreach ($paymentStatuses as $status)
                        <option value="{{ $status }}" @selected($job->payment_status === $status)>{{ $status }}</option>
                    @endforeach
                </select>
                <div id="mower-payment-reason-wrap" class="{{ $job->payment_status === 'Pending' ? '' : 'hidden' }}">
                    <label for="mower-payment-reason" class="mb-1 block text-sm text-slate-600">Reason</label>
                    <input
                        type="text"
                        id="mower-payment-reason"
                        maxlength="255"
                        value="{{ $job->payment_pending_reason }}"
                        class="mower-touch w-full rounded-xl border border-slate-300 px-3 py-3 text-base"
                        placeholder="Why is payment pending?"
                    >
                </div>
                <div id="mower-paid-amount-wrap" class="{{ $job->payment_status === 'Partial' ? '' : 'hidden' }} space-y-3">
                    <div>
                        <label for="mower-first-payment" class="mb-1 block text-sm text-slate-600">First Payment</label>
                        <input
                            type="number"
                            id="mower-first-payment"
                            min="0"
                            step="0.01"
                            value="{{ $job->first_payment }}"
                            class="mower-touch w-full rounded-xl border border-slate-300 px-3 py-3 text-base"
                            placeholder="0.00"
                        >
                    </div>
                    <div>
                        <label for="mower-second-payment" class="mb-1 block text-sm text-slate-600">Second Payment</label>
                        <textarea
                            id="mower-second-payment"
                            rows="3"
                            maxlength="255"
                            class="mower-touch w-full rounded-xl border border-slate-300 px-3 py-3 text-base resize-none"
                            placeholder="e.g. Remaining $30 on Friday"
                        >{{ $job->second_payment }}</textarea>
                    </div>
                </div>
                <button type="button" id="mower-save-payment" class="mower-touch w-full rounded-xl bg-slate-800 px-4 py-3 text-sm font-semibold text-white hidden">
                    Save payment
                </button>
            </div>
        </section>

        <section class="rounded-2xl bg-white p-4 shadow-sm">
            <h2 class="text-sm font-semibold text-slate-900">Time on site</h2>
            <p class="mt-1 text-xs text-slate-500">Estimated: {{ $job->estimated_duration_minutes ?: '—' }} min</p>
            <div class="mt-3 flex gap-2">
                <input
                    type="number"
                    id="mower-consumed-time"
                    min="1"
                    max="1440"
                    value="{{ $job->consumed_time_minutes }}"
                    class="mower-touch min-w-0 flex-1 rounded-xl border border-slate-300 px-3 py-3 text-base"
                    placeholder="Minutes"
                >
                <button type="button" id="mower-save-time" class="mower-touch shrink-0 rounded-xl bg-emerald-700 px-4 py-3 text-sm font-semibold text-white hidden">
                    Log time
                </button>
            </div>
        </section>

        <section class="rounded-2xl bg-white p-4 shadow-sm">
            <h2 class="text-sm font-semibold text-slate-900">Before photos</h2>
            <x-jobs.image-gallery :images="$beforeImages" gallery-id="mower-before-gallery" kind="before" />
            <label id="mower-before-label" class="mower-touch mt-3 flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-slate-300 px-4 py-6 text-center text-sm text-slate-600">
                <input type="file" id="mower-before-input" accept="image/jpeg,image/png,image/webp,image/gif" capture="environment" multiple class="hidden">
                <span id="mower-before-upload-text" class="font-medium text-emerald-700">Add before photos</span>
                <span class="mt-1 text-xs">JPEG, PNG, WebP — max 10MB each</span>
            </label>
        </section>

        <section class="rounded-2xl bg-white p-4 shadow-sm">
            <h2 class="text-sm font-semibold text-slate-900">After photos</h2>
            <x-jobs.image-gallery :images="$afterImages" gallery-id="mower-after-gallery" kind="after" />
            <label id="mower-after-label" class="mower-touch mt-3 flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-slate-300 px-4 py-6 text-center text-sm text-slate-600">
                <input type="file" id="mower-after-input" accept="image/jpeg,image/png,image/webp,image/gif" capture="environment" multiple class="hidden">
                <span id="mower-after-upload-text" class="font-medium text-emerald-700">Add after photos</span>
                <span class="mt-1 text-xs">Compressed on upload</span>
            </label>
        </section>
        <section class="rounded-2xl bg-white p-4 shadow-sm">
            <h2 class="text-sm font-semibold text-slate-900">Next visit instructions</h2>
            <p class="mt-1 text-xs text-slate-500">Next visit instructions given by the customer.</p>
            <div class="mt-3 space-y-3">
                <textarea
                    id="mower-remark-text"
                    rows="4"
                    maxlength="1000"
                    class="mower-touch w-full rounded-xl border border-slate-300 px-3 py-3 text-base resize-none"
                    placeholder="e.g. Gate code is 1234, dog is friendly, avoid the rose bed..."
                ></textarea>
                <button type="button" id="mower-save-remark" class="mower-touch w-full rounded-xl bg-emerald-700 px-4 py-3 text-sm font-semibold text-white hidden">
                    Save instructions
                </button>
            </div>
        </section>
    </div>

    <div id="mower-fixed-save" class="fixed inset-x-0 bottom-4 z-50 flex justify-center pointer-events-none">
        <button id="mower-save-all" type="button"
            @if($job->isVerified()) disabled @endif
            class="pointer-events-auto mower-touch w-11/12 max-w-lg rounded-full px-5 py-3 text-sm font-semibold text-white shadow-lg
                {{ $job->isVerified() ? 'bg-slate-400 cursor-not-allowed' : 'bg-emerald-700' }}">
            {{ $job->isVerified() ? 'Job verified — read only' : 'Save changes' }}
        </button>
    </div>

    </div>

    @push('scripts')
        <script src="{{ asset('js/mower-dashboard.js') }}?v={{ filemtime(public_path('js/mower-dashboard.js')) }}"></script>
        <script>
            window.mowerRoutes = { index: @json(route('mower.index')) };
            window.mowerJobIsVerified = @json($job->isVerified());
            window.mowerJobRoutes = {
                update: @json(route('mower.jobs.update', $job)),
                status: @json(route('mower.jobs.status.update', $job)),
                payment: @json(route('mower.jobs.payment.update', $job)),
                consumedTime: @json(route('mower.jobs.consumed-time.update', $job)),
                before: @json(route('mower.jobs.images.before', $job)),
                after: @json(route('mower.jobs.images.after', $job)),
                deleteBeforeTemplate: @json(route('mower.jobs.images.before.destroy', [$job, '__IMAGE__'])),
                deleteAfterTemplate: @json(route('mower.jobs.images.after.destroy', [$job, '__IMAGE__'])),
                remark: @json(route('mower.jobs.remark.store', $job)),
            };
        </script>
    @endpush
</x-layouts.mower>
