<x-layouts.mower :title="'Job Map'" :showBack="true" :backUrl="route('mower.index')">
    <div class="h-[calc(100vh-56px-2rem)] rounded-2xl border border-slate-200 bg-white p-2 shadow-sm">
        <div class="relative h-full w-full overflow-hidden rounded-xl" id="jobs-map-wrap">
            <div id="jobs-map" class="h-full w-full bg-slate-100"></div>

            {{-- Alert overlay --}}
            <div id="map-alert" class="absolute bottom-3 left-1/2 z-[1000] hidden -translate-x-1/2"></div>

            {{-- Route toggle + filter toggle --}}
            <div class="absolute right-3 top-3 z-[1000] flex items-center gap-2">
                <button id="map-route-toggle-btn" type="button" onclick="window.crmToggleRoute()"
                    class="mower-touch flex items-center gap-1.5 rounded-full border border-slate-200 bg-white px-3 py-2 text-xs font-medium text-slate-600 shadow-sm active:bg-slate-50">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                    </svg>
                    <span id="map-route-toggle-label">Route</span>
                </button>

                <button id="map-filter-toggle-btn" type="button"
                    class="mower-touch flex items-center gap-1.5 rounded-full border border-slate-200 bg-white px-3 py-2 text-xs font-medium text-slate-600 shadow-sm active:bg-slate-50">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z" />
                    </svg>
                    Filters
                    <span id="map-filter-count" class="hidden rounded-full bg-emerald-100 px-1.5 py-0.5 text-[10px] font-semibold text-emerald-700"></span>
                </button>
            </div>

            {{-- Equipment legend overlay --}}
            <div id="map-legend" class="absolute left-3 top-3 z-[1000] max-w-[55vw] rounded-md border border-slate-200 bg-white/95 px-2.5 py-2 text-[11px] shadow-sm">
                <p class="mb-1 font-semibold text-slate-500">Equipment</p>
                <div id="map-legend-items" class="space-y-1 text-slate-600"></div>
            </div>
        </div>
    </div>

    {{-- Filters bottom sheet --}}
    <div id="map-filters-backdrop" class="fixed inset-0 z-[1400] hidden bg-slate-900/40"></div>
    <div id="map-filters-sheet" class="fixed inset-x-0 bottom-0 z-[1500] hidden max-h-[85vh] translate-y-full flex-col rounded-t-2xl bg-white shadow-2xl transition-transform duration-200">
        <div class="flex shrink-0 items-center justify-between border-b border-slate-100 px-4 py-3">
            <p class="text-sm font-semibold text-slate-800">Filters</p>
            <button id="map-filters-close" type="button" class="mower-touch flex h-9 w-9 items-center justify-center rounded-full text-slate-400 hover:bg-slate-100" aria-label="Close filters">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form id="job-filter-form" class="min-h-0 flex-1 space-y-3 overflow-y-auto px-4 py-3">

            {{-- Scope pills --}}
            <div class="flex flex-wrap gap-1.5">
                @foreach ($listScopes as $scopeKey => $scopeLabel)
                    <button type="button" data-list-scope="{{ $scopeKey }}"
                        class="job-list-scope rounded-full border border-slate-300 px-2.5 py-1.5 text-xs font-medium text-slate-600">{{ $scopeLabel }}</button>
                @endforeach
                <button type="button" data-list-scope=""
                    class="job-list-scope rounded-full border border-slate-800 bg-slate-800 px-2.5 py-1.5 text-xs font-medium text-white">All</button>
            </div>
            <input type="hidden" name="list_scope" id="job-list-scope" value="">

            <div class="flex flex-col gap-1">
                <label class="text-[10px] font-medium uppercase tracking-wide text-slate-400">Search</label>
                <input type="text" name="search" placeholder="Customer, address, ID…"
                    class="filter w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 placeholder-slate-400 outline-none focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-100">
            </div>

            <div class="flex flex-col gap-1">
                <label class="text-[10px] font-medium uppercase tracking-wide text-slate-400">Zone</label>
                <select name="zone_id" class="filter w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-100">
                    <option value="">All zones</option>
                    @foreach ($zones as $zone)
                        <option value="{{ $zone->id }}">{{ $zone->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex flex-col gap-1">
                <label class="text-[10px] font-medium uppercase tracking-wide text-slate-400">Status</label>
                <select name="status" class="filter w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-100">
                    <option value="">Any status</option>
                    @foreach ($workflowStatuses as $workflowStatus)
                        <option value="{{ $workflowStatus }}">{{ $workflowStatus }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex flex-col gap-1">
                <label class="text-[10px] font-medium uppercase tracking-wide text-slate-400">Recurrence</label>
                <select name="recurrence_id" class="filter w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-100">
                    <option value="">Any recurrence</option>
                    @foreach ($recurrences as $recurrence)
                        <option value="{{ $recurrence->id }}">{{ $recurrence->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex flex-col gap-1">
                <label class="text-[10px] font-medium uppercase tracking-wide text-slate-400">Payment Mode</label>
                <select name="payment_mode" class="filter w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-100">
                    <option value="">Any payment mode</option>
                    @foreach ($paymentModes as $mode)
                        <option value="{{ $mode }}">{{ $mode }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex flex-col gap-1">
                <label class="text-[10px] font-medium uppercase tracking-wide text-slate-400">Payment Status</label>
                <select name="payment_status" class="filter w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-100">
                    <option value="">Any payment status</option>
                    @foreach ($paymentStatuses as $payStatus)
                        <option value="{{ $payStatus }}">{{ $payStatus }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex flex-col gap-1">
                <label class="text-[10px] font-medium uppercase tracking-wide text-slate-400">Equipment Type</label>
                <select name="equipment_type_id" class="filter w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-100">
                    <option value="">Any equipment</option>
                    @foreach ($equipmentTypes as $equipmentType)
                        <option value="{{ $equipmentType['id'] }}">{{ $equipmentType['name'] }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex flex-col gap-1">
                <label class="text-[10px] font-medium uppercase tracking-wide text-slate-400">Customer Type</label>
                <select name="customer_type" class="filter w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-100">
                    <option value="">Any type</option>
                    @foreach ($customerTypes as $type)
                        <option value="{{ $type }}">{{ $type }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex flex-col gap-1">
                <label class="text-[10px] font-medium uppercase tracking-wide text-slate-400">Service Type</label>
                <select name="service_type" class="filter w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-100">
                    <option value="">Any service</option>
                    @foreach ($serviceTypes as $serviceType)
                        <option value="{{ $serviceType }}">{{ $serviceType }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex flex-col gap-1">
                <label class="text-[10px] font-medium uppercase tracking-wide text-slate-400">Scheduled Date</label>
                <x-ui.daterange-picker placeholder="Date range" name="date_range" :show-ranges="true" class="filter" />
            </div>

        </form>

        <div class="flex shrink-0 items-center gap-2 border-t border-slate-100 px-4 py-3 mower-safe-bottom">
            <button id="map-filters-reset" type="button" class="mower-touch flex-1 rounded-xl border border-slate-200 text-sm font-medium text-slate-600 active:bg-slate-50">Reset</button>
            <button id="map-filters-apply" type="submit" form="job-filter-form" class="mower-touch flex-1 rounded-xl bg-emerald-700 text-sm font-semibold text-white active:bg-emerald-800">Apply</button>
        </div>
    </div>

    @push('scripts')
        <script src="{{ asset('js/map-job-popup.js') }}?v={{ @filemtime(public_path('js/map-job-popup.js')) ?: 1 }}"></script>
        @php
            $jsMapConfig = array_merge($mapConfig, [
                'canViewJobs'     => true,
                'canManageJobs'   => false,
                'canSelectJobs'   => false,
                'isMower'         => true,
                'mowerJobBaseUrl' => url('/mower/jobs'),
            ]);
        @endphp
        <script>
            window.crmJobsMapConfig = @json($jsMapConfig);
            window.crmJobsMapConfig.highlightJob = null;

            function showMapAlert(message, isError) {
                var baseClass = isError
                    ? 'rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700'
                    : 'rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700';
                $('#map-alert').removeClass('hidden').attr('class', baseClass).text(message);
            }
        </script>

        @if ($mapConfig['provider'] === 'google')
            @include('components.scripts.google-maps')
            <script src="{{ asset('js/google-jobs-map.js') }}?v={{ @filemtime(public_path('js/google-jobs-map.js')) ?: 1 }}"></script>
            <script>window.crmInitJobsMap(window.crmJobsMapConfig);</script>
        @else
            <link rel="stylesheet" href="{{ asset('js/leaflet/leaflet.css') }}">
            <link rel="stylesheet" href="{{ asset('js/leaflet/MarkerCluster.css') }}">
            <link rel="stylesheet" href="{{ asset('js/leaflet/MarkerCluster.Default.css') }}">
            <script src="{{ asset('js/leaflet/leaflet.js') }}"></script>
            <script src="{{ asset('js/leaflet/leaflet.markercluster.js') }}"></script>
            <script src="{{ asset('js/leaflet-jobs-map.js') }}?v={{ @filemtime(public_path('js/leaflet-jobs-map.js')) ?: 1 }}"></script>
            <script>window.crmInitJobsMap(window.crmJobsMapConfig);</script>
        @endif

        <script>
            (function () {
                function getFilters() {
                    var params = new URLSearchParams(new FormData(document.getElementById('job-filter-form')));
                    var filters = Object.fromEntries(params.entries());
                    filters.list_scope = $('#job-list-scope').val();
                    return filters;
                }

                function activeFilterCount(filters) {
                    return Object.keys(filters).filter(function (key) {
                        return filters[key] !== '' && filters[key] !== null && filters[key] !== undefined;
                    }).length;
                }

                function syncFilterBadge() {
                    var count = activeFilterCount(getFilters());
                    $('#map-filter-count').toggleClass('hidden', count === 0).text(count);
                }

                function openSheet() {
                    $('#map-filters-backdrop').removeClass('hidden');
                    $('#map-filters-sheet').removeClass('hidden').addClass('flex');
                    requestAnimationFrame(function () {
                        $('#map-filters-sheet').removeClass('translate-y-full');
                    });
                }

                function closeSheet() {
                    $('#map-filters-sheet').addClass('translate-y-full');
                    setTimeout(function () {
                        $('#map-filters-sheet').addClass('hidden').removeClass('flex');
                        $('#map-filters-backdrop').addClass('hidden');
                    }, 200);
                }

                $('#map-filter-toggle-btn').on('click', openSheet);
                $('#map-filters-close, #map-filters-backdrop').on('click', closeSheet);

                $('.job-list-scope').on('click', function () {
                    var scope = $(this).data('list-scope');
                    $('#job-list-scope').val(scope);
                    $('.job-list-scope').removeClass('border-slate-800 bg-slate-800 text-white border-emerald-600 bg-emerald-50 text-emerald-800')
                        .addClass('border-slate-300 text-slate-600');
                    $(this).removeClass('border-slate-300 text-slate-600').addClass(
                        scope === '' ? 'border-slate-800 bg-slate-800 text-white' : 'border-emerald-600 bg-emerald-50 text-emerald-800'
                    );
                });

                $('#job-filter-form').on('submit', function (e) {
                    e.preventDefault();
                    syncFilterBadge();
                    window.reloadMapFromFilter(getFilters());
                    closeSheet();
                });

                $('#map-filters-reset').on('click', function () {
                    document.getElementById('job-filter-form').reset();
                    $('#job-list-scope').val('');
                    $('.job-list-scope').removeClass('border-slate-800 bg-slate-800 text-white border-emerald-600 bg-emerald-50 text-emerald-800')
                        .addClass('border-slate-300 text-slate-600');
                    $('.job-list-scope[data-list-scope=""]').removeClass('border-slate-300 text-slate-600').addClass('border-slate-800 bg-slate-800 text-white');
                    syncFilterBadge();
                    window.reloadMapFromFilter({});
                    closeSheet();
                });

                var currentFilters = {};
                window.reloadMapFromFilter = function (filters) {
                    currentFilters = filters || {};
                    window.crmMapLoad(currentFilters);
                };

                window.crmMapLoad(getFilters());
            })();
        </script>
    @endpush
</x-layouts.mower>
