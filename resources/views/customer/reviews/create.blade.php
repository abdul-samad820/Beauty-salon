@extends('layouts.customer')

@section('title', 'Leave a Review')

@section('content')

<div class="container py-4" style="max-width:600px;">

    {{-- Header --}}
    <div class="mb-4 fade-up s1">
        <a href="{{ route('customer.appointments', $subdomain) }}" style="font-size:0.75rem;color:var(--text-3);text-decoration:none;">
            <i class="bi bi-arrow-left"></i> Back to Appointments
        </a>
        <h2 class="serif mt-2" style="font-size:1.4rem;color:var(--text);">Leave a Review</h2>
        <p style="font-size:0.75rem;color:var(--text-3);">{{ $appointment->service?->name }} · {{ \Carbon\Carbon::parse($appointment->appointment_date)->format('d M Y') }}</p>
    </div>

    {{-- Form --}}
    <div class="fade-up s2" style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--r-md);padding:1.8rem;">

        @if($errors->any())
        <div class="alert alert-danger mb-3" style="font-size:0.78rem;">
            @foreach($errors->all() as $error)
            <div>{{ $error }}</div>
            @endforeach
        </div>
        @endif

        <form action="{{ route('customer.review.store', [$subdomain, $appointment->id]) }}" method="POST">
            @csrf

            {{-- Star Rating --}}
            <div class="mb-4">
                <label style="font-size:0.75rem;font-weight:500;color:var(--text-2);letter-spacing:0.1em;text-transform:uppercase;">Rating</label>
                <div class="d-flex gap-2 mt-2" id="starContainer">
                    @for($i = 1; $i <= 5; $i++) <i class="bi bi-star" data-value="{{ $i }}" style="font-size:1.8rem;cursor:pointer;color:var(--gold);transition:transform 0.2s;" onclick="setRating({{ $i }})" onmouseover="hoverRating({{ $i }})" onmouseout="resetHover()">
                        </i>
                        @endfor
                </div>
                <input type="hidden" name="rating" id="ratingInput" value="{{ old('rating', 5) }}" />
            </div>

            {{-- Comment --}}
            <div class="mb-4">
                <label style="font-size:0.75rem;font-weight:500;color:var(--text-2);letter-spacing:0.1em;text-transform:uppercase;">Your Review</label>
                <textarea name="comment" rows="4" placeholder="Share your experience... (min 10 characters)" style="width:100%;margin-top:0.5rem;background:var(--bg-input);border:1px solid var(--border-2);border-radius:var(--r-sm);color:var(--text);font-family:var(--ff-body);font-size:0.82rem;padding:0.8rem 1rem;outline:none;resize:vertical;transition:border-color 0.3s;" onfocus="this.style.borderColor='var(--gold)'" onblur="this.style.borderColor='var(--border-2)'">{{ old('comment') }}</textarea>
                @error('comment')
                <div style="font-size:0.7rem;color:var(--rose);margin-top:0.3rem;">{{ $message }}</div>
                @enderror
            </div>

            {{-- Submit --}}
            <button type="submit" class="btn-lux-gold w-100 justify-content-center">
                <i class="bi bi-send"></i> Submit Review
            </button>

        </form>
    </div>

</div>

@endsection

@push('scripts')
<script>
    let currentRating = {
        {
            old('rating', 5)
        }
    };

    function setRating(val) {
        currentRating = val;
        document.getElementById('ratingInput').value = val;
        updateStars(val);
    }

    function hoverRating(val) {
        updateStars(val);
    }

    function resetHover() {
        updateStars(currentRating);
    }

    function updateStars(val) {
        document.querySelectorAll('#starContainer i').forEach((star, i) => {
            star.classList.toggle('bi-star-fill', i < val);
            star.classList.toggle('bi-star', i >= val);
            star.style.transform = i < val ? 'scale(1.1)' : 'scale(1)';
        });
    }

    // Init
    updateStars(currentRating);

</script>
@endpush
