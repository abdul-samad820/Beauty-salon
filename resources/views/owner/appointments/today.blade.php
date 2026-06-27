@extends('layouts.owner')

@section('title', "Today's Appointments")
@section('page-title', "Today's Appointments")
@section('page-sub', 'Daily Operations · ' . ($date ?? now()->format('d M Y')))
@section('breadcrumb', 'Workspace / Bookings / Today')

@section('topbar-actions')
{{-- Sleek New Booking Button --}}
<a href="{{ route('owner.appointments.create') }}" class="btn-lux-gold btn-sm" style="flex-shrink: 0; padding: 0.5rem 1rem;">
    <i class="bi bi-plus-lg me-1"></i> <span class="d-none d-sm-inline">New Booking Entry</span>
</a>
@endsection

@section('content')

<div class="card-lux p-0 fade-up s1">
    {{-- Refined Card Header --}}
    <div class="p-4 border-bottom" style="border-color: rgba(255,255,255,0.05) !important; display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h3 class="serif" style="font-size: 1.25rem; color: var(--gold); margin-bottom: 0.2rem;">Today's Ledger</h3>
            <p style="font-size: 0.65rem; color: var(--text-3); text-transform: uppercase; letter-spacing: 0.05em; margin: 0;">
                <i class="bi bi-clock me-1"></i> Real-time Schedule
            </p>
        </div>
        <div style="font-size: 0.8rem; color: var(--text-2); font-weight: 500; background: var(--bg-input); padding: 0.4rem 0.8rem; border-radius: 8px; border: 1px solid var(--border);">
            Total: <span style="color: var(--gold);">{{ count($appointments ?? []) }}</span>
        </div>
    </div>

    {{-- Premium Scroller Table --}}
    <div class="lux-table-wrapper lux-scroller" style="max-height: 650px; overflow-y: auto;">
        <table class="lux-table mb-0">
            <thead style="position: sticky; top: 0; background: rgba(15, 15, 20, 0.95); backdrop-filter: blur(10px); z-index: 10;">
                <tr>
                    <th style="width: 130px; padding-left: 1.5rem;">Schedule Time</th>
                    <th>Customer Identity</th>
                    <th>Assigned Staff</th>
                    <th>Treatment</th>
                    <th>Status</th>
                    <th class="text-end" style="padding-right: 1.5rem;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($appointments ?? [] as $booking)
                <tr style="transition: background 0.2s ease;" onmouseover="this.style.background='rgba(255,255,255,0.015)'" onmouseout="this.style.background='transparent'">

                    {{-- Time --}}
                    <td style="padding-left: 1.5rem;">
                        <div style="font-family: var(--ff-display); color: var(--gold); font-size: 1.05rem; font-weight: 600; letter-spacing: 0.02em;">
                            {{ \Carbon\Carbon::parse($booking->start_time)->format('h:i A') }}
                        </div>
                    </td>

                    {{-- Customer details with Avatar --}}
                    <td>
                        <div style="display: flex; align-items: center; gap: 0.8rem;">
                            <div style="width: 34px; height: 34px; border-radius: 50%; background: var(--bg-input); border: 1px solid var(--border-2); display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 600; color: var(--text-2);">
                                {{ strtoupper(substr($booking->customer->name ?? 'W', 0, 1)) }}
                            </div>
                            <div>
                                <div style="font-weight: 600; font-size: 0.85rem; color: var(--text);">{{ $booking->customer->name ?? 'Walk-in Client' }}</div>
                                <div style="font-size: 0.65rem; color: var(--text-3); font-family: monospace; letter-spacing: 0.05em; margin-top: 2px;">
                                    <i class="bi bi-telephone-fill" style="font-size: 0.55rem; opacity: 0.7;"></i> {{ $booking->customer->phone ?? 'No Contact' }}
                                </div>
                            </div>
                        </div>
                    </td>

                    {{-- Staff --}}
                    <td>
                        <div style="display: inline-flex; align-items: center; gap: 0.5rem; color: var(--text-2); font-size: 0.8rem; background: rgba(255,255,255,0.03); padding: 0.3rem 0.6rem; border-radius: 6px;">
                            <span style="height: 6px; width: 6px; border-radius: 50%; background: var(--purple); box-shadow: 0 0 6px var(--purple);"></span>
                            <span style="font-weight: 500;">{{ $booking->staff->user->name ?? 'Unassigned' }}</span>
                        </div>
                    </td>

                    {{-- Service --}}
                    <td>
                        <div style="font-weight: 600; font-size: 0.85rem; color: var(--text-2);">{{ $booking->service->name }}</div>
                        <div style="font-size: 0.65rem; color: var(--text-3); margin-top: 2px;">
                            <i class="bi bi-hourglass-split"></i> {{ $booking->service->duration_minutes }} Mins
                        </div>
                    </td>

                    {{-- Status --}}
                    <td>
                        @php
                        $statusClass = match(strtolower($booking->status)) {
                        'completed' => 'badge-active',
                        'confirmed' => 'badge-active',
                        'checked_in' => 'badge-active',
                        'cancelled' => 'badge-suspended',
                        'no_show' => 'badge-suspended',
                        default => 'badge-trial'
                        };
                        @endphp
                        <span class="status-badge {{ $statusClass }}" style="font-size: 0.65rem; padding: 0.3rem 0.6rem;">
                            {{ $booking->status === 'no_show' ? 'No Show' : ucfirst(str_replace('_', ' ', $booking->status)) }}
                        </span>
                    </td>

                    {{-- Actions --}}
                    <td class="text-end" style="padding-right: 1.5rem;">
                        <div class="d-flex align-items-center justify-content-end gap-2">
                            @if(!in_array(strtolower($booking->status), ['completed', 'cancelled', 'no_show']))
                            @if(in_array($booking->status, ['pending', 'confirmed']))
                            {{-- Check In Action --}}
                            <form action="{{ route('owner.appointments.status', $booking->id) }}" method="POST" class="d-inline">
                                @csrf
                                <input type="hidden" name="status" value="checked_in">
                                <button type="submit" class="action-btn-pro action-success" title="Check In" data-no-spinner>

                                    <i class="bi bi-box-arrow-in-right"></i>
                                </button>
                            </form>
                            @endif

                            @if($booking->status === 'checked_in')
                            {{-- Complete Action --}}
                            <form action="{{ route('owner.appointments.status', $booking->id) }}" method="POST" class="d-inline">
                                @csrf
                                <input type="hidden" name="status" value="completed">
                                <button type="submit" class="action-btn-pro action-success" title="Mark as Complete" data-no-spinner>

                                    <i class="bi bi-check2"></i>
                                </button>
                            </form>
                            @endif

                            {{-- No Show Action --}}
                            <form action="{{ route('owner.appointments.status', $booking->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Mark this appointment as a no-show?');">
                                @csrf
                                <input type="hidden" name="status" value="no_show">
                                <button type="submit" class="action-btn-pro action-danger" title="Mark No Show" data-no-spinner>

                                    <i class="bi bi-person-x"></i>
                                </button>
                            </form>

                            {{-- Cancel Action --}}
                            <form action="{{ route('owner.appointments.status', $booking->id) }}" method="POST" class="d-inline">
                                @csrf
                                <input type="hidden" name="status" value="cancelled">
                                <button type="submit" class="action-btn-pro action-danger" title="Cancel Appointment" data-no-spinner>

                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </form>
                            @else
                            <span style="font-size: 0.7rem; color: var(--text-3); font-style: italic; background: rgba(255,255,255,0.03); padding: 0.3rem 0.6rem; border-radius: 4px;">Ledger Closed</span>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 5rem 2rem;">
                        <div style="width: 64px; height: 64px; background: rgba(255,255,255,0.02); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                            <i class="bi bi-calendar2-x" style="font-size: 1.8rem; color: var(--text-3); opacity: 0.6;"></i>
                        </div>
                        <h4 style="font-size: 1rem; color: var(--text); font-weight: 500; margin-bottom: 0.3rem;">No Bookings Scheduled</h4>
                        <p style="font-size: 0.75rem; color: var(--text-3); max-width: 300px; margin: 0 auto; line-height: 1.5;">
                            The schedule for today is currently empty. New walk-ins or reservations will appear here.
                        </p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection

@push('styles')
<style>
    /* Premium Scroller */
    .lux-scroller::-webkit-scrollbar {
        width: 5px;
        height: 5px;
    }

    .lux-scroller::-webkit-scrollbar-thumb {
        background: rgba(201, 169, 110, 0.2);
        border-radius: 10px;
    }

    .lux-scroller::-webkit-scrollbar-thumb:hover {
        background: var(--gold);
    }

    /* Action Buttons Pro Design */
    .action-btn-pro {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        border: 1px solid transparent;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.9rem;
        background: var(--bg-input);
        color: var(--text-3);
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .action-success:hover {
        background: var(--emerald-dim);
        border-color: rgba(16, 185, 129, 0.3);
        color: var(--emerald);
        transform: translateY(-2px);
    }

    .action-danger:hover {
        background: var(--rose-dim);
        border-color: rgba(244, 63, 94, 0.3);
        color: var(--rose);
        transform: translateY(-2px);
    }

</style>
@endpush
