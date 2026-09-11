{{-- Keeps the in-progress till cart in localStorage so a reload, an expired CSRF token,
     or an accidental back button never wipes a sale the user already rang up.
     Shared by the till (pos.create) and the receipt page (pos.show), which clears it. --}}
<script>
window.PosDraft = (function () {
    var MAX_AGE_MS = 12 * 60 * 60 * 1000;
    // Same key on both pages: everything in the path before "/pos/" (store-scoped).
    var key = 'easygrox.pos.draft:' + (window.location.pathname.split('/pos/')[0] || '/');

    function clear() {
        try { window.localStorage.removeItem(key); } catch (e) { /* storage unavailable */ }
    }

    function read() {
        try {
            var raw = window.localStorage.getItem(key);
            if (!raw) return null;
            var draft = JSON.parse(raw);
            if (!draft || !Array.isArray(draft.cart) || draft.cart.length === 0) return null;
            if (!draft.savedAt || (Date.now() - draft.savedAt) > MAX_AGE_MS) {
                clear();
                return null;
            }
            return draft;
        } catch (e) {
            return null;
        }
    }

    function write(draft) {
        if (!draft || !Array.isArray(draft.cart) || draft.cart.length === 0) {
            clear();
            return;
        }
        try {
            window.localStorage.setItem(key, JSON.stringify(Object.assign({}, draft, { savedAt: Date.now() })));
        } catch (e) { /* private mode or quota — the draft is a convenience only */ }
    }

    return { key: key, read: read, write: write, clear: clear };
})();
</script>
