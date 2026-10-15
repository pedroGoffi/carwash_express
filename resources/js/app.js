import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

// Resposta visual imediata a navegação real, sem atrasos artificiais.
const progress = document.getElementById('cw-progress');
const markNavigating = () => progress?.classList.add('is-loading');
window.addEventListener('pageshow', () => progress?.classList.remove('is-loading'));
document.addEventListener('click', (event) => {
    const link = event.target.closest?.('a[href]');
    if (!link || event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
    const href = link.getAttribute('href');
    if (!href || href.startsWith('#') || link.hasAttribute('download') || link.target === '_blank') return;
    const url = new URL(link.href);
    if (url.origin === location.origin && (url.pathname + url.search) !== (location.pathname + location.search)) markNavigating();
});
document.addEventListener('submit', (event) => {
    if (event.defaultPrevented) return;
    const form = event.target;
    if (form instanceof HTMLFormElement && form.target !== '_blank') markNavigating();
});
