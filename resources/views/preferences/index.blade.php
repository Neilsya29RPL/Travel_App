@extends('layouts.modern')

@section('content')
<h2>Preferensi Liburan</h2>
<form method="POST" action="{{ route('preferences.store') }}" class="grid grid-2">
    @csrf
    <div>
        <label>Kategori</label>
        <select name="category_id">
            <option value="">-- pilih --</option>
            @foreach ($categories as $c)
                <option value="{{ $c->category_id ?? $c->id }}">{{ $c->category_name ?? $c->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label>Negara</label>
        <select name="country_id">
            <option value="">-- pilih --</option>
            @foreach ($countries as $co)
                <option value="{{ $co->country_id ?? $co->id }}">{{ $co->country_name ?? $co->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label>Mood</label>
        <select name="mood_id">
            <option value="">-- pilih --</option>
            @foreach ($moods as $m)
                <option value="{{ $m->mood_id ?? $m->id }}">{{ $m->mood_name ?? $m->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label>Hint Destinasi</label>
        <input type="text" name="destination_hint" placeholder="cth: pantai, pegunungan">
    </div>
    <div>
        <label>Durasi (hari)</label>
        <input type="number" name="duration_days" min="1" value="{{ $holiday->duration_days ?? '' }}">
    </div>
    <div>
        <label>Budget Minimal</label>
        <input type="number" name="budget_min" min="0" step="0.01" value="{{ $holiday->budget_min ?? '' }}">
    </div>
    <div>
        <label>Budget Maksimal</label>
        <input type="number" name="budget_max" min="0" step="0.01" value="{{ $holiday->budget_max ?? '' }}">
    </div>
    <div>
        <label>&nbsp;</label>
        <button type="submit" class="btn">Simpan Preferensi</button>
    </div>
</form>

@endsection