/**
 */

var AnselSlugCheck = {

    // Set by calling code: text

    checkSlug: function()
    {
        var slug = document.getElementById('gallery_slug').value;

        // Empty slugs are always allowed.
        if (slug.length && slug != this.text) {
            HordeCore.doAction('checkSlug', {
                slug: slug
            }, {
                callback: this.checkSlugCallback.bind(this)
            });
        } else {
            this.checkSlugCallback(true);
        }
    },

    checkSlugCallback: function(r)
    {
        var slugFlag = document.getElementById('slug_flag'),
            submitBtn = document.getElementById('gallery_submit');

        if (r) {
            slugFlag.classList.remove('problem');
            slugFlag.classList.add('success');
            submitBtn.disabled = false;
            // In case we try various slugs
            this.text = slug;
        } else {
            slugFlag.classList.remove('success');
            slugFlag.classList.add('problem');
            submitBtn.disabled = true;
        }
    },

    onDomLoad: function()
    {
        document.getElementById('gallery_slug').addEventListener('change', this.checkSlug.bind(this));
    }

};

document.addEventListener('DOMContentLoaded', AnselSlugCheck.onDomLoad.bind(AnselSlugCheck));
