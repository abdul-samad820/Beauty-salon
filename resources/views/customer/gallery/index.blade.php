@extends('layouts.customer')

@section('title', $tenant->name . ' — Gallery')

@section('content')

<div class="container py-5">

    {{-- Header --}}
    <div class="mb-5 text-center fade-up s1">
        <a href="{{ route('customer.landing', $subdomain) }}" style="display:inline-flex; align-items:center; gap:0.4rem; font-size:0.75rem; color:var(--text-3); text-decoration:none; text-transform:uppercase; letter-spacing:0.1em; margin-bottom:1.5rem; transition:color 0.3s;" onmouseover="this.style.color='var(--gold)'" onmouseout="this.style.color='var(--text-3)'">
            <i class="bi bi-arrow-left"></i> Back to Home
        </a>
        <h2 class="serif" style="font-size: clamp(2rem, 5vw, 3rem); font-weight: 300; margin-bottom: 0.5rem; color:var(--text);">
            Our <em style="color:var(--gold); font-style:italic;">Gallery</em>
        </h2>
        <p style="color:var(--text-3); font-size:0.85rem; letter-spacing:0.1em; text-transform:uppercase;">
            {{ $gallery->count() }} Captured Moments
        </p>
    </div>

    {{-- Gallery Section --}}
    @if($gallery->count() > 0)
    <div class="fade-up s2">

        {{-- Collapse Wrapper --}}
        <div id="galleryWrapper" class="gallery-wrapper collapsed">

            {{-- Masonry Grid --}}
            <div class="masonry-grid">
                @foreach($gallery as $image)
                <div class="masonry-item" onclick="openLightbox('{{ Storage::disk('cloudinary')->url($image->image) }}', '{{ $image->caption ?? '' }}')">
                    <img src="{{ Storage::disk('cloudinary')->url($image->image) }}" alt="{{ $image->caption ?? 'Gallery Image' }}" loading="lazy" />

                    {{-- Hover Overlay --}}
                    <div class="gallery-overlay">
                        <i class="bi bi-zoom-in"></i>
                    </div>

                    {{-- Caption --}}
                    @if($image->caption)
                    <div class="gallery-caption">
                        {{ $image->caption }}
                    </div>
                    @endif
                </div>
                @endforeach
            </div>

            {{-- Gradient Fade for Collapsed State --}}
            <div class="gallery-fade"></div>
        </div>

        {{-- Toggle Button --}}
        @if($gallery->count() > 6)
        <div class="text-center mt-5">
            <button id="toggleBtn" onclick="toggleGallery()" class="btn-lux-ghost" style="padding: 0.8rem 2.5rem; font-size: 0.75rem; letter-spacing: 0.2em; text-transform: uppercase;">
                <i class="bi bi-chevron-down me-2" id="toggleIcon"></i> <span id="toggleText">View More</span>
            </button>
        </div>
        @endif

    </div>
    @else
    <div style="text-align:center; padding:5rem 1rem;" class="fade-up s2">
        <i class="bi bi-images" style="font-size:3rem; opacity:0.1; color:var(--text);"></i>
        <p class="mt-3" style="font-size:0.85rem; color:var(--text-3); letter-spacing: 0.1em; text-transform: uppercase;">The canvas is currently empty.</p>
    </div>
    @endif

</div>

{{-- Premium Lightbox --}}
<div id="lightbox" class="lightbox-overlay" onclick="closeLightbox()">
    <button class="lightbox-close" onclick="closeLightbox()" aria-label="Close">
        <i class="bi bi-x-lg"></i>
    </button>
    <div class="lightbox-content" onclick="event.stopPropagation()">
        <img id="lightboxImg" src="" alt="Expanded View" />
        <div id="lightboxCaption" class="lightbox-caption-text"></div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Expand/Collapse Logic
    function toggleGallery() {
        const wrapper = document.getElementById('galleryWrapper');
        const icon = document.getElementById('toggleIcon');
        const text = document.getElementById('toggleText');

        if (wrapper.classList.contains('collapsed')) {
            wrapper.classList.remove('collapsed');
            wrapper.classList.add('expanded');
            icon.classList.remove('bi-chevron-down');
            icon.classList.add('bi-chevron-up');
            text.textContent = 'Collapse';
        } else {
            wrapper.classList.remove('expanded');
            wrapper.classList.add('collapsed');
            icon.classList.remove('bi-chevron-up');
            icon.classList.add('bi-chevron-down');
            text.textContent = 'View More';

            // Scroll back to top of gallery smoothly
            setTimeout(() => {
                wrapper.scrollIntoView({
                    behavior: 'smooth'
                    , block: 'start'
                });
            }, 300);
        }
    }

    // Lightbox Logic
    function openLightbox(src, caption) {
        const lb = document.getElementById('lightbox');
        document.getElementById('lightboxImg').src = src;
        document.getElementById('lightboxCaption').textContent = caption;

        lb.style.display = 'flex';
        // Small delay to trigger CSS transition
        setTimeout(() => lb.classList.add('show'), 10);
        document.body.style.overflow = 'hidden'; // Prevent background scrolling
    }

    function closeLightbox() {
        const lb = document.getElementById('lightbox');
        lb.classList.remove('show');

        // Wait for transition to finish before hiding
        setTimeout(() => {
            lb.style.display = 'none';
            document.body.style.overflow = '';
        }, 400);
    }

    // Close lightbox on Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === "Escape" && document.getElementById('lightbox').style.display === 'flex') {
            closeLightbox();
        }
    });

</script>
@endpush
