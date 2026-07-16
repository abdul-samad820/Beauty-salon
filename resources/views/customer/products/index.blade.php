@extends('layouts.customer')

@section('title', $tenant->name . ' — Products')

@section('content')

<div class="container py-5">

    {{-- Header --}}
    <div class="mb-5 text-center fade-up s1">
        <a href="{{ route('customer.landing', $subdomain) }}" style="display:inline-flex; align-items:center; gap:0.4rem; font-size:0.75rem; color:var(--text-3); text-decoration:none; text-transform:uppercase; letter-spacing:0.1em; margin-bottom:1.5rem; transition:color 0.3s;" onmouseover="this.style.color='var(--gold)'" onmouseout="this.style.color='var(--text-3)'">
            <i class="bi bi-arrow-left"></i> Back to Home
        </a>
        <h2 class="serif" style="font-size: clamp(2rem, 5vw, 3rem); font-weight: 300; margin-bottom: 0.5rem; color:var(--text);">
            Our <em style="color:var(--gold); font-style:italic;">Products</em>
        </h2>
        <p style="color:var(--text-3); font-size:0.85rem; letter-spacing:0.1em; text-transform:uppercase;">
            {{ $products->count() }} curated items · {{ $tenant->name }}
        </p>
    </div>

    {{-- Products Grid --}}
    <div class="row g-4 justify-content-center fade-up s2">
        @forelse($products as $product)
        <div class="col-12 col-md-6 col-lg-4">
            <article class="card-lux product-card-hover h-100" style="padding: 0; display: flex; flex-direction: column; overflow: hidden; border: 1px solid var(--border);">

                {{-- Image Container --}}
                <div style="height: 240px; background: rgba(255,255,255,0.02); display: flex; align-items: center; justify-content: center; position: relative; overflow: hidden;">
                    @if($product->image)
                    <img src="{{ Storage::disk('cloudinary')->url($product->image) }}" alt="{{ $product->name }}" class="product-img" style="width: 100%; height: 100%; object-fit: cover;" />
                    @else
                    <i class="bi bi-box-seam faint" style="font-size: 3rem; opacity: 0.3;"></i>
                    @endif
                </div>

                {{-- Body --}}
                <div style="padding: 1.5rem; display: flex; flex-direction: column; flex: 1;">

                    {{-- Category Badge --}}
                    @if($product->category)
                    <div style="margin-bottom: 1rem;">
                        <span class="plan-badge" style="background: var(--bg-input); color: var(--text-3); font-size: 0.6rem; letter-spacing: 0.15em; text-transform: uppercase;">
                            {{ ucfirst($product->category) }}
                        </span>
                    </div>
                    @endif

                    {{-- Name --}}
                    <h4 class="serif" style="font-size: 1.2rem; font-weight: 400; color: var(--text); margin-bottom: 1rem;">
                        {{ $product->name }}
                    </h4>

                    {{-- Price & Stock Footer --}}
                    <div style="margin-top: auto; border-top: 1px solid var(--border); padding-top: 1rem; display: flex; align-items: center; justify-content: space-between;">
                        <span class="serif" style="font-size: 1.3rem; color: var(--gold);">
                            ₹{{ number_format($product->price) }}
                        </span>

                        <div style="font-size: 0.7rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;">
                            @if($product->quantity > $product->low_stock_threshold)
                            <span style="color: var(--emerald);"><i class="bi bi-check-circle" style="margin-right: 0.2rem;"></i> In Stock</span>
                            @elseif($product->quantity > 0)
                            <span style="color: var(--amber);"><i class="bi bi-exclamation-circle" style="margin-right: 0.2rem;"></i> Low Stock</span>
                            @else
                            <span style="color: var(--rose);"><i class="bi bi-x-circle" style="margin-right: 0.2rem;"></i> Out of Stock</span>
                            @endif
                        </div>
                    </div>

                </div>
            </article>
        </div>
        @empty
        <div class="col-12 text-center py-5">
            <i class="bi bi-box-seam" style="font-size: 2.5rem; opacity: 0.2;"></i>
            <p class="mt-3" style="color: var(--text-3); font-size: 0.85rem;">No products available at the moment.</p>
        </div>
        @endforelse
    </div>

</div>

@endsection

@push('styles')
<style>
    /* Custom Luxury Hover Effects for Product Cards */
    .product-card-hover {
        transition: transform 0.6s var(--ease), box-shadow 0.6s var(--ease), border-color 0.6s var(--ease) !important;
    }

    .product-card-hover .product-img {
        transition: transform 0.8s var(--ease);
    }

    .product-card-hover:hover {
        transform: translateY(-8px);
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        border-color: var(--gold) !important;
    }

    .product-card-hover:hover .product-img {
        transform: scale(1.08);
    }

</style>
@endpush
