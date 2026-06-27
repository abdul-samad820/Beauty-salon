@extends('layouts.auth')

@section('title', '404 — Page Not Found')

@section('content')
<div style="text-align:center;padding:2rem 0;">
    <div style="font-family:var(--ff-display);font-size:clamp(5rem,20vw,9rem);font-weight:300;color:var(--gold);opacity:.3;line-height:1;margin-bottom:1rem;">
        404
    </div>
    <h2 style="font-family:var(--ff-display);font-size:1.6rem;font-weight:300;color:var(--text);margin-bottom:.5rem;">
        Page not found
    </h2>
    <p style="font-size:.8rem;color:var(--text-3);margin-bottom:2rem;">
        The page you are looking for does not exist or has been removed.
    </p>
    <a href="javascript:history.back()" class="btn-lux-gold">
        <i class="bi bi-arrow-left" aria-hidden="true"></i> Go Back
    </a>
</div>
@endsection
