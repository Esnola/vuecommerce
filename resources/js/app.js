import '@tailwindplus/elements';

document.addEventListener('DOMContentLoaded', () => {
    const mainImage = document.getElementById('mainImage');
    const otherImages = document.querySelectorAll('[data-thumbs]');

    otherImages.forEach(image => {
        image.addEventListener('mouseover', () => {
            mainImage.src = image.src;
        })
    })
})
