 <section id="gallery" class="section-pad">
     <div class="container-fluid px-4">
         <div class="row mb-5">
             <div class="col-lg-6">
                 <div class="section-label reveal">
                     <span class="label-caps" style="color:rgba(255,255,255,0.5);">Our Work</span>
                 </div>
                 <h2 class="section-title reveal reveal-delay-1" style="color:white;">
                     The Art of<br><em style="font-style:italic;color:var(--teal-light);">Transformation</em>
                 </h2>
             </div>
         </div>

         <div class="gallery-grid reveal">
             @forelse($gallery as $index => $image)
             <div class="gallery-item g{{ $index + 1 }}">
               <img src="{{ cloudinary()->image($image->image)->toUrl() }}" alt="{{ $image->caption ?? 'Gallery' }}" />
                 <div class="g-overlay"><i class="bi bi-zoom-in g-icon"></i></div>
             </div>
             @empty

             <div class="gallery-item g1">
                 <img src="https://images.unsplash.com/photo-1616394584738-fc6e612e71b9?w=800&q=80" alt="Gallery" />
                 <div class="g-overlay"><i class="bi bi-zoom-in g-icon"></i></div>
             </div>
             <div class="gallery-item g2">
                 <img src="https://images.unsplash.com/photo-1600334089648-b0d9d3028eb2?w=700&q=80" alt="Gallery" />
                 <div class="g-overlay"><i class="bi bi-zoom-in g-icon"></i></div>
             </div>
             <div class="gallery-item g3">
                 <img src="https://images.unsplash.com/photo-1522338242992-e1a54906a8da?w=700&q=80" alt="Gallery" />
                 <div class="g-overlay"><i class="bi bi-zoom-in g-icon"></i></div>
             </div>
             <div class="gallery-item g4">
                 <img src="https://images.unsplash.com/photo-1503236823255-94609f598e71?w=700&q=80" alt="Gallery" />
                 <div class="g-overlay"><i class="bi bi-zoom-in g-icon"></i></div>
             </div>
             <div class="gallery-item g5">
                 <img src="https://images.unsplash.com/photo-1487412912498-0447578fcca8?w=700&q=80" alt="Gallery" />
                 <div class="g-overlay"><i class="bi bi-zoom-in g-icon"></i></div>
             </div>
             <div class="gallery-item g6">
                 <img src="https://images.unsplash.com/photo-1596755094514-f87e34085b2c?w=700&q=80" alt="Gallery" />
                 <div class="g-overlay"><i class="bi bi-zoom-in g-icon"></i></div>
             </div>
             <div class="gallery-item g7">
                 <img src="https://images.unsplash.com/photo-1457972729786-0411a3b2b626?w=700&q=80" alt="Gallery" />
                 <div class="g-overlay"><i class="bi bi-zoom-in g-icon"></i></div>
             </div>
             @endforelse
         </div>
     </div>
 </section>
