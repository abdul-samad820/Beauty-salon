<article class="card-lux kpi-pad {{ isset($topBorder) ? '' : 'gold-border' }} glow-hover" style="{{ isset($color) ? 'border-top:2px solid '.$color.';' : '' }}">

    <div class="kpi-label">
        @if(isset($liveIndicator) && $liveIndicator)
        <span class="live-dot" aria-hidden="true"></span>
        @endif
        {{ $label }}
    </div>

    <div class="kpi-value" style="{{ isset($color) ? 'color:'.$color.';' : '' }}">
        {{ $value }}
    </div>

    @if(isset($trend))
    <span class="kpi-trend trend-{{ $trendType ?? 'up' }}">
        @if(isset($trendIcon))
        <i class="bi {{ $trendIcon }}" aria-hidden="true"></i>
        @endif
        {{ $trend }}
    </span>
    @endif

    @if(isset($icon))
    @php
    $iconBg = match($color ?? '') {
    'var(--gold)' => 'rgba(201,169,110,.12)',
    'var(--emerald)' => 'rgba(16,185,129,.12)',
    'var(--rose)' => 'rgba(244,63,94,.12)',
    'var(--amber)' => 'rgba(245,158,11,.12)',
    'var(--purple)' => 'rgba(168,85,247,.12)',
    default => 'rgba(255,255,255,.08)',
    };
    @endphp

    <div class="kpi-icon-abs" style="color:{{ $color ?? 'var(--gold)' }};
                    background:{{ $iconBg }};" aria-hidden="true">
        <i class="bi {{ $icon }}"></i>
    </div>
    @endif

    @if(isset($sparkId))
    <div class="kpi-spark">
        <canvas id="{{ $sparkId }}" aria-hidden="true"></canvas>
    </div>
    @endif

</article>
