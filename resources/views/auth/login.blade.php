<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login · LUMIÈRE</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600&family=Jost:wght@200;300;400;500;600&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
  <style>
    :root {
      --bg:      #0a0a0c;
      --bg-card: #13131a;
      --border:  rgba(255,255,255,0.06);
      --border-2:rgba(255,255,255,0.1);
      --gold:    #c9a96e;
      --gold-dim:rgba(201,169,110,0.15);
      --gold-glow:rgba(201,169,110,0.3);
      --teal:    #2d7d6f;
      --emerald: #10b981;
      --rose:    #f43f5e;
      --rose-dim:rgba(244,63,94,0.12);
      --text:    rgba(255,255,255,0.88);
      --text-2:  rgba(255,255,255,0.50);
      --text-3:  rgba(255,255,255,0.28);
      --ff-display:'Cormorant Garamond',serif;
      --ff-body:   'Jost',sans-serif;
    }
    *, *::before, *::after { box-sizing:border-box; margin:0; padding:0; }
    body { font-family:var(--ff-body); background:var(--bg); color:var(--text); min-height:100vh; display:flex; align-items:center; justify-content:center; font-weight:300; }
    body::before { content:''; position:fixed; inset:0; background:radial-gradient(ellipse 60% 60% at 50% 0%, rgba(201,169,110,0.06), transparent 70%); pointer-events:none; }
    .login-wrap { width:100%; max-width:420px; padding:1.5rem; }
    .login-logo { text-align:center; margin-bottom:2.5rem; }
    .logo-mark  { width:52px; height:52px; background:linear-gradient(135deg,var(--gold),#e8c48a); border-radius:12px; display:flex; align-items:center; justify-content:center; font-family:var(--ff-display); font-size:1.6rem; font-weight:500; color:#1a1400; margin:0 auto 1rem; }
    .logo-text  { font-family:var(--ff-display); font-size:2rem; font-weight:400; letter-spacing:0.1em; }
    .logo-text span { color:var(--gold); }
    .logo-sub   { font-size:0.68rem; color:var(--text-3); letter-spacing:0.2em; text-transform:uppercase; margin-top:0.3rem; }
    .login-card { background:var(--bg-card); border:1px solid var(--border); border-radius:16px; padding:2rem; position:relative; overflow:hidden; }
    .login-card::before { content:''; position:absolute; top:0; left:0; right:0; height:1px; background:linear-gradient(90deg,transparent,rgba(255,255,255,0.08),transparent); }
    .login-title { font-family:var(--ff-display); font-size:1.3rem; font-weight:400; color:var(--text); margin-bottom:0.3rem; }
    .login-sub   { font-size:0.72rem; color:var(--text-3); margin-bottom:1.8rem; }
    .fl-group { position:relative; margin-bottom:1.2rem; }
    .fl-group label { position:absolute; top:50%; left:1rem; transform:translateY(-50%); font-size:0.82rem; color:var(--text-3); pointer-events:none; transition:all 0.25s; background:transparent; padding:0 0.2rem; }
    .fl-group.has-icon label { left:2.8rem; }
    .fl-group input { width:100%; background:rgba(255,255,255,0.04); border:1px solid var(--border-2); border-radius:10px; color:var(--text); font-family:var(--ff-body); font-size:0.85rem; font-weight:300; padding:0.9rem 1rem; outline:none; transition:border-color 0.3s, box-shadow 0.3s; }
    .fl-group.has-icon input { padding-left:2.8rem; }
    .fl-group input:focus { border-color:var(--gold); background:rgba(201,169,110,0.04); box-shadow:0 0 0 3px rgba(201,169,110,0.08); }
    .fl-group input:focus + label, .fl-group input:not(:placeholder-shown) + label { top:0; font-size:0.65rem; font-weight:600; letter-spacing:0.12em; color:var(--gold); background:var(--bg-card); padding:0 0.4rem; }
    .fl-group input::placeholder { color:transparent; }
    .fl-input-icon { position:absolute; left:0.9rem; top:50%; transform:translateY(-50%); color:var(--text-3); font-size:0.9rem; pointer-events:none; }
    .eye-toggle { position:absolute; right:1rem; top:50%; transform:translateY(-50%); background:none; border:none; color:var(--text-3); cursor:pointer; font-size:0.9rem; padding:0; }
    .eye-toggle:hover { color:var(--text-2); }
    .btn-login { width:100%; background:var(--gold); border:none; color:#1a1400; font-family:var(--ff-body); font-size:0.82rem; font-weight:600; letter-spacing:0.12em; text-transform:uppercase; padding:0.8rem; border-radius:10px; cursor:pointer; transition:background 0.3s, box-shadow 0.3s; margin-top:0.5rem; }
    .btn-login:hover { background:#dbb97e; box-shadow:0 4px 24px var(--gold-glow); }
    .error-box { background:var(--rose-dim); border:1px solid rgba(244,63,94,0.25); border-radius:8px; padding:0.7rem 1rem; font-size:0.8rem; color:var(--rose); margin-bottom:1.2rem; display:flex; align-items:center; gap:0.5rem; }
    .remember-row { display:flex; align-items:center; justify-content:space-between; margin-bottom:1.2rem; font-size:0.75rem; }
    .remember-row label { color:var(--text-2); cursor:pointer; display:flex; align-items:center; gap:0.4rem; }
    .remember-row input[type=checkbox] { accent-color:var(--gold); }
    .remember-row a { color:var(--text-3); text-decoration:none; transition:color 0.25s; }
    .remember-row a:hover { color:var(--gold); }
  </style>
</head>
<body>
  <div class="login-wrap">
    <div class="login-logo">
      <div class="logo-mark">L</div>
      <div class="logo-text">LUMIÈRE<span>.</span></div>
      <div class="logo-sub">Salon Management Platform</div>
    </div>

    <div class="login-card">
      <div class="login-title">Welcome back</div>
      <div class="login-sub">Apne account mein login karein</div>

      {{-- Errors --}}
      @if($errors->any())
        <div class="error-box">
          <i class="bi bi-exclamation-circle-fill"></i>
          {{ $errors->first() }}
        </div>
      @endif

      @if(session('success'))
        <div style="background:rgba(16,185,129,0.12);border:1px solid rgba(16,185,129,0.25);border-radius:8px;padding:0.7rem 1rem;font-size:0.8rem;color:#10b981;margin-bottom:1.2rem;display:flex;align-items:center;gap:0.5rem;">
          <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
        </div>
      @endif

      <form method="POST" action="{{ route('login.post') }}">
        @csrf

        <div class="fl-group has-icon">
          <i class="bi bi-envelope-fill fl-input-icon"></i>
          <input type="email" name="email" id="email" placeholder="x" value="{{ old('email') }}" required autofocus />
          <label for="email">Email Address</label>
        </div>

        <div class="fl-group has-icon">
          <i class="bi bi-lock-fill fl-input-icon"></i>
          <input type="password" name="password" id="password" placeholder="x" required />
          <label for="password">Password</label>
          <button type="button" class="eye-toggle" onclick="togglePass()">
            <i class="bi bi-eye" id="eyeIcon"></i>
          </button>
        </div>

        <div class="remember-row">
          <label>
            <input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }} />
            Mujhe yaad rakho
          </label>
          <a href="#">Password bhool gaye?</a>
        </div>

        <button type="submit" class="btn-login">
          <i class="bi bi-box-arrow-in-right"></i> Login
        </button>
      </form>
    </div>
  </div>

  <script>
    function togglePass() {
      const inp = document.getElementById('password');
      const ico = document.getElementById('eyeIcon');
      if (inp.type === 'password') {
        inp.type = 'text';
        ico.classList.replace('bi-eye','bi-eye-slash');
      } else {
        inp.type = 'password';
        ico.classList.replace('bi-eye-slash','bi-eye');
      }
    }
  </script>
</body>
</html>
