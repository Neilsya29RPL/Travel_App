@extends('layouts.modern')

@section('content')
<h2>Booking Tiket/Akomodasi</h2>
{{-- Info durasi preferensi dihapus sesuai permintaan --}}
<style>
    .req { color:#b00020; font-weight:bold; margin-left:4px; }
</style>
@if (!$holiday)
    <p>Belum ada preferensi. Silakan isi terlebih dahulu.</p>
    <a class="btn" href="{{ route('preferences.index') }}">Ke Preferensi</a>
@else
    @if ($recommendation)
        @php($rec = json_decode($recommendation->response_json, true))
        <form method="POST" action="{{ route('booking.create') }}" class="grid">
            @csrf
            <label>Pilih Destinasi <span class="req">*</span></label>
            <select name="destination_index" required>
                @foreach (($rec['destinations'] ?? []) as $idx => $d)
                    <option value="{{ $idx }}">{{ $d['name'] }} — Rp {{ number_format(($d['est_budget'] ?? 0) * 1000, 0, ',', '.') }} / hari</option>
                @endforeach
            </select>

            <label>Pilih Akomodasi <span class="req">*</span></label>
            <select name="accommodation_index" required>
                @foreach (($rec['accommodations'] ?? []) as $idx => $a)
                    <option value="{{ $idx }}">{{ $a['name'] }} ({{ $a['type'] }}) — Rp {{ number_format(($a['price_per_night'] ?? 0) * 1000, 0, ',', '.') }}/malam</option>
                @endforeach
            </select>

            <label>Tanggal Mulai <span class="req">*</span></label>
            <input type="date" name="start_date" required>

            <label>Tanggal Selesai <span class="req">*</span></label>
            <input type="date" name="end_date" required>

            <label>Catatan</label>
            <input type="text" name="notes" placeholder="opsional">

            <button type="submit" class="btn">Buat Booking</button>
        </form>
    @else
        <p>Belum ada rekomendasi. Isi preferensi terlebih dahulu.</p>
        <a class="btn" href="{{ route('preferences.index') }}">Ke Preferensi</a>
    @endif

    @if ($lastBooking)
        <hr style="margin:20px 0">
        <h3>Booking Terakhir</h3>
        <p>Status: <strong>{{ $lastBooking->status }}</strong> — Pembayaran: <strong>{{ $lastBooking->payment_status }}</strong></p>
        <p>Total: Rp {{ number_format(($lastBooking->total_amount ?? 0) * 1000, 0, ',', '.') }}</p>
        @if ($lastBooking->payment_status !== 'paid')
            <a href="{{ route('payment.checkout', ['booking' => $lastBooking->booking_id]) }}" class="btn">Bayar Sekarang</a>
        @else
            <p>Pembayaran selesai. Selamat liburan!</p>
        @endif
    @endif

@endif
@endsection