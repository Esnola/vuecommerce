const FAVORITES_STORAGE_KEY = 'vuecommerce.favorites';
const FAVORITE_FEEDBACK_DURATION = 3000;
const favoriteFeedbackTimers = new WeakMap();

const favoriteIdsFromStorage = () => {
    try {
        const favoriteIds = JSON.parse(localStorage.getItem(FAVORITES_STORAGE_KEY) ?? '[]');

        if (!Array.isArray(favoriteIds)) {
            return [];
        }

        return [...new Set(
            favoriteIds
                .map(Number)
                .filter((favoriteId) => Number.isInteger(favoriteId) && favoriteId > 0),
        )];
    } catch {
        return [];
    }
};

const storeFavoriteIds = (favoriteIds) => {
    localStorage.setItem(FAVORITES_STORAGE_KEY, JSON.stringify(favoriteIds));
};

const updateFavoriteLinksVisibility = (favoriteCount) => {
    document.querySelectorAll('[data-favorite-link]').forEach((link) => {
        const shouldShow = favoriteCount > 0;

        link.classList.toggle('hidden', !shouldShow);
        link.classList.toggle('inline-flex', shouldShow);
    });
};

const updateFavoriteCount = (favoriteCount) => {
    const count = Math.max(0, favoriteCount);

    document.querySelectorAll('[data-favorite-count]').forEach((counter) => {
        counter.textContent = String(count);
    });

    updateFavoriteLinksVisibility(count);
};

const adjustFavoriteCount = (delta) => {
    let updatedCount = 0;

    document.querySelectorAll('[data-favorite-count]').forEach((counter) => {
        const currentCount = Number(counter.textContent.trim());

        updatedCount = Math.max(0, (Number.isInteger(currentCount) ? currentCount : 0) + delta);
        counter.textContent = String(updatedCount);
    });

    updateFavoriteLinksVisibility(updatedCount);
};

const showFavoriteFeedback = (button, isFavorite) => {
    const feedback = button.querySelector('[data-favorite-feedback]');

    if (!feedback) {
        return;
    }

    const existingTimer = favoriteFeedbackTimers.get(button);

    if (existingTimer) {
        clearTimeout(existingTimer);
    }

    feedback.textContent = isFavorite
        ? button.dataset.addedLabel
        : button.dataset.removedLabel;
    feedback.classList.remove('opacity-0');
    feedback.classList.add('opacity-100');

    favoriteFeedbackTimers.set(button, setTimeout(() => {
        feedback.classList.remove('opacity-100');
        feedback.classList.add('opacity-0');
        favoriteFeedbackTimers.delete(button);
    }, FAVORITE_FEEDBACK_DURATION));
};

const setFavoriteButtonState = (button, isFavorite) => {
    const icon = button.querySelector('[data-favorite-icon]');
    const label = button.querySelector('[data-favorite-label]');

    button.dataset.isFavorite = isFavorite ? 'true' : 'false';
    button.setAttribute('aria-pressed', isFavorite ? 'true' : 'false');
    button.classList.toggle('text-rose-600', isFavorite);
    button.classList.toggle('text-gray-400', !isFavorite);

    if (icon) {
        icon.setAttribute('fill', isFavorite ? 'currentColor' : 'none');
    }

    if (label) {
        label.textContent = isFavorite
            ? button.dataset.removeLabel
            : button.dataset.addLabel;
    }
};

const updateFavoriteButtons = (favoriteIds) => {
    const favoriteIdSet = new Set(favoriteIds.map(Number));

    document.querySelectorAll('[data-favorite-button]').forEach((button) => {
        setFavoriteButtonState(button, favoriteIdSet.has(Number(button.dataset.productId)));
    });
};

const favoriteButtonFromClick = (target) => {
    const button = target.closest('[data-favorite-button]');

    if (button) {
        return button;
    }

    return target.closest('[data-favorite-div]')?.querySelector('[data-favorite-button]') ?? null;
};

const synchronizeGuestFavorites = async () => {
    const syncUrl = document.body.dataset.favoritesSyncUrl;
    const favoriteIds = favoriteIdsFromStorage();

    if (document.body.dataset.authenticated !== 'true' || !syncUrl || favoriteIds.length === 0) {
        return;
    }

    try {
        const response = await fetch(syncUrl, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({product_ids: favoriteIds}),
        });

        if (!response.ok) {
            return;
        }

        const data = await response.json();

        localStorage.removeItem(FAVORITES_STORAGE_KEY);
        updateFavoriteButtons(data.favorite_ids ?? []);
        updateFavoriteCount((data.favorite_ids ?? []).length);
    } catch {

    }
};

const initializeFavoriteButtons = () => {
    if (document.body.dataset.authenticated === 'true') {
        document.querySelectorAll('[data-favorite-button]').forEach((button) => {
            setFavoriteButtonState(button, button.dataset.isFavorite === 'true');
        });
    } else {
        updateFavoriteButtons(favoriteIdsFromStorage());
    }

    synchronizeGuestFavorites();
};

document.addEventListener('click', async (event) => {
    const button = favoriteButtonFromClick(event.target);

    if (!button || button.disabled) {
        return;
    }

    event.preventDefault();

    const productId = Number(button.dataset.productId);

    if (document.body.dataset.authenticated !== 'true') {
        const favoriteIds = favoriteIdsFromStorage();
        const isFavorite = favoriteIds.includes(productId);
        const updatedFavoriteIds = isFavorite
            ? favoriteIds.filter((favoriteId) => favoriteId !== productId)
            : [...favoriteIds, productId];
        const willBeFavorite = !isFavorite;

        storeFavoriteIds(updatedFavoriteIds);
        updateFavoriteButtons(updatedFavoriteIds);
        showFavoriteFeedback(button, willBeFavorite);

        return;
    }

    button.disabled = true;

    try {
        const wasFavorite = button.dataset.isFavorite === 'true';
        const response = await fetch(button.dataset.toggleUrl, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
        });

        if (response.ok) {
            const data = await response.json();

            document
                .querySelectorAll(`[data-favorite-button][data-product-id="${productId}"]`)
                .forEach((favoriteButton) => setFavoriteButtonState(favoriteButton, data.is_favorite));
            if (data.is_favorite !== wasFavorite) {
                adjustFavoriteCount(data.is_favorite ? 1 : -1);
            }
            showFavoriteFeedback(button, data.is_favorite);
        }
    } catch {

    } finally {
        button.disabled = false;
    }
});

document.addEventListener('DOMContentLoaded', initializeFavoriteButtons);
document.addEventListener('favorite-removed', () => adjustFavoriteCount(-1));
document.addEventListener('livewire:navigated', initializeFavoriteButtons);
