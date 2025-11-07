@extends('layouts.modern')

@section('content')
<style>
    .error-text{color:#ef4444; font-size:12px; margin-top:8px}
    label.required::after{content:' *'; color:#ef4444}
    .alert-error{background:#fee2e2; color:#b91c1c; border:1px solid #fca5a5; padding:10px; border-radius:6px; margin-top:8px; margin-bottom:12px}
    /* UI pilihan destinasi yang lebih bagus */
    .card-item.selectable{position:relative; border:1px solid var(--border); transition:box-shadow .2s ease, border-color .2s ease; cursor:pointer}
    .card-item.selectable.selected{border-color:#5c8df6; box-shadow:0 0 0 2px rgba(92,141,246,.25)}
    .card-item.selectable.selected::after{content:'\2713'; position:absolute; left:12px; top:12px; background:rgba(26,34,50,.85); border:1px solid #3a4253; color:#cbd5e1; width:24px; height:24px; display:flex; align-items:center; justify-content:center; border-radius:8px; font-size:14px}
</style>
<h2>Preferensi Liburan</h2>
<form method="POST" action="{{ route('preferences.store') }}" class="grid grid-2">
    @csrf
    <div>
        <label class="required">Kategori</label>
        <select name="category_id" required>
            <option value="">-- pilih --</option>
            @foreach ($categories as $c)
                <option value="{{ $c->category_id ?? $c->id }}" {{ old('category_id') == ($c->category_id ?? $c->id) ? 'selected' : '' }}>{{ $c->category_name ?? $c->name }}</option>
            @endforeach
        </select>
        @error('category_id')
            <div class="error-text">{{ $message }}</div>
        @enderror
    </div>
    <div>
        <label class="required">Negara</label>
        <select name="country_id" required>
            <option value="">-- pilih --</option>
            @foreach ($countries as $co)
                <option value="{{ $co->country_id ?? $co->id }}" {{ old('country_id') == ($co->country_id ?? $co->id) ? 'selected' : '' }}>{{ $co->country_name ?? $co->name }}</option>
            @endforeach
        </select>
        @error('country_id')
            <div class="error-text">{{ $message }}</div>
        @enderror
    </div>
    <div>
        <label class="required">Mood</label>
        <select name="mood_id" required>
            <option value="">-- pilih --</option>
            @foreach ($moods as $m)
                <option value="{{ $m->mood_id ?? $m->id }}" {{ old('mood_id') == ($m->mood_id ?? $m->id) ? 'selected' : '' }}>{{ $m->mood_name ?? $m->name }}</option>
            @endforeach
        </select>
        @error('mood_id')
            <div class="error-text">{{ $message }}</div>
        @enderror
    </div>
    <div>
        <label>Hint Destinasi (opsional)</label>
        <input type="text" name="destination_hint" placeholder="cth: pantai, pegunungan" value="{{ old('destination_hint') }}">
        @error('destination_hint')
            <div class="error-text">{{ $message }}</div>
        @enderror
    </div>
    <div>
        <label class="required">Budget Minimal</label>
        <input type="number" name="budget_min" min="0" step="0.01" value="{{ old('budget_min') }}" placeholder="cth: 1000000" required>
        @error('budget_min')
            <div class="error-text">{{ $message }}</div>
        @enderror
    </div>
    <div>
        <label class="required">Budget Maksimal</label>
        <input type="number" name="budget_max" min="0" step="0.01" value="{{ old('budget_max') }}" placeholder="cth: 5000000" required>
        @error('budget_max')
            <div class="error-text">{{ $message }}</div>
        @enderror
    </div>
    <div>
        <label>&nbsp;</label>
        <button type="submit" class="btn">Find Recommendation</button>
    </div>
</form>

@if (isset($recommendation) && $recommendation)
    @php($rec = json_decode($recommendation->response_json, true))

    <div class="section" style="margin-top:32px">
        <h3>Rekomendasi Destinasi AI</h3>
        <form method="GET" action="{{ route('recommendations.accommodations') }}" id="destForm">
            <input type="hidden" name="recommendation_id" value="{{ $recommendation->recommendation_id ?? '' }}">
            <div class="cards">
                @foreach (($rec['destinations'] ?? []) as $d)
                    <div class="card-item selectable" style="position:relative" data-index="{{ $loop->index }}">
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
            <div style="margin-top:16px; display:flex; justify-content:space-between; align-items:center">
                <div id="selectNote" class="alert-error" style="display:none; margin:0">Silahkan pilih destinasi terlebih dahulu.</div>
                <input type="hidden" name="selected_destinations[]" id="selectedIndex">
                <button type="submit" class="btn">Next</button>
            </div>
            <script>
                // Klik kartu untuk memilih satu destinasi dengan highlight
                (function(){
                    const cards = document.querySelectorAll('.card-item.selectable');
                    const hidden = document.getElementById('selectedIndex');
                    const form = document.getElementById('destForm');
                    const note = document.getElementById('selectNote');
                    cards.forEach(card => {
                        card.addEventListener('click', () => {
                            cards.forEach(el => el.classList.remove('selected'));
                            card.classList.add('selected');
                            hidden.value = card.getAttribute('data-index');
                            if (note) { note.style.display = 'none'; }
                        });
                    });
                    if (form) {
                        form.addEventListener('submit', (e) => {
                            if (!hidden.value) {
                                e.preventDefault();
                                if (note) { note.style.display = 'block'; }
                                const firstCard = cards[0];
                                if (firstCard) { firstCard.scrollIntoView({behavior:'smooth', block:'center'}); }
                            }
                        });
                    }
                })();
            </script>
        </form>
    </div>
@endif

@endsection
