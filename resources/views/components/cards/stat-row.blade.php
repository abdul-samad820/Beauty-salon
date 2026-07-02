<div class="row g-3 mb-3">
    @foreach($stats as $i => $stat)
  <div class="col-6 col-lg-4 fade-up s{{ $i + 1 }}">
        <div class="card-lux kpi-pad {{ $i === 0 ? 'gold-border' : '' }}" style="{{ $i > 0 ? 'border-top:2px solid '.$stat['color'].';' : '' }}">
            <div class="kpi-label">{{ $stat['label'] }}</div>
            <div class="kpi-value" style="color:{{ $stat['color'] }}">{{ $stat['value'] }}</div>
        </div>
    </div>
    @endforeach
</div>
