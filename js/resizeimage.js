function resizeImage(v)
{
    document.getElementById('edit_image').width = v;
    document.getElementById('width').value = document.getElementById('edit_image').width;
    document.getElementById('height').value = document.getElementById('edit_image').height;

}

function resetImage()
{
    Ansel.slider.setValue(Ansel.image_geometry['height']); // eslint-disable-line horde/no-prototype-methods -- Scriptaculous Slider.setValue()
}
