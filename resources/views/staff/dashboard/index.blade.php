@extends('layouts.staff')

@section('title', 'My Dashboard')

@section('content')
{{-- Header --}}
<div class="page-header mb-4 fade-up s1">
    <div>
        <h1 class="page-title" style="font-size: 1.8rem;">
            Good {{ now()->hour < 12 ? 'Morning' : (now()->hour < 17 ? 'Afternoon' : 'Evening') }},
            {{ explode(' ', $staff->user->name)[0] }} ✦
        </h1>
        <p class="page-subtitle" style="color: var(--text-3); letter-spacing: 0.05em;">
            {{ $tenant->name }} · {{ now()->format('l, d M Y') }}
        </p>
    </div>
</div>

{{-- Stats Grid (Upgraded to Premium Inline Cards) --}}
<div class="row g-3 mb-5 fade-up s2">
    {{-- Card 1: Appointments --}}
    <div class="col-6 col-md-3">
        <div class="card-lux p-3 h-100" style="position: relative; display: flex; flex-direction: column; justify-content: space-between;">
            <div style="position:absolute; top:1rem; right:1rem; width:32px; height:32px; border-radius:8px; background:var(--gold-dim); color:var(--gold); display:flex; align-items:center; justify-content:center; font-size:0.9rem;">
                <i class="bi bi-calendar-check"></i>
            </div>
            <div style="font-size:0.65rem; font-weight:600; letter-spacing:0.1em; text-transform:uppercase; color:var(--text-3); margin-bottom:0.5rem; padding-right: 40px;">
                Today's Appointments
            </div>
            <div class="serif" style="font-size:1.8rem; line-height:1; color:var(--gold);">
                {{ $stats['today_total'] }}
            </div>
        </div>
    </div>

    {{-- Card 2: Completed --}}
    <div class="col-6 col-md-3">
        <div class="card-lux p-3 h-100" style="position: relative; display: flex; flex-direction: column; justify-content: space-between;">
            <div style="position:absolute; top:1rem; right:1rem; width:32px; height:32px; border-radius:8px; background:var(--emerald-dim); color:var(--emerald); display:flex; align-items:center; justify-content:center; font-size:0.9rem;">
                <i class="bi bi-check2-all"></i>
            </div>
            <div style="font-size:0.65rem; font-weight:600; letter-spacing:0.1em; text-transform:uppercase; color:var(--text-3); margin-bottom:0.5rem; padding-right: 40px;">
                Completed This Month
            </div>
            <div class="serif" style="font-size:1.8rem; line-height:1; color:var(--emerald);">
                {{ $stats['month_completed'] }}
            </div>
        </div>
    </div>

    {{-- Card 3: Earnings --}}
    <div class="col-6 col-md-3">
        <div class="card-lux p-3 h-100" style="position: relative; display: flex; flex-direction: column; justify-content: space-between;">
            <div style="position:absolute; top:1rem; right:1rem; width:32px; height:32px; border-radius:8px; background:var(--teal-dim); color:var(--teal-light); display:flex; align-items:center; justify-content:center; font-size:0.9rem;">
                <i class="bi bi-cash-stack"></i>
            </div>
            <div style="font-size:0.65rem; font-weight:600; letter-spacing:0.1em; text-transform:uppercase; color:var(--text-3); margin-bottom:0.5rem; padding-right: 40px;">
                Month Earnings
            </div>
            <div class="serif" style="font-size:1.8rem; line-height:1; color:var(--teal-light);">
                ₹{{ number_format($stats['month_earnings']) }}
            </div>
        </div>
    </div>

    {{-- Card 4: Pending --}}
    <div class="col-6 col-md-3">
        <div class="card-lux p-3 h-100" style="position: relative; display: flex; flex-direction: column; justify-content: space-between;">
            <div style="position:absolute; top:1rem; right:1rem; width:32px; height:32px; border-radius:8px; background:var(--amber-dim); color:var(--amber); display:flex; align-items:center; justify-content:center; font-size:0.9rem;">
                <i class="bi bi-hourglass-split"></i>
            </div>
            <div style="font-size:0.65rem; font-weight:600; letter-spacing:0.1em; text-transform:uppercase; color:var(--text-3); margin-bottom:0.5rem; padding-right: 40px;">
                Pending Commission
            </div>
            <div class="serif" style="font-size:1.8rem; line-height:1; color:var(--amber);">
                ₹{{ number_format($stats['pending_commission']) }}
            </div>
        </div>
    </div>
