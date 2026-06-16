const CART_COUNT_STORAGE_KEY = 'vuecommerce.cart_count';
const CART_FEEDBACK_DURATION = 3000;
const cartFeedbackTimers = new WeakMap();

const visibleDisplayClass = (element) => {
    if (element.classList.contains('sm:hidden')) {
        return 'flex';
    }

    return 'inline-flex';
};

const setCountLinksVisibility = (selector, count) => {
    document.querySelectorAll(selector).forEach((link) => {
        const shouldShow = count > 0;
        const displayClass = visibleDisplayClass(link);

        link.classList.toggle('hidden', ! shouldShow);
        link.classList.toggle(displayClass, shouldShow);
    });
};

const updateCartCount = (cartCount) => {
    const count = Math.max(0, Number(cartCount) || 0);

    localStorage.setItem(CART_COUNT_STORAGE_KEY, String(count));

    document.querySelectorAll('[data-cart-count]').forEach((counter) => {
        counter.textContent = String(count);
    });

    setCountLinksVisibility('[data-cart-link]', count);
};

const updateFavoriteLinksVisibility = () => {
    const favoriteCounters = document.querySelectorAll('[data-favorite-count]');
    const favoriteCount = Math.max(0, Number(favoriteCounters[0]?.textContent.trim()) || 0);

    setCountLinksVisibility('[data-favorite-link]', favoriteCount);
};

const updateCartStatuses = (productId, itemQuantity) => {
    const quantity = Math.max(0, Number(itemQuantity) || 0);

    document
        .querySelectorAll(`[data-cart-status][data-product-id="${productId}"]`)
        .forEach((status) => {
            const quantityLabel = status.querySelector('[data-cart-status-quantity]');
            const quantityInput = status.querySelector('[data-cart-quantity-input]');

            if (quantityLabel) {
                quantityLabel.textContent = String(quantity);
            } else {
                status.textContent = (status.dataset.cartStatusLabel || ':quantity')
                    .replace(':quantity', String(quantity));
            }

            if (quantityInput) {
                quantityInput.value = String(Math.max(1, quantity));
            }

            status.classList.toggle('hidden', quantity < 1);
        });
};

const clampCartQuantity = (input, quantity) => {
    const minimum = Number(input.min) || 1;
    const maximum = Number(input.max) || minimum;

    return Math.min(maximum, Math.max(minimum, Number(quantity) || minimum));
};

const setCartQuantityControlsDisabled = (form, isDisabled) => {
    form
        .querySelectorAll('[data-cart-quantity-step], [data-cart-quantity-input], [data-cart-remove-button]')
        .forEach((control) => {
            control.disabled = isDisabled;
        });
};

const submitCartQuantityForm = async (form, requestedQuantity = null) => {
    const input = form.querySelector('[data-cart-quantity-input]');

    if (! input) {
        return;
    }

    const quantity = clampCartQuantity(input, requestedQuantity ?? input.value);

    input.value = String(quantity);
    setCartQuantityControlsDisabled(form, true);

    try {
        const formData = new FormData(form);

        formData.set('quantity', String(quantity));

        const response = await fetch(form.action, {
            method: form.method || 'POST',
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: formData,
        });

        if (! response.ok) {
            return;
        }

        const data = await response.json();

        updateCartCount(data.cart_count);
        updateCartStatuses(form.dataset.productId, data.item_quantity);
    } catch {
        return;
    } finally {
        setCartQuantityControlsDisabled(form, false);
    }
};

const showCartFeedback = (button) => {
    const form = button.closest('form');
    const feedback = form?.querySelector('[data-cart-feedback]');

    if (! feedback) {
        return;
    }

    const existingTimer = cartFeedbackTimers.get(button);

    if (existingTimer) {
        clearTimeout(existingTimer);
    }

    feedback.textContent = button.dataset.addedLabel || '';
    feedback.classList.remove('opacity-0');
    feedback.classList.add('opacity-100');

    cartFeedbackTimers.set(button, setTimeout(() => {
        feedback.classList.remove('opacity-100');
        feedback.classList.add('opacity-0');
        cartFeedbackTimers.delete(button);
    }, CART_FEEDBACK_DURATION));
};

const initializeCartHeader = () => {
    const cartCounter = document.querySelector('[data-cart-count]');
    const cartCount = Number(cartCounter?.textContent.trim() ?? localStorage.getItem(CART_COUNT_STORAGE_KEY) ?? 0);

    updateCartCount(cartCount);
    updateFavoriteLinksVisibility();
};

document.addEventListener('submit', async (event) => {
    const quantityForm = event.target.closest('[data-cart-quantity-form]');

    if (quantityForm) {
        event.preventDefault();
        await submitCartQuantityForm(quantityForm);

        return;
    }

    const button = event.submitter?.closest('[data-cart-button]');

    if (! button || button.disabled) {
        return;
    }

    const form = button.closest('form');

    if (! form) {
        return;
    }

    event.preventDefault();

    button.disabled = true;

    try {
        const response = await fetch(form.action, {
            method: form.method || 'POST',
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: new FormData(form),
        });

        if (! response.ok) {
            return;
        }

        const data = await response.json();
        const productId = button.dataset.productId;

        updateCartCount(data.cart_count);
        updateCartStatuses(productId, data.item_quantity);
        showCartFeedback(button);
    } catch {
        return;
    } finally {
        button.disabled = false;
    }
});

document.addEventListener('click', async (event) => {
    const removeButton = event.target.closest('[data-cart-remove-button]');

    if (removeButton && ! removeButton.disabled) {
        const form = removeButton.closest('[data-cart-quantity-form]');

        if (! form) {
            return;
        }

        event.preventDefault();
        setCartQuantityControlsDisabled(form, true);

        try {
            const response = await fetch(removeButton.dataset.removeUrl, {
                method: 'DELETE',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
            });

            if (! response.ok) {
                return;
            }

            const data = await response.json();

            updateCartCount(data.cart_count);
            updateCartStatuses(form.dataset.productId, data.item_quantity);
        } catch {
            return;
        } finally {
            setCartQuantityControlsDisabled(form, false);
        }

        return;
    }

    const stepButton = event.target.closest('[data-cart-quantity-step]');

    if (! stepButton || stepButton.disabled) {
        return;
    }

    const form = stepButton.closest('[data-cart-quantity-form]');
    const input = form?.querySelector('[data-cart-quantity-input]');

    if (! form || ! input) {
        return;
    }

    const step = Number(stepButton.dataset.cartQuantityStep) || 0;
    const quantity = clampCartQuantity(input, Number(input.value) + step);

    if (quantity === Number(input.value)) {
        input.value = String(quantity);

        return;
    }

    await submitCartQuantityForm(form, quantity);
});

document.addEventListener('change', async (event) => {
    const input = event.target.closest('[data-cart-quantity-input]');

    if (! input) {
        return;
    }

    const form = input.closest('[data-cart-quantity-form]');

    if (! form) {
        return;
    }

    await submitCartQuantityForm(form);
});

document.addEventListener('cart-updated', (event) => {
    updateCartCount(event.detail?.cart_count ?? 0);
});

document.addEventListener('DOMContentLoaded', initializeCartHeader);
document.addEventListener('livewire:navigated', initializeCartHeader);
