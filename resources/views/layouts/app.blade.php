<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Travel App</title>
    <link rel="stylesheet" href="/css/app.css">
    <style>
        body { margin:0; font-family: system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial; background:#f8fafc; }
        header { background:#0ea5e9; color:white; padding:12px 18px; display:flex; align-items:center; justify-content:space-between; }
        nav a { color:white; margin-right:12px; text-decoration:none; }
        nav form { display:inline; }
        main { max-width:960px; margin:24px auto; background:white; padding:20px; border-radius:10px; box-shadow:0 2px 6px rgba(0,0,0,0.05); }
        .btn { background:#0ea5e9; color:white; border:none; border-radius:8px; padding:8px 12px; cursor:pointer; }
        .btn.secondary { background:#64748b; }
        .grid { display:grid; gap:12px; }
        .grid-2 { grid-template-columns: 1fr 1fr; }
        label { font-weight:600; }
        input, select { padding:8px; border:1px solid #cbd5e1; border-radius:8px; width:100%; }
        .status { margin-bottom:16px; padding:12px; border-radius:8px; background:#dcfce7; border:1px solid #22c55e; color:#14532d; }
        .error { margin-bottom:16px; padding:12px; border-radius:8px; background:#fee2e2; border:1px solid #ef4444; }
        footer { text-align:center; color:#64748b; padding:16px; }
        .nav-right a { margin-left:12px; }
    </style>
</head>
<body>
<header>
    <div>AI Travel App</div>
    <nav>
        @auth
            <a href="{{ route('preferences.index') }}">Preferensi</a>
            <a href="{{ route('recommendations.index') }}">Rekomendasi</a>
            <a href="{{ route('budget.index') }}">Anggaran</a>
            <a href="{{ route('booking.index') }}">Booking</a>
            <a href="{{ route('bookings.index') }}">Daftar Booking</a>
            <form method="POST" action="{{ route('logout') }}">
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
<main>
    @if (session('status'))
        <div class="status">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="error">{{ $errors->first() }}</div>
    @endif
    @yield('content')
</main>
<footer>
    <small>&copy; {{ date('Y') }} AI Travel App</small>
</footer>
</body>
</html>