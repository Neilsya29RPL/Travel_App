@extends('layouts.modern')

@section('content')
<h2>Estimasi Anggaran</h2>
<p class="subtitle"><strong>Durasi:</strong> {{ $days }} hari</p>

@php
    $rec = $recommendation ? json_decode($recommendation->response_json, true) : null;
@endphp
@if ($rec)
    @php
        $destName = $rec['destinations'][$destIndex]['name'] ?? null;
        $destCountry = $rec['destinations'][$destIndex]['country'] ?? null;
        $destEst = (float) ($rec['destinations'][$destIndex]['est_budget'] ?? 0);
        $accName = $rec['accommodations'][$accIndex]['name'] ?? ($accOptions[$accIndex] ?? null);
        $accNightly = (float) ($rec['accommodations'][$accIndex]['price_per_night'] ?? 0);
    @endphp
    <div class="section">
        <h3>Pilihan Pengguna</h3>
        <div class="cards">
            <div class="card-item">
                <div class="body">
                    <div class="badge">Destinasi</div>
                    <h4 style="margin:8px 0 6px">{{ $destName ?? '—' }}</h4>
                    <div style="color:#cbd5e1; font-size:13px;">{{ $destCountry ?? '' }}</div>
                    <div style="margin-top:6px"><strong>Rp {{ number_format($destEst * 1000, 0, ',', '.') }}</strong> estimasi destinasi per hari</div>
                </div>
            </div>
            <div class="card-item">
                <div class="body">
                    <div class="badge">Akomodasi</div>
                    <h4 style="margin:8px 0 6px">{{ $accName ?? '—' }}</h4>
                    <div><strong>Rp {{ number_format($accNightly * 1000, 0, ',', '.') }}</strong>/malam</div>
                    <div style="margin-top:4px; color:#cbd5e1; font-size:13px;">Total {{ $days }} malam: <strong>Rp {{ number_format(($accNightly * $days) * 1000, 0, ',', '.') }}</strong></div>
                </div>
            </div>
        </div>
    </div>
@endif

{{-- Form pemilihan akomodasi dihapus: halaman ini langsung menampilkan rincian berdasarkan pilihan sebelumnya --}}

@php
    $accTotal = (float) ($breakdown['accommodation'] ?? 0);
    $act = (float) ($breakdown['activities'] ?? 0);
    $trans = (float) ($breakdown['transport'] ?? 0);
    $total = max(0.0, $accTotal + $act + $trans);
    $accPct = $total > 0 ? round(($accTotal / $total) * 100) : 0;
    $actPct = $total > 0 ? round(($act / $total) * 100) : 0;
    $transPct = $total > 0 ? round(($trans / $total) * 100) : 0;
@endphp

@if (!is_null($estimate))
    <div class="grid grid-2 section">
        <div>
            <h3>Rincian Biaya</h3>
            <div style="display:grid; gap:12px; margin-top:8px">
                <div>
                    <div style="display:flex; justify-content:space-between; margin-bottom:6px"><span>Akomodasi</span><strong>Rp {{ number_format($accTotal * 1000, 0, ',', '.') }}</strong></div>
                    <div style="color:#cbd5e1; font-size:12px; margin-top:-4px; margin-bottom:6px;">{{ $days }} malam × Rp {{ number_format(($rec['accommodations'][$accIndex]['price_per_night'] ?? 0) * 1000, 0, ',', '.') }} / malam</div>
                    <div class="bar"><div class="fill" data-pct="{{ $accPct }}"></div></div>
                </div>
                <div>
                    <div style="display:flex; justify-content:space-between; margin-bottom:6px"><span>Destinasi</span><strong>Rp {{ number_format($act * 1000, 0, ',', '.') }}</strong></div>
                    <div style="color:#cbd5e1; font-size:12px; margin-top:-4px; margin-bottom:6px;">{{ $days }} hari × Rp {{ number_format(($rec['destinations'][$destIndex]['est_budget'] ?? 0) * 1000, 0, ',', '.') }} / hari</div>
                    <div class="bar"><div class="fill" data-pct="{{ $actPct }}"></div></div>
                </div>
                <div>
                    <div style="display:flex; justify-content:space-between; margin-bottom:6px"><span>Transportasi</span><strong>Rp {{ number_format($trans * 1000, 0, ',', '.') }}</strong></div>
                    <div class="bar"><div class="fill" data-pct="{{ $transPct }}"></div></div>
                </div>
            </div>
        </div>
        <div>
            <h3>Ringkasan</h3>
            <p class="subtitle">Estimasi keseluruhan berdasarkan rekomendasi dan preferensi.</p>
            <div class="card-item">
                <div class="body">
                    <div style="font-size:30px; font-weight:800">Rp {{ number_format(($estimate ?? 0) * 1000, 0, ',', '.') }}</div>
                    <div class="section">
                        <a class="btn secondary" href="{{ route('booking.index') }}">Buat Booking</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@else
    <p>Belum ada estimasi anggaran. Jalankan rekomendasi AI atau isi preferensi.</p>
    <div style="margin-top:16px">
        <a class="btn secondary" href="{{ route('booking.index') }}">Buat Booking</a>
    </div>
@endif
<script>
// Set bar widths without embedding Blade expressions in CSS values
(function(){
  try {
    document.querySelectorAll('.bar .fill').forEach(function(el){
      var pct = parseInt(el.getAttribute('data-pct') || '0', 10);
      if (!isNaN(pct) && pct >= 0 && pct <= 100) {
        el.style.width = pct + '%';
      } else {
        el.style.width = '0%';
      }
    });
  } catch(e) { /* noop */ }
})();
</script>
@endsection