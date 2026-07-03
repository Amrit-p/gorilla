{{-- jQuery Validate for client-side form validation (CDN per project rules) --}}
<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.20.0/dist/jquery.validate.min.js" crossorigin="anonymous"></script>
<script src="{{ asset('js/crm-form-validation.js') }}?v=9"></script>
@if (isset($errors) && $errors->any())
    <script>
        window.__serverValidationErrors = @json($errors->messages());
    </script>
@endif
