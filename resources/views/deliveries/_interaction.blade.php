<script>
(() => {
    const $ = id => document.getElementById(id);
    const order = $('sales_order_id'), date = $('delivery_date'), address = $('delivery_address'), notes = $('notes');
    const update = () => {
        const selectedOrder = order?.tagName === 'SELECT' ? order.options[order.selectedIndex]?.text : order?.value;
        $('deliveryPreviewOrder').textContent = selectedOrder?.replace(/^-- Select Sales Order --$/, '—') || '—';
        $('deliveryPreviewDate').textContent = date?.value ? new Date(date.value + 'T12:00:00').toLocaleDateString('en-GB', {day:'2-digit',month:'short',year:'numeric'}) : '—';
        $('deliveryPreviewAddress').textContent = address?.value.trim() || 'No delivery address selected.';
        $('deliveryPreviewNotes').textContent = notes?.value.trim() || 'No additional notes.';
    };
    [order,date,address,notes].forEach(el => el?.addEventListener('input',update)); update();
    const previous = $('deliveryPreviousAddress');
    previous?.addEventListener('change', () => {
        if (!previous.value || !address) return;
        address.value = previous.value;
        address.dispatchEvent(new Event('input', { bubbles: true }));
    });
    address?.addEventListener('input', () => {
        if (previous && previous.value && previous.value !== address.value) previous.value = '';
    });
    const modal = $('deliveryMapModal');
    const frame = $('deliveryMapFrame');
    if (!modal || !frame) return;
    document.body.appendChild(modal);
    let selected = null;
    let frameReady = false;
    let frameStarted = false;
    const feedback = message => { $('deliveryMapFeedback').textContent = message; };
    const send = (type, payload = {}) => {
        if (frameReady && frame.contentWindow) frame.contentWindow.postMessage({ channel: 'crm-delivery-map', type, ...payload }, '*');
    };
    const close = () => {
        modal.hidden = true;
        document.body.classList.remove('delivery-map-open');
    };
    const open = () => {
        modal.hidden = false;
        document.body.classList.add('delivery-map-open');
        if (!frameStarted) {
            frameStarted = true;
            const documentHtml = frame.getAttribute('data-map-srcdoc');
            if (documentHtml) {
                frame.removeAttribute('data-map-srcdoc');
                requestAnimationFrame(() => {
                    frame.srcdoc = documentHtml;
                });
            } else {
                feedback('Map content was not found. Please refresh the page.');
            }
        } else if (frameReady) {
            requestAnimationFrame(() => requestAnimationFrame(() => send('resize')));
        } else {
            feedback('Loading map...');
        }
    };
    window.addEventListener('message', event => {
        if (event.source !== frame.contentWindow) return;
        const message = event.data;
        if (!message || message.channel !== 'crm-delivery-map') return;
        if (message.type === 'ready') {
            frameReady = true;
            feedback('Search for a place or click on the map.');
            if (!modal.hidden) requestAnimationFrame(() => requestAnimationFrame(() => send('resize')));
        } else if (message.type === 'selection') {
            if (typeof message.label !== 'string' || !Number.isFinite(message.lat) || !Number.isFinite(message.lng)) return;
            selected = { lat: message.lat, lng: message.lng, label: message.label };
            $('deliveryMapSelected').textContent = selected.label;
            $('deliveryMapUse').disabled = false;
        } else if (message.type === 'feedback' && typeof message.text === 'string') {
            feedback(message.text);
        }
    });
    $('deliveryOpenMap')?.addEventListener('click', open);
    const searchAddress = () => {
        const query = $('deliveryMapQuery').value.trim();
        if (query.length < 3) { feedback('Enter at least three characters.'); return; }
        if (!frameReady) { feedback('The map is still loading. Please try again.'); return; }
        send('search', { query });
    };
    $('deliveryMapSearchButton')?.addEventListener('click', searchAddress);
    const myLocationButton = $('deliveryMapMyLocation');
    myLocationButton?.addEventListener('click', () => {
        if (!navigator.geolocation) {
            feedback('Your browser does not support location access.');
            return;
        }
        if (!frameReady) {
            feedback('Map is still loading. Please try again.');
            return;
        }
        myLocationButton.disabled = true;
        feedback('Requesting permission to access your current location...');
        navigator.geolocation.getCurrentPosition(
            position => {
                myLocationButton.disabled = false;
                send('my-location', {
                    lat: position.coords.latitude,
                    lng: position.coords.longitude,
                    accuracy: position.coords.accuracy
                });
                feedback('Your location is shown with a blue marker. Click the map to select a delivery destination.');
            },
            error => {
                myLocationButton.disabled = false;
                const messages = {
                    1: 'Location permission was denied. Allow location access in your browser settings.',
                    2: 'Your location is currently unavailable.',
                    3: 'Location request timed out. Please try again.'
                };
                feedback(messages[error.code] || 'Could not access your location.');
            },
            { enableHighAccuracy: true, timeout: 12000, maximumAge: 60000 }
        );
    });
    $('deliveryMapQuery')?.addEventListener('keydown', event => {
        if (event.key === 'Enter') { event.preventDefault(); searchAddress(); }
    });
    $('deliveryMapUse')?.addEventListener('click', () => {
        if (!selected || !address) return;
        address.value = selected.label;
        address.dispatchEvent(new Event('input', { bubbles: true }));
        close();
    });
    [$('deliveryMapClose'), $('deliveryMapCancel')].forEach(el => el?.addEventListener('click', close));
    modal.addEventListener('click', event => { if (event.target === modal) close(); });
    document.addEventListener('keydown', event => { if (event.key === 'Escape' && !modal.hidden) close(); });
})();
</script>
