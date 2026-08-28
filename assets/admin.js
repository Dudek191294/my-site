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

function parseCategories(select) {
    try {
        const raw = select.dataset.stackCategories;
        return raw ? JSON.parse(raw) : [];
    } catch {
        return [];
    }
}

function createQuickAddPanel(select) {
    const categories = parseCategories(select);
    const panel = document.createElement('div');
    panel.className = 'stack-quick-add';
    panel.innerHTML = `
        <div class="stack-quick-add__header">
            <strong>Szybkie dodawanie technologii</strong>
            <span class="stack-quick-add__hint">Bez przechodzenia do modułu Stack</span>
        </div>
        <div class="stack-quick-add__form">
            <div class="stack-quick-add__field">
                <label class="stack-quick-add__label">Nazwa</label>
                <input type="text" class="form-control stack-quick-add__name" placeholder="np. Vue.js" maxlength="120" autocomplete="off">
            </div>
            <div class="stack-quick-add__field">
                <label class="stack-quick-add__label">Kategoria</label>
                <select class="form-select stack-quick-add__category">
                    ${categories.map((category) => `<option value="${category.value}">${category.label}</option>`).join('')}
                </select>
            </div>
            <div class="stack-quick-add__field">
                <label class="stack-quick-add__label">Ikona <span class="stack-quick-add__optional">(opcjonalnie)</span></label>
                <input type="text" class="form-control stack-quick-add__icon" placeholder="np. vuedotjs" maxlength="80" autocomplete="off">
            </div>
            <div class="stack-quick-add__actions">
                <button type="button" class="btn btn-secondary btn-sm stack-quick-add__submit">
                    Dodaj i przypisz
                </button>
            </div>
        </div>
        <p class="stack-quick-add__feedback" hidden></p>
    `;

    return panel;
}

function createSelectionPreview() {
    const preview = document.createElement('div');
    preview.className = 'stack-selection-preview';
    preview.innerHTML = `
        <p class="stack-selection-preview__title">Wybrane technologie</p>
        <div class="stack-selection-preview__chips"></div>
    `;

    return preview;
}

function updateSelectionPreview(select, tomSelect, preview) {
    const chips = preview.querySelector('.stack-selection-preview__chips');
    if (!(chips instanceof HTMLElement)) {
        return;
    }

    const values = tomSelect ? tomSelect.getValue() : Array.from(select.selectedOptions).map((option) => option.value);
    const normalizedValues = Array.isArray(values) ? values : values ? [values] : [];

    chips.innerHTML = '';

    if (normalizedValues.length === 0) {
        preview.classList.add('is-empty');
        return;
    }

    preview.classList.remove('is-empty');

    normalizedValues.forEach((value) => {
        const chip = document.createElement('div');
        chip.className = 'stack-selection-preview__chip';

        if (tomSelect) {
            const option = tomSelect.options[value];
            chip.innerHTML = option?.entityAsString ?? option?.label_raw ?? option?.text ?? value;
        } else {
            const option = select.querySelector(`option[value="${CSS.escape(value)}"]`);
            chip.textContent = option?.textContent?.trim() ?? value;
        }

        chips.appendChild(chip);
    });
}

function bindTomSelect(select, preview) {
    const connect = (event) => {
        if (!(event.target instanceof HTMLElement) || !select.contains(event.target)) {
            return;
        }

        const { tomSelect } = event.detail ?? {};
        if (!tomSelect) {
            return;
        }

        const refresh = () => updateSelectionPreview(select, tomSelect, preview);
        tomSelect.on('change', refresh);
        refresh();
    };

    select.addEventListener('ea.autocomplete.connect', connect);

    if (select.classList.contains('tomselected') && select.tomselect) {
        updateSelectionPreview(select, select.tomselect, preview);
    }
}

