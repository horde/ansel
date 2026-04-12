document.addEventListener('DOMContentLoaded', function() {
    var wrapper = document.getElementById('horde-contentwrapper');
    if (wrapper) {
        var head = document.getElementById('horde-head');
        var sub = document.getElementById('horde-sub');
        wrapper.style.minHeight = document.documentElement.clientHeight - (head.offsetHeight + sub.offsetHeight) - 2 + 'px';
    }
});
