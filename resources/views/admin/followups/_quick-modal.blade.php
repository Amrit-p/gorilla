{{--
    Reusable quick "New Follow-up" modal (trigger button lives on the host page).

    Usage:
        @include('admin.followups._quick-modal', [
            'options'  => $followupOptions,
            'modalId'  => 'dashboard-followup-modal',
            'onSaved'  => "window.crmShiftWeek && window.crmShiftWeek(0);", // optional raw JS
        ])

    Open it from anywhere with a trigger carrying data-open-followup="<modalId>".
    The `onSaved` snippet runs inside the AJAX success handler with `response` in scope.
--}}
@props([
    'options',
    'modalId' => 'quick-followup-modal',
    'title' => 'New Follow-Up',
    'onSaved' => '',
])

<x-ui.modal :id="$modalId" :title="$title" maxWidth="max-w-3xl">
    <form id="{{ $modalId }}-form" method="POST" action="{{ route('admin.followups.store') }}" class="space-y-5">
        @csrf

        @include('admin.followups._form', [
            'followup' => null,
            'options' => $options,
        ])

        <div id="{{ $modalId }}-error"
            class="hidden rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700"></div>

        <div class="flex gap-3">
            <x-ui.button type="submit" id="{{ $modalId }}-submit">Save follow-up</x-ui.button>
            <button type="button" data-close-modal="{{ $modalId }}"
                class="rounded-md border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                Cancel
            </button>
        </div>
    </form>
</x-ui.modal>

@push('scripts')
<script>
    (function () {
        var modalId = @json($modalId);
        var $modal = $('#' + modalId);
        var $form = $('#' + modalId + '-form');
        var $submit = $('#' + modalId + '-submit');
        var $error = $('#' + modalId + '-error');

        function openFollowupModal() {
            $error.addClass('hidden').text('');
            $modal.removeClass('hidden').addClass('flex');
        }

        function closeFollowupModal() {
            $modal.addClass('hidden').removeClass('flex');
        }

        $(document).on('click', '[data-open-followup="' + modalId + '"]', openFollowupModal);
        $(document).on('click', '[data-close-modal="' + modalId + '"]', closeFollowupModal);

        $form.on('submit', function (event) {
            event.preventDefault();
            $error.addClass('hidden').text('');
            $submit.prop('disabled', true).addClass('opacity-60');

            $.ajax({
                url: $form.attr('action'),
                method: 'POST',
                data: $form.serialize(),
                headers: { Accept: 'application/json' },
                success: function (response) {
                    closeFollowupModal();
                    $form[0].reset();
                    $form.find('#followable_id').val('');
                    $form.find('#followable_type').trigger('change');
                    {!! $onSaved !!}
                },
                error: function (xhr) {
                    var errors = (xhr.responseJSON && xhr.responseJSON.errors) || {};
                    var message = (Object.values(errors)[0] || [])[0] ||
                        (xhr.responseJSON && xhr.responseJSON.message) ||
                        'Unable to save the follow-up.';
                    $error.removeClass('hidden').text(message);
                },
                complete: function () {
                    $submit.prop('disabled', false).removeClass('opacity-60');
                },
            });
        });
    })();
</script>
@endpush
