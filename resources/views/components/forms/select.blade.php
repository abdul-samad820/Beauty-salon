@props([
'name',
'label' => null,
'options' => [],
'selected' => null,
'blank' => null,
'required' => false,
])

<div class="form-group mb-3 w-100">
    @if($label)
    <label for="{{ $name }}" class="lux-label">
        {{ $label }}
    </label>
    @endif

    <div style="position: relative;">
        <select name="{{ $name }}" id="{{ $name }}" class="lux-input @error($name) border-rose @enderror"  style="cursor: pointer; padding-right: 2.5rem; color-scheme: dark; background-color: var(--bg-input); color: var(--text);" @if($required) required aria-required="true" @endif {{ $attributes }}>
            @if($blank !== null)
            <option value="" style="background: var(--bg-card); color: var(--text-3);">{{ $blank }}</option>
            @endif

            @foreach($options as $val => $text)
            <option value="{{ $val }}" {{ old($name, $selected) == $val ? 'selected' : '' }} style="background: var(--bg-card); color: var(--text);">
                {{ $text }}
            </option>
            @endforeach
        </select>

        {{-- Custom dropdown arrow icon matching the theme --}}
        <div style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); pointer-events: none; color: var(--text-3);">
            <i class="bi bi-chevron-down" style="font-size: 0.8rem;" aria-hidden="true"></i>
        </div>
    </div>

    @error($name)
    <div style="font-size:0.7rem;color:var(--rose);margin-top:0.3rem;">
        <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
    </div>
    @enderror
</div>
