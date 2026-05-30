<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Register · {{ $tenant->name }}</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500&family=Jost:wght@200;300;400;500&display=swap" rel="stylesheet" />
  <style>
    :root{--bg:#0a0a0c;--bg-card:#13131a;--border:rgba(255,255,255,.06);--border-2:rgba(255,255,255,.1);--gold:#c9a96e;--gold-dim:rgba(201,169,110,.15);--gold-glow:rgba(201,169,110,.3);--rose:#f43f5e;--rose-dim:rgba(244,63,94,.12);--text:rgba(255,255,255,.88);--text-2:rgba(255,255,255,.5);--text-3:rgba(255,255,255,.28);--ff-display:'Cormorant Garamond',serif;--ff-body:'Jost',sans-serif;}
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
    body{font-family:var(--ff-body);background:var(--bg);color:var(--text);min-height:100vh;display:flex;align-items:center;justify-content:center;font-weight:300;padding:1.5rem;}
    body::before{content:'';position:fixed;inset:0;background:radial-gradient(ellipse 60% 60% at 50% 0%,rgba(201,169,110,.05),transparent 65%);pointer-events:none;}
    .wrap{width:100%;max-width:440px;}
    .logo-area{text-align:center;margin-bottom:1.8rem;}
    .logo-mark{width:48px;height:48px;background:linear-gradient(135deg,var(--gold),#e8c48a);border-radius:12px;display:flex;align-items:center;justify-content:center;font-family:var(--ff-display);font-size:1.4rem;font-weight:500;color:#1a1400;margin:0 auto .8rem;}
    .logo-name{font-family:var(--ff-display);font-size:1.7rem;font-weight:400;letter-spacing:.1em;}
    .logo-name span{color:var(--gold);}
    .parlour-sub{font-size:.62rem;letter-spacing:.2em;text-transform:uppercase;color:var(--text-3);margin-top:.3rem;}
    .auth-card{background:var(--bg-card);border:1px solid var(--border);border-radius:16px;padding:2rem;position:relative;overflow:hidden;}
    .auth-card::before{content:'';position:absolute;top:0;left:0;right:0;height:1px;background:linear-gradient(90deg,transparent,rgba(255,255,255,.08),transparent);}
    .auth-title{font-family:var(--ff-display);font-size:1.2rem;margin-bottom:.3rem;}
    .auth-sub{font-size:.72rem;color:var(--text-3);margin-bottom:1.6rem;}
    .cfl{margin-bottom:1.1rem;position:relative;}
    .cfl label{position:absolute;top:50%;left:1rem;transform:translateY(-50%);font-size:.82rem;color:var(--text-3);pointer-events:none;transition:all .25s;}
    .cfl.has-icon label{left:2.8rem;}
    .cfl input{width:100%;background:rgba(255,255,255,.04);border:1px solid var(--border-2);border-radius:10px;color:var(--text);font-family:var(--ff-body);font-size:.85rem;font-weight:300;padding:.88rem 1rem;outline:none;transition:border-color .3s,box-shadow .3s;}
    .cfl.has-icon input{padding-left:2.8rem;}
    .cfl input:focus{border-color:var(--gold);background:rgba(201,169,110,.04);box-shadow:0 0 0 3px rgba(201,169,110,.08);}
    .cfl input:focus+label,.cfl input:not(:placeholder-shown)+label{top:0;font-size:.62rem;font-weight:600;letter-spacing:.12em;color:var(--gold);background:var(--bg-card);padding:0 .4rem;}
    .cfl input::placeholder{color:transparent;}
    .cfl-ic{position:absolute;left:.9rem;top:50%;transform:translateY(-50%);color:var(--text-3);font-size:.9rem;pointer-events:none;}
    .btn-submit{width:100%;background:var(--gold);border:none;color:#1a1400;font-family:var(--ff-body);font-size:.8rem;font-weight:600;letter-spacing:.12em;text-transform:uppercase;padding:.8rem;border-radius:10px;cursor:pointer;transition:background .3s,box-shadow .3s;margin-top:.4rem;}
    .btn-submit:hover{background:#dbb97e;box-shadow:0 4px 24px var(--gold-glow);}
    .err-box{background:var(--rose-dim);border:1px solid rgba(244,63,94,.25);border-radius:8px;padding:.65rem 1rem;font-size:.78rem;color:var(--rose);margin-bottom:1rem;display:flex;align-items:center;gap:.5rem;}
    .divider{display:flex;align-items:center;gap:.8rem;margin:1.2rem 0;color:var(--text-3);font-size:.7rem;}
    .divider::before,.divider::after{content:'';flex:1;height:1px;background:var(--border);}
    .switch-link{text-align:center;font-size:.78rem;color:var(--text-3);}
    .switch-link a{color:var(--gold);text-decoration:none;}
    .row-gap{display:grid;grid-template-columns:1fr 1fr;gap:.8rem;}
    @media(max-width:420px){.row-gap{grid-template-columns:1fr;}}
  </style>
</head>
<body>
<div class="wrap">
  <div class="logo-area">
    <div class="logo-mark">{{ strtoupper(substr($tenant->name, 0, 1)) }}</div>
    <div class="logo-name">LUMIÈRE<span>.</span></div>
    <div class="parlour-sub">{{ $tenant->name }}</div>
  </div>

  <div class="auth-card">
    <div class="auth-title">Create Account</div>
    <div class="auth-sub">Register karein aur appointments book karein</div>

    @if($errors->any())
      <div class="err-box"><i class="bi bi-exclamation-circle-fill"></i> {{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('customer.register.post', $subdomain) }}">
      @csrf
      <div class="cfl has-icon">
        <i class="bi bi-person-fill cfl-ic"></i>
        <input type="text" name="name" placeholder="x" value="{{ old('name') }}" required autofocus />
        <label>Full Name *</label>
      </div>
      <div class="cfl has-icon">
        <i class="bi bi-envelope-fill cfl-ic"></i>
        <input type="email" name="email" placeholder="x" value="{{ old('email') }}" required />
        <label>Email Address *</label>
      </div>
      <div class="cfl has-icon">
        <i class="bi bi-telephone-fill cfl-ic"></i>
        <input type="text" name="phone" placeholder="x" value="{{ old('phone') }}" required />
        <label>Phone Number *</label>
      </div>
      <div class="row-gap">
        <div class="cfl has-icon">
          <i class="bi bi-lock-fill cfl-ic"></i>
          <input type="password" name="password" placeholder="x" required minlength="8" />
          <label>Password *</label>
        </div>
        <div class="cfl has-icon">
          <i class="bi bi-lock-fill cfl-ic"></i>
          <input type="password" name="password_confirmation" placeholder="x" required />
          <label>Confirm *</label>
        </div>
      </div>
      <button type="submit" class="btn-submit"><i class="bi bi-person-plus-fill"></i> Create Account</button>
    </form>

    <div class="divider">ya</div>
    <div class="switch-link">
      Pehle se account hai? <a href="{{ route('customer.login', $subdomain) }}">Login karein</a>
    </div>
  </div>
</div>
</body>
</html>
