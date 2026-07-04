<div id="leads-table-container">
    @include('admin.leads.partials.table', ['leads' => $leads, 'convertedOnly' => $convertedOnly ?? false])
</div>
