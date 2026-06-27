@extends('layouts.customer')

@section('title', $tenant->name . ' — Services')

@section('content')

<div class="container py-5">

    {{-- Header --}}
    <div class="mb-5 text-center">
        <h2 class="serif" style="font-size: clamp(2rem, 5vw, 3rem); font-weight: 300; margin-bottom: 0.5rem;">
            Our <em style="color:var(--teal); font-style:italic;">Curation</em>
        </h2>
        <p style="color:var(--text-3); font-size:0.85rem; letter-spacing:0.1em; text-transform:uppercase;">
            {{ $services->flatten()->count() }} Bespoke Treatments
        </p>
    </div>

    {{-- Services Grid --}}
    <div class="row g-4 justify-content-center">
        @forelse($services->flatten() as $service)
        <div class="col-12 col-md-6 col-lg-4">
            {{-- Card styled with Lux design system --}}
            <article class="card-lux h-100" style="padding: 2rem; transition: transform 0.6s var(--ease), border-color 0.6s; border: 1px solid var(--border);">

                {{-- Category Badge --}}
                @if($service->category)
                <span style="display:inline-block; margin-bottom:1.2rem; font-size:0.6rem; font-weight:600; text-transform:uppercase; letter-spacing:0.2em; color:var(--teal); padding:0.3rem 0.8rem; background:var(--teal-dim); border-radius:20px;">
                    {{ $service->category }}
                </span>
                @endif

                {{-- Service Name --}}
                <h5 class="serif" style="font-size:1.4rem; font-weight:400; margin-bottom:1rem; color:var(--text);">
                    {{ $service->name }}
                </h5>

                {{-- Service Description --}}
                @if($service->description)
                <p style="font-size:0.85rem; color:var(--text-3); line-height:1.8; margin-bottom:2rem;">
                    {{ $service->description }}
                </p>
                @endif

                {{-- Duration & Price (Footer of card) --}}
                <div style="margin-top:auto; pt-2; border-top:1px solid var(--border); padding-top:1.2rem; display:flex; align-items:center; justify-content:space-between;">
                    <div style="font-family:var(--ff-display); font-size:1.3rem; color:var(--gold);">
                        ₹{{ number_format($service->price) }}
                    </div>
                    @if($service->duration_minutes)
                    <div style="font-size:0.75rem; color:var(--text-3); margin-left:auto; display:flex; align-items:center; gap:0.4rem;">
                        <i class="bi bi-clock faint"></i> {{ $service->duration_minutes }} min
                    </div>
                    @endif
                </div>
            </article>
        </div>
        @empty
        <div class="col-12 text-center py-5">
            <i class="bi bi-scissors" style="font-size:2.5rem; opacity:0.2;"></i>
            <p class="mt-3" style="color:var(--text-3);">No services available at the moment.</p>
        </div>
        @endforelse
    </div>

</div>

{{-- Hover effect added via CSS --}}
@push('styles')
<style>
    .card-lux {
        transition: all 0.5s cubic-bezier(0.16, 1, 0.3, 1) !important;
    }

    .card-lux:hover {
        transform: translateY(-8px);
        border-color: var(--gold) !important;
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
    }

</style>
@endpush

@endsection
