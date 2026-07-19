<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ $tenant->name }} — Luxury Beauty Studio</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400;1,500&family=Jost:wght@200;300;400;500&display=swap" rel="stylesheet" />

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />

    <link rel="stylesheet" href="{{ asset('frontend/css/landing.css') }}">
</head>
<body>

    <!-- ==================== NAVBAR ==================== -->
    @include('customer.landing.navbar')

    <!-- ==================== HERO ==================== -->

    @include('customer.landing.hero')
    <!-- ==================== ABOUT ==================== -->

    @include('customer.landing.about')
    <!-- ==================== SERVICES ==================== -->
    @include('customer.landing.services')

    <!-- ==================== TEAM ==================== -->
    @include('customer.landing.team')

    <!-- ==================== GALLERY ==================== -->
    @include('customer.landing.gallery')

    <!-- ==================== BOOKING CTA ==================== -->
    <section id="booking" class="section-pad-sm">
        <div class="container">
            <div class="booking-content text-center">
                <div class="section-label justify-content-center reveal" style="--label-color:rgba(255,255,255,0.6);">
                    <span class="label-caps" style="color:rgba(255,255,255,0.6);">Reserve Your Experience</span>
                </div>
                <h2 class="booking-title reveal reveal-delay-1">
                    Begin Your<br>Beauty Journey
                </h2>
                <p class="reveal reveal-delay-2" style="color:rgba(255,255,255,0.65);font-size:0.9rem;max-width:420px;margin:0 auto 2.5rem;line-height:1.85;">
                    Reserve your private session with one of our master specialists. Limited appointments available each week.
                </p>
                <div class="d-flex gap-3 justify-content-center flex-wrap reveal reveal-delay-3">
                    <a href="tel:{{ $tenant->phone }}" class="btn-outline-luxury light">
                        <i class="bi bi-telephone"></i> {{ $tenant->phone ?? 'Call Us' }}
                    </a>
                    @auth('customer')
                    <a href="{{ route('customer.home', $subdomain) }}" class="btn-luxury" style="background:white;color:var(--teal);">
                        Book Online <i class="bi bi-calendar-check"></i>
                    </a>
                    @else
                    <a href="{{ route('customer.login', $subdomain) }}" class="btn-luxury" style="background:white;color:var(--teal);">
                        Login to Book <i class="bi bi-calendar-check"></i>
                    </a>
                    @endauth
                </div>
                <div class="mt-4 reveal reveal-delay-4">
                    <p style="color:rgba(255,255,255,0.4);font-size:0.72rem;letter-spacing:0.15em;">
                        Open: {{ \Carbon\Carbon::parse($openTime)->format('g:i A') }} – {{ \Carbon\Carbon::parse($closeTime)->format('g:i A') }}
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- ==================== TESTIMONIALS ==================== -->
    <section id="testimonials" class="section-pad">
        <div class="container">
            <div class="text-center mb-5">
                <div class="section-label justify-content-center reveal">
                    <span class="label-caps teal-text">Client Love</span>
                </div>
                <h2 class="section-title reveal reveal-delay-1">
                    Words of<br><em style="font-style:italic;color:var(--teal);">Devotion</em>
                </h2>
            </div>

            <div class="row g-4">
                @forelse($reviews as $review)
                <div class="col-md-4 reveal reveal-delay-{{ $loop->iteration }}">
                    <div class="testimonial-card">
                        <div class="stars">
                            @for($i = 1; $i <= 5; $i++) {{ $i <= $review->rating ? '★' : '☆' }} @endfor </div>
                                <p class="testimonial-text">"{{ $review->comment }}"</p>
                                <div class="testimonial-author">
                                    <div style="width:48px;height:48px;border-radius:50%;background:var(--teal);display:flex;align-items:center;justify-content:center;color:white;font-family:var(--ff-display);font-size:1.2rem;border:2px solid var(--teal-light);">
                                        {{ strtoupper(substr($review->customer->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="author-name">{{ $review->customer->name }}</div>
                                        <div class="author-label">{{ \Carbon\Carbon::parse($review->created_at)->format('M Y') }}</div>
                                    </div>
                                </div>
                        </div>
                    </div>
                    @empty

                    <div class="col-12 text-center" style="color:rgba(26,26,26,0.4);font-size:0.85rem;padding:2rem 0;">
                        <i class="bi bi-star" style="font-size:2rem;opacity:0.3;"></i>
                        <p class="mt-2">There are no reviews yet.</p>
                    </div>
                    @endforelse
                </div>
            </div>
    </section>

    <!-- ==================== STATISTICS ==================== -->
    <section id="stats" class="section-pad-sm">
        <div class="container">
            <div class="row align-items-center justify-content-center">
                <div class="col-6 col-md-3 reveal reveal-delay-1">
                    <div class="stat-item">
                        <div class="stat-num"><span class="counter" data-target="{{ $totalAppointments }}">0</span><span class="suffix">+</span></div>
                        <div class="stat-label">Appointments Done</div>
                    </div>
                </div>
                <div class="col-12 col-md-auto d-none d-md-block reveal">
                    <div class="stat-divider"></div>
                </div>
                <div class="col-6 col-md-3 reveal reveal-delay-2">
                    <div class="stat-item">
                        <div class="stat-num"><span class="counter" data-target="{{ $totalReviews }}">0</span><span class="suffix">+</span></div>
                        <div class="stat-label">Happy Clients</div>
                    </div>
                </div>
                <div class="col-12 col-md-auto d-none d-md-block reveal">
                    <div class="stat-divider"></div>
                </div>
                <div class="col-6 col-md-3 reveal reveal-delay-3">
                    <div class="stat-item">
                        <div class="stat-num"><span class="counter" data-target="{{ $totalStaff }}">0</span><span class="suffix">+</span></div>
                        <div class="stat-label">Expert Specialists</div>
                    </div>
                </div>
                <div class="col-12 col-md-auto d-none d-md-block reveal">
                    <div class="stat-divider"></div>
                </div>
                <div class="col-6 col-md-3 reveal reveal-delay-4">
                    <div class="stat-item">
                        <div class="stat-num"><span class="counter" data-target="{{ round($avgRating * 10) }}">0</span><span class="suffix">/50</span></div>
                        <div class="stat-label">Avg Rating</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ==================== PRODUCTS ==================== -->
    <section id="products" class="section-pad">
        <div class="container">
            <div class="row justify-content-between align-items-end mb-5">
                <div class="col-lg-5">
                    <div class="section-label reveal">
                        <span class="label-caps teal-text">Our Curation</span>
                    </div>
                    <h2 class="section-title reveal reveal-delay-1">
                        Luxury<br><em style="font-style:italic;color:var(--teal);">Products</em>
                    </h2>
                </div>
                <div class="col-lg-4 text-lg-end reveal reveal-delay-2">
                    <a href="{{ route('customer.products', $subdomain) }}" class="btn-outline-luxury">Shop Collection <i class="bi bi-arrow-right"></i></a>
                </div>
            </div>

            <div class="row g-4">
                @forelse($products as $product)
                <div class="col-sm-6 col-lg-3 reveal">
                    <div class="product-card">
                        <div class="product-img-wrap">
                        <img src="{{ $product->image ? cloudinary()->image($product->image)->toUrl() : 'https://images.unsplash.com/photo-1620916566398-39f1143ab7be?w=500&q=80' }}" alt="{{ $product->name }}" />
                        </div>
                        <div class="product-body">
                            <div class="product-sub">{{ $product->category ?? '' }}</div>
                            <div class="product-name">{{ $product->name }}</div>
                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <div class="product-price">₹{{ $product->price }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <p style="color:rgba(26,26,26,0.5);">No products available.</p>
                @endforelse
            </div>
        </div>
    </section>

    <!-- ==================== FOOTER ==================== -->
    @include('customer.landing.footer')

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    @include('customer.landing.script')

</body>
</html>
