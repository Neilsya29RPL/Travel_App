<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Travel App</title>
    <style>
        :root{
            --bg:#0b0d12; --panel:#12141b; --muted:#8b93a7; --border:#262a33;
            --input:#1a1e27; --white:#e5e7eb; --primary:#5c8df6; --primary-press:#4c7aed;
        }
        *{box-sizing:border-box}
        body{margin:0; font-family:system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial,"Apple Color Emoji","Segoe UI Emoji"; background:var(--bg); color:var(--white)}
        header{background:linear-gradient(180deg,#0d1016,#0b0d12); border-bottom:1px solid var(--border); padding:12px 18px; display:flex; align-items:center; justify-content:space-between; position:sticky; top:0; z-index:1000}
        header .brand{font-weight:800; letter-spacing:.2px}
        header nav a{color:#cdd3e1; text-decoration:none; margin-right:14px; padding:8px 10px; border-radius:8px; transition:all .2s ease}
        header nav a:hover{color:#fff; background:#1a2232}
        header nav a.active{color:#fff; background:#263147; border:1px solid #3a4253; box-shadow:0 6px 20px rgba(92,141,246,0.2), inset 0 0 0 1px #3a4253}
        header .btn{background:#263147; color:#cdd3e1; border:1px solid #3a4253; padding:8px 12px; border-radius:8px; cursor:pointer}
        main{max-width:1000px; margin:24px auto; padding:0 16px}
        .card{background:var(--panel); border:1px solid var(--border); border-radius:14px; padding:24px; box-shadow:0 10px 30px rgba(0,0,0,0.35)}
        h1,h2{margin:0 0 10px}
        .subtitle{color:var(--muted); font-size:14px; margin-bottom:20px}
        .grid{display:grid; gap:16px}
        .grid-2{grid-template-columns:1fr 1fr}
        

        input,select{width:100%; padding:12px 14px; border:1px solid var(--border); background:var(--input); border-radius:10px; color:var(--white)}
        input::placeholder{color:#9aa3b2}
        .btn{display:inline-block; background:var(--primary); color:#fff; border:none; border-radius:10px; padding:10px 14px; cursor:pointer; text-decoration:none}
        .btn.secondary{background:#3a4253}
        .btn:active{background:var(--primary-press)}
        .status{background:#052e1b; border:1px solid #14532d; color:#a7f3d0; padding:10px 12px; border-radius:8px; margin-bottom:16px}
        .error{background:#3f0d12; border:1px solid #ef4444; color:#fecaca; padding:10px 12px; border-radius:8px; margin-bottom:16px}
        /* Alert styles */
        .alert{display:flex; align-items:center; gap:10px; padding:12px 14px; border-radius:10px; border:1px solid var(--border); margin-bottom:16px; font-size:14px; line-height:1.4}
        .alert .icon{flex:0 0 auto; width:20px; height:20px; border-radius:6px; display:flex; align-items:center; justify-content:center; font-weight:700}
        .alert-inline{display:inline-flex; max-width:600px}
        .alert-danger{background:#2a0f12; border-color:#5b1b20; color:#fecaca}
        .alert-danger .icon{background:#3f0d12; color:#fca5a5; border:1px solid #5b1b20}
        .alert-info{background:#122032; border-color:#243348; color:#cfe3ff}
        .alert-info .icon{background:#1f2d44; color:#9ec3ff; border:1px solid #243348}
        .alert-success{background:#0f2a1f; border-color:#1b5b3f; color:#a7f3d0}
        .alert-success .icon{background:#0f2a1f; color:#6ee7b7; border:1px solid #1b5b3f}
        footer{text-align:center; color:#8b93a7; padding:16px}
        /* Reusable cards and media helpers */
        .cards{display:grid; grid-template-columns:repeat(auto-fit,minmax(240px,1fr)); gap:16px}
        .card-item{background:var(--panel); border:1px solid var(--border); border-radius:12px; overflow:hidden}
        .card-item .body{padding:14px}
        .media{aspect-ratio:16/9; background:#0e1118; border-bottom:1px solid var(--border)}
        .media img{width:100%; height:100%; object-fit:cover; display:block}
        .badge{display:inline-block; font-size:12px; color:#cbd5e1; background:#263147; border:1px solid #3a4253; padding:2px 8px; border-radius:999px}
        .bar .fill{height:100%; background:var(--primary);}
        .section{margin-top:20px}
    </style>
</head>
<body>
<header>
    <div class="brand">Smart Travel System</div>
    <nav>
        @unless(isset($hideNav) && $hideNav)
        @auth
            <a href="{{ route('preferences.index') }}" class="{{ (request()->routeIs('preferences.*') || request()->routeIs('recommendations.accommodations') || request()->routeIs('budget.*')) ? 'active' : '' }}">Preferensi</a>
            <a href="{{ route('booking.index') }}" class="{{ request()->routeIs('booking.*') ? 'active' : '' }}">Booking</a>
            <a href="{{ route('bookings.index') }}" class="{{ request()->routeIs('bookings.*') ? 'active' : '' }}">Daftar Booking</a>
            <form method="POST" action="{{ route('logout') }}" style="display:inline">
                @csrf
                <button class="btn" type="submit">Logout</button>
            </form>
        @endauth
        @guest
            @unless(isset($hideAuthLinks) && $hideAuthLinks)
                <a href="{{ route('login') }}" class="{{ request()->routeIs('login') ? 'active' : '' }}">Login</a>
                <a href="{{ route('register') }}" class="{{ request()->routeIs('register') ? 'active' : '' }}">Register</a>
            @endunless
        @endguest
        @endunless
    </nav>
</header>
<main @if(isset($fullWidth) && $fullWidth) style="max-width:unset; width:100%; margin:0; padding:0" @endif>
    @if (session('status'))
        <div class="status">{{ session('status') }}</div>
    @endif
    @if(isset($fullWidth) && $fullWidth)
        @yield('content')
    @else
        <div class="card">
            @yield('content')
        </div>
    @endif
<footer>
    <small>&copy; {{ date('Y') }} Smart Travel System</small>
</footer>
</body>
</html>
