//<![CDATA[
window.addEventListener('load', function() {
    // The number of unique, embedded instances
    var nodeCount = anselnodes.length;

    // Holds any lightbox json
    var lightboxData = [];

    // Iterate over each embedded instance and create the DOM elements.
    for (var n = 0; n < nodeCount; n++) {

        // j is the textual name of the container, used as a key
        var j = anselnodes[n];

        // Do we have any lightbox data?
        if (typeof anseljson[j]['lightbox'] != 'undefined') {
            lightboxData = lightboxData.concat(anseljson[j]['lightbox']);
        }

        // Top level DOM node for this embedded instannce
        var mainNode = document.getElementById(j);

        // Used if we have requested the optional paging feature
        if (anseljson[j]['perpage']) {
            var pagecount = anseljson[j]['perpage'];
        } else {
            var pagecount = anseljson[j]['data'].length;
        }

        // For each image in this instance, create the DOM structure
        for (var i = 0; i < pagecount; i++) {
            // Need a nested function and closures to force new scope
            (function() {
                var jx = j;
                var ix = i;
                var imgContainer = document.createElement('span');
                imgContainer.className = 'anselGalleryWidget';
                if (!anseljson[jx]['hideLinks']) {
                    if (anseljson[jx]['linkToGallery']) {
                        var idx = 6;
                    } else {
                        var idx = 5;
                    }
                    var imgLink = document.createElement('a');
                    imgLink.href = anseljson[jx]['data'][ix][idx];
                    imgLink.title = anseljson[jx]['data'][ix][2];
                    imgContainer.appendChild(imgLink);
                   var lb_data = {image: anseljson[jx]['data'][ix][3]};
                   var img = document.createElement('img');
                   img.src = anseljson[jx]['data'][ix][0];
                   imgLink.appendChild(img);
                   // Attach the lightbox action if we have lightbox data
                   if (typeof anseljson[j]['lightbox'] != 'undefined') {
                       imgLink.addEventListener('click', function(e) {ansel_lb.start(lb_data.image); e.preventDefault();});
                   }
                } else {
                    var img = document.createElement('img');
                    img.src = anseljson[jx]['data'][ix][0];
                    imgContainer.appendChild(img);
                    // Attach the lightbox action if we have lightbox data
                    if (typeof anseljson[j]['lightbox'] != 'undefined') {
                        imgLink.addEventListener('click', function(e) {ansel_lb.start(lb_data.image); e.preventDefault();});
                    }
                }

                mainNode.appendChild(imgContainer);
            })();
        }

        if (anseljson[j]['perpage'] > 0) {
            (function() {
                var jx = j;

                var nextLink = document.createElement('a');
                nextLink.href = '#';
                nextLink.title = 'Next Image';
                nextLink.className = 'anselNext';
                nextLink.style.cssText = 'text-decoration:none;width:40%;float:right;';
                nextLink.textContent = '>>';
                var arg1 = {node: jx, page: 1};
                nextLink.addEventListener('click', function(e) {displayPage(e, arg1)});

                var prevLink = document.createElement('a');
                prevLink.href = '#';
                prevLink.title = 'Previous Image';
                prevLink.className = 'anselPrev';
                prevLink.style.cssText = 'text-decoration:none;width:40%;float:right;';
                prevLink.textContent = '<<';
                var arg2 = {node: jx, page: -1};
                prevLink.addEventListener('click', function(e) {displayPage(e, arg2)});
                document.getElementById(jx).appendChild(nextLink);
                document.getElementById(jx).appendChild(prevLink);

            })();
        }
    }
    if (lightboxData.length) {
        lbOptions['gallery_json'] = lightboxData;
        ansel_lb = new Lightbox(lbOptions);
    }
 });

/**
 * Display the images from the requested page for the requested node.
 *
 * @param string $node   The DOM id of the embedded widget.
 * @param integer $page  The requested page number.
 */
function displayPage(event, args) {
    var node = args.node;
    var page = args.page;
    var perpage = anseljson[node]['perpage'];
    var imgcount = anseljson[node]['data'].length;
    var pages = Math.ceil(imgcount / perpage) - 1;
    var oldPage = anseljson[node]['page'];

    page = oldPage + page;

    /* Rollover? */
    if (page > pages) {
        page = 0;
    }
    if (page < 0) {
        page = pages;
    }

    var mainNode = document.getElementById(node);
    mainNode.innerHTML = '';
    var start = page * perpage;
    var end = Math.min(imgcount - 1, start + perpage - 1);
    for (var i = start; i <= end; i++) {
        var imgContainer = document.createElement('span');
        imgContainer.className = 'anselGalleryWidget';
        mainNode.appendChild(imgContainer);
        var imgLink = document.createElement('a');
        imgLink.href = anseljson[node]['data'][i][5];
        imgLink.alt = anseljson[node]['data'][i][2];
        imgLink.title = anseljson[node]['data'][i][2];
        imgContainer.appendChild(imgLink);
        var img = document.createElement('img');
        img.src = anseljson[node]['data'][i][0];
        imgLink.appendChild(img);
    }

     var nextLink = document.createElement('a');
     nextLink.href = '';
     nextLink.title = 'Next Image';
     nextLink.style.cssText = 'text-decoration:none;width:40%;float:right;';
     nextLink.textContent = '>>';

     var args1 = {node: node, page: ++oldPage};
     nextLink.addEventListener('click', function(e) {displayPage(e, args1);});

     var prevLink = document.createElement('a');
     prevLink.href = '';
     prevLink.title = 'Previous Image';
     prevLink.style.cssText = 'text-decoration:none;width:40%;float:right;';
     prevLink.textContent = '<<';

     var args2 = {node: node, page: --oldPage};
     prevLink.addEventListener('click', function(e) {displayPage(e, args2);});

     mainNode.appendChild(nextLink);
     mainNode.appendChild(prevLink);

     anseljson[node]['page'] = page;
     event.preventDefault();
}
//]
