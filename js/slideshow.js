/**
 * You can only have one SlideController on a page.
 */
var SlideController = {

    photos: null,
    photoId: 0,

    slide: null,

    interval: null,
    intervalSeconds: 5,

    /**
     * CSS border size x 2
     */
    borderSize: 0,

    /**
     * So we can update the links
     */
    baseUrl: null,
    galleryId: 0,
    playing: false,
    tempImage: new Image(),

    /**
     * Initialization.
     */
    initialize: function(photos, start, baseUrl, galleryId)
    {
        SlideController.photoId = start || 0;
        SlideController.baseUrl = baseUrl;
        SlideController.galleryId = galleryId;

        window.addEventListener('load', function() {
            SlideController.photos = photos;
            SlideController.photo = new Slide(SlideController.photoId);
            SlideController.tempImage.addEventListener('load', function() {
                SlideController.photo.initSwap(SlideController.tempImage.width, SlideController.tempImage.height);
            });
            document.getElementById(SlideController.photo.photo).addEventListener('load', function() {
                SlideController.photo.showPhoto();
            });

            SlideController.photo.initSwap();
            SlideController.play();

        });
    },

    /**
     * Play the slideshow.
     */
    play: function()
    {
        document.getElementById('ssPlay').hidden = true;
        document.getElementById('ssPause').hidden = false;
        // This sets the first interval for the currently displayed image.
        if (SlideController.interval) {
            clearTimeout(SlideController.interval);
        }
        SlideController.interval = setTimeout(SlideController.next, SlideController.intervalSeconds * 1000);
        SlideController.playing = true;
    },

    /**
     * Leaving this in here, but currently we just redirect back to the Image view
     */
    pause: function()
    {
        document.getElementById('ssPause').hidden = true;
        document.getElementById('ssPlay').hidden = false;
        if (SlideController.interval) {
            clearTimeout(SlideController.interval);
        }
        SlideController.playing = false;
    },

    /**
     * Move to previous image.
     */
    prev: function()
    {
        SlideController.photo.prevPhoto();
    },

    /**
     * Move to next image.
     */
    next: function()
    {
        SlideController.photo.nextPhoto();
    }

};

// -----------------------------------------------------------------------------------
//
// This page coded by Scott Upton
// http://www.uptonic.com | http://www.couloir.org
//
// This work is licensed under a Creative Commons License
// Attribution-ShareAlike 2.0
// http://creativecommons.org/licenses/by-sa/2.0/
//
// Associated APIs copyright their respective owners
//
// -----------------------------------------------------------------------------------
// --- version date: 11/28/05 --------------------------------------------------------
//
// Various changes for properly updating image links, image comments, get rid
// of redundant functions that prototype can take care of etc...
// added 4/07 by Michael Rubinsky <mrubinsk@horde.org>
function Slide(photoId)
{
    this.photoId = photoId;
    this.photo = 'anselphoto';
    this.captionBox = 'anselcaptioncontainer';
    this.caption = 'anselcaption';
}

