var AnselGalleryView = {
    selectAll: function ()
    {
        for (var i = 0; i < document.gallery.elements.length; ++i) {
            document.gallery.elements[i].checked = true;
        }
    },

    selectNone: function()
    {
        for (var i = 0; i < document.gallery.elements.length; ++i) {
            document.gallery.elements[i].checked = false;
        }
    },

    deleteSelected: function()
    {
        if (!AnselGalleryView.verifyImagesSelected()) {
            alert(Ansel.galleryview_strings.choose_images);
            return false;
        }
        if (confirm(Ansel.galleryview_strings.delete_conf)) {
            document.gallery.actionID.value = 'delete';
            document.gallery.submit();
        }
    },

    moveSelected: function()
    {
        if (document.gallery.new_gallery.selectedIndex == 0) {
            window.alert(Ansel.galleryview_strings.choose_gallery_move);
            return false;
        }
        if (!AnselGalleryView.verifyImagesSelected()) {
            alert(Ansel.galleryview_strings.choose_images);
            return false;
        }
        document.gallery.actionID.value = 'move';
        document.gallery.submit();
    },

    copySelected: function()
    {
        if (document.gallery.new_gallery.selectedIndex == 0) {
            alert(Ansel.galleryview_strings.choose_gallery_move);
            return false;
        }
        if (!AnselGalleryView.verifyImagesSelected()) {
            alert(Ansel.galleryview_strings.choose_images);
            return false;
        }
        document.gallery.actionID.value = 'copy';
        document.gallery.submit();
    },

    editDates: function()
    {
        var haveImages = false;
        var imageDateUrl = Ansel.galleryview_urls.image_date;

        for (var i = 0; i< document.forms['gallery'].elements.length; ++i) {
           if (document.forms['gallery'].elements[i].checked == true &&
               document.forms['gallery'].elements[i].type == 'checkbox') {
                haveImages = true;
                imageDateUrl = imageDateUrl + '&' + document.forms['gallery'].elements[i].name + '=true';
            }
        }
        if (!haveImages) {
            alert(Ansel.galleryview_strings.choose_images);
            return false;
        }
        HordePopup.popup({ url: imageDateUrl, width: 600, height: 250 });
    },

    downloadSelected: function()
    {
        if (AnselGalleryView.verifyImagesSelected()) {
            document.forms['gallery'].actionID.value = 'downloadzip';
            document.forms['gallery'].submit();
        } else {
            window.alert(Ansel.galleryview_strings.choose_images);
            return false;
        }
    },

    verifyImagesSelected: function()
    {
        var haveImages = false;

        for (var i = 0; i< document.gallery.elements.length; ++i) {
           if (document.gallery.elements[i].checked == true &&
               document.forms.gallery.elements[i].type == 'checkbox') {
                return true;
            }
        }

        return false;
    },

    onLoad: function()
    {
        document.getElementById('anselgallery_select_all').addEventListener('click', AnselGalleryView.selectAll);
        document.getElementById('anselgallery_select_none').addEventListener('click', AnselGalleryView.selectNone);
        var dl = document.getElementById('anselgallery_download');
        if (dl) {
            dl.addEventListener('click', AnselGalleryView.downloadSelected);
        }

        var ed = document.getElementById('anselgallery_editdates');
        if (ed) {
            ed.addEventListener('click', AnselGalleryView.editDates);
        }
        var cp = document.getElementById('anselgallery_copy');
        if (cp) {
            cp.addEventListener('click', AnselGalleryView.copySelected);
        }
        var mv = document.getElementById('anselgallery_move');
        if (mv) {
            mv.addEventListener('click', AnselGalleryView.moveSelected);
            document.getElementById('anselgallery_delete').addEventListener('click', AnselGalleryView.deleteSelected);
        }
    }
};

document.addEventListener('DOMContentLoaded', AnselGalleryView.onLoad);