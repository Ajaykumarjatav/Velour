<script>
(function () {
    function reveal() {
        document.documentElement.classList.remove('css-pending');
        var guard = document.getElementById('css-load-guard');
        if (guard) guard.remove();
    }
    function afterStyles() {
        setTimeout(function () {
            requestAnimationFrame(function () {
                requestAnimationFrame(reveal);
            });
        }, 50);
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', afterStyles);
    } else {
        afterStyles();
    }
    setTimeout(reveal, 2500);
})();
</script>
