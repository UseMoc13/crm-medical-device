<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
<style>
html,body,#map{height:100%;width:100%;margin:0;padding:0}
body{overflow:hidden;background:#eef2f6;font-family:Arial,sans-serif}
#map{position:relative;overflow:hidden}
#map .leaflet-pane img,#map .leaflet-tile{max-width:none!important;max-height:none!important}
#map .leaflet-tile{margin:0!important;padding:0!important;border:0!important;box-shadow:none!important}
.map-error{position:absolute;inset:0;display:grid;place-items:center;padding:22px;text-align:center;color:#475569;font-size:13px}
</style>
</head><body><div id="map"></div>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
(function () {
    var channel = 'crm-delivery-map';
    var notify = function (type, payload) { parent.postMessage(Object.assign({ channel: channel, type: type }, payload || {}), '*'); };
    if (!window.L) {
        document.getElementById('map').innerHTML = '<div class="map-error">The map library could not load. You can enter the delivery address manually.</div>';
        notify('feedback', { text: 'Unable to load the map library. You can type the address manually.' });
        notify('ready');
        return;
    }
    var map = L.map('map', { zoomControl: true }).setView([-6.2, 106.82], 11);
    var failedTileCount = 0;
    var tiles = L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        minZoom: 2, maxZoom: 19, attribution: '&copy; OpenStreetMap contributors',
        crossOrigin: true
    });
    tiles.on('tileerror', function () {
        failedTileCount++;
        if (failedTileCount === 3) {
            notify('feedback', { text: 'Some map tiles failed to download. Check browser Network requests to tile.openstreetmap.org (403, 429, blocked, or network errors).' });
        }
    });
    tiles.on('load', function () { failedTileCount = 0; });
    tiles.addTo(map);
    function refreshMap() {
        requestAnimationFrame(function () {
            requestAnimationFrame(function () { map.invalidateSize({ pan: false, animate: false }); });
        });
    }
    if (typeof ResizeObserver !== 'undefined') {
        var lastWidth = 0, lastHeight = 0;
        var observer = new ResizeObserver(function (entries) {
            var rect = entries[0].contentRect;
            var w = Math.round(rect.width), h = Math.round(rect.height);
            if (w > 0 && h > 0 && (w !== lastWidth || h !== lastHeight)) {
                lastWidth = w; lastHeight = h; refreshMap();
            }
        });
        observer.observe(document.getElementById('map'));
    }
    var marker = null;
    var userMarker = null;
    var accuracyCircle = null;
    var sequence = 0;
    var lastRequest = 0;
    function pick(lat, lng, label) {
        if (!marker) marker = L.marker([lat, lng]).addTo(map);
        else marker.setLatLng([lat, lng]);
        notify('selection', { lat: lat, lng: lng, label: label });
    }
    async function lookup(url) {
        var wait = Math.max(0, 1100 - (Date.now() - lastRequest));
        if (wait) await new Promise(function (resolve) { setTimeout(resolve, wait); });
        lastRequest = Date.now();
        var response = await fetch(url, { headers: { Accept: 'application/json' } });
        if (!response.ok) throw new Error('Lookup failed');
        return response.json();
    }
    map.on('click', async function (event) {
        var lat = event.latlng.lat, lng = event.latlng.lng, seq = ++sequence;
        pick(lat, lng, lat.toFixed(6) + ', ' + lng.toFixed(6));
        notify('feedback', { text: 'Looking up the selected location...' });
        try {
            var result = await lookup('https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=' + encodeURIComponent(lat) + '&lon=' + encodeURIComponent(lng) + '&accept-language=id');
            if (seq !== sequence) return;
            pick(lat, lng, result.display_name || lat.toFixed(6) + ', ' + lng.toFixed(6));
            notify('feedback', { text: 'Address found. Confirm before using it.' });
        } catch (error) {
            if (seq === sequence) notify('feedback', { text: 'Address lookup unavailable. You may still use coordinates or type an address manually.' });
        }
    });
    window.addEventListener('message', async function (event) {
        if (event.source !== parent) return;
        var message = event.data;
        if (!message || message.channel !== channel) return;
        if (message.type === 'resize') {
            refreshMap();
        }
        if (message.type === 'my-location' && Number.isFinite(message.lat) && Number.isFinite(message.lng)) {
            var here = [message.lat, message.lng];
            if (!userMarker) {
                userMarker = L.circleMarker(here, {radius: 9, color: '#ffffff', weight: 3, fillColor: '#2563eb', fillOpacity: 1}).addTo(map);
                userMarker.bindPopup('Your current location');
            } else {
                userMarker.setLatLng(here);
            }
            var accuracy = Math.max(0, Math.min(Number(message.accuracy) || 0, 10000));
            if (accuracyCircle) map.removeLayer(accuracyCircle);
            if (accuracy > 0) accuracyCircle = L.circle(here, {radius: accuracy, color: '#2563eb', weight: 1, fillColor: '#60a5fa', fillOpacity: 0.12, interactive: false}).addTo(map);
            map.setView(here, 16);
            userMarker.openPopup();
            refreshMap();
        }
        if (message.type === 'search' && typeof message.query === 'string') {
            var query = message.query.trim(), seq = ++sequence;
            if (query.length < 3) return;
            notify('feedback', { text: 'Searching address...' });
            try {
                var items = await lookup('https://nominatim.openstreetmap.org/search?format=jsonv2&limit=1&accept-language=id&q=' + encodeURIComponent(query));
                if (seq !== sequence) return;
                if (!items.length) { notify('feedback', { text: 'Address not found. Try a more specific location.' }); return; }
                var item = items[0], lat = Number(item.lat), lng = Number(item.lon);
                map.setView([lat, lng], 16);
                pick(lat, lng, item.display_name);
                notify('feedback', { text: 'Location found. Select Use This Address.' });
            } catch (error) { if (seq === sequence) notify('feedback', { text: 'Address search unavailable. Please type the address manually.' }); }
        }
    });
    notify('ready');
    setTimeout(refreshMap, 100);
})();
</script></body></html>
