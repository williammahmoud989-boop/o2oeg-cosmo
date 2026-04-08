/* modal.js */
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('moduleModal');
    const modalBody = document.getElementById('modalBody');

    if (!modal || !modalBody) return;

    window.openModule = async function(type) {
        // Show loading state
        modalBody.innerHTML = `
            <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 300px;">
                <div class="loader-spinner" style="width: 50px; height: 50px; border: 3px solid rgba(255,92,0,0.1); border-top-color: #FF5C00; border-radius: 50%; animation: spin 1s linear infinite;"></div>
                <p style="margin-top: 1.5rem; color: #94A3B8;">جاري تحميل الموديول الذكي...</p>
            </div>
            <style>@keyframes spin { to { transform: rotate(360deg); } }</style>
        `;

        modal.classList.add('active');
        document.body.style.overflow = 'hidden';

        try {
            const response = await fetch(`/modals/${type}.html`);
            if (!response.ok) throw new Error('Failed to load content');
            const content = await response.text();
            
            // Add a slight delay for cinematic feel
            setTimeout(() => {
                modalBody.innerHTML = content;
            }, 300);

        } catch (error) {
            modalBody.innerHTML = `
                <div style="text-align: center; padding: 3rem;">
                    <h3 style="color: #ef4444;">عذراً، تعذر تحميل البيانات</h3>
                    <p style="color: #94A3B8; margin-top: 1rem;">يرجى المحاولة مرة أخرى لاحقاً.</p>
                    <button onclick="closeModal()" class="btn btn-orange" style="margin-top: 2rem;">إغلاق</button>
                </div>
            `;
        }
    };

    window.closeModal = function() {
        modal.classList.remove('active');
        document.body.style.overflow = '';
    };

    // Close on escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') window.closeModal();
    });

    // Close when clicking outside content
    modal.addEventListener('click', (e) => {
        if (e.target === modal) window.closeModal();
    });
});
