/**
 * Geotagging widget
 *
 * Copyright 2009-2017 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (GPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/gpl.html.
 *
 * @author Michael J. Rubinsky <mrubinsk@horde.org>
 */
function AnselGeoTagWidget(imgs, opts)
{
     var o = {
        smallMap: 'ansel_map_small',
        mainMap:  'ansel_map',
        geocoder: 'None',
        calculateMaxZoom: true,
        deleteGeotagCallback: this.deleteLocation.bind(this),
        defaultBaseLayer: false
    };
    this._images = imgs;
    this.opts = Object.assign(o, opts || {});
}

AnselGeoTagWidget.prototype = {
    _bigMap: null,
    _smallMap: null,
    _images: null,
    locationId: 'ansel_locationtext',
    coordId: 'ansel_latlng',
    relocateId: 'ansel_relocate',
    deleteId: 'ansel_deleteGeotag',
    opts: null,
    _iLayer: null,

    setLocation: function(img, lat, lng)
    {
        fetch(this.opts.updateEndpoint, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                action: 'geotag',
                img: img,
                lat: lat,
                lng: lng
            })
        })
        .then(function(response) { return response.json(); })
        .then(function(data) {
             if (data.response == 1) {
                var w = document.createElement('div');
                var mapDiv = document.createElement('div');
                mapDiv.id = 'ansel_map';
                w.appendChild(mapDiv);
                var ag = document.createElement('div');
                ag.className = 'ansel_geolocation';
                var locDiv = document.createElement('div');
                locDiv.id = 'ansel_locationtext';
                ag.appendChild(locDiv);
                var latlngDiv = document.createElement('div');
                latlngDiv.id = 'ansel_latlng';
                ag.appendChild(latlngDiv);
                var relocDiv = document.createElement('div');
                relocDiv.id = 'ansel_relocate';
                ag.appendChild(relocDiv);
                var delDiv = document.createElement('div');
                delDiv.id = 'ansel_deleteGeotag';
                ag.appendChild(delDiv);
                w.appendChild(ag);
                var smallMapDiv = document.createElement('div');
                smallMapDiv.id = 'ansel_map_small';
                w.appendChild(smallMapDiv);
                document.getElementById('ansel_geo_widget').innerHTML = '';
                document.getElementById('ansel_geo_widget').appendChild(w);
                this._images.unshift({
                    image_id: img,
                    image_latitude: lat,
                    image_longitude: lng,
                    image_location: '',
                    markerOnly: true
                });
                this.doMap();
             }
         }.bind(this));
    },

    deleteLocation: function(iid)
    {
        fetch(this.opts.updateEndpoint, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                action: 'untag',
                img: iid
            })
        })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.response == 1) {
                document.getElementById('ansel_geo_widget').innerHTML = data.message;
            }
        });
    },

    updateBaseLayer: function(l)
    {
        fetch(this.opts.layerUpdateEndpoint, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                pref: this.opts.layerUpdatePref,
                value: l.layer.name
            })
        });
    },

    doMap: function()
    {
        var self = this;

        // Create map and geocoder objects
        this._bigMap = AnselMap.initMainMap('ansel_map', {
            'onHover': function(e) {
                switch (e.type) {
                case 'featurehighlighted':
                    if (self.opts.viewType == 'Gallery') {
                        document.querySelector('#imagetile_' + e.feature.attributes.image_id + ' img').classList.toggle('image-tile-highlight'); // eslint-disable-line horde/no-prototype-methods -- native classList.toggle()
                    }
                    break;
                case 'featureunhighlighted':
                    if (self.opts.viewType == 'Gallery') {
                        document.querySelector('#imagetile_' + e.feature.attributes.image_id + ' img').classList.toggle('image-tile-highlight'); // eslint-disable-line horde/no-prototype-methods -- native classList.toggle()
                    }
                }
                return true;
            },

            'onClick': function(f) {
                if (f.object.name == self.opts.markerLayerTitle) {
                   self._bigMap.setCenter(f.feature.getLonLat());
                   self._bigMap.zoomToFit();
                   return false;
                }
                var uri = f.feature.attributes.image_link;
                location.href = uri;
            },
            'onBaseLayerChange': this.updateBaseLayer.bind(this),
            'imageLayer': (this.opts.viewType == 'Image') ? true : false,
            'imageLayerText': this.opts.imageLayerTitle,
            'markerLayerText': (this.opts.viewType == 'Image') ? this.opts.markerLayerTitle : this.opts.imageLayerTitle,
            'defaultBaseLayer': this.opts.defaultBaseLayer
        });
        this._smallMap = AnselMap.initMiniMap('ansel_map_small', {});
        this.geocoder = new HordeMap.Geocoder[this.opts.geocoder](this._bigMap.map, 'ansel_map');

        // Place the image markers
        var centerImage;
        this._images.forEach(function(img) {
            if (img.markerOnly) {
                // Only here in ImageView and for the current image
                AnselMap.placeMapMarker(
                    'ansel_map_small',
                    {
                        'lat': img.image_latitude,
                        'lon': img.image_longitude
                    },
                    { 'center': true, 'zoom': 1 }
                );
                (function() {
                    var p = img;
                    var f = m;
                    self.getLocation(p, m);
                })();
                centerImage = img;
                return;
            }
            var m = AnselMap.placeMapMarker(
                'ansel_map',
                {
                    'lat': img.image_latitude,
                    'lon': img.image_longitude
                },
                {
                    'img': (!img.markerOnly) ? img.icon : Ansel.conf.markeruri,
                    'background': (!img.markerOnly) ? Ansel.conf.pixeluri + '?c=ffffff' : Ansel.conf.shadowuri,
                    'image_id': img.image_id,
                    'markerOnly': (img.markerOnly) ? 'markerOnly' : 'noMarkerOnly',
                    'center': false,
                    'image_link': img.link
                }
            );

            // Watch for hover on imagetiles too, need closures
            if (self.opts.viewType == 'Gallery') {
                AnselMap.placeMapMarker(
                    'ansel_map_small',
                    {
                        'lat': img.image_latitude,
                        'lon': img.image_longitude
                    }
                );
                (function() {
                    var f = m;
                    document.querySelector('#imagetile_' + img.image_id + ' img').addEventListener(
                        'mouseover',
                        function(e) {
                            AnselMap.selectMarker('ansel_map', f);
                        }
                    );
                    document.querySelector('#imagetile_' + img.image_id + ' img').addEventListener(
                        'mouseout',
                        function(e) {
                            AnselMap.unselectMarker('ansel_map', f);
                        }
                    );
                })();
            }
        });
        if (centerImage) {
            AnselMap.placeMapMarker(
                'ansel_map',
                {
                    'lat': centerImage.image_latitude,
                    'lon': centerImage.image_longitude
                },
                {
                    'img': (!centerImage.markerOnly) ? centerImage.icon : Ansel.conf.markeruri,
                    'background': (!centerImage.markerOnly) ? Ansel.conf.pixeluri + '?c=ffffff' : Ansel.conf.shadowuri,
                    'image_id': centerImage.image_id,
                    'markerOnly': 'markerOnly',
                    'center': true,
                    'zoom': 10,
                    'image_link': centerImage.link
                }
            );
        } else {
            this._bigMap.zoomToFit();
        }
        // Attempt to make a good guess as to where to center the mini-map
        if (this.opts.viewType == 'Gallery') {
            this._smallMap.setCenter({
                'lat': this._images[0].image_latitude,
                'lon': 0
            }, 0);
        }
    },

    /**
     * p = image data
     * m = marker
     */
    getLocation: function(p, m)
    {
        if (p.image_location.length > 0) {
            // Have cached reverse geocode results
            var r = [ { address: p.image_location, lat: p.image_latitude, lon: p.image_longitude, precision: 1 } ];
            this.getLocationCallback(p, false, m, r);
        } else {
            this.geocoder.reverseGeocode(
                { lat: p.image_latitude, lon: p.image_longitude },
                this.getLocationCallback.bind(this, p, true, m),
                this.onError.bind(this));
        }
    },

    /**
     * callback for reverse geocode call
     *
     * @param object i   The image hash
     * @param boolean u  Update the image location in the backend
     * @param object m   Marker
     * @param object r   The AJAX response
     */
    getLocationCallback: function(i, u, m, r)
    {
        var self = this;
        // Update image view links
        if (i.markerOnly) {
            if (r.length) {
                var found = false;
                r.forEach(function(result) {
                    if (found) {
                        return;
                    }
                    if (result.precision == 1) {
                        if (self.locationId) {
                            document.getElementById(self.locationId).textContent = result.address;
                        }
                        if (self.coordId) {
                            document.getElementById(self.coordId).textContent = AnselMap.point2Deg({ lat: result.lat, lon: result.lon });
                        }
                        if (self.relocateId) {
                            var relocateEl = document.getElementById(self.relocateId);
                            relocateEl.innerHTML = '';
                            relocateEl.appendChild(self._getRelocateLink(i.image_id));
                        }
                        if (self.deleteId) {
                            var deleteEl = document.getElementById(self.deleteId);
                            deleteEl.innerHTML = '';
                            deleteEl.appendChild(self._getDeleteLink(i.image_id));
                        }
                        // Save the results?
                        if (u) {
                            fetch(self.opts.updateEndpoint, {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                                body: new URLSearchParams({
                                    action: 'location',
                                    location: result.address,
                                    img: i.image_id
                                })
                            });
                        }
                        found = true;
                   }
               });
           }
        }
    },

    onError: function(r)
    {
    },

    _getRelocateLink: function(iid)
    {
        if (this.opts.hasEdit) {
            var a = document.createElement('a');
            a.href = this.opts.relocateUrl + '?image=' + iid;
            a.textContent = this.opts.relocateText;

            var self = this;
            a.addEventListener('click', function(e) {
                HordePopup.popup({
                    url: self.opts.relocateUrl,
                    params: { 'image': iid },
                    width: 720,
                    height: 520
                });
                e.preventDefault();
            });

            return a;
        } else {
            return document.createTextNode('');
        }
    },

    _getDeleteLink: function(iid)
    {
        if (this.opts.hasEdit) {
            var x = document.createElement('a');
            x.href = this.opts.relocateUrl + '?image=' + iid;
            x.textContent = this.opts.deleteGeotagText;

            var self = this;
            x.addEventListener('click', function(e) {
                self.opts.deleteGeotagCallback(iid);
                e.preventDefault();
            });

            return x;
        } else {
            return document.createTextNode('');
        }
    }
};
