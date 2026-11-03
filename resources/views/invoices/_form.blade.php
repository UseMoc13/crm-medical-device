{{--
    Form bersama Create & Edit Invoice.
    Variabel: $salesOrders, $salesOrderItems, $products
              Create: $invoiceNumber, $statuses, $defaultTaxRate
              Edit  : $invoice, $initialItems, $taxRate
--}}
@php
    $isEdit = isset($invoice) && $invoice;

    $currentTaxRate = old('tax_rate', $isEdit ? $taxRate : ($defaultTaxRate ?? 11));
    $currentDiscount = old('discount', $isEdit ? (float) ($invoice->discount ?? 0) : 0);

    $selectedSalesOrder = old('sales_order_id', request('sales_order_id'));
@endphp

<form
    method="POST"
    action="{{ $isEdit ? route('invoices.update', $invoice->invoice_id) : route('invoices.store') }}"
    id="invoiceForm"
>
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    <div class="inv-form-layout">

        <div class="inv-form-main">

            {{-- SALES ORDER --}}
            <div class="inv-section first">
                <h4>Invoice Source</h4>
                <p>
                    @if($isEdit)
                        The sales order of an existing invoice cannot be changed.
                    @else
                        Choose a sales order — its items are copied into the invoice automatically and remain editable.
                    @endif
                </p>
            </div>

            @if($isEdit)
                <div class="inv-two">
                    <div class="inv-field">
                        <label>Invoice Number</label>
                        <input type="text" value="{{ $invoice->invoice_number }}" readonly>
                    </div>

                    <div class="inv-field">
                        <label>Sales Order</label>
                        <input
                            type="text"
                            value="{{ $invoice->sales_order_number }}{{ $invoice->customer_name ? ' — ' . $invoice->customer_name : '' }}"
                            readonly
                        >
                    </div>
                </div>
            @else
                <div class="inv-field">
                    <label for="sales_order_id">
                        Sales Order <span class="required">*</span>
                    </label>

                    <select id="sales_order_id" name="sales_order_id" required>
                        <option value="">Select a sales order</option>

                        @foreach($salesOrders as $salesOrder)
                            <option
                                value="{{ $salesOrder->sales_order_id }}"
                                @selected($selectedSalesOrder == $salesOrder->sales_order_id)
                            >
                                {{ $salesOrder->sales_order_number }}
                                — {{ $salesOrder->customer_name ?? 'Unknown customer' }}
                                (Rp {{ number_format((float) $salesOrder->total_amount, 0, ',', '.') }})
                            </option>
                        @endforeach
                    </select>

                    @error('sales_order_id')
                        <span class="inv-error">{{ $message }}</span>
                    @enderror
                </div>
            @endif

            {{-- DATES --}}
            <div class="inv-section">
                <h4>Invoice Details</h4>
                <p>Invoice date, payment due date, and initial status.</p>
            </div>

            <div class="inv-two">
                <div class="inv-field">
                    <label for="invoice_date">
                        Invoice Date <span class="required">*</span>
                    </label>
                    <input
                        type="date"
                        id="invoice_date"
                        name="invoice_date"
                        value="{{ old('invoice_date', $isEdit ? \Illuminate\Support\Carbon::parse($invoice->invoice_date)->format('Y-m-d') : now()->format('Y-m-d')) }}"
                        required
                    >
                    @error('invoice_date')
                        <span class="inv-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="inv-field">
                    <label for="due_date">Due Date</label>
                    <input
                        type="date"
                        id="due_date"
                        name="due_date"
                        value="{{ old('due_date', $isEdit && $invoice->due_date ? \Illuminate\Support\Carbon::parse($invoice->due_date)->format('Y-m-d') : now()->addDays(30)->format('Y-m-d')) }}"
                    >
                    @error('due_date')
                        <span class="inv-error">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            @unless($isEdit)
                <div class="inv-field">
                    <label for="status">
                        Status <span class="required">*</span>
                    </label>
                    <select id="status" name="status" required>
                        @foreach($statuses as $statusOption)
                            <option value="{{ $statusOption }}" @selected(old('status', 'Draft') === $statusOption)>
                                {{ $statusOption }}
                            </option>
                        @endforeach
                    </select>
                    <span class="inv-hint">Mark the invoice as Paid or Cancelled later from its detail page.</span>
                    @error('status')
                        <span class="inv-error">{{ $message }}</span>
                    @enderror
                </div>
            @endunless

            {{-- ITEMS --}}
            <div class="inv-section">
                <h4>Invoice Items</h4>
                <p>Products being billed. Discount is a nominal amount per line.</p>
            </div>

            @error('items')
                <span class="inv-error" style="margin:-8px 0 12px">{{ $message }}</span>
            @enderror

            <div class="inv-items-wrap">
                <table class="inv-items">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Qty</th>
                            <th>Unit Price (Rp)</th>
                            <th>Discount (Rp)</th>
                            <th style="text-align:right">Subtotal</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="itemsBody"></tbody>
                </table>

                <div class="inv-items-empty" id="itemsEmpty">
                    No items yet. Select a sales order or add an item manually.
                </div>
            </div>

            <button type="button" class="inv-add-row" id="addItemBtn">+ Add Item</button>

            {{-- ADJUSTMENTS --}}
            <div class="inv-section">
                <h4>Adjustments & Notes</h4>
                <p>Invoice-level discount, tax rate, and additional notes.</p>
            </div>

            <div class="inv-two">
                <div class="inv-field">
                    <label for="discount">Invoice Discount (Rp)</label>
                    <input
                        type="number"
                        id="discount"
                        name="discount"
                        min="0"
                        step="0.01"
                        value="{{ $currentDiscount }}"
                    >
                    @error('discount')
                        <span class="inv-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="inv-field">
                    <label for="tax_rate">Tax Rate (%)</label>
                    <input
                        type="number"
                        id="tax_rate"
                        name="tax_rate"
                        min="0"
                        max="100"
                        step="0.01"
                        value="{{ $currentTaxRate }}"
                    >
                    <span class="inv-hint">Tax is calculated on subtotal minus invoice discount.</span>
                    @error('tax_rate')
                        <span class="inv-error">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="inv-field">
                <label for="notes">Notes</label>
                <textarea
                    id="notes"
                    name="notes"
                    rows="3"
                    placeholder="Payment instructions, bank account, or other notes..."
                >{{ old('notes', $isEdit ? $invoice->notes : '') }}</textarea>
                @error('notes')
                    <span class="inv-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="inv-form-actions">
                <a
                    href="{{ $isEdit ? route('invoices.show', $invoice->invoice_id) : route('invoices.index') }}"
                    class="btn"
                >
                    Cancel
                </a>

                <button type="submit" class="btn primary">
                    {{ $isEdit ? 'Save Changes' : 'Create Invoice' }}
                </button>
            </div>

        </div>

        {{-- LIVE TOTALS --}}
        <aside class="inv-panel">

            <div class="inv-panel-label">INVOICE SUMMARY</div>

            <div class="inv-panel-number">
                {{ $isEdit ? $invoice->invoice_number : $invoiceNumber }}
            </div>
            <div class="inv-panel-caption">
                {{ $isEdit ? 'Invoice Number' : 'Estimated invoice number' }}
            </div>

            <div class="inv-panel-divider"></div>

            <div class="inv-sum-row"><span>Items</span><strong id="sumCount">0</strong></div>
            <div class="inv-sum-row"><span>Subtotal</span><strong id="sumSubtotal">Rp 0</strong></div>
            <div class="inv-sum-row"><span>Discount</span><strong id="sumDiscount">- Rp 0</strong></div>
            <div class="inv-sum-row"><span>Tax (<span id="sumRate">0</span>%)</span><strong id="sumTax">Rp 0</strong></div>

            <div class="inv-sum-total">
                <span>Total</span>
                <strong id="sumTotal">Rp 0</strong>
            </div>

            <div class="inv-panel-note">
                <strong>Calculated on save</strong>
                <p>
                    Totals shown here are a preview. The server recalculates every
                    amount from the items when the invoice is saved.
                </p>
            </div>

        </aside>

    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const PRODUCTS = @json($products->keyBy('product_id'));
    const SALES_ORDERS = @json($salesOrders->keyBy('sales_order_id'));
    const SO_ITEMS = @json($salesOrderItems);
    const INITIAL_ITEMS = @json(old('items', $initialItems ?? []));
    const IS_EDIT = @json($isEdit);
    const DEFAULT_TAX = @json($defaultTaxRate ?? 11);

    const body = document.getElementById('itemsBody');
    const empty = document.getElementById('itemsEmpty');
    const addBtn = document.getElementById('addItemBtn');
    const salesOrder = document.getElementById('sales_order_id');
    const invoiceDate = document.getElementById('invoice_date');
    const dueDate = document.getElementById('due_date');
    const discountInput = document.getElementById('discount');
    const taxInput = document.getElementById('tax_rate');

    let rowIndex = 0;

    const rupiah = function (value) {
        return 'Rp ' + Math.round(value).toLocaleString('id-ID');
    };

    const esc = function (value) {
        return String(value ?? '').replace(/[&<>"']/g, function (ch) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[ch];
        });
    };

    function productOptions(selected) {
        let html = '<option value="">Select product</option>';

        Object.values(PRODUCTS).forEach(function (p) {
            const label = (p.product_code ? p.product_code + ' — ' : '') + p.product_name;
            html += '<option value="' + esc(p.product_id) + '"'
                + (p.product_id === selected ? ' selected' : '') + '>'
                + esc(label) + '</option>';
        });

        return html;
    }

    function addRow(item) {
        item = item || {};
        const i = rowIndex++;
        const tr = document.createElement('tr');

        tr.innerHTML =
            '<td class="c-product"><select name="items[' + i + '][product_id]" required>'
                + productOptions(item.product_id) + '</select></td>'
            + '<td class="c-qty"><input type="number" name="items[' + i + '][quantity]" min="1" step="1" value="' + esc(item.quantity ?? 1) + '" required></td>'
            + '<td class="c-price"><input type="number" name="items[' + i + '][unit_price]" min="0" step="0.01" value="' + esc(item.unit_price ?? '') + '" required></td>'
            + '<td class="c-disc"><input type="number" name="items[' + i + '][discount]" min="0" step="0.01" value="' + esc(item.discount ?? 0) + '"></td>'
            + '<td class="c-total">Rp 0</td>'
            + '<td class="c-remove"><button type="button" class="inv-remove" title="Remove item" aria-label="Remove item">×</button></td>';

        body.appendChild(tr);
        recalc();
    }

    function clearRows() {
        body.innerHTML = '';
    }

    function recalc() {
        let subtotal = 0;
        const rows = body.querySelectorAll('tr');

        rows.forEach(function (tr) {
            const qty = parseFloat(tr.querySelector('.c-qty input').value) || 0;
            const price = parseFloat(tr.querySelector('.c-price input').value) || 0;
            const disc = parseFloat(tr.querySelector('.c-disc input').value) || 0;
            const line = Math.max(0, qty * price - disc);

            tr.querySelector('.c-total').textContent = rupiah(line);
            subtotal += line;
        });

        const discount = Math.min(parseFloat(discountInput.value) || 0, subtotal);
        const rate = parseFloat(taxInput.value) || 0;
        const taxable = subtotal - discount;
        const tax = taxable * rate / 100;

        document.getElementById('sumCount').textContent = rows.length;
        document.getElementById('sumSubtotal').textContent = rupiah(subtotal);
        document.getElementById('sumDiscount').textContent = '- ' + rupiah(discount);
        document.getElementById('sumRate').textContent = rate;
        document.getElementById('sumTax').textContent = rupiah(tax);
        document.getElementById('sumTotal').textContent = rupiah(taxable + tax);

        empty.style.display = rows.length ? 'none' : 'block';
    }

    function loadFromSalesOrder(id) {
        const items = SO_ITEMS[id];
        const so = SALES_ORDERS[id];

        if (!items || !items.length) return;

        if (body.children.length &&
            !confirm('Replace the current items with the items from this sales order?')) {
            return;
        }

        clearRows();
        items.forEach(addRow);

        if (so && parseFloat(so.subtotal) > 0) {
            taxInput.value = (Math.round(parseFloat(so.tax || 0) / parseFloat(so.subtotal) * 10000) / 100);
        } else {
            taxInput.value = DEFAULT_TAX;
        }

        recalc();
    }

    // Product dipilih -> isi harga default bila masih kosong.
    body.addEventListener('change', function (e) {
        if (!e.target.matches('.c-product select')) return;

        const tr = e.target.closest('tr');
        const priceInput = tr.querySelector('.c-price input');
        const product = PRODUCTS[e.target.value];

        if (product && product.price !== null && !parseFloat(priceInput.value)) {
            priceInput.value = parseFloat(product.price);
        }

        recalc();
    });

    body.addEventListener('input', recalc);

    body.addEventListener('click', function (e) {
        if (!e.target.closest('.inv-remove')) return;

        e.target.closest('tr').remove();
        recalc();
    });

    addBtn.addEventListener('click', function () { addRow(); });

    [discountInput, taxInput].forEach(function (el) {
        el.addEventListener('input', recalc);
    });

    if (salesOrder) {
        salesOrder.addEventListener('change', function () {
            if (this.value) loadFromSalesOrder(this.value);
        });
    }

    // Isi due date otomatis 30 hari setelah tanggal invoice.
    invoiceDate.addEventListener('change', function () {
        if (!this.value) return;

        const d = new Date(this.value + 'T00:00:00');
        d.setDate(d.getDate() + 30);
        dueDate.value = d.toISOString().slice(0, 10);
    });

    // Inisialisasi.
    if (INITIAL_ITEMS && INITIAL_ITEMS.length) {
        INITIAL_ITEMS.forEach(addRow);
    } else if (!IS_EDIT && salesOrder && salesOrder.value) {
        loadFromSalesOrder(salesOrder.value);
    }

    recalc();
});
</script>