Slide.prototype = {
    setNewPhotoParams: function()
    {
        // Set source of new image.
        document.getElementById(this.photo).src = SlideController.photos[SlideController.photoId][0];

        // Add caption from gallery array.
        document.getElementById(this.caption).textContent = SlideController.photos[SlideController.photoId][2];

        document.title = document.title.replace(SlideController.photos[this.photoId][1],
                                                SlideController.photos[SlideController.photoId][1]);
    },

    updateLinks: function()
    {
        var params = '?gallery=' + SlideController.galleryId + '&image=' + SlideController.photos[SlideController.photoId][3] + '&page=' + SlideController.photos[SlideController.photoId][4];
        document.getElementById('PhotoName').textContent = SlideController.photos[SlideController.photoId][1];
        var propLink = document.getElementById('image_properties_link');
        if (propLink) {
            propLink.href = SlideController.baseUrl + '/image.php' + params + '&actionID=modify';
            propLink.onclick = function(e){ SlideController.pause();HordePopup.popup({ url: this.href }); e.preventDefault(); };
        }
        var editLink = document.getElementById('image_edit_link');
        if (editLink) {
            editLink.href = SlideController.baseUrl + '/image.php' + params + '&actionID=editimage';
        }
        var ecardLink = document.getElementById('image_ecard_link');
        if (ecardLink) {
          ecardLink.href = SlideController.baseUrl + '/img/ecard.php?image=' + SlideController.photos[SlideController.photoId][3] + '&gallery=' + SlideController.galleryId;
          ecardLink.onclick = function(e){ SlideController.pause();HordePopup.popup({ url: this.href }); e.preventDefault(); };
        }
        var deleteLink = document.getElementById('image_delete_link');
        if (deleteLink) {
            var deleteAction = function() { SlideController.pause(); if (!window.confirm("Do you want to permanently delete " +  SlideController.photos[SlideController.photoId][1])) { return false; } return true;};
            deleteLink.href = SlideController.baseUrl + '/image.php' + params + '&actionID=delete';
            deleteLink.onclick = function(e) { return deleteAction(); };
        }
        var dlLink = document.getElementById('image_download_link');
        dlLink.href = SlideController.baseUrl + '/img/download.php?image=' + SlideController.photos[SlideController.photoId][3];
        dlLink.onclick = function(e) { SlideController.pause(); };
    },

    showPhoto: function()
    {
        var photoEl = document.getElementById(this.photo),
            captionBoxEl = document.getElementById(this.captionBox),
            self = this;

        photoEl.style.transition = 'opacity 1s';
        photoEl.style.opacity = '1';
        photoEl.addEventListener('transitionend', function handler() {
            photoEl.removeEventListener('transitionend', handler);
            captionBoxEl.hidden = false;
            self.updateLinks();
        });

        if (SlideController.playing) {
            if (SlideController.interval) {
                clearTimeout(SlideController.interval);
            }
            SlideController.interval = setTimeout(SlideController.next, SlideController.intervalSeconds * 1000);
        }
    },

    nextPhoto: function()
    {
        // Figure out which photo is next.
        (SlideController.photoId == (SlideController.photos.length - 1)) ? SlideController.photoId = 0 : ++SlideController.photoId;
        // Make sure the photo is loaded locally before we fade the current image.
        SlideController.tempImage.src = SlideController.photos[SlideController.photoId][0];

    },

    prevPhoto: function()
    {
        // Figure out which photo is previous.
        (SlideController.photoId == 0) ? SlideController.photoId = SlideController.photos.length - 1 : --SlideController.photoId;
        SlideController.tempImage.src = SlideController.photos[SlideController.photoId][0];
    },

    initSwap: function(w, h)
    {
        var captionBoxEl = document.getElementById(this.captionBox),
            photoEl = document.getElementById(this.photo),
            self = this;

        // Begin by hiding main elements.
        captionBoxEl.style.transition = 'opacity 0.5s';
        captionBoxEl.style.opacity = '0';
        captionBoxEl.addEventListener('transitionend', function handler() {
            captionBoxEl.removeEventListener('transitionend', handler);
            captionBoxEl.hidden = true;
        });

        photoEl.style.transition = 'opacity 1s';
        photoEl.style.opacity = '0';
        photoEl.addEventListener('transitionend', function handler() {
            photoEl.removeEventListener('transitionend', handler);
            if (w) { self.fixSize(w, h); }
            SlideController.photo.setNewPhotoParams();
        });

        // Update the current photo id.
        this.photoId = SlideController.photoId;
    },

    fixSize: function(w, h)
    {
        document.getElementById(this.photo).width = w;
        document.getElementById(this.photo).height = h;
        document.getElementById(this.captionBox).style.width = w + 'px';
    }
};

// Arrow keys for navigation
document.addEventListener('keydown', function(e) {
    if (e.altKey || e.shiftKey || e.ctrlKey) {
        return;
    }

    switch (e.key) {
    case 'ArrowLeft':
        SlideController.prev();
        break;

    case 'ArrowRight':
        SlideController.next(); // eslint-disable-line horde/no-prototype-methods -- SlideController.next()
        break;
    }
});
