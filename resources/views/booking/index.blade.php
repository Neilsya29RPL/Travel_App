@extends('layouts.modern')

@section('content')
<h2>Booking Tiket/Akomodasi</h2>
@if (!$holiday)
    <p>Belum ada preferensi. Silakan isi terlebih dahulu.</p>
    <a class="btn" href="{{ route('preferences.index') }}">Ke Preferensi</a>
@else
    @if ($recommendation)
        @php($rec = json_decode($recommendation->response_json, true))
        <form method="POST" action="{{ route('booking.create') }}" class="grid">
            @csrf
            <label>Pilih Destinasi</label>
            <select name="destination_index" required>
                @foreach (($rec['destinations'] ?? []) as $idx => $d)
                    <option value="{{ $idx }}">{{ $d['name'] }} — Rp {{ number_format(($d['est_budget'] ?? 0) * 1000, 0, ',', '.') }}</option>
                @endforeach
            </select>

            <label>Pilih Akomodasi</label>
            <select name="accommodation_index" required>
                @foreach (($rec['accommodations'] ?? []) as $idx => $a)
                    <option value="{{ $idx }}">{{ $a['name'] }} ({{ $a['type'] }}) — Rp {{ number_format(($a['price_per_night'] ?? 0) * 1000, 0, ',', '.') }}/malam</option>
                @endforeach
            </select>

            <label>Tanggal Mulai</label>
            <input type="date" name="start_date" required>

            <label>Tanggal Selesai</label>
            <input type="date" name="end_date" required>

            <label>Jumlah Penumpang</label>
            <input type="number" name="passengers" min="1" value="1" required>

            <label>Catatan</label>
            <input type="text" name="notes" placeholder="opsional">

            <button type="submit" class="btn">Buat Booking</button>
        </form>
    @else
        <p>Belum ada rekomendasi. Jalankan AI terlebih dahulu.</p>
        <a class="btn" href="{{ route('recommendations.index') }}">Jalankan Rekomendasi</a>
    @endif

    @if ($lastBooking)
        <hr style="margin:20px 0">
        <h3>Booking Terakhir</h3>
        <p>Status: <strong>{{ $lastBooking->status }}</strong> — Pembayaran: <strong>{{ $lastBooking->payment_status }}</strong></p>
        <p>Total: Rp {{ number_format(($lastBooking->total_amount ?? 0) * 1000, 0, ',', '.') }}</p>
        @if ($lastBooking->payment_status !== 'paid')
            <form method="POST" action="{{ route('payment.pay') }}">
                @csrf
                <label>Metode Pembayaran</label>
                <select name="method" required>
                    <option value="credit_card" {{ (($holiday->default_payment_method ?? 'ewallet') === 'credit_card') ? 'selected' : '' }}>Kartu Kredit</option>
                    <option value="bank_transfer" {{ (($holiday->default_payment_method ?? 'ewallet') === 'bank_transfer') ? 'selected' : '' }}>Transfer Bank</option>
                    <option value="ewallet" {{ (($holiday->default_payment_method ?? 'ewallet') === 'ewallet') ? 'selected' : '' }}>E-Wallet</option>
                </select>
                <button type="submit" class="btn">Bayar Sekarang</button>
            </form>
        @else
            <p>Pembayaran selesai. Selamat liburan!</p>
        @endif
    @endif

@endif
@endsection