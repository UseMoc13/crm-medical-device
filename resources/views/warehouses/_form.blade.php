
<div class="warehouse-form-grid">
    <div class="form-group">
        <label for="warehouse_code">Kode Gudang <span>*</span></label>
        <input
            type="text"
            id="warehouse_code"
            name="warehouse_code"
            value="{{ old('warehouse_code', $warehouse->warehouse_code) }}"
            maxlength="30"
            placeholder="Contoh: WH-JKT-001"
            required
        >
        @error('warehouse_code')
            <small class="field-error">{{ $message }}</small>
        @enderror
    </div>

    <div class="form-group">
        <label for="warehouse_name">Nama Gudang <span>*</span></label>
        <input
            type="text"
            id="warehouse_name"
            name="warehouse_name"
            value="{{ old('warehouse_name', $warehouse->warehouse_name) }}"
            maxlength="100"
            placeholder="Masukkan nama gudang"
            required
        >
        @error('warehouse_name')
            <small class="field-error">{{ $message }}</small>
        @enderror
    </div>

    <div class="form-group full-width">
        <label for="address">Alamat Gudang</label>
        <textarea
            id="address"
            name="address"
            rows="4"
            placeholder="Masukkan alamat lengkap gudang"
        >{{ old('address', $warehouse->address) }}</textarea>
        @error('address')
            <small class="field-error">{{ $message }}</small>
        @enderror
    </div>

    <div class="form-group">
        <label for="status">Status <span>*</span></label>
        <select id="status" name="status" required>
            <option value="Active"
                @selected(old('status', $warehouse->status ?: 'Active') === 'Active')>
                Active
            </option>
            <option value="Inactive"
                @selected(old('status', $warehouse->status) === 'Inactive')>
                Inactive
            </option>
        </select>
        @error('status')
            <small class="field-error">{{ $message }}</small>
        @enderror
    </div>
</div>

<div class="form-actions">
    <a href="{{ route('warehouses.index') }}" class="btn btn-secondary">
        Batal
    </a>

    <button type="submit" class="btn btn-primary">
        {{ $warehouse->exists ? 'Simpan Perubahan' : 'Simpan Gudang' }}
    </button>
</div>

<style>
    .warehouse-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 22px;
    }

    .warehouse-form-grid .form-group {
        display: flex;
        flex-direction: column;
        gap: 8px;
        min-width: 0;
    }

    .warehouse-form-grid .full-width {
        grid-column: 1 / -1;
    }

    .warehouse-form-grid label {
        font-size: 13px;
        font-weight: 600;
        color: #263653;
    }

    .warehouse-form-grid label span {
        color: #dc3545;
    }

    .warehouse-form-grid input,
    .warehouse-form-grid select,
    .warehouse-form-grid textarea {
        width: 100%;
        box-sizing: border-box;
        padding: 12px 14px;
        border: 1px solid #dce2ec;
        border-radius: 9px;
        background: #fff;
        color: #263653;
        font: inherit;
        font-size: 14px;
        outline: none;
        transition: border-color .2s, box-shadow .2s;
    }

    .warehouse-form-grid input:focus,
    .warehouse-form-grid select:focus,
    .warehouse-form-grid textarea:focus {
        border-color: #2ba7a0;
        box-shadow: 0 0 0 3px rgba(43, 167, 160, .12);
    }

    .warehouse-form-grid textarea {
        resize: vertical;
    }

    .field-error {
        color: #dc3545;
        font-size: 12px;
    }

    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 28px;
        padding-top: 20px;
        border-top: 1px solid #edf0f5;
    }

    .form-actions .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 11px 18px;
        border: 0;
        border-radius: 8px;
        text-decoration: none;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
    }

    .form-actions .btn-primary {
        background: #223a70;
        color: #fff;
    }

    .form-actions .btn-secondary {
        background: #edf0f5;
        color: #263653;
    }

    @media (max-width: 640px) {
        .warehouse-form-grid {
            grid-template-columns: 1fr;
        }

        .warehouse-form-grid .full-width {
            grid-column: auto;
        }
    }
</style>