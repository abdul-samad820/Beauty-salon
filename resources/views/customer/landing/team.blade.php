<section id="team" class="team-ov-section">
    <div class="container">

        <div class="text-center mb-5">
            <div class="section-label justify-content-center reveal">
                <span class="label-caps teal-text">Meet The Artists</span>
            </div>
            <h2 class="section-title reveal reveal-delay-1">
                Our Beauty<br><em class="text-teal-italic">Specialists</em>
            </h2>
        </div>

        <div class="row g-4 gy-5">
            @forelse($staff as $member)
            <div class="col-12 col-md-6 col-lg-4 reveal">
                <div class="team-ov-card">

                    <div class="team-ov-card__photo-wrap">
                        @if($member->user->profile_photo)
                        <img
                            src="{{ cloudinary()->image($member->user->profile_photo)->toUrl() }}"
                            alt="{{ $member->user->name }}"
                            class="team-ov-card__photo"
                        />
                        @else
                        <div class="team-ov-card__photo-fallback">
                            {{ strtoupper(substr($member->user->name, 0, 1)) }}
                        </div>
                        @endif
                    </div>

                    <div class="team-ov-card__body">
                        <h3 class="team-ov-card__name">{{ $member->user->name }}</h3>

                        <p class="team-ov-card__role">
                            {{ is_array($member->specializations) ? implode(', ', $member->specializations) : ($member->specializations ?? 'Specialist') }}
                            @if($member->user->phone)
                                &nbsp;|&nbsp;{{ $member->user->phone }}
                            @endif
                        </p>

                        <div class="team-ov-card__socials">
                            <a href="tel:{{ $member->user->phone }}"><i class="bi bi-telephone-fill"></i></a>
                        </div>
                    </div>

                </div>
            </div>
            @empty
            <div class="col-12 text-center" style="color:rgba(26,26,26,0.4);padding:2rem 0;">
                <i class="bi bi-people" style="font-size:2rem;opacity:0.3;"></i>
                <p class="mt-2" style="font-size:0.85rem;">No staff available.</p>
            </div>
            @endforelse
        </div>

    </div>
</section>