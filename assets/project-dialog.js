function initProjectDialogs() {
    document.querySelectorAll('[data-dialog-open]').forEach((button) => {
        const id = button.getAttribute('data-dialog-open');
        const dialog = id ? document.getElementById(id) : null;
        if (!(button instanceof HTMLButtonElement) || !(dialog instanceof HTMLDialogElement)) {
            return;
        }

        button.addEventListener('click', () => {
            dialog.showModal();
        });

        dialog.addEventListener('click', (event) => {
            if (event.target === dialog) {
                dialog.close();
            }
        });
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initProjectDialogs);
} else {
    initProjectDialogs();
}
