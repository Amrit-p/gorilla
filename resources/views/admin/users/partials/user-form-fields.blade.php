{{-- Legacy include — delegates to shared Gorilla CRM user form component. --}}
@include('components.forms.user-fields', [
    'roles' => $roles ?? [],
    'efficiencies' => $efficiencies ?? \App\Enums\UserEfficiency::values(),
    'statuses' => $statuses ?? \App\Enums\UserStatus::values(),
    'user' => $user ?? null,
    'showUniqueId' => $showUniqueId ?? false,
])
