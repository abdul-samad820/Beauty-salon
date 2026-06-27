 <section id="services" class="section-pad">
     <div class="container">
         <div class="row justify-content-between align-items-end mb-5">
             <div class="col-lg-6">
                 <div class="section-label reveal" style="--label-color:rgba(255,255,255,0.5);">
                     <span class="label-caps" style="color:rgba(255,255,255,0.5);">What We Offer</span>
                 </div>
                 <h2 class="section-title reveal reveal-delay-1" style="color:white;">
                     Our Signature<br><em style="font-style:italic;color:var(--teal-light);">Services</em>
                 </h2>
             </div>
             <div class="col-lg-4 text-lg-end reveal reveal-delay-2">
                 <p style="color:rgba(255,255,255,0.45);font-size:0.85rem;line-height:1.8;">
                     Each service is a curated experience designed to deliver transformative results with uncompromising luxury.
                 </p>
             </div>
         </div>

         <div class="row g-4">
             @forelse($services as $service)
             @php
             // Category ke hisaab se icon map karo
             $iconClass = match(strtolower($service->category)) {
             'hair' => 'bi-scissors',
             'skin' => 'bi-flower1',
             'nail' => 'bi-palette',
             'massage' => 'bi-water',
             default => 'bi-stars',
             };
             @endphp

             <div class="col-md-6 col-lg-4 reveal">
                 <div class="service-card">
                     <div class="service-num">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</div>

                     {{-- Dynamically assigned icon --}}
                     <div class="service-icon">
                         <i class="bi {{ $iconClass }}"></i>
                     </div>

                     <h3 class="service-title">{{ $service->name }}</h3>
                     <p class="service-desc">{{ $service->description ?? '' }}</p>
                     <div class="service-price">₹{{ number_format($service->price, 0) }} <small>/ session</small></div>
                 </div>
             </div>
             @empty
             <p style="color:rgba(255,255,255,0.5);">No services available.</p>
             @endforelse
         </div>

         <div class="text-center mt-5 reveal">
             <a href="{{route('customer.services', $subdomain) }}" class="btn-outline-luxury light">View All Services <i class="bi bi-arrow-right ms-1"></i></a>
         </div>
     </div>
 </section>
