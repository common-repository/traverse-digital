function traverseInitMap() {
    // get all maps on page
    var pageMaps = document.getElementsByClassName('traverse_map');

    // loop through maps (won't work in older browsers)
    [].forEach.call(pageMaps, function (mapElem) {
        try {
            // collect markers
            var markers = JSON.parse(mapElem.getAttribute('data-markers'));
        } catch (e) {
            // empty if JSON fails
            var markers = [];
        }
        if (0 < markers.length) {
            // Create a map object and specify the DOM element for display.
            var map = new google.maps.Map(mapElem, {
                    center: markers[0],
                    scrollwheel: false,
                    zoom: 16
                }),
            // stylized map
                styleArray = [
                    // mute colors
                    {
                        featureType: "all",
                        stylers: [
                            {saturation: -80}
                        ]
                    },
                    // remove businesses
                    {
                        featureType: "poi.business",
                        elementType: "labels",
                        stylers: [
                            {visibility: "off"}
                        ]
                    }
                ],
                bounds = new google.maps.LatLngBounds(),

                marker = [];

            // set up markers and bounds
            markers.forEach(function (loc) {
                marker.push(new google.maps.Marker({
                    map: map,
                    position: loc
                }));
                var bound = new google.maps.LatLng(loc.lat, loc.lng);
                bounds.extend(bound);
            });

            // only fit to bounds if there's more than one
            if (1 < markers.length) {
                map.fitBounds(bounds);
            }

            // set style options
            map.setOptions({styles: styleArray});
        } else {
            // hide if no map found
            mapElem.style.display = 'none';
        }
    });
}
