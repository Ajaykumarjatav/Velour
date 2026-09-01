/**
 * EasyGrox shared CSRF-safe HTTP helpers.
 *
 * Laravel prefers request `_token` / `X-CSRF-TOKEN` over `X-XSRF-TOKEN`.
 * Sending a stale meta token alongside a fresh XSRF cookie causes permanent 419s.
 * Rule: when the XSRF-TOKEN cookie exists, send ONLY X-XSRF-TOKEN.
 */
(function (window, document) {
  'use strict';

  var cfg = window.__EASYGROX__ || {};
  var csrfCookieUrl = cfg.csrfCookieUrl || '/sanctum/csrf-cookie';
  var csrfTokenUrl = cfg.csrfTokenUrl || null;
  var basePath = cfg.basePath || '';

  function metaEl() {
    return document.querySelector('meta[name="csrf-token"]');
  }

  function metaToken() {
    return metaEl()?.getAttribute('content') || '';
  }

  function setMetaToken(token) {
    if (!token) return;
    var el = metaEl();
    if (el) el.setAttribute('content', token);
  }

  function xsrfToken() {
    var match = document.cookie.match(/(?:^|;\s*)XSRF-TOKEN=([^;]+)/);
    return match ? decodeURIComponent(match[1]) : '';
  }

  function sameOrigin(url) {
    if (!url) return url;
    if (typeof url !== 'string') return url;
    if (url.startsWith('/') || url.startsWith('?') || url.startsWith('#')) {
      // Root-absolute API paths need the app subdirectory (from APP_URL).
      if (basePath && url.startsWith('/api/')) {
        return basePath + url;
      }
      return url;
    }
    try {
      var parsed = new URL(url, window.location.href);
      if (parsed.origin === window.location.origin) {
        return parsed.pathname + parsed.search + parsed.hash;
      }
    } catch (e) { /* keep original */ }
    return url;
  }

  function csrfHeaders(extra) {
    var headers = Object.assign({
      'Accept': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
    }, extra || {});

    var xsrf = xsrfToken();
    if (xsrf) {
      headers['X-XSRF-TOKEN'] = xsrf;
      delete headers['X-CSRF-TOKEN'];
    } else {
      var token = metaToken();
      if (token) headers['X-CSRF-TOKEN'] = token;
    }

    return headers;
  }

  function appendCsrfToFormData(fd) {
    // Only embed _token when no XSRF cookie — otherwise stale _token wins in Laravel.
    if (xsrfToken()) return fd;
    var token = metaToken();
    if (token && !fd.has('_token')) fd.append('_token', token);
    return fd;
  }

  async function refreshCsrf() {
    await fetch(csrfCookieUrl, {
      method: 'GET',
      credentials: 'same-origin',
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
    });

    if (csrfTokenUrl) {
      try {
        var res = await fetch(csrfTokenUrl, {
          method: 'GET',
          credentials: 'same-origin',
          headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
          },
        });
        if (res.ok) {
          var data = await res.json().catch(function () { return {}; });
          if (data.token) setMetaToken(data.token);
        }
      } catch (e) { /* cookie refresh alone is enough for XSRF */ }
    }

    return xsrfToken() || metaToken();
  }

  async function request(url, options, retried) {
    options = options || {};
    var opts = Object.assign({ credentials: 'same-origin' }, options);
    var headers = csrfHeaders(opts.headers || {});

    if (opts.body instanceof FormData) {
      appendCsrfToFormData(opts.body);
      // Let the browser set multipart boundary; strip explicit JSON content-type.
      delete headers['Content-Type'];
    }

    opts.headers = headers;

    var response = await fetch(sameOrigin(url), opts);

    if ((response.status === 419 || response.status === 401) && !retried) {
      await refreshCsrf();
      return request(url, options, true);
    }

    return response;
  }

  async function post(url, body, options) {
    options = options || {};
    var opts = Object.assign({}, options, { method: 'POST' });

    if (body instanceof FormData) {
      opts.body = body;
    } else if (body != null) {
      opts.headers = Object.assign({ 'Content-Type': 'application/json' }, opts.headers || {});
      opts.body = typeof body === 'string' ? body : JSON.stringify(body);
    }

    return request(url, opts, false);
  }

  // Keep HTML forms in sync: before submit, drop stale _token when XSRF cookie exists
  // and refresh the field from meta after a cookie refresh when needed.
  document.addEventListener('submit', function (event) {
    var form = event.target;
    if (!form || form.tagName !== 'FORM') return;
    if ((form.method || 'get').toLowerCase() === 'get') return;

    var tokenInput = form.querySelector('input[name="_token"]');
    if (!tokenInput) return;

    // Prefer live meta token over a possibly stale compiled value in the DOM.
    var live = metaToken();
    if (live) tokenInput.value = live;
  }, true);

  window.EasyGroxHttp = {
    metaToken: metaToken,
    xsrfToken: xsrfToken,
    setMetaToken: setMetaToken,
    sameOrigin: sameOrigin,
    csrfHeaders: csrfHeaders,
    refreshCsrf: refreshCsrf,
    request: request,
    post: post,
    appendCsrfToFormData: appendCsrfToFormData,
  };
})(window, document);
