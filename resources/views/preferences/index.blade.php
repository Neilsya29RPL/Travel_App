@extends('layouts.modern')

@section('content')
<style>
    .error-text{color:#ef4444; font-size:12px; margin-top:8px}
    label.required::after{content:' *'; color:#ef4444}
    /* UI pilihan destinasi yang lebih bagus */
    .card-item.selectable{position:relative; border:1px solid var(--border); transition:box-shadow .2s ease, border-color .2s ease; cursor:pointer}
    .card-item.selectable.selected{border-color:#5c8df6; box-shadow:0 0 0 2px rgba(92,141,246,.25)}
    .card-item.selectable.selected::after{content:'\2713'; position:absolute; left:12px; top:12px; background:rgba(26,34,50,.85); border:1px solid #3a4253; color:#cbd5e1; width:24px; height:24px; display:flex; align-items:center; justify-content:center; border-radius:8px; font-size:14px}
    /* Loading popup */
    #loading-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 9999; justify-content: center; align-items: center; }
    .loader { border: 4px solid #3a4253; border-top: 4px solid #5c8df6; border-radius: 50%; width: 40px; height: 40px; animation: spin 1s linear infinite; margin: 0 auto; }
    @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    /* Flat step navigation */
    .flat-steps{display:flex; align-items:center; gap:12px; margin:8px 0 20px}
    .flat-steps .step{display:flex; flex-direction:column; align-items:center; text-decoration:none; color:#cdd3e1}
    .flat-steps .step .label{margin-bottom:6px; font-size:14px}
    .flat-steps .step .node{width:28px; height:28px; border-radius:999px; background:#6b7280; display:flex; align-items:center; justify-content:center; font-weight:700; color:#fff}
    .flat-steps .connector{flex:1; height:6px; background:#6b7280; border-radius:999px; min-width:40px}
    .flat-steps .step.active .node{background:#8b5cf6}
    .flat-steps .step.completed .node{background:#8b5cf6}
    .flat-steps .connector.active{background:#8b5cf6}
</style>
<h2>Preferensi Destinasi</h2>
<div class="flat-steps">
    <a href="{{ route('preferences.index') }}" class="step active">
        <div class="label">Preferensi Destinasi</div>
        <div class="node">1</div>
    </a>
    <div class="connector{{ isset($recommendation) && $recommendation ? ' active' : '' }}"></div>
    <a href="{{ isset($recommendation) && $recommendation ? route('recommendations.accommodations', ['recommendation_id' => $recommendation->recommendation_id]) : route('recommendations.accommodations') }}" class="step">
        <div class="label">Preferensi Akomodasi</div>
        <div class="node">2</div>
    </a>
    <div class="connector"></div>
    <a href="{{ route('budget.index') }}" class="step">
        <div class="label">Anggaran</div>
        <div class="node">3</div>
    </a>
</div>
<form method="POST" action="{{ route('preferences.store') }}" class="grid grid-2" id="prefForm">
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

<div id="loading-overlay">
    <div style="background: #1e293b; padding: 20px 40px; border-radius: 8px; text-align: center; color: #cbd5e1;">
        <div class="loader"></div>
        <h4 style="margin-top: 16px; margin-bottom: 0;">Finding Recommendations...</h4>
        <p style="font-size: 14px; color: #94a3b8;">Please wait a moment, this may take a while.</p>
    </div>
</div>

@if (isset($recommendation) && $recommendation)
    @php($rec = json_decode($recommendation->response_json, true))

    <div class="section" style="margin-top:32px">
        <h3>Rekomendasi Destinasi AI</h3>
        <form method="GET" action="{{ route('recommendations.accommodations') }}" id="destForm">
            <input type="hidden" name="recommendation_id" value="{{ $recommendation->recommendation_id ?? '' }}">
            <div class="cards">
                @foreach (($rec['destinations'] ?? []) as $d)
                    <div class="card-item selectable" style="position:relative" data-index="{{ $loop->index }}">
                        @php($imgKeyword = !empty($d['name']) ? explode(',', $d['name'])[0] : 'travel')
                        {{-- Unsplash Currently Error --}}
                        {{-- <div class="media"><img src="https://source.unsplash.com/400x300/?{{ urlencode($imgKeyword) }}" alt="{{ $d['name'] ?? 'Destination' }}"></div> --}}
                        {{-- Picsum --}}
                        <div class="media"><img src="https://picsum.photos/400/300?random={{ $loop->index }}" alt="{{ $d['name'] ?? 'Destination' }}"></div>
                        <div class="body">
                            <div class="badge">{{ $d['mood'] }} · {{ $d['country'] }}</div>
                            <h4 style="margin:8px 0 6px">{{ $d['name'] }}</h4>
                            <div style="color:#cbd5e1; font-size:13px;">Kegiatan: {{ implode(', ', $d['activities'] ?? []) }}</div>
                            <div style="margin-top:8px"><strong>Rp {{ number_format(($d['est_budget'] ?? 0), 0, ',', '.') }}</strong> estimasi</div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div style="margin-top:16px;">
                <div id="selectNote" class="alert alert-danger alert-inline" role="alert" style="display:none; margin:0 0 10px 0">
                    <span>Silahkan pilih destinasi terlebih dahulu! </span>
                </div>
                <div style="display:flex; justify-content:space-between; align-items:center">
                    <button type="submit" class="btn">Next</button>
                    <input type="hidden" name="selected_destinations[]" id="selectedIndex">
                </div>
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

<script>
    // Show loading popup on form submit
    (function(){
        const prefForm = document.getElementById('prefForm');
        const loadingOverlay = document.getElementById('loading-overlay');
        if (prefForm && loadingOverlay) {
            prefForm.addEventListener('submit', function() {
                // Basic validation check before showing loader
                if (prefForm.checkValidity()) {
                    loadingOverlay.style.display = 'flex';
                }
            });
        }
    })();
</script>

@endsection
