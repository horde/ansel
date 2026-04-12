function showFace(id)
{
    document.getElementById('facediv' + id).classList.add('shown');
    document.getElementById('facethumb' + id).style.border = '1px solid red';
    document.getElementById('facedivname' + id).style.display = 'inline';
}
function hideFace(id)
{
    document.getElementById('facediv' + id).classList.remove('shown');
    document.getElementById('facethumb' + id).style.border = '1px solid black';
    document.getElementById('facedivname' + id).style.display = 'none';
}
document.addEventListener('DOMContentLoaded', function() {
    var photo = document.getElementById('ansel-photodiv');
    photo.addEventListener('load', function() {
        var faces = document.getElementById('faces-on-image');
        if (faces) {
            var photoRect = photo.getBoundingClientRect();
            Array.from(faces.children).forEach(function(element) {
                element.style.left = photoRect.left + 'px';
                element.style.top = photoRect.top + 'px';
            });
        }
    });
});
