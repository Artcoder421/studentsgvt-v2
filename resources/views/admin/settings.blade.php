@extends('layouts.admin')

@section('title', 'Site Identity, Motto, Vision & Mission — SONIT Admin')
@section('page_title', 'Editable Organization Motto, Vision, Mission & Contacts')

@section('content')
<div class="card" style="max-width:920px;">
    <p class="text-sm muted mb-4">All changes saved here update the public homepage, leadership pages, and footer immediately across the portal.</p>

    <form method="POST" action="{{ route('admin.settings.update') }}">
        @csrf
        @foreach($settings as $setting)
            <div class="form-group">
                <label class="form-label" for="setting_{{ $setting->key }}">
                    {{ $setting->label ?? ucwords(str_replace('_', ' ', $setting->key)) }}
                    <span class="badge badge-slate mono" style="margin-left:6px;">{{ $setting->group }}</span>
                </label>
                @if(in_array($setting->key, ['vision', 'mission', 'history', 'address']))
                    <textarea id="setting_{{ $setting->key }}" name="settings[{{ $setting->key }}]" rows="3" class="form-control">{{ $setting->value }}</textarea>
                @else
                    <input id="setting_{{ $setting->key }}" type="text" name="settings[{{ $setting->key }}]" value="{{ $setting->value }}" class="form-control">
                @endif
            </div>
        @endforeach

        <button type="submit" class="btn btn-gold btn-lg">Save Portal Identity & Settings</button>
    </form>
</div>
@endsection
