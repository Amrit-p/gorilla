{{--
    Global "New Follow-up" modal, triggered from the navbar button (data-open-followup).
    Rendered once by the dashboard layout so it is available on every page, like Hold Jobs.
    `followupOptions` is shared by a view composer on components.layouts.dashboard.
--}}
@if ($followupOptions ?? null)
    @include('admin.followups._quick-modal', [
        'options' => $followupOptions,
        'modalId' => 'global-followup-modal',
        'onSaved' => "if (typeof window.crmShiftWeek === 'function') { window.crmShiftWeek(0); }",
    ])
@endif
