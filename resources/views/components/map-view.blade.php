@php $mapId = 'map-' . uniqid(); @endphp

<div id="{{ $mapId }}" style="height: {{ $height }}; width: 100%;" class="rounded-2xl overflow-hidden border border-gray-100"></div>

<script>
(function () {
    function initMap() {
        var locations = @json($locations);
        var mapEl = document.getElementById('{{ $mapId }}');

        if (! mapEl || ! locations.length) return;

        var center = { lat: parseFloat(locations[0].latitude), lng: parseFloat(locations[0].longitude) };
        var map = new google.maps.Map(mapEl, {
            center: center,
            zoom: {{ $zoom }},
            styles: window.knuckleballMapStyle || [],
            disableDefaultUI: false,
            zoomControl: true,
            mapTypeControl: false,
            streetViewControl: false,
            fullscreenControl: true,
        });

        locations.forEach(function (loc) {
            if (! loc.latitude || ! loc.longitude) return;

            var marker = new google.maps.Marker({
                position: { lat: parseFloat(loc.latitude), lng: parseFloat(loc.longitude) },
                map: map,
                title: loc.name,
            });

            if (loc.popup) {
                var infoWindow = new google.maps.InfoWindow({ content: loc.popup });
                marker.addListener('click', function () {
                    infoWindow.open(map, marker);
                });
            }
        });

        // Auto-fit bounds when multiple locations
        if (locations.length > 1) {
            var bounds = new google.maps.LatLngBounds();
            locations.forEach(function (loc) {
                if (loc.latitude && loc.longitude) {
                    bounds.extend({ lat: parseFloat(loc.latitude), lng: parseFloat(loc.longitude) });
                }
            });
            map.fitBounds(bounds);
        }
    }

    (function poll() {
        if (typeof google !== 'undefined' && google.maps) {
            initMap();
        } else {
            setTimeout(poll, 150);
        }
    })();
})();
</script>
