document.addEventListener('DOMContentLoaded', function() {
    const lookupFields = document.querySelectorAll('input[name$="_lookup"]');

    lookupFields.forEach(field => {
        const originalParent = field.parentElement;

        const lookupWrapper = document.createElement('div');
        lookupWrapper.className = 'lookup-wrapper';

        const inputWrapper = document.createElement('div');
        inputWrapper.className = 'input-wrapper';

        const loadingIcon = document.createElement('div');
        loadingIcon.className = 'loading-icon';
        loadingIcon.style.display = 'none';

        const loadingSpinner = document.createElement('div');
        loadingSpinner.className = 'loading-spinner';
        loadingIcon.appendChild(loadingSpinner);

        let url = field.dataset.url;
        if (!url) {
            const paramName = field.name.replace('_lookup', '');
            url = `/admin/${paramName}s/autocomplete.json`;
        }

        const idName = field.dataset.idName || 'id';
        const valueName = field.dataset.valueName || 'name';
        const query = field.dataset.query || `search=%QUERY`;
        const wildcard = field.dataset.wildcard || '%QUERY';
        const minLength = parseInt(field.dataset.minLength || '2', 10);
        const delay = parseInt(field.dataset.delay || '300', 10);
        const parentField = field.dataset.parentField;
        const parentIdParam = field.dataset.parentIdParam;
        const dependentFields = JSON.parse(field.dataset.dependentFields || '[]');
        const additionalParents = JSON.parse(field.dataset.additionalParents || '{}');

        const hiddenFieldName = field.name.replace('_lookup', '');
        const hiddenField = document.querySelector(`input[name="${hiddenFieldName}"]`);

        if (!hiddenField) {
            console.error(`Cannot find hidden field for ${field.name}`);
            return;
        }

        const suggestionsList = document.createElement('ul');
        suggestionsList.className = 'suggestions-list';
        suggestionsList.style.display = 'none';
        suggestionsList.setAttribute('role', 'listbox');

        originalParent.replaceChild(lookupWrapper, field);
        lookupWrapper.appendChild(inputWrapper);
        inputWrapper.appendChild(field);
        inputWrapper.appendChild(loadingIcon);
        lookupWrapper.appendChild(suggestionsList);
        lookupWrapper.appendChild(hiddenField);

        let debounceTimer;

        const resetDependentFields = () => {
            dependentFields.forEach(fieldName => {
                const dependentField = document.querySelector(`input[name="${fieldName}_lookup"]`);
                const dependentHidden = document.querySelector(`input[name="${fieldName}"]`);
                if (dependentField) {
                    dependentField.value = '';
                    dependentField.disabled = true;
                }
                if (dependentHidden) {
                    dependentHidden.value = '';
                }
            });
        };

        const resetField = () => {
            field.value = '';
            hiddenField.value = '';
            resetDependentFields();
        };

        if (parentField) {
            const parentWrapper = document.querySelector(`input[name="${parentField}_lookup"]`)?.closest('.lookup-wrapper');
            if (parentWrapper) {
                parentWrapper.addEventListener('lookup.changed', () => {
                    field.disabled = false;
                    resetField();
                });
                parentWrapper.addEventListener('lookup.reset', () => {
                    field.disabled = true;
                    resetField();
                });
            }
        }

        Object.keys(additionalParents).forEach(fieldName => {
            const additionalWrapper = document.querySelector(`input[name="${fieldName}_lookup"]`)?.closest('.lookup-wrapper');
            if (additionalWrapper) {
                additionalWrapper.addEventListener('lookup.changed', resetField);
                additionalWrapper.addEventListener('lookup.reset', resetField);
            }
        });

        const fetchSuggestions = async (searchTerm) => {
            if (searchTerm.length < minLength || field.disabled) {
                suggestionsList.style.display = 'none';
                return;
            }

            try {
                loadingIcon.style.display = 'block';

                const finalQuery = query.replace(wildcard, encodeURIComponent(searchTerm));
                let fetchUrl = `${url}${url.includes('?') ? '&' : '?'}${finalQuery}`;

                if (parentField) {
                    const parentHidden = document.querySelector(`input[name="${parentField}"]`);
                    if (parentHidden?.value) {
                        fetchUrl += `&${parentIdParam}=${parentHidden.value}`;
                    }
                }

                Object.entries(additionalParents).forEach(([fieldName, param]) => {
                    const additionalHidden = document.querySelector(`input[name="${fieldName}"]`);
                    if (additionalHidden?.value) {
                        fetchUrl += `&${param}=${additionalHidden.value}`;
                    }
                });

                const response = await fetch(fetchUrl);
                let data = await response.json();

                let items = [];
                if (data.success && Array.isArray(data.data)) {
                    items = data.data;
                } else if (Array.isArray(data)) {
                    items = data;
                }

                suggestionsList.innerHTML = '';

                if (!items || items.length === 0) {
                    suggestionsList.style.display = 'none';
                    loadingIcon.style.display = 'none';
                    return;
                }

                items.forEach(item => {
                    const li = document.createElement('li');
                    li.setAttribute('role', 'option');

                    const displayValue = getNestedValue(item, valueName);
                    const idValue = getNestedValue(item, idName);

                    li.textContent = displayValue || 'Unknown';

                    li.addEventListener('click', () => {
                        field.value = displayValue;
                        hiddenField.value = idValue;
                        suggestionsList.style.display = 'none';

                        const changeEvent = new CustomEvent('lookup.changed', {
                            bubbles: true,
                            detail: { id: idValue, value: displayValue }
                        });
                        lookupWrapper.dispatchEvent(changeEvent);
                    });

                    li.addEventListener('mouseenter', () => {
                        suggestionsList.querySelectorAll('li').forEach(el => {
                            el.classList.remove('is-active');
                        });
                        li.classList.add('is-active');
                    });

                    suggestionsList.appendChild(li);
                });

                suggestionsList.style.display = 'block';

            } catch (error) {
                console.error('Error fetching autocomplete suggestions:', error);
                suggestionsList.style.display = 'none';
            } finally {
                loadingIcon.style.display = 'none';
            }
        };

        function getNestedValue(obj, path) {
            if (!path || !obj) return '';

            return path.split('.').reduce((current, key) => {
                return current && current[key] !== undefined ? current[key] : '';
            }, obj);
        }

        field.addEventListener('input', (event) => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                fetchSuggestions(event.target.value);
            }, delay);
        });

        document.addEventListener('click', (event) => {
            if (!lookupWrapper.contains(event.target)) {
                suggestionsList.style.display = 'none';
            }
        });

        field.addEventListener('keydown', (event) => {
            const items = suggestionsList.querySelectorAll('li');
            const activeItem = suggestionsList.querySelector('li.is-active');
            let activeIndex = Array.from(items).indexOf(activeItem);

            if (suggestionsList.style.display === 'none') {
                return;
            }

            switch(event.key) {
                case 'ArrowDown':
                    event.preventDefault();
                    if (activeItem) {
                        activeItem.classList.remove('is-active');
                        activeIndex = Math.min(activeIndex + 1, items.length - 1);
                    } else {
                        activeIndex = 0;
                    }
                    items[activeIndex]?.classList.add('is-active');
                    items[activeIndex]?.scrollIntoView({ block: 'nearest' });
                    break;

                case 'ArrowUp':
                    event.preventDefault();
                    if (activeItem) {
                        activeItem.classList.remove('is-active');
                        activeIndex = Math.max(activeIndex - 1, 0);
                    } else {
                        activeIndex = items.length - 1;
                    }
                    items[activeIndex]?.classList.add('is-active');
                    items[activeIndex]?.scrollIntoView({ block: 'nearest' });
                    break;

                case 'Enter':
                    if (activeItem) {
                        event.preventDefault();
                        activeItem.click();
                    }
                    break;

                case 'Escape':
                    suggestionsList.style.display = 'none';
                    break;
            }
        });
    });
});