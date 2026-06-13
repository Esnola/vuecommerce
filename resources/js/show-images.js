document.addEventListener('DOMContentLoaded', () => {
    const mainImage = document.getElementById('mainImage');
    const thumbs = document.querySelectorAll('[data-thumbs]');

    /*
    const firstImage = mainImage.src
    const imgsThumbs = [...thumbs].map(thumb => thumb.src);

            const rebuild = () => {
                thumbs.forEach((thumb, index) => {
                    thumb.src = imgsThumbs[index]
                })
            }
            mainImage.addEventListener('mouseover', () => {
                mainImage.src = firstImage;
                rebuild();
            })
       */

    thumbs.forEach(image => {
        image.addEventListener('mouseover', () => {
            //  const actualImage = mainImage.src
            mainImage.src = image.src;
            // image.src = actualImage;
        })
    })


})