function bindQuickAdd(select, panel) {
    const submit = panel.querySelector('.stack-quick-add__submit');
    const nameInput = panel.querySelector('.stack-quick-add__name');
    const categoryInput = panel.querySelector('.stack-quick-add__category');
    const iconInput = panel.querySelector('.stack-quick-add__icon');
    const feedback = panel.querySelector('.stack-quick-add__feedback');

    if (
        !(submit instanceof HTMLButtonElement)
        || !(nameInput instanceof HTMLInputElement)
        || !(categoryInput instanceof HTMLSelectElement)
        || !(iconInput instanceof HTMLInputElement)
        || !(feedback instanceof HTMLParagraphElement)
    ) {
        return;
    }

    const showFeedback = (message, isError = false) => {
        feedback.hidden = false;
        feedback.textContent = message;
        feedback.classList.toggle('is-error', isError);
        feedback.classList.toggle('is-success', !isError);
    };

    const clearFeedback = () => {
        feedback.hidden = true;
        feedback.textContent = '';
        feedback.classList.remove('is-error', 'is-success');
    };

    submit.addEventListener('click', async () => {
        clearFeedback();

        const name = nameInput.value.trim();
        if (name === '') {
            showFeedback('Podaj nazwę technologii.', true);
            nameInput.focus();
            return;
        }

        const icon = iconInput.value.trim().toLowerCase();
        if (icon !== '' && !/^[a-z0-9]{1,80}$/.test(icon)) {
            showFeedback('Ikona musi być slugiem Simple Icons (tylko a–z, 0–9).', true);
            iconInput.focus();
            return;
        }

        submit.disabled = true;
        submit.textContent = 'Dodawanie…';

        try {
            const response = await fetch(select.dataset.stackQuickCreateUrl ?? '', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-Stack-Quick-Create-Token': select.dataset.stackQuickCreateToken ?? '',
                },
                body: JSON.stringify({
                    name,
                    category: categoryInput.value,
                    icon: icon === '' ? null : icon,
                }),
            });

            const payload = await response.json();
            if (!response.ok) {
                showFeedback(payload.error ?? 'Nie udało się dodać technologii.', true);
                return;
            }

            const tomSelect = select.tomselect;
            if (tomSelect) {
                tomSelect.addOption({
                    entityId: payload.entityId,
                    entityAsString: payload.entityAsString,
                    entityGroup: payload.entityGroup,
                });
                tomSelect.addItem(payload.entityId);
            } else {
                const option = document.createElement('option');
                option.value = payload.entityId;
                option.textContent = name;
                option.selected = true;
                select.appendChild(option);
                select.dispatchEvent(new Event('change', { bubbles: true }));
            }

            nameInput.value = '';
            iconInput.value = '';
            showFeedback(payload.message ?? `Dodano „${name}” i przypisano do projektu.`);

            const preview = select.closest('.field-stack-association')?.querySelector('.stack-selection-preview');
            if (preview && tomSelect) {
                updateSelectionPreview(select, tomSelect, preview);
            }
        } catch {
            showFeedback('Błąd połączenia. Spróbuj ponownie.', true);
        } finally {
            submit.disabled = false;
            submit.textContent = 'Dodaj i przypisz';
        }
    });
}

function initStackAssociationFields() {
    document.querySelectorAll('select[data-stack-field]').forEach((select) => {
        if (!(select instanceof HTMLSelectElement) || select.dataset.stackUiReady === '1') {
            return;
        }

        select.dataset.stackUiReady = '1';

        const field = select.closest('.field-stack-association') ?? select.closest('.form-group');
        if (!(field instanceof HTMLElement)) {
            return;
        }

        const preview = createSelectionPreview();
        const quickAdd = createQuickAddPanel(select);

        field.appendChild(preview);
        field.appendChild(quickAdd);

        bindTomSelect(select, preview);
        bindQuickAdd(select, quickAdd);
        updateSelectionPreview(select, select.tomselect ?? null, preview);
    });
}

function initAdminUi() {
    initFileDropzones();
    initStackAssociationFields();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAdminUi);
} else {
    initAdminUi();
}

document.addEventListener('ea.autocomplete.connect', () => {
    initStackAssociationFields();
});
