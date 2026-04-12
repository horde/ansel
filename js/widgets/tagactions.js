/**
 */

var AnselTagActions = {

    // Set by calling script: gallery, image

    add: function()
    {
        var addtag = document.getElementById('addtag');
        if (addtag.value.trim() !== '') {
            HordeCore.doAction('addTag', {
                gallery: this.gallery,
                image: this.image,
                tags: addtag.value
            }, {
                callback: function(r) {
                    addtag.value = '';
                    AnselTagActions.updateTags(r);
                }
            });
        }

        return true;
    },

    remove: function(tagid)
    {
        HordeCore.doAction('removeTag', {
            gallery: this.gallery,
            image: this.image,
            tags: tagid
        }, {
            callback: function(r) {
                AnselTagActions.updateTags(r);
            }
        });

        return false;
    },

    // Since onsubmit is never called when submitting programatically we
    // can use this function to add tags when we press enter on the tag form.
    submitcheck: function()
    {
        return !this.add();
    },

    updateTags: function(r)
    {
        var tags = document.getElementById('tags'),
            oldUl = tags.querySelector('ul');
        if (oldUl) {
            oldUl.remove();
        }
        if (Object.keys(r).length === 0) {
            return;
        }
        var ul = document.createElement('ul');
        ul.className = 'horde-tags';
        var self = this;
        Object.keys(r).forEach(function(key) {
            var x = r[key];
            var a = document.createElement('a');
            a.href = x.link;
            a.innerHTML = x.tag_name + '&nbsp;';
            var l = document.createElement('li');
            l.appendChild(a);
            var img = document.createElement('img');
            img.src = self.remove_image;
            img.addEventListener('click', function() { return self.remove(x.tag_id); });
            l.appendChild(img);
            ul.appendChild(l);
        });
        tags.insertBefore(ul, tags.firstChild);
    }

};
