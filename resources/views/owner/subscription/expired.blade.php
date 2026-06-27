@extends('layouts.owner')

@section('title', 'Subscription Expired')

@section('content')
<div style="display:flex;align-items:center;justify-content:center;min-height:60vh;">
    <div style="text-align:center;max-width:480px;padding:2rem;">

        <div style="font-size:3rem;margin-bottom:1rem;">⏰</div>

        <h1 style="font-family:var(--ff-display);color:var(--gold);font-size:1.8rem;margin-bottom:.5rem;">
            Subscription Expired
        </h1>

        <p style="color:var(--text-2);margin-bottom:2rem;">
            Your trial or subscription has expired. Upgrade your plan to restore full access to LUMIÈRE.
        </p>

        @if(session('error'))
        <div style="background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);color:#f87171;padding:.75rem 1rem;border-radius:8px;margin-bottom:1.5rem;font-size:.85rem;">
            {{ session('error') }}
        </div>
        @endif

        <a href="{{ route('owner.billing') }}" style="display:inline-block;background:var(--gold);color:#000;padding:.75rem 2rem;border-radius:8px;font-weight:600;text-decoration:none;margin-right:.5rem;">
            <i class="bi bi-arrow-up-circle me-1"></i> Upgrade Now
        </a>

        <form method="POST" action="{{ route('logout') }}" style="display:inline;">
            @csrf
            <button type="submit" style="background:transparent;border:1px solid var(--border);color:var(--text-2);padding:.75rem 2rem;border-radius:8px;cursor:pointer;">
                Logout
            </button>
        </form>

    </div>
</div>
@endsection