</div>

{{-- Today's Schedule Terminal --}}
<div class="card-lux fade-up s3" style="max-width: 900px; margin: 0 auto;">
    <div class="p-4" style="border-bottom:1px solid var(--border);">
        <h3 class="serif" style="font-size:1.1rem; margin-bottom:0.2rem;">Today's Schedule</h3>
        <p style="font-size:0.7rem; color:var(--text-3); text-transform:uppercase; letter-spacing:0.05em; margin:0;">
            Real-time session tracking
        </p>
    </div>

    <div class="lux-scroller" style="max-height: 450px; overflow-y: auto; padding: 1.5rem;">
        @forelse($todayAppointments as $a)
        <div style="display:flex; align-items:center; gap:1.25rem; padding:1.25rem; background:var(--bg-card); border-radius:var(--r-md); border-left: 3px solid {{ $a->status === 'confirmed' ? 'var(--gold)' : 'var(--border)' }}; margin-bottom:0.75rem; transition: all 0.3s ease;">

            {{-- Time --}}
            <div style="font-family:monospace; font-size:0.9rem; font-weight:600; color:var(--gold); min-width:75px;">
                {{ \Carbon\Carbon::parse($a->start_time)->format('h:i A') }}
            </div>

            {{-- Service --}}
            <div style="flex:1;">
                <div style="font-size:0.95rem; font-weight:600; color:var(--text);">{{ $a->service?->name }}</div>
                <div style="font-size:0.75rem; color:var(--text-3); margin-top:3px;">
                    <i class="bi bi-person me-1"></i>{{ $a->customer?->name ?? 'Walk-in' }}
                </div>
            </div>

            {{-- Status --}}
            <span class="status-badge {{ match($a->status) { 'completed' => 'badge-active', 'cancelled' => 'badge-suspended', default => 'badge-trial' } }}">
                {{ ucfirst($a->status) }}
            </span>
        </div>
        @empty
        <div style="text-align:center; padding:4rem 1rem; color:var(--text-3);">
            <div style="width: 60px; height: 60px; background: rgba(255,255,255,0.03); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                <i class="bi bi-calendar-x" style="font-size: 1.5rem; opacity: 0.5;"></i>
            </div>
            <p style="font-size:0.85rem; font-weight:500;">No appointments scheduled for today.</p>
        </div>
        @endforelse
    </div>
</div>
@endsection

@push('styles')
<style>
    /* Scroller */
    .lux-scroller::-webkit-scrollbar {
        width: 5px;
    }

    .lux-scroller::-webkit-scrollbar-thumb {
        background: rgba(201, 169, 110, 0.3);
        border-radius: 10px;
    }

    .lux-scroller::-webkit-scrollbar-thumb:hover {
        background: var(--gold);
    }

    /* Animation */
    .fade-up {
        animation: fadeUp 0.6s ease forwards;
        opacity: 0;
    }

    @keyframes fadeUp {
        to {
            opacity: 1;
            transform: translateY(0);
        }

        from {
            opacity: 0;
            transform: translateY(10px);
        }
    }

    .s1 {
        animation-delay: 0.1s;
    }

    .s2 {
        animation-delay: 0.2s;
    }

    .s3 {
        animation-delay: 0.3s;
    }

</style>
@endpush
