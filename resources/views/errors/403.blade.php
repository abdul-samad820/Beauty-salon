<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>403 — Access Denied · LUMIÈRE</title>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400&family=Jost:wght@300;400;500&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
  <style>
    :root { --gold:#c9a96e; --bg:#0a0a0c; --text:rgba(255,255,255,0.88); --text-3:rgba(255,255,255,0.28); --rose:#f43f5e; --rose-dim:rgba(244,63,94,0.1); --ff-display:'Cormorant Garamond',serif; --ff-body:'Jost',sans-serif; }
    * { box-sizing:border-box; margin:0; padding:0; }
    body { font-family:var(--ff-body); background:var(--bg); color:var(--text); min-height:100vh; display:flex; align-items:center; justify-content:center; text-align:center; font-weight:300; }
    body::before { content:''; position:fixed; inset:0; background:radial-gradient(ellipse 50% 50% at 50% 50%, rgba(244,63,94,0.04), transparent 70%); pointer-events:none; }
    .wrap { padding:2rem; max-width:480px; }
    .icon-ring { width:80px; height:80px; border-radius:50%; background:var(--rose-dim); border:1.5px solid rgba(244,63,94,0.25); display:flex; align-items:center; justify-content:center; font-size:2rem; color:var(--rose); margin:0 auto 2rem; }
    .code { font-family:var(--ff-display); font-size:6rem; font-weight:300; line-height:1; color:rgba(255,255,255,0.06); letter-spacing:-4px; margin-bottom:-1rem; }
    h1 { font-family:var(--ff-display); font-size:1.8rem; font-weight:400; color:var(--text); margin-bottom:0.7rem; }
    p { font-size:0.82rem; color:var(--text-3); line-height:1.7; margin-bottom:2rem; }
    .btn { display:inline-flex; align-items:center; gap:0.5rem; background:var(--gold); border:none; color:#1a1400; font-family:var(--ff-body); font-size:0.75rem; font-weight:600; letter-spacing:0.1em; text-transform:uppercase; padding:0.6rem 1.5rem; border-radius:6px; text-decoration:none; transition:background 0.3s; }
    .btn:hover { background:#dbb97e; }
    .btn-ghost { background:transparent; border:1px solid rgba(255,255,255,0.1); color:rgba(255,255,255,0.5); margin-left:0.5rem; }
    .btn-ghost:hover { border-color:var(--gold); color:var(--gold); background:transparent; }
  </style>
</head>
<body>
  <div class="wrap">
    <div class="icon-ring"><i class="bi bi-shield-fill-x"></i></div>
    <div class="code">403</div>
    <h1>Access Denied</h1>
    <p>Aapke paas is page ko access karne ki permission nahi hai.<br>
       Agar aapko lagta hai ye galat hai, toh admin se contact karein.</p>
    <div>
      <a href="{{ url()->previous() }}" class="btn"><i class="bi bi-arrow-left"></i> Wapas Jao</a>
      <a href="{{ route('login') }}" class="btn btn-ghost"><i class="bi bi-box-arrow-in-right"></i> Login</a>
    </div>
  </div>
</body>
</html>
