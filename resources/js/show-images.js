const initializeProductImages = () => {
    const mainImage = document.getElementById('mainImage');
    const zoomContainer = document.getElementById('mainImageZoom');
    const thumbs = document.querySelectorAll('[data-thumbs]');

    if (! mainImage) {
        return;
    }

    thumbs.forEach((image) => {
        image.addEventListener('mouseover', () => {
            mainImage.src = image.src;
        });
    });

    if (mainImage && zoomContainer) {
        const zoomScale = 2;

        zoomContainer.addEventListener('mouseenter', () => {
            mainImage.style.transform = `scale(${zoomScale})`;
        });

        zoomContainer.addEventListener('mousemove', (event) => {
            const rect = zoomContainer.getBoundingClientRect();
            const x = ((event.clientX - rect.left) / rect.width) * 100;
            const y = ((event.clientY - rect.top) / rect.height) * 100;
            mainImage.style.transformOrigin = `${x}% ${y}%`;
        });

        zoomContainer.addEventListener('mouseleave', () => {
            mainImage.style.transform = 'scale(1)';
            mainImage.style.transformOrigin = 'center center';
        });
    }
};

document.addEventListener('DOMContentLoaded', initializeProductImages);
document.addEventListener('livewire:navigated', initializeProductImages);
