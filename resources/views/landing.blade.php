@extends('layouts.modern')
@php($hideAuthLinks = true)

@section('content')
<style>
main{max-width:1140px}
@keyframes fadeInUp{from{opacity:0; transform:translateY(12px)} to{opacity:1; transform:translateY(0)}}
@keyframes slideInRight{from{opacity:0; transform:translateX(24px)} to{opacity:1; transform:translateX(0)}}
.hero-anim-img{animation:slideInRight 600ms ease-out both}
.hero-anim-title{animation:fadeInUp 500ms ease-out 60ms both}
.hero-anim-sub{animation:fadeInUp 500ms ease-out 120ms both}
.hero-anim-cta{animation:fadeInUp 500ms ease-out 180ms both}
@media (prefers-reduced-motion: reduce){.hero-anim-img,.hero-anim-title,.hero-anim-sub,.hero-anim-cta{animation:none}}
/* Landing: wide feature card + checklist */
.feature-card{background:var(--panel); border:1px solid var(--border); border-radius:14px; padding:24px; max-width:960px; margin:0 auto; box-shadow:0 10px 30px rgba(0,0,0,0.35)}
.checklist{list-style:none; padding:0; margin:0; display:grid; gap:12px}
.checklist li{display:flex; align-items:flex-start; gap:10px; color:var(--white)}
.check{display:inline-flex; align-items:center; justify-content:center; width:22px; height:22px; border-radius:999px; background:#0b2b19; color:#a7f3d0; border:1px solid #14532d; font-weight:700; flex:0 0 22px}
/* Stats band, chips, testimonials, CTA band */
.statband{display:grid; grid-template-columns:repeat(3,1fr); gap:12px; margin-top:16px}
.stat{background:var(--panel); border:1px solid var(--border); border-radius:12px; padding:14px; text-align:center}
.stat strong{font-size:22px}
.chips{display:flex; flex-wrap:wrap; gap:8px; margin-top:14px}
.chip{display:inline-block; padding:6px 10px; border:1px solid var(--border); border-radius:999px; background:#263147; color:#cbd5e1}
.stars{color:#f59e0b; letter-spacing:2px}
.quote{border-left:3px solid var(--primary); padding-left:10px}
.cta-band{background:linear-gradient(180deg, rgba(92,141,246,.12), rgba(38,49,71,.18)); border:1px solid var(--border); border-radius:14px; padding:20px; text-align:center}
</style>
<div class="section grid grid-2" style="align-items:center">
    <div class="media">
        <img class="hero-anim-img" src="/images/travel-hero.png" alt="Hero">
    </div>
    <div>
        <h1 class="hero-anim-title">Smart Travel System</h1>
        <p class="subtitle hero-anim-sub">Jelajahi destinasi terbaik, atur budget dalam Rupiah, dan booking tanpa ribet — semua di satu tempat. Rekomendasi AI kami menyesuaikan mood, negara, dan preferensi Anda.</p>
        <div style="margin-top:8px">
            <span class="badge">AI Rekomendasi • Budget Rupiah • Booking</span>
        </div>
        <div class="section hero-anim-cta" style="display:flex; gap:12px">
            <a class="btn" href="{{ route('register') }}">Daftar Sekarang</a>
            <a class="btn secondary" href="#fitur">Lihat Fitur</a>
        </div>
        <div class="statband">
            <div class="stat"><strong>20+</strong><div class="subtitle">Destinasi</div></div>
            <div class="stat"><strong>50+</strong><div class="subtitle">Akomodasi</div></div>
            <div class="stat"><strong>100+</strong><div class="subtitle">Pengguna</div></div>
        </div>
        <div class="chips">
            <span class="chip">Santai</span>
            <span class="chip">Petualangan</span>
            <span class="chip">Relaksasi</span>
            <span class="chip">Keluarga</span>
            <span class="chip">Budaya</span>
        </div>
    </div>
</div>

<div class="section" id="fitur">
    <h2>Kenapa Memilih Smart Travel?</h2>
    <p class="subtitle">Fitur lengkap untuk membantu Anda merencanakan liburan sempurna.</p>
    <div class="feature-card">
        <ul class="checklist">
            <li><span class="check">✓</span><div><strong>Akurasi Rekomendasi AI</strong><p class="subtitle">Destinasi & akomodasi disesuaikan dengan mood, negara, dan budget.</p></div></li>
            <li><span class="check">✓</span><div><strong>Budget Transparan (Rupiah)</strong><p class="subtitle">Estimasi biaya ditampilkan rapi dalam Rupiah dan format ribuan.</p></div></li>
            <li><span class="check">✓</span><div><strong>Booking Aman & Mudah</strong><p class="subtitle">Pilih tanggal, jumlah penumpang, metode pembayaran, lalu konfirmasi.</p></div></li>
        </ul>
    </div>
</div>

<div class="section">
    <h2>Destinasi Populer</h2>
    <p class="subtitle">Contoh destinasi yang sering direkomendasikan berdasarkan preferensi pengguna.</p>
    <div class="cards">
        <div class="card-item"><div class="body"><strong>Bali, Indonesia</strong><p class="subtitle">Pantai, budaya, kuliner; cocok untuk mood santai.</p></div></div>
        <div class="card-item"><div class="body"><strong>Tokyo, Jepang</strong><p class="subtitle">Teknologi, belanja, kuliner; cocok untuk mood dinamis.</p></div></div>
        <div class="card-item"><div class="body"><strong>Paris, Prancis</strong><p class="subtitle">Seni, sejarah, romantis; cocok untuk pasangan.</p></div></div>
    </div>
</div>

<div class="section">
    <h2>Apa Kata Pengguna</h2>
    <div class="cards">
        <div class="card-item"><div class="body"><div class="stars">★★★★★</div><p class="subtitle quote">“Akurat banget, rekomendasi AI-nya sesuai mood dan budget.”</p><small>— Rina, Jakarta</small></div></div>
        <div class="card-item"><div class="body"><div class="stars">★★★★☆</div><p class="subtitle quote">“Booking cepat, estimasi biaya jelas dalam Rupiah.”</p><small>— Ardi, Bandung</small></div></div>
    </div>
</div>

<div class="section">
    <h2>Cara Kerja</h2>
    <div class="grid grid-2">
        <div class="card-item"><div class="body"><span class="badge">Langkah 1</span><div style="margin-top:8px"><strong>Isi Preferensi</strong><p class="subtitle">Pilih mood, negara tujuan, dan rentang budget.</p><div class="bar"><div class="fill" style="width:25%"></div></div></div></div></div>
        <div class="card-item"><div class="body"><span class="badge">Langkah 2</span><div style="margin-top:8px"><strong>Jalankan AI</strong><p class="subtitle">Dapatkan rekomendasi destinasi dan akomodasi terbaik.</p><div class="bar"><div class="fill" style="width:50%"></div></div></div></div></div>
        <div class="card-item"><div class="body"><span class="badge">Langkah 3</span><div style="margin-top:8px"><strong>Cek Anggaran</strong><p class="subtitle">Lihat estimasi biaya dalam Rupiah dengan format ribuan.</p><div class="bar"><div class="fill" style="width:75%"></div></div></div></div></div>
        <div class="card-item"><div class="body"><span class="badge">Langkah 4</span><div style="margin-top:8px"><strong>Buat Booking</strong><p class="subtitle">Konfirmasi detail perjalanan dan lanjutkan pembayaran.</p><div class="bar"><div class="fill" style="width:100%"></div></div></div></div></div>
    </div>
</div>


<div class="section">
    <div class="cta-band">
        <h3>Siap merencanakan liburan impian?</h3>
        <p class="subtitle">Aktifkan rekomendasi AI, atur anggaran, dan booking dengan sekali klik.</p>
        <div style="margin-top:12px">
            <a class="btn" href="{{ route('register') }}">Mulai Gratis</a>
            <a class="btn secondary" href="#fitur" style="margin-left:8px">Lihat Fitur</a>
        </div>
    </div>
</div>
@endsection