  <footer id="footer" class="pt-5 pb-4">
      <div class="container">
          <div class="row g-5">
              <!-- Brand -->
              <div class="col-lg-3">
                  <div class="d-flex align-items-center gap-3 mb-4">
                      <a class="footer-logo mb-0" href="{{ route('customer.landing', $subdomain) }}">{{ $tenant->name }}<span>.</span></a>

                      @auth('customer')
                      <span style="color: rgba(255,255,255,0.2); font-size: 1.2rem; font-weight: 300;">|</span>
                      <form action="{{ route('customer.logout', $subdomain) }}" method="POST" class="m-0 p-0">
                          @csrf
                          <button type="submit" style="color: rgba(255,255,255,0.5); text-decoration: none; border: none; background: transparent; padding: 0; font-size: 0.8rem; letter-spacing: 0.1em; text-transform: uppercase; cursor: pointer; transition: color 0.3s;" onmouseover="this.style.color='var(--teal-light)'" onmouseout="this.style.color='rgba(255,255,255,0.5)'">
                              Logout
                          </button>
                      </form>
                      @endauth
                  </div>

                  <p class="footer-tagline">A sanctuary of beauty, where artistry and luxury converge into an unparalleled experience.</p>

                  <div class="footer-social">
                      {{-- Icons will always show. If no URL in DB, it redirects to '#' --}}
                      <a href="{{ $tenant->instagram_url ?? '#' }}" target="_blank" rel="noopener" title="Instagram">
                          <i class="bi bi-instagram"></i>
                      </a>

                      <a href="{{ $tenant->facebook_url ?? '#' }}" target="_blank" rel="noopener" title="Facebook">
                          <i class="bi bi-facebook"></i>
                      </a>
                  </div>
              </div>

              <!-- Quick Links -->
              <div class="col-6 col-lg-2">
                  <div class="footer-heading">Services</div>
                  <ul class="footer-links">
                      @foreach($services->take(6) as $service)
                      <li><a href="{{ route('customer.services', $subdomain) }}">{{ $service->name }}</a></li>
                      @endforeach
                  </ul>
              </div>
              <div class="col-6 col-lg-2">
                  <div class="footer-heading">Studio</div>
                  <ul class="footer-links">
                      <li><a href="#about">Our Story</a></li>
                      <li><a href="{{ route('customer.gallery', $subdomain) }}">Gallery</a></li>
                      <li><a href="{{ route('customer.products', $subdomain) }}">Products</a></li>

                  </ul>
              </div>

              <!-- Contact -->
              <div class="col-lg-2">
                  <div class="footer-heading">Contact</div>
                  <ul class="footer-links footer-contact list-unstyled">
                      <li><i class="bi bi-geo-alt-fill"></i><span>{{ $tenant->address ?? '—' }}</span></li>
                      <li><i class="bi bi-telephone-fill"></i><span>{{ $tenant->phone ?? '—' }}</span></li>
                      <li><i class="bi bi-envelope-fill"></i><span>{{ $tenant->email ?? '—' }}</span></li>
                      <li><i class="bi bi-clock-fill"></i><span>{{ \Carbon\Carbon::parse($openTime)->format('g:i A') }} – {{ \Carbon\Carbon::parse($closeTime)->format('g:i A') }}</span></li>
                  </ul>
              </div>

              <!-- Newsletter -->
              <div class="col-lg-3">
                  <div class="footer-heading">Ready to Visit?</div>
                  <p style="color:rgba(255,255,255,0.35);font-size:0.78rem;line-height:1.7;margin-bottom:1.2rem;">
                      Book your next appointment with us and experience luxury beauty care.
                  </p>
                  @auth('customer')
                  <a href="{{ route('customer.home', $subdomain) }}" class="btn-luxury w-100 justify-content-center">
                      Book Now <i class="bi bi-arrow-right"></i>
                  </a>
                  @endauth
              </div>
          </div>

          <hr class="footer-divider" />

          <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
              <!-- Copyright Text -->
              <div class="footer-bottom">
                  © {{ date('Y') }} {{ $tenant->name }}. All rights reserved.
              </div>
          </div>
      </div>
  </footer>
