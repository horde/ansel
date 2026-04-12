AnselImageView = {
    urls: {},
    arrowHandler: function(e)
    {
        if (e.altKey || e.shiftKey || e.ctrlKey) {
            return;
        }

        switch (e.target.tagName) {
        case 'INPUT':
        case 'SELECT':
        case 'TEXTAREA':
            return;
        }
        switch (e.key) {
        case 'ArrowLeft':
            var prev = document.getElementById('PrevLink');
            if (prev) {
                document.location.href = prev.href;
            }
            break;

        case 'ArrowRight':
            var next = document.getElementById('NextLink');
            if (next) {
                document.location.href = next.href;
            }
            break;
        }
    },

    onload: function()
    {
        var photo = document.getElementById('ansel-photodiv');
        photo.addEventListener('load', function() {
            photo.style.opacity = '1';
            photo.style.transition = 'opacity 0.5s';
            document.querySelectorAll('.imgloading').forEach(function(n) { n.style.visibility = 'hidden'; });
            var caption = document.getElementById('anselcaption');
            caption.style.opacity = '1';
            caption.style.transition = 'opacity 0.5s';

            var nextImg = new Image();
            var prvImg = new Image();
            nextImg.src = AnselImageView.nextImgSrc;
            prvImg.src = AnselImageView.prevImgSrc;
        });

        // Fade out then load new image
        photo.style.transition = 'opacity 0.5s';
        photo.style.opacity = '0';
        photo.addEventListener('transitionend', function handler() {
            photo.removeEventListener('transitionend', handler);
            photo.src = AnselImageView.urls['imgsrc'];
        });

        // Arrow keys for navigation
        document.addEventListener('keydown', AnselImageView.arrowHandler);
    }
};

document.addEventListener('DOMContentLoaded', AnselImageView.onload);
