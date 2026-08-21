{{-- Guided setup: blink/highlight the next incomplete field 2–3 times. --}}
<style>
    @keyframes setup-focus-pulse {
        0%, 100% {
            box-shadow: 0 0 0 0 rgba(245, 158, 11, 0);
            outline: 2px solid transparent;
            outline-offset: 4px;
        }
        35% {
            box-shadow: 0 0 0 6px rgba(245, 158, 11, 0.35);
            outline: 2px solid rgb(245, 158, 11);
            outline-offset: 4px;
        }
        70% {
            box-shadow: 0 0 0 2px rgba(245, 158, 11, 0.15);
            outline: 2px solid rgba(245, 158, 11, 0.7);
            outline-offset: 3px;
        }
    }
    .setup-focus-cue {
        animation: setup-focus-pulse 0.7s ease-in-out 3;
        border-radius: 0.75rem;
        position: relative;
        z-index: 1;
    }
</style>
<script>
(function () {
    var params = new URLSearchParams(window.location.search);
    var focusId = params.get('setup_focus');
    if (!focusId) return;

    function clearFocusParam() {
        params.delete('setup_focus');
        var next = window.location.pathname;
        var qs = params.toString();
        if (qs) next += '?' + qs;
        window.history.replaceState({}, '', next);
    }

    function runCue(el) {
        if (!el) return;
        el.classList.add('setup-focus-cue');
        try { el.scrollIntoView({ behavior: 'smooth', block: 'center' }); } catch (e) {}
        var input = el.matches('input, select, textarea, button, a')
            ? el
            : el.querySelector('input:not([type="hidden"]), select, textarea, button, a');
        if (input && typeof input.focus === 'function') {
            try { input.focus({ preventScroll: true }); } catch (e) { try { input.focus(); } catch (e2) {} }
        }
        window.setTimeout(function () {
            el.classList.remove('setup-focus-cue');
            clearFocusParam();
        }, 2300);
    }

    function attempt(triesLeft) {
        var el = document.getElementById(focusId);
        if (el) {
            // Wait one frame so Alpine x-show tabs are painted.
            window.requestAnimationFrame(function () {
                window.setTimeout(function () { runCue(el); }, 120);
            });
            return;
        }
        if (triesLeft <= 0) {
            clearFocusParam();
            return;
        }
        window.setTimeout(function () { attempt(triesLeft - 1); }, 150);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () { attempt(20); });
    } else {
        attempt(20);
    }
})();
</script>
