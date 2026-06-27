{{-- components/buttons/ghost.blade.php --}}
<button type="{{ $type ?? 'button' }}" class="btn-lux-ghost {{ $class ?? '' }}" {{ $attributes }}>
    @if(isset($icon))
    <i class="bi {{ $icon }}" aria-hidden="true"></i>
    @endif
    {{ $slot }}
</button>
