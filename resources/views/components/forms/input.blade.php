<div class="form-group mb-3">
    <label for="{{ $name }}" class="lux-label">{{ $label }}</label>
    <input type="{{ $type ?? 'text' }}" name="{{ $name }}" id="{{ $name }}" class="lux-input @error($name) border-rose @enderror" value="{{ old($name, $value ?? '') }}" @if(isset($placeholder)) placeholder="{{ $placeholder }}" @endif @if(isset($required) && $required) required aria-required="true" @endif @if(isset($min)) min="{{ $min }}" @endif @if(isset($max)) max="{{ $max }}" @endif @if(isset($step)) step="{{ $step }}" @endif />
    @error($name)
    <div style="font-size:0.7rem;color:var(--rose);margin-top:0.3rem;">
        <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
    </div>
    @enderror
</div>
