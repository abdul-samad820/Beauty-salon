 <section id="team" class="section-pad">
     <div class="container">
         <div class="text-center mb-5">
             <div class="section-label justify-content-center reveal">
                 <span class="label-caps teal-text">Meet The Artists</span>
             </div>
             <h2 class="section-title reveal reveal-delay-1">
                 Our Beauty<br><em style="font-style:italic;color:var(--teal);">Specialists</em>
             </h2>
         </div>

         <div class="row g-4">
             @forelse($staff as $member)
             <div class="col-sm-6 col-lg-3 reveal">
                 <div class="testimonial-card" style="text-align:center;padding:2rem 1.5rem;">

                     {{-- Avatar initials --}}
                     <div style="width:70px;height:70px;border-radius:50%;background:var(--teal);display:flex;align-items:center;justify-content:center;color:white;font-family:var(--ff-display);font-size:1.8rem;font-weight:300;margin:0 auto 1.2rem;border:2px solid var(--teal-light);">
                         {{ strtoupper(substr($member->user->name, 0, 1)) }}
                     </div>

                     {{-- Name --}}
                     <div style="font-family:var(--ff-display);font-size:1.3rem;font-weight:400;color:var(--charcoal);margin-bottom:0.3rem;">
                         {{ $member->user->name }}
                     </div>

                     {{-- Role --}}
                     <div style="font-size:0.65rem;font-weight:500;letter-spacing:0.2em;text-transform:uppercase;color:var(--teal);margin-bottom:1rem;">
                         {{ is_array($member->specializations) ? implode(', ', $member->specializations) : ($member->specializations ?? 'Specialist') }}
                     </div>

                     {{-- Divider --}}
                     <div style="width:30px;height:1px;background:var(--teal);margin:0 auto 1rem;"></div>


                     {{-- Available badge --}}
                     <div style="display:inline-flex;align-items:center;gap:0.4rem;background:rgba(45,125,111,0.1);padding:0.3rem 0.8rem;border-radius:20px;font-size:0.65rem;color:var(--teal);">
                         <div style="width:6px;height:6px;border-radius:50%;background:var(--teal);"></div>
                         Available
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
