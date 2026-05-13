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

    function setActiveButtons(root, lang) {
        var buttons = root.querySelectorAll('.lang-btn[data-lang]');
        buttons.forEach(function (btn) {
            var code = btn.getAttribute('data-lang');
            var active = code === lang;
            btn.classList.toggle('is-active', active);
            btn.setAttribute('aria-pressed', active ? 'true' : 'false');
        });
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
                .then(function (data) {
                    if (data && data.ok) {
                        var url = new URL(window.location.href);
                        url.searchParams.set('lang', lang);
                        window.location.assign(url.toString());
                        return;
                    }
                    setActiveButtons(root, lang);
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
