/* feature-grid.js */
document.addEventListener('DOMContentLoaded', () => {
    // Reveal Animations using Intersection Observer
    const revealObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('active');
                revealObserver.unobserve(entry.target); // Reveal only once
            }
        });
    }, {
        threshold: 0.05,
        rootMargin: '0px 0px -20px 0px'
    });

    document.querySelectorAll('.reveal').forEach(el => revealObserver.observe(el));

    // Custom Cursor Interaction with cards
    const cursor = document.getElementById('custom-cursor');
    const cards = document.querySelectorAll('.module-card');

    cards.forEach(card => {
        card.addEventListener('mouseenter', () => {
            if (cursor) {
                cursor.style.transform = 'scale(4)';
                cursor.style.backgroundColor = 'rgba(255, 92, 0, 0.2)';
            }
        });

        card.addEventListener('mouseleave', () => {
            if (cursor) {
                cursor.style.transform = 'scale(1)';
                cursor.style.backgroundColor = 'var(--primary-orange)';
            }
        });
    });
});
