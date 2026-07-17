@props([
'name',
'label' => null,
'value' => null,
'placeholder' => 'Enter details...',
'rows' => 3,
'required' => false,
])

<div class="w-100">
    {{-- Label --}}
    @if($label)
    <label for="{{ $name }}" class="lux-label">
        {{ $label }}
    </label>
    @endif

    {{-- Textarea --}}
    <textarea name="{{ $name }}" id="{{ $name }}" rows="{{ $rows }}"
        class="lux-input @error($name) border-rose @enderror"
        style="resize: vertical; min-height: 80px;"
        placeholder="{{ $placeholder }}"
        @if($required) required aria-required="true" @endif
        {{ $attributes }}>{{ old($name, $value) }}</textarea>

    {{-- Error Message --}}
    @error($name)
    <p style="margin-top:0.4rem; font-size:0.7rem; color:var(--rose); display:flex; align-items:center; gap:0.3rem;">
        <i class="bi bi-exclamation-circle-fill" aria-hidden="true"></i>
        <span>{{ $message }}</span>
    </p>
    @enderror
</div>
