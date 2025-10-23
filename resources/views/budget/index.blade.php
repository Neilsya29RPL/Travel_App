@extends('layouts.modern')

@section('content')
<h2>Estimasi Anggaran</h2>
@if ($holiday)
    <p class="subtitle"><strong>Durasi:</strong> {{ $holiday->duration_days ?? 'N/A' }} hari</p>
@endif

@if (!empty($accOptions))
    <form method="GET" action="{{ route('budget.index') }}" style="margin:12px 0; display:flex; gap:8px; align-items:center">
        <label for="acc">Akomodasi:</label>
        <select id="acc" name="acc">
            @foreach ($accOptions as $idx => $name)
                <option value="{{ $idx }}" {{ ($accIndex ?? 0) == $idx ? 'selected' : '' }}>{{ $name }}</option>
            @endforeach
        </select>
        <button class="btn" type="submit">Terapkan</button>
    </form>
    <p class="subtitle">Perhitungan di bawah mengikuti akomodasi yang dipilih.</p>
@endif

@php
    $acc = (float) ($breakdown['accommodation'] ?? 0);
    $act = (float) ($breakdown['activities'] ?? 0);
    $trans = (float) ($breakdown['transport'] ?? 0);
    $total = max(0.0, $acc + $act + $trans);
@endphp

@if (!is_null($estimate))
    <div class="grid grid-2 section">
        <div>
            <h3>Rincian Biaya</h3>
            <div style="display:grid; gap:12px; margin-top:8px">
                <div>
                    <div style="display:flex; justify-content:space-between; margin-bottom:6px"><span>Akomodasi</span><strong>Rp {{ number_format($acc * 1000, 0, ',', '.') }}</strong></div>
                    <div class="bar"><div class="fill" style="width: {{ $total > 0 ? round(($acc / $total) * 100) : 0 }}%"></div></div>
                </div>
                <div>
                    <div style="display:flex; justify-content:space-between; margin-bottom:6px"><span>Aktivitas</span><strong>Rp {{ number_format($act * 1000, 0, ',', '.') }}</strong></div>
                    <div class="bar"><div class="fill" style="width: {{ $total > 0 ? round(($act / $total) * 100) : 0 }}%"></div></div>
                </div>
                <div>
                    <div style="display:flex; justify-content:space-between; margin-bottom:6px"><span>Transportasi</span><strong>Rp {{ number_format($trans * 1000, 0, ',', '.') }}</strong></div>
                    <div class="bar"><div class="fill" style="width: {{ $total > 0 ? round(($trans / $total) * 100) : 0 }}%"></div></div>
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
                        <a class="btn" href="{{ route('recommendations.index') }}">Lihat Rekomendasi</a>
                        <a class="btn secondary" href="{{ route('booking.index') }}">Buat Booking</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@else
    <p>Belum ada estimasi anggaran. Jalankan rekomendasi AI atau isi preferensi.</p>
    <div style="margin-top:16px">
        <a class="btn" href="{{ route('recommendations.index') }}">Lihat Rekomendasi</a>
        <a class="btn secondary" href="{{ route('booking.index') }}">Buat Booking</a>
    </div>
@endif
@endsection