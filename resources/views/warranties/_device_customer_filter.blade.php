<style>
.device-customer-filter-hint{display:block;margin-top:7px;color:#64748b;font-size:11px;line-height:1.5}
</style>
<script>
(() => {
  const customer = document.getElementById('customer_id');
  const serial = document.getElementById('serial_number_id');
  const form = document.querySelector('.warranty-form');
  if (!form || !customer || !serial || !form.contains(customer) || !form.contains(serial)) return;

  // Keep the original options in memory; rebuild the dropdown to reliably remove
  // nonmatching choices across browsers (including native select popups).
  const devices = Array.from(serial.options).filter(option => option.value).map(option => ({
    id: option.value,
    customerId: option.dataset.customerId || '',
    node: option.cloneNode(true),
  }));
  const initialSerial = serial.value;
  const hint = document.createElement('small');
  hint.className = 'device-customer-filter-hint';
  hint.setAttribute('role', 'status');
  hint.setAttribute('aria-live', 'polite');
  serial.insertAdjacentElement('afterend', hint);

  function notifyPreview() {
    // Existing Live Preview and Warranty Coverage listeners use 'change'.
    serial.dispatchEvent(new Event('change', { bubbles: true }));
  }

  function updateOptions(keepId = serial.value) {
    const owner = customer.value;
    const available = owner ? devices.filter(device => device.customerId === owner) : devices;
    const placeholder = new Option(owner ? 'Select a device for this customer' : 'Select a device', '');
    serial.replaceChildren(placeholder, ...available.map(device => device.node.cloneNode(true)));
    const retained = available.some(device => device.id === keepId);
    serial.value = retained ? keepId : '';
    serial.disabled = available.length === 0;

    if (owner && available.length === 0) {
      hint.textContent = 'No registered devices are assigned to this customer.';
    } else if (owner) {
      hint.textContent = available.length + ' registered device(s) available for the selected customer.';
    } else {
      hint.textContent = 'Choose a customer to filter devices, or select a serial number to fill its customer automatically.';
    }
    if (!retained && keepId) notifyPreview();
  }

  customer.addEventListener('change', () => {
    updateOptions();
    notifyPreview();
  });

  serial.addEventListener('change', () => {
    if (!serial.value) return;
    const chosen = devices.find(device => device.id === serial.value);
    if (!chosen || !chosen.customerId) return;
    if (customer.value !== chosen.customerId) {
      customer.value = chosen.customerId;
      customer.dispatchEvent(new Event('change', { bubbles: true }));
    } else {
      // Warranty's existing preview script may have selected the owner already.
      updateOptions(serial.value);
    }
  });

  // Preserve old form selections after server-side validation errors.
  if (!customer.value && initialSerial) {
    const initial = devices.find(device => device.id === initialSerial);
    if (initial?.customerId) customer.value = initial.customerId;
  }
  updateOptions(initialSerial);
})();
</script>
