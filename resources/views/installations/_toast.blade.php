@if(session('success') || session('error') || $errors->any())
    <div id="installationToast" class="installation-toast {{ session('success') ? 'success' : 'danger' }}" role="alert">
        {{ session('success') ?? session('error') ?? $errors->first() }}
    </div>
@endif
