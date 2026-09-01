/**
 * Auto-compress images on file inputs marked with [data-compress-image]
 * before the form is submitted, so large photos still upload under server limits.
 */
(function () {
    'use strict';

    var TARGET_BYTES = 1.5 * 1024 * 1024;
    var MAX_EDGE = 1600;
    var MIN_QUALITY = 0.45;

    function formatMb(bytes) {
        return (bytes / (1024 * 1024)).toFixed(bytes >= 10 * 1024 * 1024 ? 0 : 1);
    }

    function hintFor(input) {
        var wrap = input.closest('div') || input.parentElement;
        if (!wrap) return null;
        return wrap.querySelector('[data-compress-hint]') || wrap.querySelector('.form-hint, .text-\\[11px\\], p.text-muted, p.text-\\[11px\\]');
    }

    function setHint(input, text, isError) {
        var el = hintFor(input);
        if (!el) return;
        if (!el.getAttribute('data-compress-hint-default')) {
            el.setAttribute('data-compress-hint-default', el.textContent.trim());
        }
        el.textContent = text;
        el.classList.toggle('text-red-600', !!isError);
        el.classList.toggle('dark:text-red-400', !!isError);
    }

    function resetHint(input) {
        var el = hintFor(input);
        if (!el) return;
        var def = el.getAttribute('data-compress-hint-default');
        if (def) el.textContent = def;
        el.classList.remove('text-red-600', 'dark:text-red-400');
    }

    function loadImage(file) {
        return new Promise(function (resolve, reject) {
            var url = URL.createObjectURL(file);
            var img = new Image();
            img.onload = function () {
                URL.revokeObjectURL(url);
                resolve(img);
            };
            img.onerror = function () {
                URL.revokeObjectURL(url);
                reject(new Error('Could not read this image.'));
            };
            img.src = url;
        });
    }

    function canvasToBlob(canvas, type, quality) {
        return new Promise(function (resolve) {
            canvas.toBlob(function (blob) {
                resolve(blob);
            }, type, quality);
        });
    }

    async function compressFile(file) {
        if (!file || !file.type || file.type.indexOf('image/') !== 0) {
            return file;
        }
        // GIFs / animated — leave alone (canvas would flatten)
        if (file.type === 'image/gif') {
            return file;
        }

        var img = await loadImage(file);
        var w = img.naturalWidth || img.width;
        var h = img.naturalHeight || img.height;
        if (!w || !h) {
            return file;
        }

        var scale = Math.min(1, MAX_EDGE / Math.max(w, h));
        var tw = Math.max(1, Math.round(w * scale));
        var th = Math.max(1, Math.round(h * scale));

        var canvas = document.createElement('canvas');
        canvas.width = tw;
        canvas.height = th;
        var ctx = canvas.getContext('2d');
        if (!ctx) {
            return file;
        }
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, tw, th);
        ctx.drawImage(img, 0, 0, tw, th);

        var outType = 'image/jpeg';
        var quality = 0.85;
        var blob = await canvasToBlob(canvas, outType, quality);

        while (blob && blob.size > TARGET_BYTES && quality > MIN_QUALITY) {
            quality = Math.max(MIN_QUALITY, quality - 0.1);
            blob = await canvasToBlob(canvas, outType, quality);
        }

        // Still too big? shrink canvas further.
        var edge = Math.min(tw, th, MAX_EDGE);
        while (blob && blob.size > TARGET_BYTES && edge > 640) {
            edge = Math.round(edge * 0.8);
            var s2 = edge / Math.max(w, h);
            tw = Math.max(1, Math.round(w * s2));
            th = Math.max(1, Math.round(h * s2));
            canvas.width = tw;
            canvas.height = th;
            ctx = canvas.getContext('2d');
            ctx.fillStyle = '#ffffff';
            ctx.fillRect(0, 0, tw, th);
            ctx.drawImage(img, 0, 0, tw, th);
            quality = 0.8;
            blob = await canvasToBlob(canvas, outType, quality);
            while (blob && blob.size > TARGET_BYTES && quality > MIN_QUALITY) {
                quality = Math.max(MIN_QUALITY, quality - 0.1);
                blob = await canvasToBlob(canvas, outType, quality);
            }
        }

        if (!blob) {
            return file;
        }

        // Prefer compressed result when smaller, or when original exceeded target.
        if (blob.size >= file.size && file.size <= TARGET_BYTES) {
            return file;
        }

        var base = (file.name || 'photo').replace(/\.[^.]+$/, '');
        return new File([blob], base + '.jpg', {
            type: outType,
            lastModified: Date.now(),
        });
    }

    function replaceInputFile(input, file) {
        try {
            var dt = new DataTransfer();
            dt.items.add(file);
            input.files = dt.files;
        } catch (e) {
            // Older browsers: leave original file (server may still reject).
        }
    }

    async function onFileChange(input) {
        var file = input.files && input.files[0];
        if (!file) {
            resetHint(input);
            input.removeAttribute('data-compressing');
            return;
        }

        input.setAttribute('data-compressing', '1');
        setHint(input, 'Compressing ' + formatMb(file.size) + ' MB image…');

        try {
            var out = await compressFile(file);
            replaceInputFile(input, out);
            if (out.size < file.size) {
                setHint(
                    input,
                    'Compressed ' + formatMb(file.size) + ' MB → ' + formatMb(out.size) + ' MB · JPG/PNG/WebP auto-optimized'
                );
            } else {
                setHint(input, 'JPG, PNG or WebP · large images are auto-compressed before upload');
            }
        } catch (err) {
            setHint(input, (err && err.message) || 'Could not compress this image. Try JPG or PNG.', true);
            input.value = '';
        } finally {
            input.removeAttribute('data-compressing');
        }
    }

    function isCompressInput(el) {
        return el && el.matches && el.matches('input[type="file"][data-compress-image]');
    }

    document.addEventListener('change', function (e) {
        var input = e.target;
        if (!isCompressInput(input)) return;
        onFileChange(input);
    });

    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (!form || form.tagName !== 'FORM') return;
        var busy = form.querySelector('input[type="file"][data-compress-image][data-compressing="1"]');
        if (busy) {
            e.preventDefault();
            setHint(busy, 'Please wait — image is still compressing…');
        }
    }, true);
})();
