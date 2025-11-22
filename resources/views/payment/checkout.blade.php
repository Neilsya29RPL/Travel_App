@extends('layouts.modern')

@section('content')
<h2>Checkout Pembayaran</h2>
<p class="subtitle">Pilih metode dan selesaikan pembayaran untuk booking Anda.</p>

@if (session('error'))
    <div class="error">{{ session('error') }}</div>
@endif

@if (isset($booking))
    <div class="card" style="padding:16px; margin-bottom:16px">
        <h3 style="margin-top:0">Ringkasan Booking</h3>
        <p><strong>ID Booking:</strong> {{ $booking->booking_id }}</p>
        <p><strong>Destinasi:</strong> {{ $booking->destination_name ?? 'Destinasi' }}</p>
        <p><strong>Akomodasi:</strong> {{ $booking->accommodation_name ?? 'Akomodasi' }}</p>
        <p><strong>Total:</strong> Rp {{ number_format(($booking->total_amount ?? 0), 0, ',', '.') }}</p>
    </div>

    <form method="POST" action="{{ route('payment.pay') }}" class="card" style="padding:16px">
        @csrf
        <input type="hidden" name="booking_id" value="{{ $booking->booking_id }}" />

        <h3 style="margin-top:0">Metode Pembayaran</h3>
        <style>
            .method-group { display:flex; gap:12px; flex-wrap:wrap; align-items:center; }
            .method-chip { display:flex; align-items:center; gap:10px; padding:10px 14px; border:1px solid var(--border); border-radius:999px; cursor:pointer; background:#0f131a; }
            .method-chip input { accent-color:#4da3ff; }
            .method-chip span { font-weight:600; }
            .req { color:#ef4444; margin-left:4px; font-weight:bold }
        </style>
        <div class="method-group">
            <label class="method-chip">
                <input type="radio" name="method" value="ewallet" checked>
                <span>E-Wallet</span>
                <small style="opacity:.7">(Gopay / OVO / DANA)</small>
            </label>
            <label class="method-chip">
                <input type="radio" name="method" value="bank_transfer">
                <span>Transfer Bank</span>
                <small style="opacity:.7">(BCA / Mandiri / BRI / BNI / Permata)</small>
            </label>
        </div>

        <div style="margin-top:14px">
            <div id="ewalletFields" class="card" style="background:#0f131a; padding:12px; margin-bottom:12px">
                <h4 style="margin:0 0 8px 0">Detail E-Wallet</h4>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px">
                    <div>
                        <label>Penyedia <span class="req">*</span></label>
                        <select name="ewallet_provider">
                            <option value="Gopay">Gopay</option>
                            <option value="Ovo">OVO</option>
                            <option value="DANA">DANA</option>
                        </select>
                    </div>
                    <div>
                        <label>No. Ponsel <span class="req">*</span></label>
                        <input type="tel" name="ewallet_phone" placeholder="0812xxxxxxx">
                    </div>
                </div>
            </div>

            <div id="bankFields" class="card" style="display:none; background:#0f131a; padding:12px; margin-bottom:12px">
                <h4 style="margin:0 0 8px 0">Detail Transfer Bank</h4>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px">
                    <div>
                        <label>Bank <span class="req">*</span></label>
                        <select name="bank_name">
                            <option value="BCA">BCA</option>
                            <option value="Mandiri">Mandiri</option>
                            <option value="BRI">BRI</option>
                            <option value="BNI">BNI</option>
                            <option value="Permata">Permata</option>
                        </select>
                    </div>
                    <div>
                        <label>No. Rekening <span class="req">*</span></label>
                        <input type="text" name="account_number" placeholder="##########">
                    </div>
                </div>
            </div>
        </div>

        <p style="margin-top:12px; color:#9fb0c7">Simulasi pembayaran: setelah klik Bayar, sistem memvalidasi data, memproses, dan menandai booking sebagai <strong>Lunas</strong>.</p>

        <button type="submit" class="btn">Bayar Sekarang</button>
        <a href="{{ route('bookings.index') }}" class="btn secondary" style="margin-left:8px">Kembali</a>

        <script>
            (function() {
                const radios = document.querySelectorAll('input[name="method"]');
                const ewallet = document.getElementById('ewalletFields');
                const bank = document.getElementById('bankFields');

                function setRequired(group, required) {
                    group.querySelectorAll('input, select').forEach(el => {
                        if (required) el.setAttribute('required', 'required');
                        else el.removeAttribute('required');
                    });
                }

                function update() {
                    const v = document.querySelector('input[name="method"]:checked').value;
                    ewallet.style.display = v === 'ewallet' ? 'block' : 'none';
                    bank.style.display = v === 'bank_transfer' ? 'block' : 'none';

                    setRequired(ewallet, v === 'ewallet');
                    setRequired(bank, v === 'bank_transfer');
                }

                radios.forEach(r => r.addEventListener('change', update));
                update();
            })();
        </script>
    </form>
@else
    <p>Booking tidak ditemukan.</p>
@endif
@endsection