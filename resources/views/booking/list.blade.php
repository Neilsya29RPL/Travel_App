@extends('layouts.modern')

@section('content')
<h2>Daftar Booking</h2>
<p class="subtitle">Lihat semua pemesanan Anda dan bayar yang belum lunas.</p>

<a href="{{ route('booking.index') }}" class="btn" style="margin-bottom:14px">Buat Booking</a>

@if ($errors->any())
    <div class="error">{{ $errors->first() }}</div>
@endif

@if (isset($bookings) && count($bookings))
    <style>
        /* Tambahkan jarak di dalam sel tabel agar tidak dempet */
        .table { width: 100%; }
        .table th, .table td { padding: 10px 12px; line-height: 1.4; vertical-align: middle; }
        .table .btn{white-space:nowrap; padding:8px 12px; font-size:14px}
        .badge-success{display:inline-flex; align-items:center; white-space:nowrap; background:#0f2a1f; border:1px solid #1b5b3f; color:#a7f3d0; padding:4px 10px; border-radius:999px; font-size:12px; line-height:1}
        .badge-danger{display:inline-flex; align-items:center; white-space:nowrap; background:#2a0f12; border:1px solid #b91c1c; color:#fca5a5; padding:4px 10px; border-radius:999px; font-size:12px; line-height:1}
    </style>
    <table class="table">
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Destinasi</th>
                <th>Akomodasi</th>
                <th>Total</th>
                <th>Status</th>
                <th>Pembayaran</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($bookings as $b)
                <tr>
                    <td>
                        @php($fmt = fn($d) => \Carbon\Carbon::parse($d)->format('d M Y'))
                        @if (!empty($b->start_date) && !empty($b->end_date))
                            {{ $fmt($b->start_date) }} — {{ $fmt($b->end_date) }}
                        @elseif (!empty($b->booking_date) && isset($b->duration_days) && $b->duration_days)
                            @php($start = \Carbon\Carbon::parse($b->booking_date))
                            @php($end = (clone $start)->addDays(max(1, (int) $b->duration_days) - 1))
                            {{ $start->format('d M Y') }} — {{ $end->format('d M Y') }}
                        @else
                            {{ $b->booking_date ? \Carbon\Carbon::parse($b->booking_date)->format('d M Y') : '-' }}
                        @endif
                    </td>
                    <td>{{ $b->destination_name ?? 'Destinasi' }}</td>
                    <td>{{ $b->accommodation_name ?? 'Akomodasi' }}</td>
                    <td>Rp {{ number_format(($b->total_amount ?? 0), 0, ',', '.') }}</td>
                    <td>{{ $b->status }}</td>
                    <td>
                        @if ($b->payment_status !== 'paid')
                            <span class="badge badge-danger">Belum dibayar</span>
                        @else
                            <span class="badge badge-success">Lunas</span>
                        @endif
                    </td>
                    <td>
                        @if ($b->payment_status !== 'paid')
                            <a href="{{ route('payment.checkout', ['booking' => $b->booking_id]) }}" class="btn">Bayar</a>
                        @else
                            <a href="{{ route('bookings.show', ['booking' => $b->booking_id]) }}" class="btn secondary">Lihat Detail</a>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@else
    <p>Belum ada booking.</p>
@endif
@endsection