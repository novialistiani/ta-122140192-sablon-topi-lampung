document.addEventListener('DOMContentLoaded', () => {
    const dropdownHeaders = document.querySelectorAll('.dropdown-header');

    const toggleContent = content => {
        if (!content || !content.classList.contains('dropdown-content')) return;
        const header = content.previousElementSibling;
        if (!header) return;
        const isActive = !header.classList.contains('active');
        header.classList.toggle('active', isActive);
        content.classList.toggle('active', isActive);
    };

    dropdownHeaders.forEach(header => {
        header.addEventListener('click', () => {
            const content = header.nextElementSibling;
            toggleContent(content);
        });
    });

    window.toggleDropdown = id => {
        const content = document.getElementById(id);
        toggleContent(content);
    };

    // Map selection group -> wrapper element (chip container)
    const selectionWrappers = {};
    document.querySelectorAll('.selected-items').forEach(wrapper => {
        const group = wrapper.dataset.selectionGroup;
        if (group) selectionWrappers[group] = wrapper;
    });

    // Single select groups
    const singleSelectGroups = ['cutting'];

    const updateAddButtonState = (optionItem, isActive) => {
        const addButton = optionItem.querySelector('.add-btn');
        if (addButton) {
            addButton.classList.toggle('option-select-active', isActive);
            addButton.textContent = isActive ? '-' : '+';
        }
    };

    const removeSelectionChip = (group, value, wrapper) => {
        const chip = wrapper.querySelector(`.selection-item[data-option-group="${group}"][data-option-value="${value}"]`);
        if (chip) chip.remove();
    };

    const createSelectionChip = (group, value, wrapper) => {
        const chip = document.createElement('div');
        chip.className = 'selected-badge selection-item';
        chip.dataset.optionGroup = group;
        chip.dataset.optionValue = value;

        const text = document.createElement('span');
        text.className = 'selection-text';
        text.textContent = value;
        chip.appendChild(text);

        const removeButton = document.createElement('button');
        removeButton.type = 'button';
        removeButton.className = 'remove-badge';
        removeButton.dataset.optionGroup = group;
        removeButton.dataset.optionValue = value;
        removeButton.textContent = '×';
        removeButton.addEventListener('click', event => {
            event.stopPropagation();
            const option = document.querySelector(`.dropdown-item.option-item[data-option-group="${group}"][data-option-value="${value}"]`);
            if (option) {
                option.classList.remove('option-item-active');
                updateAddButtonState(option, false);
            }
            removeSelectionChip(group, value, wrapper);
        });
        chip.appendChild(removeButton);
        return chip;
    };

    // Use event delegation to ensure clicks always register
    document.addEventListener('click', (event) => {
        const item = event.target.closest('.dropdown-item.option-item');
        if (!item) return;
        // Avoid handling clicks coming from outside dropdowns
        if (!document.body.contains(item)) return;

        const group = item.dataset.optionGroup;
        const value = item.dataset.optionValue || item.querySelector('span')?.textContent?.trim() || '';
        const wrapper = selectionWrappers[group];
        const content = item.closest('.dropdown-content');
        const header = content?.previousElementSibling;
        const isSingleSelect = singleSelectGroups.includes(group);
        const isActive = item.classList.contains('option-item-active');

        if (!group || !value || !wrapper) return;

        if (isSingleSelect) {
            // Clear previous selections
            document.querySelectorAll(`.dropdown-item.option-item[data-option-group="${group}"]`).forEach(optionItem => {
                optionItem.classList.remove('option-item-active');
                updateAddButtonState(optionItem, false);
            });
            wrapper.innerHTML = '';

            // Set new selection
            item.classList.add('option-item-active');
            updateAddButtonState(item, true);
            const chip = createSelectionChip(group, value, wrapper);
            wrapper.appendChild(chip);

            // Close dropdown after selecting
            if (content && header) {
                header.classList.remove('active');
                content.classList.remove('active');
            }
        } else {
            // Multi-select toggle
            if (isActive) {
                item.classList.remove('option-item-active');
                updateAddButtonState(item, false);
                removeSelectionChip(group, value, wrapper);
            } else {
                item.classList.add('option-item-active');
                updateAddButtonState(item, true);
                const chip = createSelectionChip(group, value, wrapper);
                wrapper.appendChild(chip);
            }
        }
    });

    // Ensure dropdown content sits above possible overlays
    document.querySelectorAll('.dropdown-content').forEach(dc => {
        dc.style.position = dc.style.position || 'relative';
        dc.style.zIndex = dc.style.zIndex || '1';
    });
});
