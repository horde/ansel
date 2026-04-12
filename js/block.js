document.addEventListener('DOMContentLoaded', function() {
    Ansel.previewImage = function(e, image_id) {
        document.getElementById('ansel_preview').style.left = e.clientX + 'px';
        document.getElementById('ansel_preview').style.top = e.clientY + 'px';
        fetch(Ansel.conf['BASE_URI'] + '/preview.php?image=' + encodeURIComponent(image_id))
            .then(function(response) { return response.text(); })
            .then(function(html) {
                document.getElementById('ansel_preview').innerHTML = html;
                document.getElementById('ansel_preview').hidden = false;
            });
    };

    var preview = document.createElement('div');
    preview.id = 'ansel_preview';
    document.body.appendChild(preview);
});
