@extends('layouts.owner')

@section('title', 'New Appointment')
@section('page-title', 'New Appointment')
@section('breadcrumb', 'Bookings / New')

@section('topbar-actions')
<a href="{{ route('owner.appointments.index') }}" class="btn-lux-ghost btn-sm border-0">
    <i class="bi bi-arrow-left"></i> Back
</a>
@endsection

@section('content')
<div class="row justify-content-center">
    <!-- Balanced Dynamic Column Width Constraints for Premium SaaS Views -->
    <div class="col-12 col-xl-10 fade-up s1">
        <div class="card-lux" style="padding: 0; overflow: hidden;">

            <!-- Structural Component Header Section -->
            <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid var(--border); padding: 1.25rem 1.5rem; background: rgba(0,0,0,0.15);">
                <div style="min-width: 0;">
                    <h3 class="serif" style="font-size: 1.2rem; margin-bottom: 0;">Book Appointment</h3>
                    <p style="font-size: 0.75rem; color: var(--text-3); margin-top: 0.2rem; margin-bottom: 0;">Create a manual appointment for the customer</p>
                </div>
                <div style="display: flex; height: 40px; width: 40px; flex-shrink: 0; align-items: center; justify-content: center; border-radius: var(--r-md); background: var(--amber-dim); color: var(--amber);">
                    <i class="bi bi-calendar-plus" style="font-size: 1.1rem;"></i>
                </div>
            </div>

            <!-- Operational Core Multi-Grid Form -->
            <form method="POST" action="{{ route('owner.appointments.store') }}" id="createApptForm" style="padding: 1.5rem;">
                @csrf

                <div class="row g-4">

                    <!-- Form Row Unit: Customer Registry -->
                    <div class="col-12 col-md-6 col-lg-8">
                        <label class="lux-label" for="customer_id">Customer *</label>
                        <div style="position: relative;">
                            <select name="customer_id" id="customer_id" class="lux-input @error('customer_id') border-rose @enderror" style="color-scheme: dark; padding-right: 2.5rem; cursor: pointer; background-color: var(--bg-input); color: var(--text);" required>
                                <option value="" style="background: var(--bg-card); color: var(--text-3);">Select Customer…</option>
                                @foreach($customers as $c)
                                {{-- FIX: Added background and color styles to options --}}
                                <option value="{{ $c->id }}" style="background: var(--bg-card); color: var(--text);" {{ old('customer_id') == $c->id ? 'selected' : '' }}>
                                    {{ $c->name }} — {{ $c->phone }}
                                </option>
                                @endforeach
                            </select>
                            <div style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); pointer-events: none; color: var(--text-3);">
                                <i class="bi bi-chevron-down" style="font-size: 0.8rem;"></i>
                            </div>
                        </div>
                        @error('customer_id')
                        <p style="margin-top: 0.4rem; font-size: 0.75rem; color: var(--rose); display: flex; align-items: center; gap: 0.3rem;">
                            <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                        </p>
                        @enderror
                    </div>

                    <!-- Form Row Unit: Service Selection Matrix -->
                    <div class="col-12 col-md-6 col-lg-8">
                        <label class="lux-label" for="service_id">Service *</label>
                        <div style="position: relative;">
                            <select name="service_id" id="service_id" class="lux-input @error('service_id') border-rose @enderror" style="color-scheme: dark; padding-right: 2.5rem; cursor: pointer; background-color: var(--bg-input); color: var(--text);" required>
                                <option value="" style="background: var(--bg-card); color: var(--text-3);">Select Service…</option>
                                @foreach($services as $s)
                                {{-- FIX: Added background and color styles to options --}}
                                <option value="{{ $s->id }}" data-duration="{{ $s->duration_minutes }}" data-price="{{ $s->price }}" style="background: var(--bg-card); color: var(--text);" {{ old('service_id') == $s->id ? 'selected' : '' }}>
                                    {{ $s->name }} — ₹{{ number_format($s->price, 0) }} ({{ $s->duration_minutes }} min)
                                </option>
                                @endforeach
                            </select>
                            <div style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); pointer-events: none; color: var(--text-3);">
                                <i class="bi bi-chevron-down" style="font-size: 0.8rem;"></i>
                            </div>
                        </div>
                        @error('service_id')
                        <p style="margin-top: 0.4rem; font-size: 0.75rem; color: var(--rose); display: flex; align-items: center; gap: 0.3rem;">
                            <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                        </p>
                        @enderror
                    </div>

                    <!-- Form Row Unit: Staff Allocation -->
                    <div class="col-12 col-md-6 col-lg-4">
                        <label class="lux-label" for="staff_id">Staff *</label>
                        <div style="position: relative;">
                            <select name="staff_id" id="staff_id" class="lux-input @error('staff_id') border-rose @enderror" style="color-scheme: dark; padding-right: 2.5rem; cursor: pointer; background-color: var(--bg-input); color: var(--text);" required>
                                <option value="" style="background: var(--bg-card); color: var(--text-3);">Select Staff…</option>
                                @foreach($staffList as $st)
                                {{-- FIX: Added background and color styles to options --}}
                                <option value="{{ $st->id }}" style="background: var(--bg-card); color: var(--text);" {{ old('staff_id') == $st->id ? 'selected' : '' }}>
                                    {{ $st->user?->name }} @if($st->specialization) — {{ $st->specialization }} @endif
                                </option>
                                @endforeach
                            </select>
                            <div style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); pointer-events: none; color: var(--text-3);">
                                <i class="bi bi-chevron-down" style="font-size: 0.8rem;"></i>
                            </div>
                        </div>
                        @error('staff_id')
                        <p style="margin-top: 0.4rem; font-size: 0.75rem; color: var(--rose); display: flex; align-items: center; gap: 0.3rem;">
                            <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                        </p>
                        @enderror
                    </div>

                    <!-- Form Row Unit: Date Context Config -->
                    <div class="col-12 col-md-6">
                        <label class="lux-label" for="appointment_date">Date *</label>
                        <input type="date" name="appointment_date" id="appointment_date" value="{{ old('appointment_date', today()->format('Y-m-d')) }}" min="{{ today()->format('Y-m-d') }}" class="lux-input @error('appointment_date') border-rose @enderror" style="color-scheme: dark; background-color: var(--bg-input); color: var(--text);" required>
                        @error('appointment_date')
                        <p style="margin-top: 0.4rem; font-size: 0.75rem; color: var(--rose); display: flex; align-items: center; gap: 0.3rem;">
                            <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                        </p>
                        @enderror
                    </div>

                    <!-- Form Row Unit: Temporal Scale Time Entry -->
                    <div class="col-12 col-md-6">
                        <label class="lux-label" for="start_time">Start Time *</label>
                        <input type="time" name="start_time" id="start_time" value="{{ old('start_time') }}" class="lux-input @error('start_time') border-rose @enderror" style="color-scheme: dark; background-color: var(--bg-input); color: var(--text);" required>
                        @error('start_time')
                        <p style="margin-top: 0.4rem; font-size: 0.75rem; color: var(--rose); display: flex; align-items: center; gap: 0.3rem;">
                            <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                        </p>
                        @enderror
                    </div>

                    <!-- Asynchronous Data Calculations Terminal Display -->
                    <div class="col-12" id="timePreview" style="display:none;">
                        <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 1rem; border-radius: var(--r-md); border: 1px solid rgba(245, 158, 11, 0.2); background: var(--amber-dim); padding: 1rem; font-size: 0.85rem; color: var(--amber);">
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <i class="bi bi-clock"></i>
                                <span>End Time: <strong id="endTimeDisplay" style="color: var(--text);">—</strong></span>
                            </div>
                            <span class="d-none d-sm-inline" style="opacity: 0.3;">|</span>
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <i class="bi bi-hourglass-split"></i>
                                <span>Duration: <strong id="durationDisplay" style="color: var(--text);">—</strong> min</span>
                            </div>
                            <span class="d-none d-sm-inline" style="opacity: 0.3;">|</span>
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <i class="bi bi-wallet2"></i>
                                <span>Total Gross Amount: <strong id="amountDisplay" style="color: var(--text);">—</strong></span>
                            </div>
                        </div>
                    </div>

                    <!-- Form Row Unit: Textarea Logs Entry -->
                    <div class="col-12">
                        <label class="lux-label" for="notes">Notes <span style="text-transform: none; font-weight: 300; color: var(--text-3);">(optional)</span></label>
                        <textarea name="notes" id="notes" rows="3" class="lux-input" placeholder="Provide internal notes or booking specifications for staff setup…">{{ old('notes') }}</textarea>
                    </div>

                </div>

                <!-- Footer Base Action Layout Tray -->
                <div style="margin-top: 2rem; display: flex; align-items: center; justify-content: flex-end; gap: 0.75rem; border-top: 1px solid var(--border); padding-top: 1.5rem;">
                    <a href="{{ route('owner.appointments.index') }}" class="btn-lux-ghost btn-sm border-0">
                        Cancel
                    </a>
                    <button type="submit" class="btn-lux-gold btn-sm">
                        <i class="bi bi-calendar-check"></i> Book Appointment
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const serviceSelect = document.getElementById('service_id');
        const startTimeInput = document.getElementById('start_time');
        const preview = document.getElementById('timePreview');
        const endDisplay = document.getElementById('endTimeDisplay');
        const durDisplay = document.getElementById('durationDisplay');
        const amtDisplay = document.getElementById('amountDisplay');

        function updatePreview() {
            const opt = serviceSelect.options[serviceSelect.selectedIndex];
            const duration = parseInt(opt ? .dataset ? .duration || 0);
            const price = parseFloat(opt ? .dataset ? .price || 0);
            const start = startTimeInput.value;

            if (!duration || !start) {
                preview.style.display = 'none';
                return;
            }

            // Calculation algorithms
            const [h, m] = start.split(':').map(Number);
            const endMins = h * 60 + m + duration;
            const endH = String(Math.floor(endMins / 60) % 24).padStart(2, '0');
            const endM = String(endMins % 60).padStart(2, '0');

            endDisplay.textContent = endH + ':' + endM;
            durDisplay.textContent = duration;
            amtDisplay.textContent = '₹' + price.toLocaleString('en-IN');
            preview.style.display = 'block';
        }

        serviceSelect.addEventListener('change', updatePreview);
        startTimeInput.addEventListener('change', updatePreview);
        updatePreview();
    });

</script>
@endpush
