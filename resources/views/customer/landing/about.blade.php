<section id="about" class="section-pad">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-5">
                <div class="about-image-wrap reveal reveal-left">
                    <img src="https://images.unsplash.com/photo-1522337360788-8b13dee7a37e?w=800&q=85&auto=format&fit=crop" alt="Salon interior" />
                    <div class="about-badge">
                        <strong>{{ date('Y') - \Carbon\Carbon::parse($tenant->created_at)->format('Y') }}</strong>
                        <small>Years<br>Excellence</small>
                    </div>
                    <!-- Corner brackets -->
                    <div class="corner-bracket tl"></div>
                    <div class="corner-bracket br"></div>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="about-content">
                    <div class="section-label reveal">
                        <span class="label-caps teal-text">Our Story</span>
                    </div>
                    <h2 class="section-title reveal reveal-delay-1">
                        A Legacy of<br>Refined <em style="font-style:italic;color:var(--teal);">Beauty</em>
                    </h2>
                    <div class="divider-teal reveal reveal-delay-2"></div>
                    <p class="reveal reveal-delay-2" style="color:rgba(26,26,26,0.65);font-size:0.9rem;line-height:1.9;margin-bottom:2rem;">
                        {{ $tenant->description ?? 'Born from a vision to redefine luxury beauty in its truest form, ' . $tenant->name . ' has been the preferred destination for discerning clientele who seek transformative beauty experiences.' }}
                    </p>

                    <div class="about-feature reveal reveal-delay-3">
                        <div class="feature-icon"><i class="bi bi-gem"></i></div>
                        <div>
                            <div style="font-weight:500;font-size:0.9rem;margin-bottom:0.3rem;">Premium Ingredients Only</div>
                            <div style="color:rgba(26,26,26,0.55);font-size:0.82rem;line-height:1.7;">We source the world's finest botanicals and clinically proven actives for every treatment.</div>
                        </div>
                    </div>
                    <div class="about-feature reveal reveal-delay-4">
                        <div class="feature-icon"><i class="bi bi-award"></i></div>
                        <div>
                            <div style="font-weight:500;font-size:0.9rem;margin-bottom:0.3rem;">Award-Winning Expertise</div>
                            <div style="color:rgba(26,26,26,0.55);font-size:0.82rem;line-height:1.7;">Our specialists are internationally trained and regularly recognized by leading beauty authorities.</div>
                        </div>
                    </div>
                    <div class="about-feature reveal reveal-delay-5">
                        <div class="feature-icon"><i class="bi bi-heart"></i></div>
                        <div>
                            <div style="font-weight:500;font-size:0.9rem;margin-bottom:0.3rem;">Personalized Rituals</div>
                            <div style="color:rgba(26,26,26,0.55);font-size:0.82rem;line-height:1.7;">Every client receives a bespoke consultation and a tailored beauty protocol.</div>
                        </div>
                    </div>

                    <div class="mt-4 reveal reveal-delay-5">
                        <a href="{{route('customer.services', $subdomain)}}" class="btn-luxury">Discover Services <i class="bi bi-arrow-right"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
