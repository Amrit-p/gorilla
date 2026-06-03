@php
    $bonusModel = $bonus ?? null;
@endphp

<div>
    <label class="mb-1 block text-sm font-medium text-slate-700">Employee <span class="text-red-500">*</span></label>
    <select name="user_id" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm @error('user_id') border-red-400 @enderror" required>
        <option value="">Select employee</option>
        @php $defaultUserId = old('user_id', $bonusModel?->user_id ?? request('user_id')); @endphp
        @foreach ($employees as $employee)
            <option value="{{ $employee->id }}" @selected($defaultUserId == $employee->id)>
                #{{ $employee->user_unique_id }} — {{ $employee->name }}
            </option>
        @endforeach
    </select>
    @error('user_id')
        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
    @enderror
</div>

<div>
    <label class="mb-1 block text-sm font-medium text-slate-700">Amount <span class="text-red-500">*</span></label>
    <input
        type="number"
        name="amount"
        step="0.01"
        min="0.01"
        value="{{ old('amount', $bonusModel?->amount) }}"
        placeholder="0.00"
        class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm @error('amount') border-red-400 @enderror"
        required
    >
    @error('amount')
        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
    @enderror
</div>

<div>
    <label class="mb-1 block text-sm font-medium text-slate-700">Bonus Date <span class="text-red-500">*</span></label>
    <input
        type="date"
        name="bonus_date"
        value="{{ old('bonus_date', $bonusModel?->bonus_date?->format('Y-m-d')) }}"
        class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm @error('bonus_date') border-red-400 @enderror"
        required
    >
    @error('bonus_date')
        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
    @enderror
</div>

<div class="sm:col-span-2">
    <label class="mb-1 block text-sm font-medium text-slate-700">Description</label>
    <textarea
        name="description"
        rows="3"
        placeholder="Optional notes about this bonus..."
        class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm @error('description') border-red-400 @enderror"
    >{{ old('description', $bonusModel?->description) }}</textarea>
    @error('description')
        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
    @enderror
</div>
