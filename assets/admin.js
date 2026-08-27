import './styles/admin.css';

function isAcceptedImage(file) {
    return file.type.startsWith('image/') && file.type !== 'image/svg+xml';
}

function initFileDropzones() {
    document.querySelectorAll('[data-ea-fileupload-field]').forEach((container) => {
        if (container.dataset.dropzoneReady === '1') {
            return;
        }

        container.dataset.dropzoneReady = '1';

        const input = container.querySelector('[data-ea-fileupload-input]');
        if (!(input instanceof HTMLInputElement)) {
            return;
        }

        const hint = document.createElement('p');
        hint.className = 'ea-fileupload-drop-hint';
        hint.textContent = 'Przeciągnij obraz tutaj albo użyj przycisku powyżej.';
        container.appendChild(hint);

        const onDragOver = (event) => {
            event.preventDefault();
            container.classList.add('is-dragover');
        };

        const onDragLeave = (event) => {
            if (event.relatedTarget instanceof Node && container.contains(event.relatedTarget)) {
                return;
            }

            container.classList.remove('is-dragover');
        };

        const onDrop = (event) => {
            event.preventDefault();
            container.classList.remove('is-dragover');

            const files = Array.from(event.dataTransfer?.files ?? []).filter(isAcceptedImage);
            if (files.length === 0) {
                return;
            }

            const transfer = new DataTransfer();
            transfer.items.add(files[0]);
            input.files = transfer.files;
            input.dispatchEvent(new Event('change', { bubbles: true }));
        };

        container.addEventListener('dragover', onDragOver);
        container.addEventListener('dragleave', onDragLeave);
        container.addEventListener('drop', onDrop);
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initFileDropzones);
} else {
    initFileDropzones();
}
