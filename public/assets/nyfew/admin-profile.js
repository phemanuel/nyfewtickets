document.addEventListener('DOMContentLoaded', () => {
    const menu = document.querySelector('.nyfew-profile-menu');
    if (!menu) return;

    // Keep native <details> behaviour; only enhance dismissal.
    document.addEventListener('click', (event) => {
        if (menu.open && !menu.contains(event.target)) menu.open = false;
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && menu.open) {
            menu.open = false;
            menu.querySelector('summary')?.focus();
        }
    });
});
