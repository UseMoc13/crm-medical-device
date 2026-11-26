@php
    $editing = $branch->exists;
    $statusValue = strtolower((string) old('status', $branch->status ?: 'active'));
@endphp

<div class="card branch-form-card">
    <div class="card-head branch-form-card-head">
        <div>
            <h3>{{ $editing ? 'Edit Branch Information' : 'Branch Information' }}</h3>
            <p>Configure branch identity, contact details, and operational status.</p>
        </div>
        <span class="branch-required-note"><span>*</span> Required fields</span>
    </div>
    <div class="card-body branch-form-card-body">
        <form action="{{ $editing ? route('branches.update', $branch) : route('branches.store') }}"
            method="POST" id="branchEditorForm">
            @csrf
            @if($editing) @method('PUT') @endif

            <div class="branch-editor-layout">
                <div class="branch-editor-main">
                    <section class="branch-form-section">
                        <div class="branch-section-heading">
                            <h4>Branch Identity</h4>
                            <p>Identify this branch so users can recognize the correct office.</p>
                        </div>
                        <div class="branch-identity-fields">
                            <div class="branch-field">
                                <label for="branch_code">Branch Code <span class="branch-required">*</span></label>
                                <input id="branch_code" name="branch_code" type="text" maxlength="30"
                                    pattern="[A-Za-z0-9][A-Za-z0-9_-]*" required autocomplete="off"
                                    placeholder="e.g. JKT-HQ" value="{{ old('branch_code', $branch->branch_code) }}"
                                    class="branch-control @error('branch_code') is-invalid @enderror">
                                <small>Unique code using letters, numbers, underscore or hyphen.</small>
                                @error('branch_code')<span class="branch-error">{{ $message }}</span>@enderror
                            </div>
                            <div class="branch-field">
                                <label for="branch_name">Branch Name <span class="branch-required">*</span></label>
                                <input id="branch_name" name="branch_name" type="text" maxlength="100" required
                                    placeholder="e.g. Jakarta Head Office"
                                    value="{{ old('branch_name', $branch->branch_name) }}"
                                    class="branch-control @error('branch_name') is-invalid @enderror">
                                @error('branch_name')<span class="branch-error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </section>
                    <section class="branch-form-section">
                        <div class="branch-section-heading">
                            <h4>Contact Information</h4>
                            <p>Provide details used for branch coordination.</p>
                        </div>
                        <div class="branch-identity-fields">
                            <div class="branch-field">
                                <label for="phone">Phone Number</label>
                                <input id="phone" name="phone" type="text" maxlength="30" autocomplete="tel"
                                    placeholder="e.g. 021-1234567" value="{{ old('phone', $branch->phone) }}"
                                    class="branch-control @error('phone') is-invalid @enderror">
                                @error('phone')<span class="branch-error">{{ $message }}</span>@enderror
                            </div>
                            <div class="branch-field">
                                <label for="address">Branch Address</label>
                                <textarea id="address" name="address" rows="4" maxlength="5000"
                                    placeholder="Enter branch address" class="branch-control @error('address') is-invalid @enderror">{{ old('address', $branch->address) }}</textarea>
                                @error('address')<span class="branch-error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </section>
                    <section class="branch-form-section">
                        <div class="branch-section-heading">
                            <h4>Operational Settings</h4>
                            <p>Control whether this branch can be used for new assignments.</p>
                        </div>
                        <div class="branch-field">
                            <label for="status">Branch Status <span class="branch-required">*</span></label>
                            <select id="status" name="status" required class="branch-control @error('status') is-invalid @enderror">
                                <option value="active" @selected($statusValue === 'active')>Active</option>
                                <option value="inactive" @selected($statusValue === 'inactive')>Inactive</option>
                            </select>
                            <small>Existing user and team assignments remain linked when a branch becomes inactive.</small>
                            @error('status')<span class="branch-error">{{ $message }}</span>@enderror
                        </div>
                    </section>
                    <div class="branch-form-footer">
                        <a href="{{ $editing ? route('branches.show', $branch) : route('branches.index') }}" class="btn">Cancel</a>
                        <button type="submit" class="btn primary">{{ $editing ? 'Save Changes' : 'Create Branch' }}</button>
                    </div>
                </div>

                <aside class="branch-editor-preview" aria-label="Live branch preview">
                    <div class="branch-eyebrow">BRANCH PREVIEW</div>
                    <div class="branch-preview-identity">
                        <span class="branch-avatar" id="branchPreviewInitial" aria-hidden="true">{{ strtoupper(substr((string) old('branch_name', $branch->branch_name ?: 'B'), 0, 1)) }}</span>
                        <div class="branch-preview-primary">
                            <h3 id="branchPreviewName">{{ old('branch_name', $branch->branch_name) ?: 'New Branch' }}</h3>
                            <span id="branchPreviewCode">{{ old('branch_code', $branch->branch_code) ?: 'No Code Assigned' }}</span>
                        </div>
                    </div>
                    <div class="branch-preview-separator"></div>
                    <div class="branch-preview-row"><span>Operational Status</span><strong id="branchPreviewStatus">{{ ucfirst($statusValue) }}</strong></div>
                    <div class="branch-preview-row"><span>Phone Number</span><strong id="branchPreviewPhone">{{ old('phone', $branch->phone) ?: 'Not provided' }}</strong></div>
                    <div class="branch-preview-separator"></div>
                    <div class="branch-preview-specification">
                        <div class="branch-preview-heading">Address</div>
                        <p id="branchPreviewAddress">{{ old('address', $branch->address) ?: 'No address provided.' }}</p>
                    </div>
                    <div class="branch-preview-note">
                        <strong>{{ $editing ? 'Existing Assignments' : 'New Branch' }}</strong>
                        <p>{{ $editing ? 'Editing keeps the Branch ID and existing User/Team links.' : 'After creation, this branch can be selected in the Users and Teams forms.' }}</p>
                    </div>
                </aside>
            </div>
        </form>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const mappings = [
        ['branch_name', 'branchPreviewName', 'New Branch'],
        ['branch_code', 'branchPreviewCode', 'No Code Assigned'],
        ['phone', 'branchPreviewPhone', 'Not provided'],
        ['address', 'branchPreviewAddress', 'No address provided.'],
    ];
    for (const [inputId, previewId, fallback] of mappings) {
        const input = document.getElementById(inputId);
        const preview = document.getElementById(previewId);
        input?.addEventListener('input', () => {
            preview.textContent = input.value.trim() || fallback;
            if (inputId === 'branch_name') {
                document.getElementById('branchPreviewInitial').textContent = input.value.trim().charAt(0).toUpperCase() || 'B';
            }
        });
    }
    const status = document.getElementById('status');
    status?.addEventListener('change', () => {
        document.getElementById('branchPreviewStatus').textContent = status.selectedOptions[0].text;
    });
});
</script>
