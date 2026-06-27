{{-- components/buttons/gold.blade.php --}}
{{-- Usage: <x-buttons.gold icon="bi-plus-lg">Add Service</x-buttons.gold> --}}
<button type="{{ $type ?? 'button' }}" class="btn-lux-gold {{ $class ?? '' }}" {{ $attributes }}>
    @if(isset($icon))
    <i class="bi {{ $icon }}" aria-hidden="true"></i>
    @endif
    {{ $slot }}
</button>
