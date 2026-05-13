/**
 * Public site behaviour (no third-party libraries):
 * - Theme persistence (localStorage + data-theme)
 * - Scroll reveal (IntersectionObserver, respects prefers-reduced-motion)
 * - Project listing: Fetch JSON from /api/projects.php, skeleton UI, filters (delegated clicks)
 * - Contact form: reportValidity + trim/length checks, then Fetch POST to contact.php (Accept: application/json)
 * - Project detail: in-page section nav highlights current block while scrolling
 */
(function () {
    'use strict';

    var STORAGE_KEY = 'portfolio-theme';

    /* ---------- Theme ---------- */

    function getTheme() {
        return document.documentElement.getAttribute('data-theme') === 'light' ? 'light' : 'dark';
    }

    function setTheme(theme) {
        if (theme !== 'light' && theme !== 'dark') return;
        document.documentElement.setAttribute('data-theme', theme);
        try {
            localStorage.setItem(STORAGE_KEY, theme);
        } catch (e) {}
        syncThemeToggle();
    }

    function syncThemeToggle() {
        var btn = document.querySelector('[data-theme-toggle]');
        if (!btn) return;
        var toLight = btn.getAttribute('data-label-to-light') || 'Switch to light mode';
        var toDark = btn.getAttribute('data-label-to-dark') || 'Switch to dark mode';
        var next = getTheme() === 'dark' ? toLight : toDark;
        btn.setAttribute('aria-label', next);
    }

    function initThemeToggle() {
        var btn = document.querySelector('[data-theme-toggle]');
        if (!btn) return;
        syncThemeToggle();
        btn.addEventListener('click', function () {
            setTheme(getTheme() === 'dark' ? 'light' : 'dark');
        });
    }

    /* ---------- DOM helpers ---------- */

    function getMeta(name) {
        var meta = document.querySelector('meta[name="' + name + '"]');
        return meta ? meta.getAttribute('content') || '' : '';
    }

    function createEl(tag, className, attrs) {
        var node = document.createElement(tag);
        if (className) node.className = className;
        if (attrs) {
            Object.keys(attrs).forEach(function (k) {
                if (attrs[k] !== undefined && attrs[k] !== null) {
                    node.setAttribute(k, attrs[k]);
                }
            });
        }
        return node;
    }

    /* ---------- Scroll reveal ---------- */

    function applyRevealDelay(el) {
        var raw = el.getAttribute('data-reveal-delay');
        if (raw === null || raw === '') return;
        var ms = parseInt(raw, 10);
        if (isNaN(ms) || ms < 0) return;
        el.style.setProperty('--reveal-delay', ms + 'ms');
    }

    function observeRevealElements(els) {
        if (!els || !els.length) return;

        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            els.forEach(function (el) {
                el.classList.add('is-visible');
            });
            return;
        }

        if (!('IntersectionObserver' in window)) {
            els.forEach(function (el) {
                el.classList.add('is-visible');
            });
            return;
        }

        var io = new IntersectionObserver(
            function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                        io.unobserve(entry.target);
                    }
                });
            },
            {
                root: null,
                rootMargin: '0px 0px 14% 0px',
                threshold: 0,
            }
        );

        els.forEach(function (el) {
            applyRevealDelay(el);
            io.observe(el);
        });
    }

    function initReveal() {
        observeRevealElements(document.querySelectorAll('[data-reveal]'));
    }

    /* ---------- Project category filters (event delegation) ---------- */

    var filterRootsBound = new WeakSet();

    function bindProjectFilterDelegation(root) {
        if (!root || filterRootsBound.has(root)) return;
        filterRootsBound.add(root);

        root.addEventListener('click', function (e) {
            var t = e.target;
            if (!(t && t.closest)) return;
            var btn = t.closest('[data-project-filter]');
            if (!btn || !root.contains(btn)) return;

            var filter = btn.getAttribute('data-project-filter');
            if (!filter) return;

            var buttons = root.querySelectorAll('[data-project-filter]');
            var cards = root.querySelectorAll('[data-project-category]');
            var emptyEl = root.querySelector('[data-project-filter-empty]');

            buttons.forEach(function (b) {
                var f = b.getAttribute('data-project-filter') || '';
                var on = f === filter;
                b.classList.toggle('is-active', on);
                b.setAttribute('aria-pressed', on ? 'true' : 'false');
            });

            var visible = 0;
            cards.forEach(function (card) {
                var cat = card.getAttribute('data-project-category') || '';
                var show = filter === 'all' || cat === filter;
                card.classList.toggle('is-filter-hidden', !show);
                card.hidden = !show;
                if (show) visible += 1;
            });

            if (emptyEl) {
                emptyEl.hidden = visible !== 0;
            }
        });
    }

    function initProjectFilterRoots() {
        document.querySelectorAll('[data-project-filter-root]').forEach(bindProjectFilterDelegation);
    }

    /* ---------- Projects: Fetch API + skeleton replacement ---------- */

    function initAjaxProjects() {
        var root = document.querySelector('[data-ajax-projects]');
        if (!root || !window.fetch) return;

        var endpoint = root.getAttribute('data-projects-endpoint');
        if (!endpoint) return;

        var grid = root.querySelector('[data-projects-grid]');
        var filtersHost = root.querySelector('[data-projects-filters-host]');
        var emptyFilterEl = root.querySelector('[data-project-filter-empty]');
        if (!grid) return;

        var loadErrorMsg = root.getAttribute('data-projects-load-error') || 'Could not load projects.';

        function finishLoad() {
            root.setAttribute('aria-busy', 'false');
            root.removeAttribute('aria-describedby');
            grid.classList.remove('project-grid--loading');
        }

        fetch(endpoint, {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
            },
        })
            .then(function (res) {
                if (!res.ok) throw new Error('bad status');
                return res.json();
            })
            .then(function (data) {
                if (!data || !data.ok || !data.i18n) throw new Error('bad payload');

                var i18n = data.i18n;
                var projects = data.projects || [];

                grid.textContent = '';

                if (emptyFilterEl) {
                    emptyFilterEl.textContent = i18n.noneFiltered || '';
                }

                if (projects.length === 0) {
                    var emptyP = createEl('p', 'projects-empty');
                    emptyP.setAttribute('data-reveal', '');
                    emptyP.textContent = i18n.empty || '';
                    grid.appendChild(emptyP);
                    observeRevealElements([emptyP]);
                    if (filtersHost) {
                        filtersHost.hidden = true;
                        filtersHost.textContent = '';
                    }
                    return;
                }

                var categories = [];
                var seen = {};
                projects.forEach(function (p) {
                    var s = p.category_slug || 'general';
                    if (!seen[s]) {
                        seen[s] = true;
                        categories.push(s);
                    }
                });
                categories.sort();

                if (filtersHost) {
                    filtersHost.textContent = '';
                    if (categories.length > 1) {
                        var toolbar = createEl('div', 'project-filters', { role: 'toolbar', 'aria-label': i18n.filterAria || '' });
                        toolbar.setAttribute('data-reveal', '');

                        var label = createEl('span', 'project-filters__label', { id: 'project-filters-label-ajax' });
                        label.textContent = i18n.filterLabel || '';

                        var group = createEl('div', 'project-filters__buttons', {
                            role: 'group',
                            'aria-labelledby': 'project-filters-label-ajax',
                        });

                        var allBtn = createEl('button', 'project-filter-btn is-active', { type: 'button' });
                        allBtn.setAttribute('data-project-filter', 'all');
                        allBtn.setAttribute('aria-pressed', 'true');
                        allBtn.textContent = i18n.filterAll || 'All';
                        group.appendChild(allBtn);

                        categories.forEach(function (slug) {
                            var item;
                            for (var i = 0; i < projects.length; i++) {
                                if ((projects[i].category_slug || '') === slug) {
                                    item = projects[i];
                                    break;
                                }
                            }
                            var lbl = item && item.category_label ? item.category_label : slug;
                            var b = createEl('button', 'project-filter-btn', { type: 'button' });
                            b.setAttribute('data-project-filter', slug);
                            b.setAttribute('aria-pressed', 'false');
                            b.textContent = lbl;
                            group.appendChild(b);
                        });

                        toolbar.appendChild(label);
                        toolbar.appendChild(group);
                        filtersHost.appendChild(toolbar);
                        filtersHost.hidden = false;
                        observeRevealElements([toolbar]);
                    } else {
                        filtersHost.hidden = true;
                    }
                }

                var revealed = [];
                projects.forEach(function (p, index) {
                    var slug = p.category_slug || 'general';
                    var n = String(index + 1).padStart(2, '0');
                    var article = createEl('article', 'project-card', {});
                    article.setAttribute('data-reveal', '');
                    article.setAttribute('data-reveal-delay', String(index * 50));
                    article.setAttribute('data-project-category', slug);

                    var media = createEl('a', 'project-card__media', { href: p.detail_url || '#', tabindex: '-1', 'aria-hidden': 'true' });
                    var img = createEl('img', 'project-card__thumb', {
                        src: p.image_url || '',
                        alt: '',
                        width: '600',
                        height: '375',
                        loading: 'lazy',
                        decoding: 'async',
                    });
                    media.appendChild(img);
                    article.appendChild(media);

                    var idx = createEl('span', 'project-card__index');
                    idx.textContent = n;
                    article.appendChild(idx);

                    var catP = createEl('p', 'project-card__category');
                    catP.textContent = p.category_label || slug;
                    article.appendChild(catP);

                    var h3 = createEl('h3');
                    var titleA = createEl('a', '', { href: p.detail_url || '#' });
                    titleA.textContent = p.title || '';
                    h3.appendChild(titleA);
                    article.appendChild(h3);

                    var sumP = createEl('p');
                    sumP.textContent = p.summary || '';
                    article.appendChild(sumP);

                    if (p.tag) {
                        var tagSp = createEl('span', 'project-card__tag');
                        tagSp.textContent = p.tag;
                        article.appendChild(tagSp);
                    }

                    var linkWrap = createEl('p', 'project-card__link-wrap');
                    var link = createEl('a', 'project-card__link', { href: p.detail_url || '#' });
                    link.textContent = i18n.viewDetails || '';
                    linkWrap.appendChild(link);
                    article.appendChild(linkWrap);

                    grid.appendChild(article);
                    revealed.push(article);
                });

                observeRevealElements(revealed);
            })
            .catch(function () {
                grid.textContent = '';
                var err = createEl('p', 'projects-empty');
                err.textContent = loadErrorMsg;
                grid.appendChild(err);
            })
            .finally(finishLoad);

        bindProjectFilterDelegation(root);
    }

    /* ---------- Contact: Fetch + inline feedback ---------- */

    function initAjaxContact() {
        var form = document.querySelector('form[data-ajax-contact]');
        if (!form || !window.FormData || !window.fetch) return;

        var alertEl = document.querySelector('[data-contact-alert]');
        var submitBtn = form.querySelector('[data-contact-submit]');

        function showAlert(ok, message) {
            if (!alertEl) return;
            alertEl.className =
                'contact-form__alert form-flash ' + (ok ? 'form-flash--success' : 'form-flash--error');
            alertEl.textContent = message || '';
            alertEl.hidden = !message;
            try {
                alertEl.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
            } catch (e2) {
                alertEl.scrollIntoView(true);
            }
        }

        function clearAlert() {
            if (!alertEl) return;
            alertEl.textContent = '';
            alertEl.hidden = true;
            alertEl.className = 'contact-form__alert';
        }

        var validationMsg =
            form.getAttribute('data-contact-validation-message') ||
            'Please check your name, email, and message, then try again.';
        var bodyMaxAttr = form.getAttribute('data-contact-body-max');
        var bodyMax = bodyMaxAttr ? parseInt(bodyMaxAttr, 10) : 8000;
        if (isNaN(bodyMax) || bodyMax < 1) bodyMax = 8000;

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            clearAlert();

            if (typeof form.reportValidity === 'function' && !form.reportValidity()) {
                return;
            }

            var nameInput = form.querySelector('input[name="name"]');
            var emailInput = form.querySelector('input[name="email"]');
            var messageInput = form.querySelector('textarea[name="message"]');
            var nameVal = nameInput && nameInput.value ? nameInput.value.trim() : '';
            var emailVal = emailInput && emailInput.value ? emailInput.value.trim() : '';
            var messageVal = messageInput && messageInput.value ? messageInput.value.trim() : '';
            if (nameVal === '' || emailVal === '' || messageVal === '') {
                showAlert(false, validationMsg);
                if (nameVal === '' && nameInput) {
                    nameInput.focus();
                } else if (emailVal === '' && emailInput) {
                    emailInput.focus();
                } else if (messageVal === '' && messageInput) {
                    messageInput.focus();
                }
                return;
            }
            if (nameVal.length > 120 || emailVal.length > 255 || messageVal.length > bodyMax) {
                showAlert(false, validationMsg);
                return;
            }

            var fd = new FormData(form);
            var action = form.getAttribute('action') || window.location.pathname;

            var defaultLabel = '';
            var sendingLabel = '';
            if (submitBtn) {
                defaultLabel = submitBtn.textContent.trim();
                sendingLabel = submitBtn.getAttribute('data-sending-label') || 'Sending…';
                submitBtn.disabled = true;
                submitBtn.setAttribute('aria-busy', 'true');
                submitBtn.classList.add('is-loading');
                submitBtn.textContent = sendingLabel;
            }

            fetch(action, {
                method: 'POST',
                body: fd,
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            })
                .then(function (res) {
                    return res.json().catch(function () {
                        return { ok: false, message: '' };
                    });
                })
                .then(function (data) {
                    if (data && data.ok) {
                        showAlert(true, data.message || '');
                        form.reset();
                        var token = getMeta('csrf-token');
                        var hidden = form.querySelector('input[name="csrf_token"]');
                        if (hidden && token) hidden.value = token;
                    } else {
                        showAlert(false, (data && data.message) || 'Something went wrong.');
                    }
                })
                .catch(function () {
                    var net = form.getAttribute('data-contact-network-error') || 'Network error. Please try again.';
                    showAlert(false, net);
                })
                .finally(function () {
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.removeAttribute('aria-busy');
                        submitBtn.classList.remove('is-loading');
                        submitBtn.textContent = defaultLabel;
                    }
                });
        });
    }

    function initProjectDetailToc() {
        var root = document.querySelector('[data-project-detail]');
        if (!root) return;
        var links = root.querySelectorAll('.project-detail-toc__link[data-toc-target]');
        if (!links.length) return;

        function setActive(id) {
            links.forEach(function (l) {
                var tid = l.getAttribute('data-toc-target');
                l.classList.toggle('is-active', tid === id);
            });
        }

        if (!('IntersectionObserver' in window)) return;

        var sections = [];
        links.forEach(function (link) {
            var tid = link.getAttribute('data-toc-target');
            if (!tid) return;
            var el = document.getElementById(tid);
            if (el) sections.push(el);
        });
        if (!sections.length) return;

        var io = new IntersectionObserver(
            function (entries) {
                var topPick = null;
                var topRatio = 0;
                entries.forEach(function (entry) {
                    if (!entry.isIntersecting) return;
                    if (entry.intersectionRatio >= topRatio) {
                        topRatio = entry.intersectionRatio;
                        topPick = entry.target;
                    }
                });
                if (topPick && topPick.id) setActive(topPick.id);
            },
            {
                root: null,
                rootMargin: '-28% 0px -52% 0px',
                threshold: [0, 0.08, 0.15, 0.25, 0.4, 0.6],
            }
        );

        sections.forEach(function (sec) {
            io.observe(sec);
        });
    }

    /* ---------- Boot ---------- */

    document.addEventListener('DOMContentLoaded', function () {
        initThemeToggle();
        initReveal();
        initProjectFilterRoots();
        initAjaxProjects();
        initAjaxContact();
        initProjectDetailToc();
    });
})();
