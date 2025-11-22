@extends('layouts.modern')

@section('content')
<div class="container py-4">
    <h1 class="page-title">Preferensi Akomodasi</h1>
    <style>
        .flat-steps{display:flex; align-items:center; gap:12px; margin:8px 0 20px}
        .flat-steps .step{display:flex; flex-direction:column; align-items:center; text-decoration:none; color:#cdd3e1}
        .flat-steps .step .label{margin-bottom:6px; font-size:14px}
        .flat-steps .step .node{width:28px; height:28px; border-radius:999px; background:#6b7280; display:flex; align-items:center; justify-content:center; font-weight:700; color:#fff}
        .flat-steps .connector{flex:1; height:6px; background:#6b7280; border-radius:999px; min-width:40px}
        .flat-steps .step.active .node{background:#8b5cf6}
        .flat-steps .step.completed .node{background:#8b5cf6}
        .flat-steps .connector.active{background:#8b5cf6}
    </style>
    <div class="flat-steps">
        <a href="{{ route('preferences.index') }}" class="step completed">
            <div class="label">Preferensi Destinasi</div>
            <div class="node">1</div>
        </a>
        <div class="connector active"></div>
        <a href="{{ route('recommendations.accommodations') }}" class="step active">
            <div class="label">Preferensi Akomodasi</div>
            <div class="node">2</div>
        </a>
        <div class="connector"></div>
        <a href="{{ route('budget.index') }}" class="step">
            <div class="label">Anggaran</div>
            <div class="node">3</div>
        </a>
    </div>

    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if (empty($selectedDestinations))
        <p>Tidak ada destinasi yang dipilih. Silakan kembali dan pilih rekomendasi.</p>
        <a href="{{ route('preferences.index') }}" class="btn btn-secondary">Kembali ke Preferensi</a>
    @else
        <div class="mt-3" style="margin-bottom:16px">
            <h4>Destinasi Terpilih</h4>
            <div style="margin-bottom:8px; display:flex; gap:6px; flex-wrap:wrap;">
                <span class="badge">Kategori: {{ $prefCategoryName ?? '-' }}</span>
                <span class="badge">Negara: {{ $prefCountryName ?? '-' }}</span>
                <span class="badge">Mood: {{ $prefMoodName ?? '-' }}</span>
            </div>
            <div style="font-size:16px; margin-bottom:12px;">
                <strong>{{ $selectedDestinations[0]['name'] ?? 'Destinasi' }}</strong>
                <span style="color:#94a3b8; margin-left:6px;">{{ $selectedDestinations[0]['country'] ?? '' }}</span>
            </div>
        </div>

        <div class="mt-4" style="margin-top:20px">
            <form method="POST" action="{{ route('recommendations.accommodations') }}" class="form-inline" style="display:flex; align-items:center; gap:12px; max-width:700px">
                @csrf
                <input type="hidden" name="recommendation_id" value="{{ $recommendationId }}" />
                @if (!empty($selectedIndexes))
                    @foreach ($selectedIndexes as $idx)
                        <input type="hidden" name="selected_destinations[]" value="{{ $idx }}" />
                    @endforeach
                @endif
                <div class="form-group" style="flex:1">
                    <label for="duration_days" style="display:block; margin-bottom:6px; font-weight:600">Durasi Menginap <span style="color:#ef4444" title="Wajib" aria-hidden="true">*</span></label>
                    <input type="number" id="duration_days" name="duration_days" min="1" max="60" class="form-control" placeholder="Durasi (malam)" value="{{ old('duration_days') }}" required aria-required="true" />
                    @error('duration_days')
                        <div class="text-danger" style="margin-top:6px; font-size:13px;">{{ $message }}</div>
                    @enderror
                </div>
                <button type="submit" class="btn btn-primary">Find Recommendation</button>
            </form>
        </div>

        @if(isset($showResults) && $showResults)
            @if(!empty($accommodations))
                <style>
                    .card-item.selectable{position:relative; border:1px solid var(--border); transition:box-shadow .2s ease, border-color .2s ease; cursor:pointer}
                    .card-item.selectable.selected{border-color:#5c8df6; box-shadow:0 0 0 2px rgba(92,141,246,.25)}
                    .card-item.selectable.selected::after{content:'\2713'; position:absolute; left:12px; top:12px; background:rgba(26,34,50,.85); border:1px solid #3a4253; color:#cbd5e1; width:24px; height:24px; display:flex; align-items:center; justify-content:center; border-radius:8px; font-size:14px}
                    .bottom-actions{margin-top:16px; padding-top:12px; border-top:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px}
                    .budget-line{color:#cbd5e1; font-size:13px}
                </style>
                <div class="mt-4">
                    <h3>Rekomendasi Akomodasi</h3>
                    <p>Menampilkan harga per malam dan estimasi total untuk {{ $nights }} malam.</p>
                    <div class="cards" id="accCards" data-dest-index="{{ !empty($selectedIndexes) ? (int) $selectedIndexes[0] : 0 }}">
                        @foreach ($accommodations as $acc)
                            <div class="card-item selectable" data-index="{{ $acc['rec_index'] ?? $loop->index }}" data-total="{{ (int)($acc['total_estimate'] ?? 0) }}">
                                <div class="media"><img src="/images/Akomodasi_Bali.jpg" alt="Akomodasi"></div>
                                <div class="body">
                                    <div class="badge">{{ $acc['type'] ?? 'Tipe' }} · {{ $acc['price_per_night'] ? ('Rp '.number_format(($acc['price_per_night'] ?? 0),0,',','.').'/malam') : '—' }}</div>
                                    <h4 style="margin:8px 0 6px">{{ $acc['name'] ?? 'Akomodasi' }}</h4>
                                    <div style="color:#cbd5e1; font-size:13px;">Durasi: {{ $nights }} malam</div>
                                    <div style="margin-top:8px"><strong>Rp {{ number_format(($acc['total_estimate'] ?? 0),0,',','.') }}</strong> estimasi</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="bottom-actions">
                        <div>
                            <div><strong>Estimasi pilihan: Rp <span id="selTotal">0</span></strong></div>
                            @if(!is_null($prefBudgetMax))
                                <div class="budget-line">Budget maksimum: Rp {{ number_format(($prefBudgetMax ?? 0),0,',','.') }} @if(!is_null($prefBudgetMin)) · Min: Rp {{ number_format(($prefBudgetMin ?? 0),0,',','.') }} @endif</div>
                            @endif
                        </div>
                        <div style="display:flex; gap:8px">
                            <a href="{{ route('budget.index') }}" id="budgetLink" class="btn btn-secondary">Cek Estimasi Anggaran</a>
                        </div>
                    </div>
                    <div id="noSelectionAlert" class="alert alert-danger" style="display:none; margin-top:12px">
                        <span class="icon">!</span>
                        <div>Silakan pilih akomodasi terlebih dahulu.</div>
                    </div>
                    <script>
                        (function(){
                            const cards = document.querySelectorAll('#accCards .card-item.selectable');
                            const totalEl = document.getElementById('selTotal');
                            const budgetLink = document.getElementById('budgetLink');
                            const bookingLink = document.getElementById('bookingLink');
                            const noSelAlert = document.getElementById('noSelectionAlert');
                            const fmt = (n)=> new Intl.NumberFormat('id-ID').format(n);
                            const destIndex = (function(){
                                const el = document.getElementById('accCards');
                                const raw = el ? el.getAttribute('data-dest-index') : '0';
                                const num = parseInt(raw || '0', 10);
                                return isNaN(num) ? 0 : num;
                            })();
                            let selected = null;
                            let selectedIndex = 0;
                            function updateLink(accIdx){
                                if (!budgetLink) return;
                                const base = budgetLink.getAttribute('href').split('?')[0];
                                const qs = new URLSearchParams({acc: String(accIdx||0), dest: String(destIndex||0)}).toString();
                                budgetLink.setAttribute('href', base + '?' + qs);
                            }
                            function refresh(){
                                selected = null;
                                selectedIndex = 0;
                                cards.forEach(c => { if (c.classList.contains('selected') && selected === null) { selected = c; } });
                                const val = selected ? parseInt(selected.getAttribute('data-total')||'0', 10) : 0;
                                selectedIndex = selected ? parseInt(selected.getAttribute('data-index')||'0', 10) : 0;
                                totalEl.textContent = fmt(val);
                                updateLink(selectedIndex);
                                if (selected && noSelAlert) { noSelAlert.style.display = 'none'; }
                            }
                            cards.forEach(card => {
                                card.addEventListener('click', (e)=>{
                                    // selalu pilih tunggal, seperti destinasi
                                    cards.forEach(c => c.classList.remove('selected'));
                                    card.classList.add('selected');
                                    refresh();
                                });
                            });
                            function guardSelection(e){
                                if (!selected) {
                                    if (noSelAlert) { noSelAlert.style.display = 'flex'; }
                                    e.preventDefault();
                                    e.stopPropagation();
                                    return false;
                                }
                                return true;
                            }
                            if (budgetLink) budgetLink.addEventListener('click', guardSelection);
                            if (bookingLink) bookingLink.addEventListener('click', guardSelection);
                            refresh();
                        })();
                    </script>
                </div>
            @else
                <div class="mt-4">
                    <p>Tidak ada rekomendasi akomodasi dari AI. Silakan klik <strong>Find Recommendation</strong> setelah mengisi durasi.</p>
                </div>
            @endif
        @else
            <div class="mt-4">
                <p>Isi durasi lalu klik <strong>Find Recommendation</strong> untuk melihat rekomendasi akomodasi dari AI.</p>
            </div>
        @endif
    @endif
</div>
@endsection