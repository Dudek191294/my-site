/**
 * Section reveal via Intersection Observer.
 * Skipped when prefers-reduced-motion: reduce (CSS already shows final state).
 */
function prefersReducedMotion() {
    return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
}

function initReveal() {
    const nodes = document.querySelectorAll('[data-reveal]');

    if (!nodes.length) {
        return;
    }

    if (prefersReducedMotion() || !('IntersectionObserver' in window)) {
        nodes.forEach((node) => node.classList.add('is-visible'));
        return;
    }

    const observer = new IntersectionObserver(
        (entries, obs) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) {
                    return;
                }

                entry.target.classList.add('is-visible');
                obs.unobserve(entry.target);
            });
        },
        {
            root: null,
            rootMargin: '0px 0px -8% 0px',
            threshold: 0.12,
        },
    );

    nodes.forEach((node) => observer.observe(node));
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initReveal);
} else {
    initReveal();
}
