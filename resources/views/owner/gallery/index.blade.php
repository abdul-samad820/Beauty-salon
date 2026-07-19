@extends('layouts.owner')

@section('title', 'Gallery')
@section('page-title', 'Gallery Management')
@section('breadcrumb', 'Workspace / Gallery')

@section('topbar-actions')
<button class="btn-lux-gold btn-sm" onclick="LuxModal.open('addImageModal')" style="padding: 0.5rem 1rem;">
    <i class="bi bi-plus-lg me-1"></i> Upload Image
</button>
@endsection

@section('content')

{{-- Stats --}}
<div class="mb-4 fade-up s1">
    <x-cards.stat-row :stats="[
        ['label' => 'Total Images', 'value' => $images->count(), 'color' => 'var(--gold)'],
    ]" />
</div>

{{-- Gallery Grid --}}
<div class="card-lux fade-up s2 p-4">
    @if($images->count() > 0)
    <div class="row g-4" id="galleryGrid">
        @foreach($images as $image)
        <div class="col-6 col-md-4 col-lg-3 gallery-item" data-id="{{ $image->id }}">

            <div class="gallery-card">
                {{-- Image --}}
             <img src="{{ cloudinary()->image($image->image)->toUrl() }}" alt="{{ $image->caption ?? 'Gallery Image' }}" />

                {{-- Hover Overlay --}}
                <div class="gallery-overlay">
                    {{-- Top Controls --}}
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="drag-handle" title="Drag to reorder">
                            <i class="bi bi-arrows-move"></i>
                        </div>

                        {{-- FIX: <form> hata diya gaya hai. Ab custom JS bypass use hoga --}}
                        <button type="button" class="delete-btn" title="Delete Image" onclick="deleteGalleryImage('{{ route('owner.gallery.destroy', $image->id) }}', this)">
                            <i class="bi bi-trash3"></i>
                        </button>
                    </div>

                    {{-- Bottom Caption --}}
                    @if($image->caption)
                    <div class="gallery-caption">
                        {{ $image->caption }}
                    </div>
                    @endif
                </div>
            </div>

        </div>
        @endforeach
    </div>
    @else
    {{-- Premium Empty State --}}
    <div style="text-align:center; padding:5rem 2rem;">
        <div style="width: 72px; height: 72px; background: rgba(255,255,255,0.02); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
            <i class="bi bi-images faint" style="font-size: 2.5rem;"></i>
        </div>
        <h4 style="font-size: 1.1rem; color: var(--text); font-weight: 500; margin-bottom: 0.3rem;">Your Portfolio is Empty</h4>
        <p class="faint" style="font-size:0.85rem; margin-bottom: 1.5rem;">Showcase your best work by uploading your first image.</p>
        <button class="btn-lux-gold btn-sm mx-auto" onclick="LuxModal.open('addImageModal')">
            <i class="bi bi-cloud-arrow-up me-1"></i> Upload First Image
        </button>
    </div>
    @endif
</div>

{{-- Upload Modal --}}
<x-cards.modal id="addImageModal" title="Upload Gallery Image">
    <form method="POST" action="{{ route('owner.gallery.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="row g-3">
            <div class="col-12">
                <label class="lux-label">Select Image <span class="text-danger">*</span></label>
                <div class="upload-area" style="border: 1px dashed var(--border-2); padding: 1.5rem; text-align: center; border-radius: 8px; background: var(--bg-input);">
                    <input type="file" name="image" accept="image/*" class="form-control bg-transparent text-white border-0" required />
                </div>
            </div>
            <div class="col-12 mt-3">
                <x-forms.input name="caption" label="Caption (Optional)" placeholder="e.g. Premium Bridal Makeup Session" />
            </div>
        </div>

        <div style="margin-top:2rem; display:flex; align-items:center; justify-content:flex-end; gap:0.75rem; border-top:1px solid var(--border); padding-top:1.2rem;">
            <button type="button" onclick="LuxModal.close('addImageModal')" class="btn-lux-ghost btn-sm border-0">Cancel</button>
            <button type="submit" class="btn-lux-gold btn-sm">Upload to Gallery</button>
        </div>
    </form>
</x-cards.modal>

@endsection

@push('styles')
<style>
    /* Premium Gallery Card Styles */
    .gallery-card {
        position: relative;
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid var(--border);
        aspect-ratio: 4 / 3;
        background: var(--bg-input);
    }

    .gallery-card img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
        transition: transform 0.5s ease;
    }

    .gallery-card:hover img {
        transform: scale(1.08);
        /* Subtle zoom effect */
    }

    /* Gradient Overlay on Hover */
    .gallery-overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, rgba(0, 0, 0, 0.5) 0%, transparent 40%, rgba(0, 0, 0, 0.8) 100%);
        opacity: 0;
        transition: opacity 0.3s ease;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        padding: 0.8rem;
    }

    .gallery-card:hover .gallery-overlay {
        opacity: 1;
    }

    /* Controls */
    .drag-handle {
        color: white;
        cursor: grab;
        background: rgba(0, 0, 0, 0.4);
        backdrop-filter: blur(4px);
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid rgba(255, 255, 255, 0.1);
        transition: background 0.2s;
    }

    .drag-handle:active {
        cursor: grabbing;
    }

    .drag-handle:hover {
        background: var(--gold);
        color: var(--charcoal);
        border-color: var(--gold);
    }

    .delete-btn {
        background: rgba(244, 63, 94, 0.8);
        backdrop-filter: blur(4px);
        color: white;
        border: 1px solid rgba(255, 255, 255, 0.1);
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
    }

    .delete-btn:hover {
        background: #e11d48;
        transform: scale(1.05);
    }

    /* Caption Styling */
    .gallery-caption {
        color: white;
        font-size: 0.8rem;
        font-weight: 500;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.8);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        padding: 0 0.2rem;
    }

</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const grid = document.getElementById('galleryGrid');
        if (grid) {
            Sortable.create(grid, {
                animation: 250
                , handle: '.drag-handle'
                , ghostClass: 'opacity-50'
                , onEnd: function() {
                    const order = [...grid.querySelectorAll('.gallery-item')].map(el => el.dataset.id);

                    fetch('{{ route("owner.gallery.reorder") }}', {
                        method: 'POST'
                        , headers: {
                            'Content-Type': 'application/json'
                            , 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        , }
                        , body: JSON.stringify({
                            order
                        })
                    , });
                }
            });
        }
    });

    // FIX: Custom Delete Function (Bypasses buggy global "Saving..." scripts)
    function deleteGalleryImage(url, btnElement) {
        if (!confirm('Are you sure you want to delete this image?')) return;

        // Show a clean loading state (hides the trash icon, shows a spinner)
        let originalHtml = btnElement.innerHTML;
        btnElement.innerHTML = '<span class="spinner-border spinner-border-sm" style="width: 1rem; height: 1rem; border-width: 0.15em;"></span>';
        btnElement.disabled = true;

        fetch(url, {
                method: 'POST'
                , headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    , 'X-Requested-With': 'XMLHttpRequest'
                    , 'Accept': 'application/json'
                }
                , body: new URLSearchParams({
                    '_method': 'DELETE'
                })
            })
            .then(response => {
                if (response.ok) {
                    window.location.reload(); // Force page refresh to update the grid
                } else {
                    alert('Something went wrong while deleting. Please try again.');
                    btnElement.innerHTML = originalHtml;
                    btnElement.disabled = false;
                }
            })
            .catch(error => {
                console.error(error);
                alert('Server error occurred.');
                btnElement.innerHTML = originalHtml;
                btnElement.disabled = false;
            });
    }

</script>
@endpush
