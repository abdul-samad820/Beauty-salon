@extends('layouts.owner')

@section('title', 'All Appointments')
@section('page-title', 'All Appointments')
@section('breadcrumb', 'Bookings / All')

@section('topbar-actions')
<button class="btn-lux-gold btn-sm" onclick="LuxModal.open('bookModal')" style="padding: 0.5rem 1rem;">
    <i class="bi bi-calendar-plus me-1" aria-hidden="true"></i> Book Now
</button>
@endsection

@section('content')

{{-- KPI Row --}}
<div class="row g-3 mb-4 fade-up s1">
    <div class="col-6 col-md-3">
        <x-cards.kpi-card label="Total Bookings" :value="$stats['total']" icon="bi-calendar3" color="var(--purple)" bg="var(--purple-dim)" />
    </div>
    <div class="col-6 col-md-3">
        <x-cards.kpi-card label="Active Today" :value="$stats['today']" icon="bi-calendar-check" color="var(--teal-light)" bg="var(--teal-dim)" :liveIndicator="true" />
    </div>
    <div class="col-6 col-md-3">
        <x-cards.kpi-card label="Pending Slots" :value="$stats['pending']" icon="bi-hourglass-split" color="var(--amber)" bg="var(--amber-dim)" />
    </div>
    <div class="col-6 col-md-3">
        <x-cards.kpi-card label="Completed" :value="$stats['completed']" icon="bi-check2-all" color="var(--emerald)" bg="var(--emerald-dim)" />
    </div>
    <div class="col-6 col-md-3">
        <x-cards.kpi-card label="No Show" :value="$stats['no_show']" icon="bi-person-x" color="var(--rose)" bg="var(--rose-dim)" />
    </div>
</div>

{{-- Premium Filters Console --}}
<div class="card-lux mb-4 fade-up s2" style="padding: 1.25rem;">
    <form method="GET" action="{{ route('owner.appointments.index') }}" id="filterForm" role="search" class="row g-3 align-items-center">

        {{-- Search --}}
        <div class="col-12 col-md-3 position-relative">
            <i class="bi bi-search position-absolute top-50 translate-middle-y" style="left: 1rem; font-size: 0.85rem; color: var(--text-3);"></i>
            <input type="search" name="search" value="{{ request('search') }}" placeholder="Search customer..." class="lux-input w-100" style="padding-left: 2.2rem; background-color: var(--bg-input); color: var(--text); border: 1px solid var(--border);" aria-label="Search customer" />
        </div>

        {{-- Date Picker --}}
        <div class="col-6 col-md-2">
            <input type="date" name="date" value="{{ request('date') }}" class="lux-input w-100" style="color-scheme: dark; background-color: var(--bg-input); color: var(--text); border: 1px solid var(--border);" aria-label="Filter by date" />
        </div>

        {{-- Status Dropdown --}}
        <div class="col-6 col-md-2 position-relative">
            <select name="status" class="lux-input w-100" style="padding-right: 2rem; color-scheme: dark; background-color: var(--bg-input); color: var(--text); border: 1px solid var(--border); cursor: pointer; -webkit-appearance: none; appearance: none;" aria-label="Filter by status">
                <option value="all" style="background: var(--bg-card); color: var(--text-3);" {{ request('status','all') === 'all' ? 'selected':'' }}>All Statuses</option>
                <option value="pending" style="background: var(--bg-card); color: var(--text);" {{ request('status') === 'pending' ? 'selected':'' }}>Pending</option>
                <option value="confirmed" style="background: var(--bg-card); color: var(--text);" {{ request('status') === 'confirmed' ? 'selected':'' }}>Confirmed</option>
                <option value="checked_in" style="background: var(--bg-card); color: var(--text);" {{ request('status') === 'checked_in' ? 'selected':'' }}>Checked In</option>
                <option value="completed" style="background: var(--bg-card); color: var(--text);" {{ request('status') === 'completed' ? 'selected':'' }}>Completed</option>
                <option value="cancelled" style="background: var(--bg-card); color: var(--text);" {{ request('status') === 'cancelled' ? 'selected':'' }}>Cancelled</option>
                <option value="no_show" style="background: var(--bg-card); color: var(--text);" {{ request('status') === 'no_show' ? 'selected':'' }}>No Show</option>
            </select>
            <div style="position: absolute; right: 0.8rem; top: 50%; transform: translateY(-50%); pointer-events: none; color: var(--text-3);">
                <i class="bi bi-chevron-down" style="font-size: 0.7rem;"></i>
            </div>
        </div>

        {{-- Staff Dropdown --}}
        <div class="col-12 col-md-2 position-relative">
            <select name="staff_id" class="lux-input w-100" style="padding-right: 2rem; color-scheme: dark; background-color: var(--bg-input); color: var(--text); border: 1px solid var(--border); cursor: pointer; -webkit-appearance: none; appearance: none;" aria-label="Filter by staff">
                <option value="" style="background: var(--bg-card); color: var(--text-3);">All Stylists</option>
                @foreach($staffList as $s)
                <option value="{{ $s->id }}" style="background: var(--bg-card); color: var(--text);" {{ request('staff_id') == $s->id ? 'selected':'' }}>
                    {{ $s->user?->name }}
                </option>
                @endforeach
            </select>
            <div style="position: absolute; right: 0.8rem; top: 50%; transform: translateY(-50%); pointer-events: none; color: var(--text-3);">
                <i class="bi bi-chevron-down" style="font-size: 0.7rem;"></i>
            </div>
        </div>

        {{-- Buttons --}}
        <div class="col-12 col-md-3 d-flex gap-2 justify-content-md-end">
            <a href="{{ route('owner.appointments.index') }}" class="btn-lux-ghost btn-sm text-center" style="padding: 0.5rem; justify-content: center; flex: 1;">Reset</a>
            <button type="submit" class="btn-lux-gold btn-sm" style="padding: 0.5rem; justify-content: center; flex: 1.5;">Apply Filter</button>
            <a href="{{ route('owner.appointments.export', request()->query()) }}" class="btn-lux-ghost btn-sm" style="padding: 0.5rem; border-color: rgba(201, 169, 110, 0.3); color: var(--gold);" title="Export to CSV">
                <i class="bi bi-download"></i>
            </a>
        </div>
    </form>
