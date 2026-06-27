@props([
'icon' => 'bi-inbox',
'title' => 'No Data Found',
'text' => 'Nothing to show right now.',
])

<div class="empty-state">
    <i class="bi {{ $icon }}"></i>

    <h4>{{ $title }}</h4>

    <p>{{ $text }}</p>
</div>
