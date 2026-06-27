@extends('layouts.superadmin')

@section('title', 'Edit — ' . $tenant->name)
@section('breadcrumb')
<a href="{{ route('superadmin.tenants.index') }}" style="color:var(--text-3);text-decoration:none;">Tenants</a>
<i class="bi bi-chevron-right" style="font-size:0.55rem;margin:0 0.4rem;"></i>
<a href="{{ route('superadmin.tenants.show', $tenant) }}" style="color:var(--text-3);text-decoration:none;">{{ $tenant->name }}</a>
<i class="bi bi-chevron-right" style="font-size:0.55rem;margin:0 0.4rem;"></i>
<span style="color:var(--text-2);">Edit</span>
@endsection
@section('page-title', 'Edit Tenant')

@section('topbar-actions')
<a href="{{ route('superadmin.tenants.show', $tenant) }}" class="btn-lux-ghost btn-sm border-0">
    <i class="bi bi-x-lg"></i> Cancel
</a>
@endsection

@push('styles')
<style>
    .form-card {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: 16px;
        padding: 2rem;
        margin-bottom: 1.5rem;
        position: relative;
        overflow: hidden;
    }

    .form-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.08), transparent);
    }

    .form-card-title {
        font-family: var(--ff-display);
        font-size: 1.2rem;
        font-weight: 400;
        color: var(--text);
        margin-bottom: 0.3rem;
    }

    .form-card-sub {
        font-size: 0.75rem;
        color: var(--text-3);
        margin-bottom: 1.8rem;
    }

    .fl-group {
        position: relative;
        margin-bottom: 1.4rem;
    }

    .fl-group label {
        position: absolute;
        top: 0;
        left: 1rem;
        font-size: 0.65rem;
        font-weight: 600;
        letter-spacing: 0.12em;
        color: var(--gold);
        background: var(--bg-card);
        padding: 0 0.4rem;
        transform: translateY(-50%);
    }

    .fl-group.has-icon label {
        left: 2.8rem;
    }

    .fl-group input,
    .fl-group select,
    .fl-group textarea {
        width: 100%;
        background: var(--bg-input);
        border: 1px solid var(--border-2);
        border-radius: 10px;
        color: var(--text);
        font-family: var(--ff-body);
        font-size: 0.85rem;
        font-weight: 300;
        padding: 0.9rem 1rem;
        outline: none;
        transition: border-color 0.3s;
        appearance: none;
    }

    .fl-group.has-icon input,
    .fl-group.has-icon select,
    .fl-group.has-icon textarea {
        padding-left: 2.8rem;
    }

    .fl-group textarea {
        padding-top: 1.2rem;
        resize: vertical;
        min-height: 80px;
    }

    .fl-group input:focus,
    .fl-group select:focus,
    .fl-group textarea:focus {
        border-color: var(--gold);
        background: rgba(201, 169, 110, 0.04);
        box-shadow: 0 0 0 3px rgba(201, 169, 110, 0.08);
    }

    .fl-group select option {
        background: var(--bg-card);
        color: var(--text);
    }

    .fl-input-icon {
        position: absolute;
        left: 0.9rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-3);
        font-size: 0.9rem;
        pointer-events: none;
    }

    .fl-group textarea~.fl-input-icon {
        top: 1.2rem;
        transform: none;
    }

    .danger-zone {
        background: var(--rose-dim);
        border: 1px solid rgba(244, 63, 94, 0.2);
        border-radius: 12px;
        padding: 1.5rem;
    }

</style>
@endpush

@section('content')
<div class="row justify-content-center">
    <div class="col-xl-8 col-lg-10">
        <form method="POST" action="{{ route('superadmin.tenants.update', $tenant) }}">
            @csrf
            @method('PUT')

            {{-- Business Info --}}
            <div class="form-card fade-in-up">
                <div class="form-card-title">Business Information</div>
                <div class="form-card-sub">Update core details for this tenant</div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="fl-group has-icon">
                            <i class="bi bi-buildings fl-input-icon"></i>
                            <input type="text" name="business_name" value="{{ old('business_name', $tenant->name) }}" required />
                            <label>Salon / Parlour Name *</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="fl-group has-icon">
                            <i class="bi bi-link-45deg fl-input-icon"></i>
                            <input type="text" name="subdomain" value="{{ old('subdomain', $tenant->subdomain) }}" required />
                            <label>Subdomain *</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="fl-group has-icon">
                            <i class="bi bi-telephone-fill fl-input-icon"></i>
                            <input type="text" name="phone" value="{{ old('phone', $tenant->phone) }}" />
                            <label>Phone Number</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="fl-group has-icon">
                            <i class="bi bi-layers fl-input-icon"></i>
                            <select name="plan">
                                @foreach(['free' => 'Free', 'basic' => 'Basic', 'premium' => 'Premium'] as $val => $label)

                                <option value="{{ $val }}" {{ old('plan', $tenant->plan) === $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            <label>Subscription Plan *</label>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="fl-group has-icon">
                            <textarea name="address">{{ old('address', $tenant->address) }}</textarea>
                            <i class="bi bi-geo-alt-fill fl-input-icon"></i>
                            <label>Physical Address</label>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Status --}}
            <div class="form-card fade-in-up stagger-2">
                <div class="form-card-title">Account Status</div>
                <div class="form-card-sub">Update the operational status of this tenant</div>
                <div class="fl-group">
                    <select name="status" style="width:100%;background:var(--bg-input);border:1px solid var(--border-2);border-radius:10px;color:var(--text);font-family:var(--ff-body);font-size:0.85rem;padding:0.9rem 1rem;outline:none;appearance:none;">
                        <option value="active" {{ $tenant->status === 'active'    ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ $tenant->status === 'inactive'  ? 'selected' : '' }}>Inactive</option>
                        <option value="suspended" {{ $tenant->status === 'suspended' ? 'selected' : '' }}>Suspended</option>
                    </select>
                </div>
                <div style="font-size:0.72rem;color:var(--text-3);">
                    <i class="bi bi-info-circle"></i> Suspending an account will revoke all access for associated users.
                </div>
            </div>

            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:2rem;">
                <a href="{{ route('superadmin.tenants.show', $tenant) }}" class="btn-lux-ghost">
                    <i class="bi bi-x-lg"></i> Cancel
                </a>
                <button type="submit" class="btn-lux-gold">
                    <i class="bi bi-check-lg"></i> Save Changes
                </button>
            </div>
        </form>

        {{-- Danger Zone --}}
        <div class="danger-zone fade-in-up stagger-3">
            <div style="font-family:var(--ff-display);font-size:1rem;color:var(--rose);margin-bottom:0.5rem;">Danger Zone</div>
            <div style="font-size:0.78rem;color:var(--text-2);margin-bottom:1rem;">
                Deleting this tenant will permanently remove all associated data. This action cannot be undone.
            </div>
            <form method="POST" action="{{ route('superadmin.tenants.destroy', $tenant) }}" onsubmit="return confirm('WARNING: Are you sure you want to permanently delete this tenant? This action cannot be undone.');">
                @csrf @method('DELETE')
                <button type="submit" class="btn-lux-danger">
                    <i class="bi bi-trash3-fill"></i> Suspend & Archive Tenant
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
