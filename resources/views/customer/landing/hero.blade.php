<section id="hero">
      <div class="hero-bg parallax-img" data-parallax="0.3" style="background-image: url('{{ $tenant->hero_image ? cloudinary()->image($tenant->hero_image)->toUrl() : 'https://images.unsplash.com/photo-1560066984-138dadb4c035?w=1800&q=85&auto=format&fit=crop' }}');"></div>
      <div class="hero-overlay"></div>
      <div class="float-orb orb-teal"></div>
      <div class="float-orb orb-gold"></div>

      <div class="container hero-content">
          <div class="row">
              <div class="col-lg-8 col-xl-7">
                  <div class="hero-eyebrow reveal">{{ $tenant->name }} · Est. {{ \Carbon\Carbon::parse($tenant->created_at)->format('Y') }}</div>
                  <h1 class="hero-title reveal reveal-delay-1">
                      Where Beauty<br>Becomes <em>Art</em>
                  </h1>
                  <p class="hero-subtitle reveal reveal-delay-2">
                      An elevated sanctuary of beauty, curated for those who demand nothing less than perfection in every detail.
                  </p>
                  <div class="hero-cta reveal reveal-delay-3">
                      @auth('customer')
                      <a href="{{ route('customer.home', $subdomain) }}" class="btn-luxury">
                          Book Appointment
                          @else
                          <a href="{{ route('customer.login', $subdomain) }}" class="btn-luxury">
                              Login to Book
                              @endauth
                              <i class="bi bi-arrow-right"></i>
                          </a>
                          <a href="{{route('customer.services', $subdomain)}}" class="btn-outline-luxury light">
                              Explore Services
                          </a>
                  </div>
              </div>
          </div>
      </div>

      <!-- Side decoration -->
      <div class="hero-side">
          <div class="hero-social">
              <a href="{{ $tenant->instagram_url ?? '#' }}"><i class="bi bi-instagram"></i></a>
              <a href="{{ $tenant->facebook_url ?? '#' }}"><i class="bi bi-facebook"></i></a>
          </div>
          <div class="hero-side-text">Follow the beauty journey</div>
      </div>

      <!-- Scroll indicator -->
      <div class="hero-scroll">
          <div class="scroll-line"></div>
          <span>Scroll</span>
      </div>

      <!-- Floating stat card -->
      <div style="position:absolute; bottom:3rem; right:8%; z-index:2;" class="d-none d-lg-block reveal reveal-right">
          <div class="glass-card p-3" style="min-width:180px;">
              <div class="d-flex align-items-center gap-2 mb-2">
                  <div style="width:8px;height:8px;border-radius:50%;background:var(--teal-light);animation:pulse 2s infinite;"></div>
                  <span style="color:rgba(255,255,255,0.5);font-size:0.62rem;letter-spacing:0.15em;">BOOKING OPEN</span>
              </div>
              <div style="font-family:var(--ff-display);font-size:1.3rem;color:white;font-weight:300;">Next available</div>
              <div style="color:var(--teal-light);font-size:0.75rem;letter-spacing:0.1em;">
                  {{ $nextSlot ? 'Today · ' . $nextSlot : 'No slots today' }}
              </div>
          </div>
      </div>
  </section>