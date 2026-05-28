<x-layouts.dashboard :title="'Edit Customer'">
    <x-ui.breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard.index')],
        ['label' => 'Customers', 'url' => route('admin.clients.index')],
        ['label' => '#' . $client->customer_unique_id, 'url' => route('admin.clients.show', $client)],
        ['label' => 'Edit'],
    ]" />

    <div class="mx-auto max-w-6xl space-y-5">
        <div>
            <h2 class="text-lg font-semibold text-slate-900">Edit Customer</h2>
            <p class="text-sm text-slate-600">{{ $client->name }}</p>
        </div>

        <form method="POST" action="{{ route('admin.clients.update', $client) }}" novalidate class="js-validate-form grid grid-cols-1 gap-4 rounded-2xl border border-slate-200 bg-white p-6 sm:grid-cols-2" data-validate="client">
            @csrf
            @method('PATCH')
            @include('admin.clients.partials.form-fields', ['client' => $client])
            <div class="flex gap-3 sm:col-span-2">
                <x-ui.button type="submit">Update Customer</x-ui.button>
                <a href="{{ route('admin.clients.index') }}" class="rounded-md border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Cancel</a>
            </div>
        </form>
    </div>
</x-layouts.dashboard>

<script>
    function toggleClientPaymentReason() {
        $('#client-payment-reason-wrap').toggleClass('hidden', $('#client-payment-status').val() !== 'Pending');
    }
    $('#client-payment-status').on('change', function () {
        toggleClientPaymentReason();
        $('[name="payment_status_reason"]').valid();
    });
    toggleClientPaymentReason();
</script>
