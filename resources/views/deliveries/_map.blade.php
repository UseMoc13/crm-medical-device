<div class="delivery-previous-addresses" style="margin-top: 10px; margin-bottom: 10px;">
    <label for="deliveryPreviousAddress" style="display:block;margin-bottom:6px;">Use Previous Delivery Address</label>
    <select id="deliveryPreviousAddress" class="form-control" aria-label="Use a previously saved delivery address">
        <option value="">-- Select a previous address (optional) --</option>
        @foreach (($previousAddresses ?? collect()) as $previousAddress)
            <option value="{{ $previousAddress }}">{{ \Illuminate\Support\Str::limit($previousAddress, 110) }}</option>
        @endforeach
    </select>
    <small class="form-hint">Select an address used by another Delivery, or type a new one above.</small>
</div>
<div class="delivery-address-controls"><button type="button" class="btn delivery-map-button" id="deliveryOpenMap">⌖ Find Address on Map</button><small>Search or pin a location; you can still type the address manually.</small></div>
<div class="delivery-map-modal" id="deliveryMapModal" hidden role="dialog" aria-modal="true" aria-labelledby="deliveryMapTitle">
    <div class="delivery-map-dialog">
        <div class="delivery-map-top"><div><h3 id="deliveryMapTitle">Find Delivery Address</h3><p>Search a place or click on the map to choose a destination.</p></div><button type="button" class="delivery-map-close" id="deliveryMapClose" aria-label="Close map">×</button></div>
        <div class="delivery-map-search" role="search"><input id="deliveryMapQuery" type="search" placeholder="Search place, street, city..." autocomplete="off" aria-label="Search map address"><button type="button" class="btn primary" id="deliveryMapSearchButton">Search</button><button type="button" class="btn" id="deliveryMapMyLocation" title="Center map on my current location">⌖ My Location</button></div>
        @php($deliveryMapFrameHtml = view('deliveries._map_frame')->render())
        <iframe id="deliveryMapFrame" class="delivery-map-frame" title="Interactive delivery address map" sandbox="allow-scripts allow-same-origin" referrerpolicy="strict-origin-when-cross-origin" data-map-srcdoc="{{ $deliveryMapFrameHtml }}"></iframe>
        <p id="deliveryMapFeedback" class="delivery-map-feedback" role="status">Loading map...</p>
        <div class="delivery-map-selected" id="deliveryMapSelected">No location selected.</div>
        <div class="delivery-map-footer"><span>Map data © OpenStreetMap contributors</span><div><button type="button" class="btn" id="deliveryMapCancel">Cancel</button><button type="button" class="btn primary" id="deliveryMapUse" disabled>Use This Address</button></div></div>
    </div>
</div>
