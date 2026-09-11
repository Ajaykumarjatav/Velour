(function () {
    'use strict';

    function config() {
        var el = document.getElementById('phone-country-config');
        if (!el) return null;
        try {
            return JSON.parse(el.textContent || '{}');
        } catch (e) {
            return null;
        }
    }

    function ruleFor(cfg, iso) {
        return cfg.countries[iso] || cfg.countries[cfg.default_iso] || cfg.countries.IN;
    }

    function digitsOnly(value) {
        return String(value || '').replace(/\D+/g, '');
    }

    function errorMessage(cfg, iso) {
        var rule = ruleFor(cfg, iso);
        return rule.message || ('Enter a valid ' + (rule.name || iso) + ' phone number (' + rule.hint + ').');
    }

    function isValid(cfg, iso, digits) {
        if (digits === '') return true;
        var rule = ruleFor(cfg, iso);
        try {
            return new RegExp(rule.pattern).test(digits);
        } catch (e) {
            return digits.length >= rule.min && digits.length <= rule.max;
        }
    }

    function normalizeNational(cfg, iso, value) {
        var rule = ruleFor(cfg, iso);
        var digits = digitsOnly(value);
        if (digits.indexOf(rule.dial) === 0) {
            var rest = digits.slice(rule.dial.length);
            if (rest.length >= rule.min && rest.length <= rule.max) {
                digits = rest;
            }
        }
        if (digits.charAt(0) === '0') {
            var stripped = digits.replace(/^0+/, '');
            if (stripped.length >= rule.min && stripped.length <= rule.max) {
                digits = stripped;
            }
        }
        if (digits.length > rule.max) {
            digits = digits.slice(0, rule.max);
        }
        return digits;
    }

    function splitStored(cfg, stored, preferredIso) {
        var digits = digitsOnly(stored);
        if (digits === '') {
            return { iso: preferredIso || cfg.default_iso, national: '' };
        }
        if (preferredIso && cfg.countries[preferredIso] && String(stored || '').charAt(0) === '+') {
            var prefDial = cfg.countries[preferredIso].dial;
            if (digits.indexOf(prefDial) === 0) {
                return { iso: preferredIso, national: digits.slice(prefDial.length) };
            }
        }
        var ranked = Object.keys(cfg.countries).map(function (iso) {
            return { iso: iso, dial: cfg.countries[iso].dial };
        }).sort(function (a, b) { return b.dial.length - a.dial.length; });
        var i;
        for (i = 0; i < ranked.length; i++) {
            var row = ranked[i];
            var rule = cfg.countries[row.iso];
            if (digits.indexOf(row.dial) !== 0) continue;
            var national = digits.slice(row.dial.length);
            if (national.length >= rule.min && national.length <= rule.max && isValid(cfg, row.iso, national)) {
                return { iso: row.iso, national: national };
            }
        }
        return { iso: cfg.default_iso, national: digits };
    }

    function countrySelect(root) {
        return root.querySelector('[data-phone-country]') || document.getElementById((root.querySelector('[data-phone-full]') || {}).id + '-country') || root.querySelector('select');
    }

    function bind(root, cfg) {
        if (!root || root.getAttribute('data-phone-ready') === '1') return;
        var country = countrySelect(root);
        var national = root.querySelector('[data-phone-national]');
        var full = root.querySelector('[data-phone-full]');
        var errorEl = root.querySelector('[data-phone-error]');
        if (!country || !national || !full) return;
        root.setAttribute('data-phone-ready', '1');

        var syncing = false;
        var required = root.getAttribute('data-phone-required') === '1';

        function showError(message) {
            national.classList.add('form-input-error');
            if (!errorEl) return;
            errorEl.textContent = message;
            errorEl.classList.remove('hidden');
        }

        function clearError() {
            national.classList.remove('form-input-error');
            if (!errorEl) return;
            errorEl.textContent = '';
            errorEl.classList.add('hidden');
        }

        function validate(show) {
            var iso = country.value || cfg.default_iso;
            var digits = digitsOnly(national.value);
            if (digits === '') {
                if (required && show) {
                    showError(errorMessage(cfg, iso));
                    return false;
                }
                clearError();
                return !required;
            }
            if (isValid(cfg, iso, digits)) {
                clearError();
                return true;
            }
            if (show) showError(errorMessage(cfg, iso));
            return false;
        }

        function syncFull() {
            var iso = country.value || cfg.default_iso;
            var rule = ruleFor(cfg, iso);
            var digits = digitsOnly(national.value);
            var next = digits ? ('+' + rule.dial + digits) : '';
            if (full.value !== next) {
                full.value = next;
                full.dispatchEvent(new Event('input', { bubbles: true }));
            }
        }

        function applyCountry(iso, fromStored) {
            var rule = ruleFor(cfg, iso);
            national.maxLength = rule.max;
            national.placeholder = rule.min === rule.max ? (rule.max + ' digits') : (rule.min + '–' + rule.max + ' digits');
            if (!fromStored) {
                national.value = normalizeNational(cfg, iso, national.value);
            }
            validate(digitsOnly(national.value).length >= rule.min);
            syncFull();
        }

        country.addEventListener('change', function () {
            if (syncing) return;
            applyCountry(country.value || cfg.default_iso, false);
        });

        national.addEventListener('input', function () {
            if (syncing) return;
            var iso = country.value || cfg.default_iso;
            var next = normalizeNational(cfg, iso, national.value);
            if (national.value !== next) national.value = next;
            var rule = ruleFor(cfg, iso);
            validate(digitsOnly(national.value).length >= rule.min);
            syncFull();
        });

        national.addEventListener('blur', function () {
            if (syncing) return;
            validate(digitsOnly(national.value) !== '' || required);
        });

        var form = root.closest('form');
        if (form && !form.__phoneCountrySubmitBound) {
            form.__phoneCountrySubmitBound = true;
            form.addEventListener('submit', function (e) {
                var ok = true;
                var firstBad = null;
                form.querySelectorAll('[data-phone-country-field]').forEach(function (field) {
                    var nat = field.querySelector('[data-phone-national]');
                    var sel = countrySelect(field);
                    if (!nat || !sel) return;
                    var iso = sel.value || cfg.default_iso;
                    var digits = digitsOnly(nat.value);
                    var need = field.getAttribute('data-phone-required') === '1';
                    var valid = digits === '' ? !need : isValid(cfg, iso, digits);
                    if (!valid) {
                        ok = false;
                        if (!firstBad) firstBad = nat;
                        nat.classList.add('form-input-error');
                        var err = field.querySelector('[data-phone-error]');
                        if (err) {
                            err.textContent = errorMessage(cfg, iso);
                            err.classList.remove('hidden');
                        }
                    }
                });
                if (!ok) {
                    e.preventDefault();
                    e.stopPropagation();
                    if (firstBad) {
                        firstBad.focus({ preventScroll: true });
                        firstBad.scrollIntoView({ block: 'center', behavior: 'smooth' });
                    }
                }
            }, true);
        }

        root._phoneSyncFromStored = function (stored) {
            var next = stored || '';
            if (full.value === next && digitsOnly(national.value) === splitStored(cfg, next, country.value || cfg.default_iso).national) {
                return;
            }
            syncing = true;
            var parts = splitStored(cfg, next, country.value || cfg.default_iso);
            if (cfg.countries[parts.iso] && country.value !== parts.iso) {
                country.value = parts.iso;
                country.dispatchEvent(new Event('change', { bubbles: true }));
            }
            national.value = parts.national;
            applyCountry(country.value || cfg.default_iso, true);
            syncing = false;
        };

        applyCountry(country.value || cfg.default_iso, true);
    }

    window.initPhoneCountryFields = function (scope) {
        var cfg = config();
        if (!cfg || !cfg.countries) return;
        var root = scope && scope.querySelectorAll ? scope : document;
        root.querySelectorAll('[data-phone-country-field]').forEach(function (el) {
            bind(el, cfg);
        });
        if (root.matches && root.matches('[data-phone-country-field]')) {
            bind(root, cfg);
        }
    };

    window.syncPhoneCountryField = function (el, stored) {
        if (!el) return;
        window.initPhoneCountryFields(el);
        if (typeof el._phoneSyncFromStored === 'function') {
            el._phoneSyncFromStored(stored || '');
        }
    };

    window.phoneCountryErrorForValue = function (stored, required) {
        var cfg = config();
        if (!cfg || !cfg.countries) return required ? 'Enter a valid phone number.' : null;
        var parts = splitStored(cfg, stored || '');
        var digits = digitsOnly(parts.national);
        if (digits === '') {
            return required ? errorMessage(cfg, parts.iso) : null;
        }
        if (!isValid(cfg, parts.iso, digits)) {
            return errorMessage(cfg, parts.iso);
        }
        return null;
    };

    window.prepareClonedPhoneCountryField = function (field) {
        if (!field) return;
        var uid = 'phone-' + Date.now() + '-' + Math.random().toString(36).slice(2, 7);
        var full = field.querySelector('[data-phone-full]');
        var nat = field.querySelector('[data-phone-national]');
        var country = countrySelect(field);
        var err = field.querySelector('[data-phone-error]');
        field.removeAttribute('data-phone-ready');
        if (full) {
            full.id = uid;
            full.value = '';
        }
        if (nat) {
            nat.id = uid + '-national';
            nat.value = '';
            nat.classList.remove('form-input-error');
        }
        if (err) {
            err.textContent = '';
            err.classList.add('hidden');
        }
        if (country) {
            country.id = uid + '-country';
            var cfg = config();
            country.value = (cfg && cfg.default_iso) ? cfg.default_iso : 'IN';
        }
        var ss = field.querySelector('[data-searchable-select]');
        if (ss) {
            ss.setAttribute('data-select-id', uid + '-country');
            var trigger = ss.querySelector('button');
            var panel = ss.querySelector('[role="listbox"]');
            var search = ss.querySelector('input[type="search"]');
            var label = ss.querySelector('[id$="-label"]');
            if (trigger) {
                trigger.id = uid + '-country-trigger';
                trigger.setAttribute('aria-controls', uid + '-country-panel');
            }
            if (panel) panel.id = uid + '-country-panel';
            if (search) search.id = uid + '-country-search';
            if (label) label.id = uid + '-country-label';
        }
        window.initPhoneCountryFields(field);
        if (ss && typeof window.initSearchableSelectRoot === 'function') {
            window.initSearchableSelectRoot(ss);
        }
    };

    window.applyPhoneCountryFromLocation = function (region, timezone) {
        var cfg = config();
        if (!cfg || !cfg.countries) return;
        var iso = cfg.default_iso;
        timezone = String(timezone || '');
        region = String(region || '').toUpperCase();
        if (cfg.timezone_iso && cfg.timezone_iso[timezone]) iso = cfg.timezone_iso[timezone];
        else if (region && cfg.countries[region]) iso = region;
        else if (timezone.indexOf('Asia/Kolkata') === 0 || timezone.indexOf('Asia/Calcutta') === 0) iso = 'IN';
        else if (timezone.indexOf('Asia/Dubai') === 0) iso = 'AE';
        else if (timezone.indexOf('Europe/London') === 0) iso = 'GB';
        else if (timezone.indexOf('America/') === 0) iso = cfg.countries.US ? 'US' : iso;
        if (!cfg.countries[iso]) iso = cfg.default_iso;

        document.querySelectorAll('[data-phone-detect-target]').forEach(function (root) {
            window.initPhoneCountryFields(root);
            var country = countrySelect(root);
            if (!country) return;
            if (country.value !== iso) {
                country.value = iso;
                country.dispatchEvent(new Event('change', { bubbles: true }));
            }
        });
    };

    window.applySettingsPhoneCountryFromLocation = window.applyPhoneCountryFromLocation;

    document.addEventListener('DOMContentLoaded', function () {
        window.initPhoneCountryFields(document);
    });
})();
