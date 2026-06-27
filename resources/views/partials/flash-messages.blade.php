@if(session('success'))
<div class="flash-alert flash-success fade-up" role="alert">
    <i class="bi bi-check-circle-fill" aria-hidden="true"></i>
    <span>{{ session('success') }}</span>
</div>
@endif
@if(session('error'))
<div class="flash-alert flash-error fade-up" role="alert">
    <i class="bi bi-exclamation-circle-fill" aria-hidden="true"></i>
    <span>{{ session('error') }}</span>
</div>
@endif
@if(session('warning'))
<div class="flash-alert flash-warning fade-up" role="alert">
    <i class="bi bi-exclamation-triangle-fill" aria-hidden="true"></i>
    <span>{{ session('warning') }}</span>
</div>
@endif
@if(session('info'))
<div class="flash-alert flash-info fade-up" role="alert">
    <i class="bi bi-info-circle-fill" aria-hidden="true"></i>
    <span>{{ session('info') }}</span>
</div>
@endif

@if($errors->any())
<div class="flash-alert flash-error fade-up" role="alert">
    <i class="bi bi-exclamation-circle-fill" aria-hidden="true"></i>
    <span>{{ $errors->first() }}</span>
</div>
@endif
