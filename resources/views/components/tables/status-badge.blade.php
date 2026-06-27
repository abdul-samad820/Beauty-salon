@php
$statusString = strtolower($status ?? 'pending');

$cfg = match($statusString) {
'completed', 'active', 'paid', 'settled' => [
'bg' => 'var(--emerald-dim)',
'color' => 'var(--emerald)',
'pulse' => false,
'label' => in_array($statusString, ['paid', 'settled']) ? 'Settled' : 'Completed',
],
'cancelled', 'suspended', 'failed', 'deficit' => [
'bg' => 'var(--rose-dim)',
'color' => 'var(--rose)',
'pulse' => false,
'label' => ucfirst($statusString),
],
'confirmed', 'approved', 'pro' => [
'bg' => 'rgba(99,102,241,0.12)',
'color' => '#818cf8',
'pulse' => true,
'label' => ucfirst($statusString),
],
'pending', 'trial', 'processing' => [
'bg' => 'var(--amber-dim)',
'color' => 'var(--amber)',
'pulse' => true,
'label' => $statusString === 'trial' ? 'Trial' : 'Pending',
],
default => [
'bg' => 'rgba(255,255,255,0.05)',
'color' => 'var(--text-3)',
'pulse' => false,
'label' => ucfirst($statusString),
],
};
@endphp

<span style="
    display:inline-flex;
    align-items:center;
    gap:0.35rem;
    background:{{ $cfg['bg'] }};
    color:{{ $cfg['color'] }};
    font-size:0.62rem;
    font-weight:700;
    letter-spacing:0.1em;
    text-transform:uppercase;
    padding:0.22rem 0.6rem;
    border-radius:20px;
    white-space:nowrap;
    user-select:none;
">
    <span style="
        position:relative;
        display:inline-flex;
        width:6px;
        height:6px;
        border-radius:50%;
        background:{{ $cfg['color'] }};
        flex-shrink:0;
        {{ $cfg['pulse'] ? 'animation:badgePulse 1.5s ease-in-out infinite;' : '' }}
    "></span>
    {{ $cfg['label'] }}
</span>

{{-- Pulse animation keyframe — injected once per page render --}}
@once
<style>
    @keyframes badgePulse {

        0%,
        100% {
            opacity: 1;
            transform: scale(1);
        }

        50% {
            opacity: 0.4;
            transform: scale(0.75);
        }
    }

</style>
@endonce
