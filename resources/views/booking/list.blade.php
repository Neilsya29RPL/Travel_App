@extends('layouts.modern')

@section('content')
<h2>Daftar Booking</h2>
<p class="subtitle">Lihat semua pemesanan Anda dan bayar yang belum lunas.</p>

<a href="{{ route('booking.index') }}" class="btn secondary" style="margin-bottom:14px">Buat Booking</a>

@if ($errors->any())
    <div class="error">{{ $errors->first() }}</div>
@endif

@if (isset($bookings) && count($bookings))
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
                    <td>{{ $b->booking_date ? \Carbon\Carbon::parse($b->booking_date)->format('d M Y') : '-' }}</td>
                    <td>{{ $b->destination_name ?? 'Destinasi' }}</td>
                    <td>{{ $b->accommodation_name ?? 'Akomodasi' }}</td>
                    <td>Rp {{ number_format(($b->total_amount ?? 0) * 1000, 0, ',', '.') }}</td>
                    <td>{{ $b->status }}</td>
                    <td>
                        @if ($b->payment_status !== 'paid')
                            <span style="color:#b00020; font-weight:bold">Belum dibayar</span>
                        @else
                            <span class="badge">Lunas</span>
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