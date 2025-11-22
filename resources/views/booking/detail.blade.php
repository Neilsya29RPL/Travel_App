@extends('layouts.modern')

@section('content')
<h2>Detail Booking</h2>
<p class="subtitle">Informasi lengkap pemesanan Anda.</p>

<a href="{{ route('bookings.index') }}" class="btn" style="margin-bottom:14px">Kembali ke Daftar</a>

@if (session('error'))
    <div class="error">{{ session('error') }}</div>
@endif

@if (isset($booking))
    <div class="card" style="padding:16px; margin-bottom:16px">
        <div style="display:flex; gap:24px; align-items:flex-start; flex-wrap:wrap">
            <div style="min-width:260px">
                <h3 style="margin:0 0 6px">Umum</h3>
                <p><strong>ID Booking:</strong> {{ $booking->booking_id }}</p>
                <p><strong>Tanggal Booking:</strong> {{ $booking->booking_date ? \Carbon\Carbon::parse($booking->booking_date)->format('d M Y') : '-' }}</p>
                <p><strong>Status:</strong> {{ $booking->status }}</p>
                <p><strong>Status Pembayaran:</strong> {{ $booking->payment_status ?? '-' }}</p>
            </div>
            <div style="min-width:260px">
                <h3 style="margin:0 0 6px">Rincian</h3>
                <p><strong>Destinasi:</strong> {{ $booking->destination_name ?? 'Destinasi' }}</p>
                <p><strong>Akomodasi:</strong> {{ $booking->accommodation_name ?? 'Akomodasi' }}</p>
                <p><strong>Durasi (hari):</strong> {{ $duration_days ?? '-' }}</p>
                <p><strong>Total:</strong> Rp {{ number_format(($booking->total_amount ?? 0), 0, ',', '.') }}</p>
            </div>
        </div>
    </div>

    <h3 style="margin:12px 0">Riwayat Pembayaran</h3>
    @if(isset($payments) && count($payments))
        <style>
            table.table { width:100%; border-collapse: collapse; }
            .table th, .table td { border:1px solid var(--border); padding:10px 12px; text-align:left; }
            .table th { background:#0e1118; color:#cdd3e1; }
            .table tr:nth-child(even) { background:#0f131a; }
        </style>
        <table class="table">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Metode</th>
                    <th>Jumlah</th>
                    <th>Status</th>
                    <th>Transaksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($payments as $p)
                <tr>
                    <td>
                        @php($dt = $p->paid_at ?: $p->created_at)
                        {{ $dt ? \Carbon\Carbon::parse($dt)->timezone('Asia/Jakarta')->format('d M Y H:i') : '-' }}
                    </td>
                    <td>{{ $p->method ?? '-' }}</td>
                    <td>Rp {{ number_format(($p->amount ?? 0), 0, ',', '.') }}</td>
                    <td>{{ $p->status ?? '-' }}</td>
                    <td><code>{{ $p->transaction_id ?? '-' }}</code></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>Belum ada pembayaran.</p>
    @endif
@else
    <p>Booking tidak ditemukan.</p>
@endif
@endsection