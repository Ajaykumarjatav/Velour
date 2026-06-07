{{-- After a successful submit (not canceled by validation or JS), disable submit controls to prevent double-post. Opt out: data-no-disable-submit on <form> or control. --}}
<script>
(function () {
    'use strict';

    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (!(form instanceof HTMLFormElement)) return;
        if (form.hasAttribute('data-no-disable-submit')) return;
        if (e.defaultPrevented) return;

        var submitter = e.submitter;
        if (submitter instanceof HTMLElement && submitter.hasAttribute('data-no-disable-submit')) return;

        form.setAttribute('aria-busy', 'true');

        form.querySelectorAll('button[type="submit"], input[type="submit"]').forEach(function (el) {
            if (el.hasAttribute('data-no-disable-submit')) return;
            el.disabled = true;
        });
        form.querySelectorAll('button:not([type])').forEach(function (el) {
            if (el.hasAttribute('data-no-disable-submit')) return;
            el.disabled = true;
        });
    }, false);
})();
</script>
