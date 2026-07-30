@php
    $convertedOnly = $convertedOnly ?? false;
    $leadsListRoute = $convertedOnly ? route('admin.leads.converted') : route('admin.leads.index');
@endphp
<x-layouts.dashboard
    :title="$convertedOnly ? 'Converted to Customer' : 'Lead Management'"
    :subtitle="$convertedOnly ? 'Leads that have been converted into jobs.' : 'View, filter, and manage your sales leads in one place.'"
>
    <div class="space-y-5">

        @if (session('success'))
            <div class="rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700">{{ session('success') }}</div>
        @endif

        <div id="lead-alert" class="hidden"></div>

        @include('admin.leads.partials.filter-bar', [
            'filters' => $filters,
            'statuses' => $statuses,
            'salesUsers' => $salesUsers,
            'zones' => $zones,
            'recurrences' => $recurrences,
            'filterUrl' => $leadsListRoute,
            'resetUrl' => $leadsListRoute,
        ])

        <div id="leads-table-container">
            @include('admin.leads.partials.table', ['leads' => $leads, 'convertedOnly' => $convertedOnly])
        </div>
    </div>

    @include('admin.leads.partials.lead-modals', ['statuses' => $statuses])

    <x-leads.import-modal />

    @include('admin.partials.dropdown-script')

    @include('admin.leads.partials.lead-actions-script')

    <script>
        // Bake the current filters into the export links on initial load
        // (subsequent changes are kept in sync by the filter bar itself).
        updateLeadExportLinks();
    </script>
</x-layouts.dashboard>
