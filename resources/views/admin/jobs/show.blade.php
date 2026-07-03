<x-layouts.dashboard :title="'Job #'.$job->id" :subtitle="$job->client?->name">
    <x-ui.breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard.index')],
        ['label' => 'Jobs', 'url' => route('admin.jobs.index')],
        ['label' => 'Job #'.$job->id],
    ]" />

    <div class="space-y-5">
        @if (session('success'))
            <div class="rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700">{{ session('success') }}</div>
        @endif

        @include('admin.partials.job-alert')

        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">{{ $job->client?->name ?: 'Job' }}</h2>
                <p class="text-sm text-slate-600">
                    {{ optional($job->scheduled_date)->format('l, M j, Y') }}
                    {{ $job->scheduled_time ? 'at '.\Illuminate\Support\Carbon::parse($job->scheduled_time)->format('g:i A') : '' }}
                    • {{ $job->estimated_duration_minutes }} min
                </p>
                <div class="mt-2"><x-jobs.status-badge :status="$job->status" /></div>
            </div>
            <div class="flex flex-wrap gap-2">
                @can('update', $job)
                    <a href="{{ route('admin.jobs.edit', $job) }}" class="rounded-md border border-slate-300 px-3 py-2 text-sm hover:bg-slate-50">Edit</a>
                @endcan
            </div>
        </div>

        <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
            <div class="space-y-4 lg:col-span-2">
                <div class="rounded-2xl border border-slate-200 bg-white p-5">
                    <h3 class="text-sm font-semibold text-slate-900">Job tracking</h3>
                    <dl class="mt-4 grid grid-cols-1 gap-3 text-sm sm:grid-cols-2">
                        <div>
                            <dt class="text-slate-500">Address</dt>
                            <dd class="font-medium text-slate-800">{{ $job->client_address }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">Services</dt>
                            <dd>{{ is_array($job->required_services) ? implode(', ', $job->required_services) : '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">Parking / site type</dt>
                            <dd>{{ $job->parking_status }} • {{ $job->customer_type }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">Payment</dt>
                            <dd>{{ $job->payment_mode }} • {{ $job->payment_status }}</dd>
                        </div>
                        @if ($job->pet_warning)
                            <div class="sm:col-span-2">
                                <dt class="text-slate-500">Pet warning</dt>
                                <dd class="text-amber-800">{{ $job->pet_warning }}</dd>
                            </div>
                        @endif
                        @if ($job->site_instructions)
                            <div class="sm:col-span-2">
                                <dt class="text-slate-500">Site instructions</dt>
                                <dd class="whitespace-pre-wrap">{{ $job->site_instructions }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>


                @if (count($beforeImages) || count($afterImages))
                    <div class="rounded-2xl border border-slate-200 bg-white p-5">
                        <h3 class="text-sm font-semibold text-slate-900">Field photos</h3>
                        @if (count($beforeImages))
                            <p class="mt-3 text-xs font-medium uppercase tracking-wide text-slate-500">Before</p>
                            <x-jobs.image-gallery :images="$beforeImages" gallery-id="job-before-gallery" kind="before" :readonly="true" />
                        @endif
                        @if (count($afterImages))
                            <p class="mt-4 text-xs font-medium uppercase tracking-wide text-slate-500">After</p>
                            <x-jobs.image-gallery :images="$afterImages" gallery-id="job-after-gallery" kind="after" :readonly="true" />
                        @endif
                    </div>
                @endif

                @if (count($attachedImages))
                    <div class="rounded-2xl border border-slate-200 bg-white p-5">
                        <h3 class="text-sm font-semibold text-slate-900">Attached images</h3>
                        <div class="mt-3 grid grid-cols-3 gap-2">
                            @foreach ($attachedImages as $image)
                                <a href="{{ $image['url'] }}" target="_blank" rel="noopener" class="aspect-square overflow-hidden rounded-lg bg-slate-100">
                                    <img
                                        src="{{ $image['thumb_url'] }}"
                                        alt="Attached"
                                        loading="lazy"
                                        decoding="async"
                                        class="h-full w-full object-cover"
                                    >
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
                @include('admin.jobs.partials.timeline', ['timeline' => $timeline])

            </div>

            <div class="space-y-4">
                <div class="rounded-2xl border border-slate-200 bg-white p-5">
                    <h3 class="text-sm font-semibold text-slate-900">Mower assignment</h3>
                    <ul class="mt-3 space-y-2 text-sm">
                        @forelse ($job->assignedEmployees as $employee)
                            <li class="flex items-center justify-between gap-2 rounded-md border border-slate-100 px-3 py-2">
                                <span>{{ $employee->name }}</span>
                                <x-ui.efficiency-badge :efficiency="$employee->efficiency" />
                            </li>
                        @empty
                            <li class="text-slate-500">No mowers assigned.</li>
                        @endforelse
                    </ul>
                    @if ($job->doneByUser)
                        <p class="mt-3 text-xs text-slate-500">Primary: <strong>{{ $job->doneByUser->name }}</strong></p>
                    @endif
                </div>

                @if ($job->client)
                    <div class="rounded-2xl border border-slate-200 bg-white p-5">
                        <h3 class="text-sm font-semibold text-slate-900">Customer</h3>
                        <p class="mt-2 text-sm">
                            <a href="{{ route('admin.clients.show', $job->client) }}" class="text-emerald-700 hover:underline">
                                #{{ $job->client->customer_unique_id }} {{ $job->client->name }}
                            </a>
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @include('admin.partials.job-actions-script')

    <script>
        $('.job-quick-status').on('click', function () {
            const id = $(this).data('id');
            const status = $(this).data('status');
            $.ajax({
                url: "{{ route('admin.jobs.bulk.status.update') }}",
                method: 'POST',
                data: { _token: "{{ csrf_token() }}", job_ids: [id], status: status },
                headers: { Accept: 'application/json' },
                success: function () { window.location.reload(); },
                error: function (xhr) {
                    showJobAlert(xhr.responseJSON?.message || 'Status update failed.', true);
                },
            });
        });
    </script>
</x-layouts.dashboard>
