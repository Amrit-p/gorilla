<x-layouts.dashboard :title="'Create Customer'" subtitle="Manual customer intake">
    <x-ui.breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard.index')],
        ['label' => 'Customers', 'url' => route('admin.clients.index')],
        ['label' => 'Create Customer'],
    ]" />

    <div class="mx-auto max-w-6xl space-y-5">
        <div>
            <h2 class="text-lg font-semibold text-slate-900">Create Customer</h2>
            <p class="text-sm text-slate-600">Manual customer intake with service, safety, payment, and property location.</p>
        </div>

        @if (session('success'))
            <div class="rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700">{{ session('success') }}</div>
        @endif

        <form method="POST" action="{{ route('admin.clients.store') }}" novalidate class="js-validate-form grid grid-cols-1 gap-4 rounded-2xl border border-slate-200 bg-white p-6 sm:grid-cols-2" data-validate="client">
            @csrf
            @include('admin.clients.partials.form-fields')
            <div class="flex gap-3 sm:col-span-2">
                <x-ui.button type="submit">Create Customer</x-ui.button>
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
