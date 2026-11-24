@php
    $editing = $branch->exists;
    $statusValue = strtolower((string) old('status', $branch->status ?: 'active'));
@endphp
<div class="card branch-form-card">
    <div class="card-head">
        <div>
            <h3>Branch Information</h3>
            <p>Master information used by CRM users, teams, and organization assignments.</p>
        </div>
    </div>
    <div class="card-body">
        @if($errors->any())
            <div class="branch-validation" role="alert">
                <strong>Please review the highlighted fields.</strong>
                <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <form action="{{ $editing ? route('branches.update', $branch) : route('branches.store') }}"
              method="POST" id="branchEditorForm">
            @csrf
            @if($editing) @method('PUT') @endif

            <div class="branch-editor-layout">
                <div class="branch-editor-main">
                    <div class="branch-section-title">Branch Identity</div>
                    <div class="branch-field-grid">
                        <div class="branch-field">
                            <label for="branch_code">Branch Code <span class="branch-required">*</span></label>
                            <input id="branch_code" name="branch_code" type="text" maxlength="30"
                                pattern="[A-Za-z0-9][A-Za-z0-9_-]*" required autocomplete="off"
                                placeholder="e.g. JKT-HQ"
                                value="{{ old('branch_code', $branch->branch_code) }}"
                                class="@error('branch_code') is-invalid @enderror">
                            <small>Unique code; letters, numbers, _ and -.</small>
                            @error('branch_code')<span class="branch-error">{{ $message }}</span>@enderror
                        </div>
                        <div class="branch-field">
                            <label for="branch_name">Branch Name <span class="branch-required">*</span></label>
                            <input id="branch_name" name="branch_name" type="text" maxlength="100" required
                                placeholder="e.g. Jakarta Head Office"
                                value="{{ old('branch_name', $branch->branch_name) }}"
                                class="@error('branch_name') is-invalid @enderror">
                            @error('branch_name')<span class="branch-error">{{ $message }}</span>@enderror
                        </div>
                        <div class="branch-field">
                            <label for="phone">Phone Number</label>
                            <input id="phone" name="phone" type="text" maxlength="30"
                                autocomplete="tel" placeholder="e.g. 021-1234567"
                                value="{{ old('phone', $branch->phone) }}"
                                class="@error('phone') is-invalid @enderror">
                            @error('phone')<span class="branch-error">{{ $message }}</span>@enderror
                        </div>
                        <div class="branch-field">
                            <label for="status">Operational Status <span class="branch-required">*</span></label>
                            <select id="status" name="status" required class="@error('status') is-invalid @enderror">
                                <option value="active" @selected($statusValue === 'active')>Active</option>
                                <option value="inactive" @selected($statusValue === 'inactive')>Inactive</option>
                            </select>
                            <small>Inactive does not automatically remove existing assignments.</small>
                            @error('status')<span class="branch-error">{{ $message }}</span>@enderror
                        </div>
                        <div class="branch-field branch-field-full">
                            <label for="address">Branch Address</label>
                            <textarea id="address" name="address" rows="4" maxlength="5000"
                                placeholder="Enter branch location or mailing address"
                                class="@error('address') is-invalid @enderror">{{ old('address', $branch->address) }}</textarea>
                            @error('address')<span class="branch-error">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    <div class="branch-form-footer">
                        <a href="{{ $editing ? route('branches.show', $branch) : route('branches.index') }}" class="btn">Cancel</a>
                        <button type="submit" class="btn primary">{{ $editing ? 'Save Changes' : 'Create Branch' }}</button>
                    </div>
                </div>
                <aside class="branch-editor-preview" aria-label="Branch preview">
                    <div class="branch-eyebrow">BRANCH PREVIEW</div>
                    <div class="branch-avatar" aria-hidden="true">B</div>
                    <h3 id="branchPreviewName">{{ old('branch_name', $branch->branch_name) ?: 'New Branch' }}</h3>
                    <div class="branch-preview-code" id="branchPreviewCode">{{ old('branch_code', $branch->branch_code) ?: 'CODE NOT SET' }}</div>
                    <div class="branch-preview-separator"></div>
                    <div class="branch-preview-row"><span>Status</span><strong id="branchPreviewStatus">{{ ucfirst($statusValue) }}</strong></div>
                    <div class="branch-preview-row"><span>Phone</span><strong id="branchPreviewPhone">{{ old('phone', $branch->phone) ?: 'Not provided' }}</strong></div>
                    @if($editing)
                        <p class="branch-preview-note">Editing this branch preserves its ID and existing user/team links.</p>
                    @else
                        <p class="branch-preview-note">A new branch becomes available for assignment in the Users and Teams forms.</p>
                    @endif
                </aside>
            </div>
        </form>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const map = [
        ['branch_name', 'branchPreviewName', 'New Branch'],
        ['branch_code', 'branchPreviewCode', 'CODE NOT SET'],
        ['phone', 'branchPreviewPhone', 'Not provided'],
    ];
    for (const [inputId, previewId, fallback] of map) {
        const input = document.getElementById(inputId);
        const preview = document.getElementById(previewId);
        input?.addEventListener('input', () => { preview.textContent = input.value.trim() || fallback; });
    }
    const status = document.getElementById('status');
    const preview = document.getElementById('branchPreviewStatus');
    status?.addEventListener('change', () => { preview.textContent = status.selectedOptions[0].text; });
});
</script>
