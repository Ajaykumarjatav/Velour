@once
<style>
    [data-phone-country-field] .searchable-select-root [role="listbox"] {
        min-width: 16rem;
        width: max-content;
        max-width: min(20rem, 80vw);
    }
    [data-phone-country-field] .searchable-select-root > button {
        padding-left: 0.5rem;
        padding-right: 0.35rem;
        gap: 0.15rem;
        font-size: 0.8125rem;
    }
    [data-phone-country-field] .searchable-select-root > button svg {
        width: 0.9rem;
        height: 0.9rem;
    }
</style>
<script type="application/json" id="phone-country-config">@json(\App\Support\PhoneCountry::clientConfig())</script>
<script src="{{ asset('js/phone-country-field.js') }}?v=3"></script>
@endonce
