<x-layouts.dashboard :title="'Add Employee Bonus'">
    <x-ui.breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard.index')],
        ['label' => 'Employee Bonuses', 'url' => route('admin.employee-bonuses.index')],
        ['label' => 'Add Bonus'],
    ]" />

    <div class="mx-auto max-w-3xl space-y-5">
        <div>
            <h2 class="text-lg font-semibold text-slate-900">Add Employee Bonus</h2>
            <p class="text-sm text-slate-600">Record a bonus payment for a team member.</p>
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

        <form method="POST" action="{{ route('admin.employee-bonuses.store') }}" novalidate
              class="grid grid-cols-1 gap-4 rounded-2xl border border-slate-200 bg-white p-6 sm:grid-cols-2">
            @csrf
            @include('admin.employee-bonuses.partials.form-fields')
            <div class="flex gap-3 sm:col-span-2">
                <x-ui.button type="submit">Add Bonus</x-ui.button>
                <a href="{{ route('admin.employee-bonuses.index') }}"
                   class="rounded-md border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</x-layouts.dashboard>
