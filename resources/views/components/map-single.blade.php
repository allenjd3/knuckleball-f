@php $mapId = 'map-single-' . uniqid(); @endphp

<div class="rounded-2xl overflow-hidden border border-gray-100">
    <div id="{{ $mapId }}" style="height: {{ $height }}; width: 100%;"></div>
    <div class="p-3 bg-white flex items-center justify-between border-t border-gray-100">
        <p class="text-sm text-gray-600 font-medium truncate">{{ $name }}</p>
        <a href="{{ $directionsUrl }}"
           target="_blank"
           rel="noopener"
           class="shrink-0 ml-3 inline-flex items-center gap-1.5 px-4 py-1.5 text-sm font-semibold text-white rounded-full"
           style="background-color:#D93C3F;">
            <x-heroicon-o-map-pin class="size-4" />
            Get Directions
        </a>
    </div>
</div>

<script>
(function () {
    function initMap() {
        var mapEl = document.getElementById('{{ $mapId }}');
        if (! mapEl) return;

        var position = { lat: {{ $latitude }}, lng: {{ $longitude }} };
        var map = new google.maps.Map(mapEl, {
            center: position,
            zoom: 15,
            styles: window.knuckleballMapStyle || [],
            disableDefaultUI: false,
            zoomControl: true,
            mapTypeControl: false,
            streetViewControl: false,
            fullscreenControl: false,
        });

        new google.maps.Marker({ position: position, map: map, title: @json($name) });
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
