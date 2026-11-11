import './bootstrap';
import { createApp, h } from 'vue';
import DashboardStats from './components/DashboardStats.vue';
import DashboardChart from './components/DashboardChart.vue';
import DataTable from './components/DataTable.vue';
import ConfirmModal from './components/ConfirmModal.vue';

function decodeHtmlEntities(str) {
    if (!str || typeof str !== 'string') return str;
    return str
        .replace(/&quot;/g, '"')
        .replace(/&#039;/g, "'")
        .replace(/&apos;/g, "'")
        .replace(/&lt;/g, '<')
        .replace(/&gt;/g, '>')
        .replace(/&amp;/g, '&');
}

function parsePropValue(val) {
    if (val === undefined || val === null) return val;
    val = String(val).trim();
    val = decodeHtmlEntities(val);
    try {
        return JSON.parse(val);
    } catch (_) {}
    try {
        return (new Function(`return (${val})`))();
    } catch (_) {}
    return val;
}

function getPropsFromElement(el) {
    const props = {};
    for (const attr of Array.from(el.attributes)) {
        let name = attr.name;
        let isBound = false;
        if (name.startsWith(':')) {
            name = name.slice(1);
            isBound = true;
        } else if (name.startsWith('v-bind:')) {
            name = name.slice(7);
            isBound = true;
        }

        const camelName = name.replace(/-([a-z])/g, (_, c) => c.toUpperCase());
        props[camelName] = isBound ? parsePropValue(attr.value) : attr.value;
    }
    return props;
}

function mountVueComponents() {
    // Mount Dashboard Charts
    document.querySelectorAll('dashboard-chart').forEach((el) => {
        if (el.dataset.vueMounted) return;
        el.dataset.vueMounted = 'true';
        const props = getPropsFromElement(el);
        const app = createApp({
            render: () => h(DashboardChart, props)
        });
        app.mount(el);
    });

    // Mount Dashboard Stats
    document.querySelectorAll('dashboard-stats').forEach((el) => {
        if (el.dataset.vueMounted) return;
        el.dataset.vueMounted = 'true';
        const props = getPropsFromElement(el);
        const app = createApp({
            render: () => h(DashboardStats, props)
        });
        app.mount(el);
    });

    // Mount custom containers if any
    const vueApp = document.getElementById('vue-app');
    if (vueApp && !vueApp.dataset.vueMounted) {
        vueApp.dataset.vueMounted = 'true';
        const app = createApp({});
        app.component('dashboard-stats', DashboardStats);
        app.component('dashboard-chart', DashboardChart);
        app.component('data-table', DataTable);
        app.component('confirm-modal', ConfirmModal);
        app.mount('#vue-app');
    }
}

function isRevealExcluded(el) {
    if (!el || el.nodeType !== 1) return true;
    if (
        el.classList.contains('fixed') ||
        el.classList.contains('hidden') ||
        el.hasAttribute('x-show') ||
        el.hasAttribute('x-cloak') ||
        el.hasAttribute('hidden') ||
        el.tagName === 'TEMPLATE' ||
        el.tagName === 'SCRIPT' ||
        el.tagName === 'STYLE'
    ) {
        return true;
    }
    if (el.closest('.fixed, [x-show], [x-cloak], [hidden], form.grid, .overflow-y-auto')) {
        return true;
    }
    if (el.offsetParent === null && getComputedStyle(el).position !== 'fixed') {
        return true;
    }
    return false;
}

function collectSpatialRevealTargets() {
    const targets = [];
    const seen = new Set();
    const addTarget = (el, isGridItem = false) => {
        if (!el || seen.has(el) || isRevealExcluded(el)) return;
        seen.add(el);
        targets.push({ el, isGridItem });
    };

    const appMain = document.querySelector('main.app-main');
    if (appMain) {
        appMain.querySelectorAll(':scope > div > *').forEach((el) => {
            if (!el.classList.contains('grid')) {
                addTarget(el, false);
            }
        });

        appMain.querySelectorAll('.grid').forEach((grid) => {
            if (grid.tagName === 'FORM' || isRevealExcluded(grid)) return;
            const inForm = Boolean(grid.closest('form'));
            Array.from(grid.children).forEach((child) => {
                if (
                    child.classList.contains('grid') ||
                    ['BUTTON', 'INPUT', 'SELECT', 'TEXTAREA', 'LABEL'].includes(child.tagName)
                ) {
                    return;
                }
                if (inForm) {
                    const cls = child.className && typeof child.className === 'string' ? child.className : '';
                    const isCardPane =
                        cls.includes('rounded-2xl') ||
                        cls.includes('rounded-3xl') ||
                        child.tagName === 'DASHBOARD-CHART' ||
                        child.tagName === 'DASHBOARD-STATS';
                    if (!isCardPane) return;
                }
                addTarget(child, true);
            });
        });

        appMain.querySelectorAll('tbody tr').forEach((row) => {
            addTarget(row, false);
        });
    } else {
        const landingSections = ['#about', '#features', '#how-it-works', '#announcements', '#contact'];
        landingSections.forEach((selector) => {
            const section = document.querySelector(selector);
            if (!section || isRevealExcluded(section)) return;

            section.querySelectorAll(':scope > .text-center, :scope > div > .text-center, :scope > div > .flex.items-center.justify-between').forEach((hdr) => {
                addTarget(hdr, false);
            });

            section.querySelectorAll('.grid').forEach((grid) => {
                if (grid.closest('form')) return;
                Array.from(grid.children).forEach((child) => {
                    if (!child.classList.contains('grid')) {
                        addTarget(child, true);
                    }
                });
            });
        });
    }

    return targets;
}

function initSpatialScrollReveal() {
    if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        return;
    }
    if (typeof IntersectionObserver === 'undefined') {
        return;
    }

    const candidates = collectSpatialRevealTargets();
    if (!candidates.length) return;

    const foldThreshold = window.innerHeight * 0.96;
    const belowFold = [];

    for (const item of candidates) {
        const rect = item.el.getBoundingClientRect();
        if (rect.top > foldThreshold) {
            belowFold.push(item);
        }
    }

    if (!belowFold.length) return;

    belowFold.forEach(({ el, isGridItem }) => {
        el.classList.add('scroll-reveal-pending');
        if (isGridItem) {
            el.classList.add('scroll-reveal-grid');
        }
    });

    const revealElement = (el, delayMs) => {
        if (!el.classList.contains('scroll-reveal-pending')) return;
        const isRow = el.tagName === 'TR';

        el.style.setProperty('--reveal-delay', `${delayMs}ms`);
        el.classList.remove('scroll-reveal-pending');
        el.classList.add('scroll-reveal-visible');

        let settled = false;
        const finish = () => {
            if (settled) return;
            settled = true;
            el.removeEventListener('animationend', onAnimEnd);
            el.classList.remove('scroll-reveal-visible', 'scroll-reveal-grid');
            el.classList.add('scroll-reveal-done');
            el.style.removeProperty('--reveal-delay');
        };
        const onAnimEnd = (event) => {
            if (event.target === el) {
                finish();
            }
        };

        el.addEventListener('animationend', onAnimEnd);
        window.setTimeout(finish, (isRow ? 950 : 1200) + delayMs + 80);
    };

    const observer = new IntersectionObserver(
        (entries) => {
            const visibleEntries = entries
                .filter((entry) => entry.isIntersecting)
                .sort((a, b) => {
                    const rowA = Math.round(a.boundingClientRect.top / 36);
                    const rowB = Math.round(b.boundingClientRect.top / 36);
                    if (rowA !== rowB) return rowA - rowB;
                    return a.boundingClientRect.left - b.boundingClientRect.left;
                });

            visibleEntries.forEach((entry, idx) => {
                const el = entry.target;
                observer.unobserve(el);
                const isRow = el.tagName === 'TR';
                const stepMs = isRow ? 55 : 105;
                const maxDelayMs = isRow ? 500 : 630;
                revealElement(el, Math.min(idx * stepMs, maxDelayMs));
            });
        },
        {
            threshold: 0.04,
            rootMargin: '0px 0px -24px 0px',
        }
    );

    belowFold.forEach(({ el }) => observer.observe(el));

    window.addEventListener('beforeprint', () => {
        observer.disconnect();
        belowFold.forEach(({ el }) => revealElement(el, 0));
    });
}

function bootApplication() {
    mountVueComponents();
    initSpatialScrollReveal();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootApplication);
} else {
    bootApplication();
}
