@extends('layouts.staff')

@section('title', 'My Profile')

@section('content')
<div class="page-header mb-4 fade-up s1">
    <h1 class="page-title">My Profile</h1>
    <p class="page-subtitle" style="color: var(--text-3);">Manage your personal information and contact details</p>
</div>

<div class="row g-4 fade-up s2">
    {{-- Left Column: Identity & Read-only Info --}}
    <div class="col-12 col-lg-4">
        <div class="card-lux p-4 text-center h-100" style="display: flex; flex-direction: column;">
            <div style="width: 80px; height: 80px; background: rgba(201, 169, 110, 0.15); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; border: 1px solid rgba(201, 169, 110, 0.3);">
                <span class="serif" style="color: var(--gold); font-size: 2.5rem;">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </span>
            </div>

            <h3 class="serif" style="font-size: 1.4rem; color: var(--text); margin-bottom: 0.2rem;">{{ $user->name }}</h3>
            <p style="font-size: 0.85rem; color: var(--gold); margin-bottom: 2rem;">Staff Member at {{ $tenant->name ?? 'LUMIÈRE' }}</p>

            <div style="border-top: 1px solid var(--border); padding-top: 1.5rem; text-align: left; margin-top: auto;">
                <div class="mb-3">
                    <label style="font-size: 0.7rem; color: var(--text-3); text-transform: uppercase; letter-spacing: 1px;">Email Address</label>
                    <div style="color: var(--text-2); font-size: 0.95rem; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="bi bi-envelope" style="color: var(--gold);"></i> {{ $user->email }}
                    </div>
                </div>
                <div>
                    <label style="font-size: 0.7rem; color: var(--text-3); text-transform: uppercase; letter-spacing: 1px;">Member Since</label>
                    <div style="color: var(--text-2); font-size: 0.95rem; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="bi bi-calendar3" style="color: var(--gold);"></i> {{ $user->created_at->format('d M Y') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Right Column: Editable Form --}}
    <div class="col-12 col-lg-8">
        <div class="card-lux p-4 h-100">
            <h4 class="serif mb-4" style="color: var(--gold); font-size: 1.2rem; border-bottom: 1px solid var(--border); padding-bottom: 1rem;">
                <i class="bi bi-person-lines-fill me-2"></i> Edit Details
            </h4>

            <form method="POST" action="{{ route('staff.profile.update') }}">
                @csrf
                @method('PUT')

                <div class="row g-4">
                    {{-- Name Input --}}
                    <div class="col-12 col-md-6">
                        <label class="lux-label">Full Name *</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" class="lux-input w-100 @error('name') border-rose @enderror" required>
                        @error('name')
                        <div style="color: var(--rose); font-size: 0.75rem; margin-top: 0.4rem;">
                            <i class="bi bi-exclamation-circle"></i> {{ $message }}
                        </div>
                        @enderror
                    </div>

                    {{-- Phone Input --}}
                    <div class="col-12 col-md-6">
                        <label class="lux-label">Phone Number</label>
                        <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="lux-input w-100 @error('phone') border-rose @enderror" placeholder="Enter contact number">
                        @error('phone')
                        <div style="color: var(--rose); font-size: 0.75rem; margin-top: 0.4rem;">
                            <i class="bi bi-exclamation-circle"></i> {{ $message }}
                        </div>
                        @enderror
                    </div>

                    {{-- Email (Disabled) --}}
                    <div class="col-12">
                        <label class="lux-label">Email Address <span style="color: var(--text-3); font-size: 0.7rem; text-transform: none; font-weight: normal;">(Cannot be changed)</span></label>
                        <input type="email" value="{{ $user->email }}" class="lux-input w-100" style="opacity: 0.5; cursor: not-allowed; background: rgba(0,0,0,0.2);" disabled>
                    </div>

                    {{-- Submit Button --}}
                    <div class="col-12 mt-4 pt-4 text-end" style="border-top: 1px solid var(--border);">
                        <button type="submit" class="btn-lux-gold px-4 py-2" style="border-radius: 6px; font-weight: 600;">
                            <i class="bi bi-save me-2"></i> Save Changes
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* Styling to match LUMIÈRE premium forms */
    .border-rose {
        border-color: var(--rose) !important;
    }

    .border-rose:focus {
        box-shadow: 0 0 0 2px rgba(244, 63, 94, 0.2) !important;
    }

</style>
@endpush
