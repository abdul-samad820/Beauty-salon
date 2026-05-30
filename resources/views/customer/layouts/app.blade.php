<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>@yield('title', 'Book Appointment') · {{ $tenant->name ?? 'LUMIÈRE' }}</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400&family=Jost:wght@200;300;400;500;600&display=swap" rel="stylesheet" />
  <style>
    :root {
      --bg:#0a0a0c; --bg-2:#0f0f12; --bg-card:#13131a; --bg-card-2:#16161f;
      --bg-input:rgba(255,255,255,0.04); --border:rgba(255,255,255,0.06);
      --border-2:rgba(255,255,255,0.10); --gold:#c9a96e;
      --gold-dim:rgba(201,169,110,0.15); --gold-glow:rgba(201,169,110,0.3);
      --teal:#2d7d6f; --teal-light:#3a9e8d; --teal-dim:rgba(45,125,111,0.15);
      --emerald:#10b981; --emerald-dim:rgba(16,185,129,0.12);
      --rose:#f43f5e; --rose-dim:rgba(244,63,94,0.12);
      --amber:#f59e0b; --amber-dim:rgba(245,158,11,0.12);
      --text:rgba(255,255,255,0.88); --text-2:rgba(255,255,255,0.50);
      --text-3:rgba(255,255,255,0.28);
      --ff-display:'Cormorant Garamond',serif; --ff-body:'Jost',sans-serif;
      --transition:0.4s cubic-bezier(0.22,1,0.36,1);
    }
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
    body{font-family:var(--ff-body);background:var(--bg);color:var(--text);font-weight:300;min-height:100vh;}
    body::before{content:'';position:fixed;inset:0;background:radial-gradient(ellipse 70% 50% at 50% 0%,rgba(201,169,110,0.04),transparent 60%);pointer-events:none;}

    /* NAV */
    .cust-nav{background:rgba(10,10,12,0.9);backdrop-filter:blur(16px);border-bottom:1px solid var(--border);padding:.9rem 1.5rem;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:100;}
    .cust-nav-logo{font-family:var(--ff-display);font-size:1.3rem;font-weight:400;color:var(--text);letter-spacing:.08em;text-decoration:none;}
    .cust-nav-logo span{color:var(--gold);}
    .cust-nav-parlour{font-size:.65rem;font-weight:600;letter-spacing:.2em;text-transform:uppercase;color:var(--text-3);margin-top:.1rem;}
    .nav-links{display:flex;align-items:center;gap:.5rem;}
    .nav-link-btn{display:flex;align-items:center;gap:.4rem;padding:.42rem .9rem;border-radius:8px;font-size:.75rem;color:var(--text-2);text-decoration:none;transition:all .25s;border:1px solid transparent;}
    .nav-link-btn:hover{color:var(--text);background:rgba(255,255,255,0.04);}
    .nav-link-btn.active{color:var(--gold);background:var(--gold-dim);border-color:rgba(201,169,110,0.15);}
    .nav-link-btn.logout{color:var(--rose);}
    .nav-link-btn.logout:hover{background:var(--rose-dim);}
    .user-chip{display:flex;align-items:center;gap:.5rem;padding:.35rem .8rem;background:rgba(255,255,255,0.04);border:1px solid var(--border-2);border-radius:20px;}
    .user-chip-av{width:22px;height:22px;border-radius:50%;background:var(--gold-dim);display:flex;align-items:center;justify-content:center;font-size:.55rem;font-family:var(--ff-display);color:var(--gold);}
    .user-chip-name{font-size:.72rem;color:var(--text-2);}

    /* PAGE */
    .cust-body{max-width:1100px;margin:0 auto;padding:2rem 1.2rem;}

    /* CARDS */
    .c-card{background:var(--bg-card);border:1px solid var(--border);border-radius:14px;overflow:hidden;position:relative;transition:border-color var(--transition),box-shadow var(--transition);}
    .c-card::before{content:'';position:absolute;top:0;left:0;right:0;height:1px;background:linear-gradient(90deg,transparent,rgba(255,255,255,.07),transparent);}
    .c-card:hover{border-color:rgba(201,169,110,.18);box-shadow:0 10px 40px rgba(0,0,0,.4);}
    .c-card-body{padding:1.4rem;}

    /* BUTTONS */
    .btn-cust-gold{background:var(--gold);border:none;color:#1a1400;font-family:var(--ff-body);font-size:.75rem;font-weight:600;letter-spacing:.1em;text-transform:uppercase;padding:.55rem 1.4rem;border-radius:8px;cursor:pointer;text-decoration:none;transition:background .3s,box-shadow .3s;display:inline-flex;align-items:center;gap:.5rem;}
    .btn-cust-gold:hover{background:#dbb97e;box-shadow:0 4px 20px var(--gold-glow);color:#1a1400;}
    .btn-cust-ghost{background:transparent;border:1px solid var(--border-2);color:var(--text-2);font-family:var(--ff-body);font-size:.75rem;padding:.5rem 1.1rem;border-radius:8px;cursor:pointer;text-decoration:none;transition:all .25s;display:inline-flex;align-items:center;gap:.4rem;}
    .btn-cust-ghost:hover{border-color:var(--gold);color:var(--gold);}
    .btn-cust-danger{background:var(--rose-dim);border:1px solid rgba(244,63,94,.3);color:var(--rose);font-family:var(--ff-body);font-size:.72rem;font-weight:500;padding:.4rem .9rem;border-radius:6px;cursor:pointer;text-decoration:none;transition:all .25s;display:inline-flex;align-items:center;gap:.4rem;}
    .btn-cust-danger:hover{background:var(--rose);color:white;}

    /* BADGES */
    .c-badge{display:inline-flex;align-items:center;gap:.3rem;font-size:.6rem;font-weight:600;letter-spacing:.1em;text-transform:uppercase;padding:.22rem .65rem;border-radius:20px;}
    .cb-green   {background:var(--emerald-dim);color:var(--emerald);}
    .cb-red     {background:var(--rose-dim);   color:var(--rose);}
    .cb-gold    {background:var(--gold-dim);   color:var(--gold);}
    .cb-amber   {background:var(--amber-dim);  color:var(--amber);}
    .cb-muted   {background:rgba(255,255,255,.05);color:var(--text-3);}

    /* FORM INPUTS */
    .cfl{margin-bottom:1.2rem;}
    .cfl label{display:block;font-size:.65rem;font-weight:600;letter-spacing:.15em;text-transform:uppercase;color:var(--text-3);margin-bottom:.4rem;}
    .cfl input,.cfl select,.cfl textarea{width:100%;background:var(--bg-input);border:1px solid var(--border-2);border-radius:10px;color:var(--text);font-family:var(--ff-body);font-size:.85rem;font-weight:300;padding:.8rem 1rem;outline:none;transition:border-color .3s,box-shadow .3s;}
    .cfl input:focus,.cfl select:focus,.cfl textarea:focus{border-color:var(--gold);background:rgba(201,169,110,.04);box-shadow:0 0 0 3px rgba(201,169,110,.08);}
    .cfl select option{background:var(--bg-card);}

    /* FLASH */
    .flash{padding:.7rem 1.1rem;border-radius:8px;font-size:.8rem;margin-bottom:1.2rem;display:flex;align-items:center;gap:.55rem;}
    .flash-ok  {background:var(--emerald-dim);border:1px solid rgba(16,185,129,.25);color:var(--emerald);}
    .flash-err {background:var(--rose-dim);   border:1px solid rgba(244,63,94,.25); color:var(--rose);}

    /* ANIMATIONS */
    .fade-up{animation:fadeUp .6s ease both;}
    .s1{animation-delay:.05s} .s2{animation-delay:.1s} .s3{animation-delay:.15s} .s4{animation-delay:.2s}
    @keyframes fadeUp{from{opacity:0;transform:translateY(18px)}to{opacity:1;transform:none}}

    /* MOBILE NAV */
    @media(max-width:576px){
      .nav-links .nav-link-btn span{display:none;}
      .cust-body{padding:1rem .8rem;}
    }
  </style>
  @stack('styles')
</head>
<body>

{{-- NAV --}}
<nav class="cust-nav">
  <div>
    <a href="{{ route('customer.home', $subdomain) }}" class="cust-nav-logo">LUMIÈRE<span>.</span></a>
    <div class="cust-nav-parlour">{{ $tenant->name }}</div>
  </div>

  @auth
  <div class="nav-links">
    <a href="{{ route('customer.home', $subdomain) }}" class="nav-link-btn {{ request()->routeIs('customer.home') ? 'active' : '' }}">
      <i class="bi bi-scissors"></i><span>Services</span>
    </a>
    <a href="{{ route('customer.appointments', $subdomain) }}" class="nav-link-btn {{ request()->routeIs('customer.appointments') ? 'active' : '' }}">
      <i class="bi bi-calendar2-check"></i><span>My Bookings</span>
    </a>
    <div class="user-chip">
      <div class="user-chip-av">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</div>
      <span class="user-chip-name">{{ explode(' ', auth()->user()->name)[0] }}</span>
    </div>
    <form method="POST" action="{{ route('customer.logout', $subdomain) }}" style="display:inline">
      @csrf
      <button type="submit" class="nav-link-btn logout" style="border:none;background:none;cursor:pointer">
        <i class="bi bi-box-arrow-right"></i>
      </button>
    </form>
  </div>
  @endauth
</nav>

{{-- BODY --}}
<div class="cust-body">

  @if(session('success'))
    <div class="flash flash-ok fade-up"><i class="bi bi-check-circle-fill"></i> {{ session('success') }}</div>
  @endif
  @if(session('error'))
    <div class="flash flash-err fade-up"><i class="bi bi-exclamation-circle-fill"></i> {{ session('error') }}</div>
  @endif
  @if($errors->any())
    <div class="flash flash-err fade-up"><i class="bi bi-exclamation-circle-fill"></i> {{ $errors->first() }}</div>
  @endif

  @yield('content')
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  setTimeout(()=>{
    document.querySelectorAll('.flash').forEach(el=>{
      el.style.transition='opacity .5s'; el.style.opacity='0';
      setTimeout(()=>el.remove(),500);
    });
  }, 4000);
</script>
@stack('scripts')
</body>
</html>
