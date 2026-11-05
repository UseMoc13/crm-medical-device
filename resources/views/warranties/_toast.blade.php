@if(session('success') || session('error') || $errors->any())
<div class="warranty-toast {{ session('success') ? 'success' : 'danger' }}" role="alert" id="warrantyToast">{{ session('success') ?? session('error') ?? $errors->first() }}</div>
<script>setTimeout(()=>{const toast=document.getElementById('warrantyToast');if(toast)toast.hidden=true},4500)</script>
@endif
