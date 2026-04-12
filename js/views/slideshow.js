AnselSlideShowView = {

    onload: function()
    {
        document.getElementById('PrevLink').addEventListener('click', SlideController.prev);
        document.getElementById('NextLink').addEventListener('click', SlideController.next);
        document.getElementById('ssPause').addEventListener('click', SlideController.pause);
        document.getElementById('ssPlay').addEventListener('click', SlideController.play);
    }
};
document.addEventListener('DOMContentLoaded', AnselSlideShowView.onload);
