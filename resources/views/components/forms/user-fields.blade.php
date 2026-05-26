@props([
    'roles' => [],
    'efficiencies' => [],
    'statuses' => [],
    'user' => null,
    'showUniqueId' => false,
])

@php
    $userModel = $user;
@endphp

@if ($showUniqueId && $userModel?->user_unique_id)
    <x-ui.input label="User ID" name="user_unique_id_display" :value="'#' . $userModel->user_unique_id" readonly class="bg-slate-50" />
@endif

<x-ui.input label="Name" name="name" :value="$userModel?->name" />
<x-ui.input label="Email" name="email" type="email" :value="$userModel?->email" />
<x-ui.input label="Phone" name="phone" :value="$userModel?->phone" />
<x-ui.select
    label="Role"
    name="role"
    placeholder="Select role"
    :options="array_combine($roles, $roles)"
    :value="$userModel?->roles?->pluck('name')->first()"
/>

<x-ui.select
    label="Efficiency"
    name="efficiency"
    :options="array_combine($efficiencies, $efficiencies)"
    :value="$userModel?->efficiency ?? 'Average'"
/>

<x-ui.select
    label="Status"
    name="status"
    :options="array_combine($statuses, $statuses)"
    :value="$userModel?->status ?? 'Active'"
/>