</div>

{{-- Data Table --}}
<div class="card-lux fade-up s3">
    <div class="p-4 border-bottom" style="border-color: rgba(255,255,255,0.05) !important;">
        <h3 class="serif" style="font-size: 1.15rem; color: var(--gold); margin-bottom: 0;">Master Ledger</h3>
        <p style="font-size: 0.65rem; color: var(--text-3); text-transform: uppercase; letter-spacing: 0.05em; margin: 0;">All Booking Records</p>
    </div>

    <div class="lux-table-wrapper lux-scroller" style="overflow-x: auto;">
        <table class="lux-table mb-0">
            <thead>
                <tr>
                    <th style="width: 80px; padding-left: 1.5rem;">ID</th>
                    <th>Customer Info</th>
                    <th>Service details</th>
                    <th>Assigned Stylist</th>
                    <th>Schedule</th>
                    <th>Status</th>
                    <th class="text-end" style="padding-right: 1.5rem;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($appointments as $a)
                <tr style="transition: background 0.2s ease;" onmouseover="this.style.background='rgba(255,255,255,0.015)'" onmouseout="this.style.background='transparent'">
                    <td class="faint" style="font-family: monospace; font-size: 0.75rem; padding-left: 1.5rem;">#{{ str_pad($a->id, 5, '0', STR_PAD_LEFT) }}</td>

                    {{-- Customer --}}
                    <td>
                        <div style="display: flex; align-items: center; gap: 0.6rem;">
                            <div style="width: 28px; height: 28px; border-radius: 50%; background: var(--bg-input); border: 1px solid var(--border-2); display: flex; align-items: center; justify-content: center; font-size: 0.65rem; font-weight: 600; color: var(--text-2);">
                                {{ strtoupper(substr($a->customer?->name ?? 'W', 0, 1)) }}
                            </div>
                            <div>
                                <div style="font-weight: 600; font-size: 0.85rem; color: var(--text);">{{ $a->customer?->name ?? 'Walk-in Client' }}</div>
                                <div style="font-size: 0.65rem; color: var(--text-3); font-family: monospace;">{{ $a->customer?->phone ?? '—' }}</div>
                            </div>
                        </div>
                    </td>

                    {{-- Service --}}
                    <td>
                        <div style="font-weight: 500; font-size: 0.85rem; color: var(--text-2);">{{ $a->service?->name }}</div>
                        <div style="font-size: 0.65rem; color: var(--gold); font-family: monospace;">₹{{ number_format($a->amount, 0) }}</div>
                    </td>

                    {{-- Stylist --}}
                    <td>
                        <div style="display: inline-flex; align-items: center; gap: 0.4rem; color: var(--text-2); font-size: 0.8rem; background: rgba(255,255,255,0.02); padding: 0.2rem 0.5rem; border-radius: 4px;">
                            <span style="height: 6px; width: 6px; border-radius: 50%; background: var(--purple); box-shadow: 0 0 6px var(--purple);"></span>
                            <span>{{ $a->staff?->user?->name ?? 'Unallocated' }}</span>
                        </div>
                    </td>

                    {{-- Schedule --}}
                    <td>
                        <div style="font-size: 0.85rem; color: var(--text);">{{ \Carbon\Carbon::parse($a->appointment_date)->format('d M Y') }}</div>
                        <div style="font-size: 0.7rem; color: var(--gold); font-family: var(--ff-display); font-weight: 600; margin-top: 2px;">
                            {{ \Carbon\Carbon::parse($a->start_time)->format('h:i A') }}
                        </div>
                    </td>

                    {{-- Status --}}
                    <td>
                        <span class="status-badge {{ match(strtolower($a->status)) { 'completed' => 'badge-active', 'cancelled' => 'badge-suspended', 'no_show' => 'badge-suspended', 'checked_in' => 'badge-active', 'confirmed' => 'badge-active', 'pending' => 'badge-trial', default => 'badge-inactive' } }}" style="padding: 0.3rem 0.6rem; font-size: 0.65rem;">
                            {{ $a->status === 'no_show' ? 'No Show' : ucfirst(str_replace('_', ' ', $a->status)) }}
                        </span>
                    </td>

                    {{-- Actions --}}
                    <td class="text-end" style="padding-right: 1.5rem;">
                        <div class="d-flex align-items-center justify-content-end gap-2">
                            @if(in_array($a->status, ['pending', 'confirmed', 'checked_in']))
                            @if(in_array($a->status, ['pending', 'confirmed']))
                            <form method="POST" action="{{ route('owner.appointments.status', $a->id) }}" class="d-inline">
                                @csrf
                                <input type="hidden" name="status" value="checked_in" />
                                <button type="submit" class="action-btn-pro action-success" title="Check In" data-no-spinner>
                                    <i class="bi bi-box-arrow-in-right"></i>
                                </button>
                            </form>
                            @endif
                            @if($a->status === 'checked_in')
                            <form method="POST" action="{{ route('owner.appointments.status', $a->id) }}" class="d-inline">
                                @csrf
                                <input type="hidden" name="status" value="completed" />
                                <button type="submit" class="action-btn-pro action-success" title="Mark Completed" data-no-spinner>
                                    <i class="bi bi-check-lg"></i>
                                </button>
                            </form>
                            @endif
                            @if(in_array($a->status, ['pending', 'confirmed', 'checked_in']))
                            <form method="POST" action="{{ route('owner.appointments.status', $a->id) }}" class="d-inline" onsubmit="return confirm('Mark this appointment as a no-show?');">
                                @csrf
                                <input type="hidden" name="status" value="no_show" />
                                <button type="submit" class="action-btn-pro action-danger" title="Mark No Show" data-no-spinner>
                                    <i class="bi bi-person-x"></i>
                                </button>
                            </form>
                            @endif
                            <form method="POST" action="{{ route('owner.appointments.status', $a->id) }}" class="d-inline">
                                @csrf
                                <input type="hidden" name="status" value="cancelled" />
                                <button type="submit" class="action-btn-pro action-danger" title="Cancel Appointment" data-no-spinner>
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </form>
                            @else
                            <span style="font-size: 0.7rem; color: var(--text-3); font-style: italic; background: rgba(255,255,255,0.03); padding: 0.3rem 0.6rem; border-radius: 4px;">Locked</span>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center" style="padding: 5rem 2rem;">
                        <div style="width: 64px; height: 64px; background: rgba(255,255,255,0.02); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                            <i class="bi bi-calendar-x faint" style="font-size: 2rem;"></i>
                        </div>
                        <h4 class="faint" style="font-size: var(--text-sm); font-weight: 500;">No records found.</h4>
                        <p class="muted" style="font-size: var(--text-xs);">Try adjusting your filters or create a new booking.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($appointments->hasPages())
    <div class="lux-pagination-wrapper border-top" style="border-color: rgba(255,255,255,0.05) !important; padding: 1rem 1.5rem;">
        <x-tables.pagination :paginator="$appointments" />
    </div>
    @endif
