  <script>
      /* =====================================================
       NAVBAR SCROLL EFFECT
    ===================================================== */
      const nav = document.getElementById('mainNav');
      window.addEventListener('scroll', () => {
          nav.classList.toggle('scrolled', window.scrollY > 60);
      }, {
          passive: true
      });

      /* =====================================================
         HERO CINEMATIC LOAD
      ===================================================== */
      window.addEventListener('load', () => {
          document.getElementById('hero').classList.add('loaded');
          // Animate hero elements immediately
          document.querySelectorAll('#hero .reveal').forEach((el, i) => {
              setTimeout(() => el.classList.add('visible'), i * 150 + 200);
          });
      });

      /* =====================================================
         SCROLL REVEAL
      ===================================================== */
      const revealObserver = new IntersectionObserver((entries) => {
          entries.forEach(entry => {
              if (entry.isIntersecting) {
                  entry.target.classList.add('visible');
                  revealObserver.unobserve(entry.target);
              }
          });
      }, {
          threshold: 0.12
          , rootMargin: '0px 0px -60px 0px'
      });

      document.querySelectorAll('.reveal').forEach(el => {
          // Skip hero items — handled on load
          if (!el.closest('#hero')) revealObserver.observe(el);
      });

      /* =====================================================
         ANIMATED COUNTERS
      ===================================================== */
      function animateCounter(el) {
          const target = parseInt(el.dataset.target);
          const duration = 1800;
          const step = target / (duration / 16);
          let current = 0;
          const interval = setInterval(() => {
              current = Math.min(current + step, target);
              el.textContent = Math.floor(current).toLocaleString();
              if (current >= target) clearInterval(interval);
          }, 16);
      }

      const counterObserver = new IntersectionObserver((entries) => {
          entries.forEach(entry => {
              if (entry.isIntersecting) {
                  animateCounter(entry.target);
                  counterObserver.unobserve(entry.target);
              }
          });
      }, {
          threshold: 0.5
      });

      document.querySelectorAll('.counter').forEach(el => counterObserver.observe(el));

      /* =====================================================
         PARALLAX ON HERO BG
      ===================================================== */
      const heroBg = document.querySelector('.hero-bg');
      if (heroBg) {
          window.addEventListener('scroll', () => {
              const scrolled = window.scrollY;
              const heroH = document.getElementById('hero').offsetHeight;
              if (scrolled < heroH) {
                  const offset = scrolled * 0.35;
                  heroBg.style.transform = `scale(1.0) translateY(${offset}px)`;
              }
          }, {
              passive: true
          });
      }

      /* =====================================================
         SMOOTH FLOATING EFFECT ON CARDS
      ===================================================== */
      document.querySelectorAll('.service-card, .product-card, .testimonial-card').forEach((card, i) => {
          card.style.animation = `floatCard ${3.5 + (i % 3) * 0.5}s ease-in-out infinite alternate`;
          card.style.animationDelay = `${(i * 0.3) % 2}s`;
      });

      // Inject keyframes for float
      const styleEl = document.createElement('style');
      styleEl.textContent = `
      @keyframes floatCard {
        from { transform: translateY(0px); }
        to   { transform: translateY(-5px); }
      }
      @keyframes pulse {
        0%, 100% { opacity: 1; }
        50%       { opacity: 0.4; }
      }
    `;
      document.head.appendChild(styleEl);

      /* =====================================================
         CURSOR GLOW EFFECT
      ===================================================== */
      const cursorGlow = document.createElement('div');
      cursorGlow.style.cssText = `
      position: fixed;
      width: 280px;
      height: 280px;
      border-radius: 50%;
      background: radial-gradient(circle, rgba(45,125,111,0.08), transparent 70%);
      pointer-events: none;
      z-index: 9999;
      transform: translate(-50%, -50%);
      transition: left 0.15s ease, top 0.15s ease;
    `;
      document.body.appendChild(cursorGlow);
      document.addEventListener('mousemove', e => {
          cursorGlow.style.left = e.clientX + 'px';
          cursorGlow.style.top = e.clientY + 'px';
      });

      /* =====================================================
         STAGGER CHILD ELEMENTS IN GRID ROWS
      ===================================================== */
      document.querySelectorAll('.row.g-4').forEach(row => {
          const children = row.querySelectorAll('.reveal');
          children.forEach((child, i) => {
              if (!child.classList.contains('reveal-delay-1') &&
                  !child.classList.contains('reveal-delay-2') &&
                  !child.classList.contains('reveal-delay-3') &&
                  !child.classList.contains('reveal-delay-4')) {
                  child.style.transitionDelay = `${i * 0.1}s`;
              }
          });
      });

  </script>
  <!-- ==================== WHATSAPP FLOAT ==================== -->
  @if($tenant->phone)
  <a href="https://wa.me/91{{ preg_replace('/[^0-9]/', '', $tenant->phone) }}" target="_blank" style="
     position:fixed;
     bottom:2rem;
     right:2rem;
     width:56px;
     height:56px;
     background:#25D366;
     border-radius:50%;
     display:flex;
     align-items:center;
     justify-content:center;
     box-shadow:0 4px 20px rgba(37,211,102,0.4);
     z-index:9999;
     transition:transform 0.3s, box-shadow 0.3s;
     text-decoration:none;
   " onmouseover="this.style.transform='scale(1.1)';this.style.boxShadow='0 8px 30px rgba(37,211,102,0.5)'" onmouseout="this.style.transform='scale(1)';this.style.boxShadow='0 4px 20px rgba(37,211,102,0.4)'">
      <i class="bi bi-whatsapp" style="font-size:1.6rem;color:white;"></i>
  </a>
  @endif
