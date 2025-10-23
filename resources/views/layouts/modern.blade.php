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
        header{background:linear-gradient(180deg,#0d1016,#0b0d12); border-bottom:1px solid var(--border); padding:12px 18px; display:flex; align-items:center; justify-content:space-between}
        header .brand{font-weight:800; letter-spacing:.2px}
        header nav a{color:#cdd3e1; text-decoration:none; margin-right:14px}
        header nav a:hover{color:#fff}
        header .btn{background:#263147; color:#cdd3e1; border:1px solid #3a4253; padding:8px 12px; border-radius:8px; cursor:pointer}
        main{max-width:1000px; margin:24px auto; padding:0 16px}
        .card{background:var(--panel); border:1px solid var(--border); border-radius:14px; padding:24px; box-shadow:0 10px 30px rgba(0,0,0,0.35)}
        h1,h2{margin:0 0 10px}
        .subtitle{color:var(--muted); font-size:14px; margin-bottom:20px}
        .grid{display:grid; gap:16px}
        .grid-2{grid-template-columns:1fr 1fr}
        
        label{font-weight:600}
        input,select{width:100%; padding:12px 14px; border:1px solid var(--border); background:var(--input); border-radius:10px; color:var(--white)}
        input::placeholder{color:#9aa3b2}
        .btn{display:inline-block; background:var(--primary); color:#fff; border:none; border-radius:10px; padding:10px 14px; cursor:pointer; text-decoration:none}
        .btn.secondary{background:#3a4253}
        .btn:active{background:var(--primary-press)}
        .status{background:#052e1b; border:1px solid #14532d; color:#a7f3d0; padding:10px 12px; border-radius:8px; margin-bottom:16px}
        .error{background:#3f0d12; border:1px solid #ef4444; color:#fecaca; padding:10px 12px; border-radius:8px; margin-bottom:16px}
        footer{text-align:center; color:#8b93a7; padding:16px}
        /* Reusable cards and media helpers */
        .cards{display:grid; grid-template-columns:repeat(auto-fit,minmax(240px,1fr)); gap:16px}
        .card-item{background:var(--panel); border:1px solid var(--border); border-radius:12px; overflow:hidden}
        .card-item .body{padding:14px}
        .media{aspect-ratio:16/9; background:#0e1118; border-bottom:1px solid var(--border)}
        .media img{width:100%; height:100%; object-fit:cover; display:block}
        .badge{display:inline-block; font-size:12px; color:#cbd5e1; background:#263147; border:1px solid #3a4253; padding:2px 8px; border-radius:999px}
        .bar{height:10px; border-radius:6px; background:#1a1e27; border:1px solid var(--border); overflow:hidden}
        .bar .fill{height:100%; background:var(--primary);}
        .section{margin-top:20px}
    </style>
</head>
<body>
<header>
    <div class="brand">Smart Travel System</div>
    <nav>
        @auth
            <a href="{{ route('preferences.index') }}">Preferensi</a>
            <a href="{{ route('recommendations.index') }}">Rekomendasi</a>
            <a href="{{ route('budget.index') }}">Anggaran</a>
            <a href="{{ route('booking.index') }}">Booking</a>
            <a href="{{ route('bookings.index') }}">Daftar Booking</a>
            <form method="POST" action="{{ route('logout') }}" style="display:inline">
                @csrf
                <button class="btn" type="submit">Logout</button>
            </form>
        @endauth
        @guest
            @unless(isset($hideAuthLinks) && $hideAuthLinks)
                <a href="{{ route('login') }}">Login</a>
                <a href="{{ route('register') }}">Register</a>
            @endunless
        @endguest
    </nav>
</header>
<main @if(isset($fullWidth) && $fullWidth) style="max-width:unset; width:100%; margin:0; padding:0" @endif>
    @if (session('status'))
        <div class="status">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="error">{{ $errors->first() }}</div>
    @endif
    @if(isset($fullWidth) && $fullWidth)
        @yield('content')
    @else
        <div class="card">
            @yield('content')
        </div>
    @endif
</main>
<footer>
    <small>&copy; {{ date('Y') }} Smart Travel System</small>
</footer>
</body>
</html>