</div>

{{-- Book Modal Component --}}
<x-cards.modal id="bookModal" title="New Appointment Booking">
    <form method="POST" action="{{ route('owner.appointments.store') }}">
        @csrf
        <div class="row g-3">
            <div class="col-12">
                <x-forms.select name="customer_id" label="Customer *" :options="$customers->pluck('name','id')->toArray()" blank="Select customer..." :required="true" />
            </div>
            <div class="col-12 col-md-6">
                <x-forms.select name="service_id" label="Service *" :options="$activeServices->mapWithKeys(fn($s) => [$s->id => $s->name.' (₹'.number_format($s->price,0).' · '.$s->duration_minutes.'min)'])->toArray()" blank="Select service..." :required="true" />
            </div>
            <div class="col-12 col-md-6">
                <x-forms.select name="staff_id" label="Assigned Stylist *" :options="$availableStaff->mapWithKeys(fn($s) => [$s->id => $s->user?->name])->toArray()" blank="Select stylist..." :required="true" />
            </div>
            <div class="col-12 col-md-6">
                <x-forms.input name="appointment_date" label="Date *" type="date" :min="date('Y-m-d')" :required="true" />
            </div>
            <div class="col-12 col-md-6">
                <x-forms.input name="start_time" label="Start Time *" type="time" :required="true" />
            </div>
            <div class="col-12">
                <label class="lux-label" for="modal_notes">Internal Notes</label>
                <textarea name="notes" id="modal_notes" rows="2" class="lux-input" placeholder="Optional notes..."></textarea>
            </div>
        </div>
        <div class="d-flex align-items-center justify-content-end gap-2 mt-4 pt-3 border-top" style="border-color: var(--border) !important;">
            <button type="button" onclick="LuxModal.close('bookModal')" class="btn-lux-ghost btn-sm border-0">Cancel</button>
            <button type="submit" class="btn-lux-gold btn-sm">Confirm Booking</button>
        </div>
    </form>
</x-cards.modal>

@endsection

@push('styles')
<style>
    /* Premium Custom Scroller */
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
