<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar</title>
    <style>
        :root{
            --bg:#0b0d12; --panel:#12141b; --muted:#8b93a7; --border:#262a33;
            --input:#1a1e27; --white:#e5e7eb; --primary:#5c8df6; --primary-press:#4c7aed;
        }
        *{box-sizing:border-box}
        body{margin:0; font-family:system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial,"Apple Color Emoji","Segoe UI Emoji"; background:var(--bg); color:var(--white)}
        .screen{min-height:100vh; display:flex}
        .visual{flex:1 1 55%; background:
            radial-gradient(60% 60% at 30% 30%, #192036 0%, rgba(11,13,18,0) 70%),
            radial-gradient(50% 50% at 70% 70%, #111931 0%, rgba(11,13,18,0) 70%),
            linear-gradient(120deg, #0b0d12 0%, #0d1016 100%);
            position:relative;
        }
        .visual::after{content:""; position:absolute; inset:27% 17% 27% 17%; border-radius:24px; box-shadow:0 40px 120px rgba(74,118,255,0.25) inset, 0 10px 30px rgba(0,0,0,0.4);
            background-image: linear-gradient(180deg, rgba(86,115,255,0.15), rgba(86,115,255,0)), url('/images/ai-travel-hero.jpg'), url('/images/ai-travel-hero.png'), url('/images/travel-hero.jpg'), url('/images/travel-hero.png');
            background-size: cover, contain, contain, contain, contain;
            background-position: center, center, center, center, center;
        }
        .panel{flex:1 1 45%; display:flex; align-items:center; justify-content:center; padding:6vh 4vw}
        .card{width:100%; max-width:520px; background:var(--panel); border:1px solid var(--border); border-radius:16px; padding:32px 28px; box-shadow:0 20px 60px rgba(0,0,0,0.35)}
        .title{font-size:32px; font-weight:700; margin:0 0 8px}
        .subtitle{color:var(--muted); font-size:14px; line-height:1.5; margin-bottom:24px}
        .form{display:flex; flex-direction:column; gap:18px}
        .field label{display:block; font-weight:600; margin-bottom:8px}
        .input{width:100%; padding:12px 14px; border:1px solid var(--border); background:var(--input); border-radius:10px; color:var(--white)}
        .input::placeholder{color:#9aa3b2}
        .actions{display:flex; flex-direction:column; gap:12px}
        .btn{width:100%; padding:12px 14px; border:none; border-radius:10px; background:var(--primary); color:#fff; font-weight:700; cursor:pointer}
        .btn:active{background:var(--primary-press)}
        .alt{color:var(--muted); font-size:14px; text-align:right}
        .alt a{color:#99b2ff; text-decoration:none}
        .error{background:#3f0d12; border:1px solid #ef4444; color:#fecaca; padding:10px 12px; border-radius:8px; margin-top:6px}
        .flash{background:#052e1b; border:1px solid #14532d; color:#a7f3d0; padding:10px 12px; border-radius:8px; margin-bottom:16px}
    </style>
</head>
<body>
    <div class="screen">
        <div class="visual"></div>
        <div class="panel">
            <div class="card">
                <h1 class="title">Daftar</h1>
                <p class="subtitle">Buat akun untuk mulai menggunakan Smart Travel System.</p>

                @if(session('status'))
                    <div class="flash">{{ session('status') }}</div>
                @endif

                @if ($errors->any())
                    <div class="error">{{ $errors->first() }}</div>
                @endif

                <form class="form" method="POST" action="{{ route('register') }}">
                    @csrf
                    <div class="field">
                        <label for="name">Nama</label>
                        <input class="input" type="text" id="name" name="name" placeholder="Nama lengkap" value="{{ old('name') }}" required autocomplete="name">
                    </div>
                    <div class="field">
                        <label for="email">E-mail</label>
                        <input class="input" type="email" id="email" name="email" placeholder="name@example.com" value="{{ old('email') }}" required autocomplete="email">
                    </div>
                    <div class="field">
                        <label for="password">Kata Sandi</label>
                        <input class="input" type="password" id="password" name="password" placeholder="••••••••" required autocomplete="new-password">
                    </div>
                    <div class="field">
                        <label for="password_confirmation">Konfirmasi Kata Sandi</label>
                        <input class="input" type="password" id="password_confirmation" name="password_confirmation" placeholder="••••••••" required autocomplete="new-password">
                    </div>
                    <div class="actions">
                        <button class="btn" type="submit">Daftar</button>
                        <div class="alt">Sudah punya akun? <a href="{{ route('login') }}">Masuk</a></div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>