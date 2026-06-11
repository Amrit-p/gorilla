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
        @if ($errors->any())
            <div class="rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <form method="POST" action="{{ route('admin.clients.update', $client) }}" enctype="multipart/form-data" novalidate class="js-validate-form grid grid-cols-1 gap-4 rounded-2xl border border-slate-200 bg-white p-6 sm:grid-cols-2" data-validate="client">
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
