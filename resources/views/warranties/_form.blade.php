<div class="warranty-form-grid">
 <div class="form-group"><label for="warranty_number">Warranty Number <span class="required-mark">*</span></label>
 @if($editing)
 <input id="warranty_number" name="warranty_number" class="form-control @error('warranty_number') is-invalid @enderror" required maxlength="50" value="{{ old('warranty_number', $warranty->warranty_number) }}">
 @else
 <div class="auto-number-field"><input id="warranty_number" class="form-control" readonly value="{{ $warrantyNumber }}"><span class="auto-number-badge">Auto</span></div><small class="form-hint">Generated automatically when saved.</small>
 @endif
 @error('warranty_number')<small class="field-error">{{ $message }}</small>@enderror</div>
 <div class="form-group"><label for="warranty_type">Warranty Type <span class="required-mark">*</span></label><select id="warranty_type" name="warranty_type" class="form-control @error('warranty_type') is-invalid @enderror" required><option value="">Select Warranty Type</option>
 @foreach(['Manufacturer'=>'Manufacturer Warranty','Extended'=>'Extended Warranty','Service'=>'Service Warranty'] as $value=>$label)<option value="{{ $value }}" @selected(old('warranty_type', $editing ? $warranty->warranty_type : '') === $value)>{{ $label }}</option>@endforeach
 </select>@error('warranty_type')<small class="field-error">{{ $message }}</small>@enderror</div>
 <div class="form-group"><label for="serial_number_id">Serial Number <span class="required-mark">*</span></label><select id="serial_number_id" name="serial_number_id" class="form-control @error('serial_number_id') is-invalid @enderror" required><option value="">Select Serial Number</option>
 @foreach($serialNumbers as $serial)<option value="{{ $serial->serial_number_id }}" data-customer-id="{{ $serial->customer_id }}" @selected(old('serial_number_id', $editing ? $warranty->serial_number_id : request('serial_number_id')) == $serial->serial_number_id)>{{ $serial->serial_number }} — {{ $serial->product_name ?? 'Medical Device' }}</option>@endforeach
 </select>@error('serial_number_id')<small class="field-error">{{ $message }}</small>@enderror</div>
 <div class="form-group"><label for="customer_id">Customer <span class="required-mark">*</span></label><select id="customer_id" name="customer_id" class="form-control @error('customer_id') is-invalid @enderror" required><option value="">Select Customer</option>
 @foreach($customers as $customer)<option value="{{ $customer->customer_id }}" @selected(old('customer_id', $editing ? $warranty->customer_id : request('customer_id')) == $customer->customer_id)>{{ $customer->customer_name }}</option>@endforeach
 </select><small class="form-hint">Automatically selected when the serial number has a customer.</small>@error('customer_id')<small class="field-error">{{ $message }}</small>@enderror</div>
 <div class="form-group"><label for="start_date">Start Date <span class="required-mark">*</span></label><input class="form-control @error('start_date') is-invalid @enderror" type="date" id="start_date" name="start_date" value="{{ old('start_date', $editing ? ($warranty->start_date ? \Illuminate\Support\Carbon::parse($warranty->start_date)->format('Y-m-d') : '') : '') }}" required>@error('start_date')<small class="field-error">{{ $message }}</small>@enderror</div>
 <div class="form-group"><label for="end_date">End Date <span class="required-mark">*</span></label><input class="form-control @error('end_date') is-invalid @enderror" type="date" id="end_date" name="end_date" value="{{ old('end_date', $editing ? ($warranty->end_date ? \Illuminate\Support\Carbon::parse($warranty->end_date)->format('Y-m-d') : '') : '') }}" required>@error('end_date')<small class="field-error">{{ $message }}</small>@enderror</div>
 <div class="form-group"><label for="status">Status <span class="required-mark">*</span></label><select id="status" name="status" class="form-control @error('status') is-invalid @enderror" required><option value="">Select Status</option>
 @foreach($statuses as $status)<option value="{{ $status }}" @selected(old('status', $editing ? $warranty->status : 'Active') == $status)>{{ $status }}</option>@endforeach
 </select>@error('status')<small class="field-error">{{ $message }}</small>@enderror</div>
 <div class="form-group full-width"><label for="terms">Warranty Terms and Conditions</label><textarea id="terms" name="terms" class="form-control @error('terms') is-invalid @enderror" rows="4" placeholder="Coverage, exclusions and additional warranty terms...">{{ old('terms', $editing ? $warranty->terms : '') }}</textarea>@error('terms')<small class="field-error">{{ $message }}</small>@enderror</div>
</div>
