/**
 * Language toggle: POST /api/set-language.php then reload with ?lang= for SSR strings.
 */
(function () {
    'use strict';

    function getCsrfToken() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    function apiUrl(path) {
        var baseMeta = document.querySelector('meta[name="app-base"]');
        var base = baseMeta ? baseMeta.getAttribute('content') || '' : '';
        if (base.endsWith('/')) {
            base = base.slice(0, -1);
        }
        return base + path;
    }

    function init(root) {
        if (!root) return;

        root.addEventListener('click', function (e) {
            var target = e.target;
            if (!(target instanceof HTMLElement)) return;
            var btn = target.closest('.lang-btn[data-lang]');
            if (!btn || !root.contains(btn)) return;

            var lang = btn.getAttribute('data-lang');
            if (!lang) return;

            var formData = new FormData();
            formData.append('lang', lang);
            var token = getCsrfToken();
            if (token) formData.append('csrf_token', token);

            fetch(apiUrl('/api/set-language.php'), {
                method: 'POST',
                body: formData,
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            })
                .then(function (res) {
                    return res.json().catch(function () {
                        return { ok: false };
                    });
                })
                .then(function () {
                    var url = new URL(window.location.href);
                    url.searchParams.set('lang', lang);
                    window.location.assign(url.toString());
                })
                .catch(function () {
                    var url = new URL(window.location.href);
                    url.searchParams.set('lang', lang);
                    window.location.assign(url.toString());
                });
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-i18n-root]').forEach(init);
    });
})();
