{{-- Client-side validation + required-field asterisks (novalidate; .form-error / .required-asterisk). --}}
<script>
(function () {
    'use strict';

    function isVisible(el) {
        if (!el || !(el instanceof Element)) return false;
        if (el.disabled || el.readOnly) return false;
        if (el.offsetParent === null && el.tagName !== 'BODY') {
            var st = window.getComputedStyle(el);
            if (st.display === 'none' || st.visibility === 'hidden' || st.opacity === '0') return false;
        }
        return true;
    }

    function labelHint(form, input) {
        var custom = input.getAttribute('data-validation-message');
        if (custom) return custom;
        if (input.labels && input.labels.length) {
            var t = input.labels[0].innerText.replace(/\s*\*\s*$/g, '').trim();
            if (t) return t;
        }
        if (input.id) {
            var lab = form.querySelector('label[for="' + CSS.escape(input.id) + '"]');
            if (lab) {
                t = lab.innerText.replace(/\s*\*\s*$/g, '').trim();
                if (t) return t;
            }
        }
        return 'This field';
    }

    function showFieldError(input, message) {
        input.setAttribute('data-cv-marked', '1');
        input.classList.add('form-input-error');
        if (input.classList.contains('auth-input')) {
            input.classList.add('is-invalid');
        }

        var anchor = input;
        var wrap = input.closest('.auth-password-wrap, .relative');
        if (wrap && wrap.contains(input)) {
            anchor = wrap;
        }

        var p = anchor.nextElementSibling;
        if (!p || p.getAttribute('data-cv-msg') !== '1') {
            p = document.createElement('p');
            p.setAttribute('data-cv-msg', '1');
            p.className = 'form-error auth-error text-xs mt-0.5';
            p.setAttribute('role', 'alert');
            anchor.insertAdjacentElement('afterend', p);
        }
        p.textContent = message;
    }

    function clearFieldClientError(input) {
        if (!input || input.getAttribute('data-cv-marked') !== '1') return;
        input.classList.remove('form-input-error', 'is-invalid');
        input.removeAttribute('data-cv-marked');
        var wrap = input.closest('.auth-password-wrap, .relative');
        var candidates = [input.nextElementSibling];
        if (wrap) candidates.push(wrap.nextElementSibling);
        candidates.forEach(function (p) {
            if (p && p.getAttribute('data-cv-msg') === '1') p.remove();
        });
    }

    function trimValue(input) {
        if (input instanceof HTMLTextAreaElement) {
            return (input.value || '').trim();
        }
        if (input.type === 'password' || input.type === 'search' || input.type === 'text' || input.type === 'email' || input.type === 'url' || input.type === 'tel' || !input.type) {
            if (typeof input.value === 'string') return input.value.trim();
        }
        return input.value;
    }

    function validateEmail(value) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
    }

    function validateUrl(value) {
        try { new URL(value); return true; } catch (e) { return false; }
    }

    function validateOne(form, input, messages) {
        if (!isVisible(input)) return true;
        if (input.closest('fieldset[disabled]')) return true;

        var tag = input.tagName;
        var type = (input.type || '').toLowerCase();
        if (type === 'hidden') return true;
        var value = trimValue(input);
        var label = labelHint(form, input);

        if (type === 'radio') return true;
        if (type === 'checkbox') {
            if (input.required && !input.checked) {
                showFieldError(input, messages.checkbox || (label + ' must be ticked.'));
                return false;
            }
            return true;
        }

        if (tag === 'SELECT') {
            if (input.required) {
                if (input.multiple) {
                    if (input.selectedOptions.length === 0) {
                        showFieldError(input, messages.select || (label + ' is required.'));
                        return false;
                    }
                } else if (value === '' || value === null) {
                    showFieldError(input, messages.required || (label + ' is required.'));
                    return false;
                }
            }
            return true;
        }

        if (type === 'file') {
            if (input.required && (!input.files || input.files.length === 0)) {
                showFieldError(input, messages.file || (label + ' is required.'));
                return false;
            }
            return true;
        }

        if (input.required && (value === '' || value === null)) {
            showFieldError(input, messages.required || (label + ' is required.'));
            return false;
        }

        if (value !== '') {
            if (type === 'email' && !validateEmail(value)) {
                showFieldError(input, messages.email || 'Please enter a valid email address.');
                return false;
            }
            if (type === 'url' && !validateUrl(value)) {
                showFieldError(input, messages.url || 'Please enter a valid URL.');
                return false;
            }
            if (input.pattern) {
                try {
                    var re = new RegExp('^(?:' + input.pattern + ')$');
                    if (!re.test(value)) {
                        showFieldError(input, input.getAttribute('data-pattern-message') || (label + ' does not match the required format.'));
                        return false;
                    }
                } catch (err) { /* ignore invalid pattern */ }
            }
            var minL = parseInt(input.getAttribute('minlength'), 10);
            if (!isNaN(minL) && minL > 0 && value.length < minL) {
                showFieldError(input, messages.minlength || (label + ' must be at least ' + minL + ' characters.'));
                return false;
            }
            var maxL = parseInt(input.getAttribute('maxlength'), 10);
            if (!isNaN(maxL) && maxL > 0 && value.length > maxL) {
                showFieldError(input, messages.maxlength || (label + ' must be at most ' + maxL + ' characters.'));
                return false;
            }
            if (type === 'number' || type === 'range') {
                var num = parseFloat(value);
                if (input.hasAttribute('min') && value !== '' && !isNaN(num) && num < parseFloat(input.min)) {
                    showFieldError(input, messages.min || (label + ' must be at least ' + input.min + '.'));
                    return false;
                }
                if (input.hasAttribute('max') && value !== '' && !isNaN(num) && num > parseFloat(input.max)) {
                    showFieldError(input, messages.max || (label + ' must be at most ' + input.max + '.'));
                    return false;
                }
            }
        }

        return true;
    }

    function validateRadioGroups(form, messages) {
        var seen = Object.create(null);
        var ok = true;
        form.querySelectorAll('input[type="radio"][required]').forEach(function (r) {
            if (!isVisible(r) || seen[r.name]) return;
            seen[r.name] = true;
            var group = form.querySelectorAll('input[type="radio"][name="' + CSS.escape(r.name) + '"]');
            var anyChecked = false;
            group.forEach(function (x) { if (x.checked) anyChecked = true; });
            if (!anyChecked) {
                showFieldError(r, messages.radio || 'Please select an option.');
                ok = false;
            }
        });
        return ok;
    }

    function validateForm(form) {
        if (form.hasAttribute('data-no-client-validation')) return true;
        if (form.getAttribute('data-allow-native-validation') === 'true') return true;

        form.querySelectorAll('[data-cv-marked="1"]').forEach(function (inp) {
            clearFieldClientError(inp);
        });
        form.querySelectorAll('p[data-cv-msg="1"]').forEach(function (p) {
            p.remove();
        });

        var messages = {
            required: form.getAttribute('data-msg-required'),
            email: form.getAttribute('data-msg-email'),
            url: form.getAttribute('data-msg-url'),
            checkbox: form.getAttribute('data-msg-checkbox'),
            select: form.getAttribute('data-msg-select'),
            file: form.getAttribute('data-msg-file'),
            radio: form.getAttribute('data-msg-radio'),
            minlength: form.getAttribute('data-msg-minlength'),
            maxlength: form.getAttribute('data-msg-maxlength'),
            min: form.getAttribute('data-msg-min'),
            max: form.getAttribute('data-msg-max'),
        };

        var ok = validateRadioGroups(form, messages);
        var firstBad = null;

        form.querySelectorAll('input, select, textarea').forEach(function (el) {
            if (!(el instanceof HTMLElement)) return;
            if (el.type === 'radio' || el.type === 'hidden' || el.type === 'button' || el.type === 'submit' || el.type === 'reset' || el.type === 'image') return;
            if (!validateOne(form, el, messages)) {
                ok = false;
                if (!firstBad) firstBad = el;
            }
        });

        if (form.getAttribute('data-password-confirm-match') === '1') {
            form.querySelectorAll('input[data-confirm-for]').forEach(function (confirmInput) {
                if (!(confirmInput instanceof HTMLInputElement)) return;
                if (!isVisible(confirmInput)) return;
                var primaryId = confirmInput.getAttribute('data-confirm-for');
                var primary = primaryId ? document.getElementById(primaryId) : null;
                if (!(primary instanceof HTMLInputElement)) return;
                if (confirmInput.value === '' || primary.value === '') return;
                if (confirmInput.value !== primary.value) {
                    showFieldError(confirmInput, 'Password confirmation does not match.');
                    ok = false;
                    if (!firstBad) firstBad = confirmInput;
                }
            });
        }

        if (!ok && firstBad) {
            firstBad.focus({ preventScroll: true });
            firstBad.scrollIntoView({ block: 'center', behavior: 'smooth' });
        }

        return ok;
    }

    function applyNovalidate(form) {
        if (form.hasAttribute('data-allow-native-validation')) return;
        form.setAttribute('novalidate', 'novalidate');
    }

    function labelAlreadyMarked(label) {
        if (!label) return true;
        if (label.querySelector('.required-asterisk')) return true;
        if (label.querySelector('.text-red-500') && /\*/.test(label.textContent || '')) return true;
        var text = (label.textContent || '').replace(/\s+/g, ' ').trim();
        return /\*/.test(text);
    }

    function findLabelForControl(control) {
        if (control.labels && control.labels.length) {
            return control.labels[0];
        }
        if (control.id) {
            try {
                var byFor = document.querySelector('label[for="' + CSS.escape(control.id) + '"]');
                if (byFor) return byFor;
            } catch (e) { /* ignore */ }
        }

        var wrap = control.closest('.auth-field, .form-group, [class*="space-y"], .grid > div, td, li, fieldset');
        if (wrap) {
            var nested = wrap.querySelector(':scope > label, label');
            if (nested) {
                if (!nested.contains(control) || nested.querySelector('input, select, textarea') === null) {
                    return nested;
                }
                if (nested.contains(control)) return nested;
            }
        }

        var node = control;
        for (var i = 0; i < 4 && node; i++) {
            var prev = node.previousElementSibling;
            while (prev) {
                if (prev.tagName === 'LABEL') return prev;
                var innerLab = prev.querySelector && prev.querySelector('label');
                if (innerLab && !innerLab.querySelector('input, select, textarea')) return innerLab;
                if (prev.classList && (prev.classList.contains('auth-password-wrap') || prev.classList.contains('relative') || prev.classList.contains('auth-label-row'))) {
                    var rowLab = prev.querySelector('label');
                    if (rowLab) return rowLab;
                }
                break;
            }
            if (node.parentElement && node.parentElement.tagName === 'LABEL') {
                return node.parentElement;
            }
            node = node.parentElement;
        }

        return null;
    }

    function markRequiredAsterisk(control) {
        if (!(control instanceof HTMLElement)) return;
        if (control.hasAttribute('data-no-required-asterisk')) return;
        if (control.closest('[data-no-required-asterisk]')) return;
        if (!control.required && control.getAttribute('aria-required') !== 'true') return;

        var type = (control.type || '').toLowerCase();
        if (type === 'hidden' || type === 'submit' || type === 'button' || type === 'reset' || type === 'image') return;

        if (type === 'radio') {
            if (control.getAttribute('data-req-star-done') === '1') return;
            var form = control.form || control.closest('form');
            if (form && control.name) {
                form.querySelectorAll('input[type="radio"][name="' + CSS.escape(control.name) + '"]').forEach(function (r) {
                    r.setAttribute('data-req-star-done', '1');
                });
            } else {
                control.setAttribute('data-req-star-done', '1');
            }
        }

        var label = findLabelForControl(control);
        if (!label || labelAlreadyMarked(label)) return;

        var star = document.createElement('span');
        star.className = 'required-asterisk';
        star.setAttribute('aria-hidden', 'true');
        star.textContent = '*';
        label.appendChild(star);
    }

    function markRequiredFields(root) {
        var scope = root && root.querySelectorAll ? root : document;
        scope.querySelectorAll('input[required], select[required], textarea[required], input[aria-required="true"], select[aria-required="true"], textarea[aria-required="true"]').forEach(markRequiredAsterisk);
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('form').forEach(applyNovalidate);
        markRequiredFields(document);

        if (typeof MutationObserver !== 'undefined' && document.body) {
            var timer = null;
            var obs = new MutationObserver(function () {
                if (timer) clearTimeout(timer);
                timer = setTimeout(function () { markRequiredFields(document); }, 80);
            });
            obs.observe(document.body, { childList: true, subtree: true, attributes: true, attributeFilter: ['required', 'aria-required'] });
        }
    });

    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (!(form instanceof HTMLFormElement)) return;
        if (form.hasAttribute('data-no-client-validation')) return;
        if (e.submitter && e.submitter.getAttribute('formnovalidate') !== null) return;

        if (!validateForm(form)) {
            e.preventDefault();
            e.stopPropagation();
        }
    }, true);

    document.addEventListener('input', function (e) {
        var t = e.target;
        if (t instanceof HTMLInputElement || t instanceof HTMLTextAreaElement || t instanceof HTMLSelectElement) {
            if (t.getAttribute('data-cv-marked') === '1') clearFieldClientError(t);
        }
    }, true);

    document.addEventListener('change', function (e) {
        var t = e.target;
        if (t instanceof HTMLInputElement || t instanceof HTMLSelectElement) {
            if (t.getAttribute('data-cv-marked') === '1') clearFieldClientError(t);
            if (t.type === 'radio') {
                var form = t.form;
                if (form) {
                    form.querySelectorAll('input[type="radio"][name="' + CSS.escape(t.name) + '"]').forEach(function (r) {
                        clearFieldClientError(r);
                    });
                }
            }
        }
    }, true);

    window.easygroxMarkRequiredFields = markRequiredFields;
})();
</script>
