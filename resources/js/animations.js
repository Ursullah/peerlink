// Lightweight animations: smooth scrolling, page transition on link click, and scroll-triggered reveals
export default function initAnimations() {
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    function setupSmoothScroll() {
        if (prefersReducedMotion) return;
        document.addEventListener('click', (e) => {
            const a = e.target.closest('a[href]');
            if (!a) return;
            const href = a.getAttribute('href');
            // only handle same-page hash links
            if (href && href.startsWith('#')) {
                const target = document.querySelector(href);
                if (target) {
                    e.preventDefault();
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    // update history so back/forward works naturally
                    history.pushState(null, '', href);
                }
            }
        });
    }

    function setupPageTransition() {
        if (prefersReducedMotion) return;

        const overlay = document.createElement('div');
        overlay.className = 'page-transition';
        document.body.appendChild(overlay);

        // delegate link clicks for internal navigation
        document.addEventListener('click', (e) => {
            const a = e.target.closest('a[href]');
            if (!a) return;

            // ignore new tab/external links and hash-only links
            if (a.target === '_blank') return;
            const href = a.href;
            if (!href) return;
            const url = new URL(href, location.href);
            if (url.origin !== location.origin) return;
            if (url.pathname === location.pathname && url.hash) return; // hash handled by smooth scroll

            // let links that only point to assets (contain '.') pass through
            if (url.pathname.includes('.')) return;

            e.preventDefault();
            overlay.classList.add('enter');
            // small delay to allow CSS animation to play
            setTimeout(() => {
                window.location.href = href;
            }, 220);
        });
    }

    function setupScrollAnimations() {
        const els = Array.from(document.querySelectorAll('.animate-on-scroll'));
        if (!els.length) return;

        if (prefersReducedMotion) {
            els.forEach(el => el.classList.add('is-visible'));
            return;
        }

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const el = entry.target;
                    const delay = el.dataset && el.dataset.delay ? Number(el.dataset.delay) : 0;
                    if (delay) el.style.transitionDelay = `${delay}ms`;
                    el.classList.add('is-visible');
                    observer.unobserve(el);
                }
            });
        }, { threshold: 0.12, rootMargin: '0px 0px -8% 0px' });

        els.forEach(el => observer.observe(el));
    }

    // init on DOMContentLoaded (works well with Vite/Alpine startup)
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            setupSmoothScroll();
            setupPageTransition();
            setupScrollAnimations();
        });
    } else {
        setupSmoothScroll();
        setupPageTransition();
        setupScrollAnimations();
    }
}

// Auto-run when imported so consumers don't need to call it explicitly
try {
    initAnimations();
} catch (e) {
    // fail silently in non-browser environments
}
