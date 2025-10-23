@extends('layouts.modern')

@section('content')
<h2>Rekomendasi AI</h2>
@if ($holiday)
    <p class="subtitle"><strong>Preferensi:</strong> Durasi {{ $holiday->duration_days ?? 'N/A' }} hari · Budget Rp {{ number_format(($holiday->budget_min ?? 0) * 1000, 0, ',', '.') }} - Rp {{ number_format(($holiday->budget_max ?? 0) * 1000, 0, ',', '.') }}</p>
@endif

<div class="section">
    <form method="POST" action="{{ route('recommendations.run') }}">
        @csrf
        <button class="btn" type="submit">Jalankan Rekomendasi AI</button>
    </form>
</div>

@if ($recommendation)
    @php($rec = json_decode($recommendation->response_json, true))

    <div class="section">
        <h3>Destinasi</h3>
        <div class="cards">
            @foreach (($rec['destinations'] ?? []) as $d)
                <div class="card-item">
                    <div class="media"><img src="/images/Destinasi_Bali.jpg" alt="Destination"></div>
                    <div class="body">
                        <div class="badge">{{ $d['mood'] }} · {{ $d['country'] }}</div>
                        <h4 style="margin:8px 0 6px">{{ $d['name'] }}</h4>
                        <div style="color:#cbd5e1; font-size:13px;">Kegiatan: {{ implode(', ', $d['activities'] ?? []) }}</div>
                        <div style="margin-top:8px"><strong>Rp {{ number_format(($d['est_budget'] ?? 0) * 1000, 0, ',', '.') }}</strong> estimasi</div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="section">
        <h3>Akomodasi</h3>
        <div class="cards">
            @foreach (($rec['accommodations'] ?? []) as $a)
                <div class="card-item">
                    <div class="media"><img src="/images/Akomodasi_Bali.jpg" alt="Accommodation"></div>
                    <div class="body">
                        <div class="badge">{{ $a['type'] }}</div>
                        <h4 style="margin:8px 0 6px">{{ $a['name'] }}</h4>
                        <div><strong>Rp {{ number_format(($a['price_per_night'] ?? 0) * 1000, 0, ',', '.') }}</strong>/malam</div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="section">
        <a class="btn" href="{{ route('booking.index') }}">Buat Booking</a>
        <a class="btn secondary" href="{{ route('budget.index') }}">Cek Anggaran</a>
    </div>


@endif
@endsection