@once
<style>
    /* The code column is sized inline in the component; these keep the row itself
       from being collapsed by page-level utilities. */
    [data-phone-country-field] > .flex {
        display: flex;
        align-items: stretch;
        gap: 0.5rem;
        min-width: 0;
        width: 100%;
    }
    [data-phone-country-field] [data-phone-national] {
        flex: 1 1 0%;
        min-width: 0;
    }
    /* The native dropdown panel keeps the browser's own background, so options must not
       inherit a page text colour like text-white or they render invisible. */
    [data-phone-country-field] select option,
    [data-phone-country-field] select optgroup {
        color: #111827;
        background-color: #ffffff;
    }
    /* Match the number input's height, whatever padding the page gives each control. */
    [data-phone-country-field] .searchable-select-root,
    [data-phone-country-field] .searchable-select-root > button {
        height: 100%;
    }
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
        width: 100%;
    }
    [data-phone-country-field] .searchable-select-root > button svg {
        width: 0.9rem;
        height: 0.9rem;
    }
</style>
<script type="application/json" id="phone-country-config">@json(\App\Support\PhoneCountry::clientConfig())</script>
<script src="{{ asset('js/phone-country-field.js') }}?v=6"></script>
@endonce
