<aside class="delivery-preview-panel" aria-label="Delivery Preview">
    <div class="delivery-preview-eyebrow">LIVE DELIVERY PREVIEW</div>
    <div class="delivery-preview-title"><span class="delivery-preview-icon">↗</span>
        <div>
            <h3>Delivery Summary</h3><small>Changes are reflected as you edit.</small>
        </div>
    </div>
    <div class="delivery-preview-line"><span>Delivery Number</span><strong id="deliveryPreviewNumber">{{ $previewNumber }}</strong></div>
    <div class="delivery-preview-line"><span>Sales Order</span><strong id="deliveryPreviewOrder">{{ $previewOrder }}</strong></div>
    <div class="delivery-preview-line"><span>Delivery Date</span><strong id="deliveryPreviewDate">—</strong></div>
    <div class="delivery-preview-line"><span>Status</span><span class="delivery-preview-badge" id="deliveryPreviewStatus">{{ $previewStatus }}</span></div>
    <div class="delivery-preview-section"><span>DESTINATION</span>
        <div id="deliveryPreviewAddress" class="delivery-preview-address">No delivery address selected.</div>
    </div>
    <div class="delivery-preview-section"><span>NOTES</span>
        <div id="deliveryPreviewNotes" class="delivery-preview-address">No additional notes.</div>
    </div>
    <p class="delivery-preview-notice">Delivery status is updated separately from the detail page. Stock and serial numbers are not yet automatically adjusted.</p>
</aside>