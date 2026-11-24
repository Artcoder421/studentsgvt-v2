@extends('layouts.app')

@section('title', 'Accommodation Services — SONIT | NIT')

@section('content')
<div class="page-banner">
    <div class="container page-banner-inner">
        <div class="page-banner-left">
            <div class="icon-3d-tile lg"><img src="{{ asset('images/icons/accommodation.svg') }}" alt="Accommodation"></div>
            <div>
                <h1>Student Accommodation & Verified Hostels</h1>
                <p>Safe, affordable on-campus NIT hostels and SONIT-inspected private student residences around Mabibo & Ubungo.</p>
            </div>
        </div>
        @auth
            <a href="#accommApply" class="btn btn-gold">Apply for Placement</a>
        @endauth
    </div>
</div>

<section class="section section-tight">
    <div class="container">
        <div class="flex-center flex-wrap mb-4" style="gap:8px;">
            <a href="{{ route('accommodations.index') }}" class="btn btn-sm {{ !$type && !$gender ? 'btn-primary' : 'btn-outline' }}">All Residences</a>
            <a href="{{ route('accommodations.index', ['type' => 'on_campus_hostel']) }}" class="btn btn-sm {{ $type === 'on_campus_hostel' ? 'btn-primary' : 'btn-outline' }}">On-Campus Hostels</a>
            <a href="{{ route('accommodations.index', ['type' => 'verified_off_campus']) }}" class="btn btn-sm {{ $type === 'verified_off_campus' ? 'btn-primary' : 'btn-outline' }}">Verified Off-Campus</a>
            <a href="{{ route('accommodations.index', ['gender' => 'female']) }}" class="btn btn-sm {{ $gender === 'female' ? 'btn-primary' : 'btn-outline' }}">Female Only</a>
            <a href="{{ route('accommodations.index', ['gender' => 'male']) }}" class="btn btn-sm {{ $gender === 'male' ? 'btn-primary' : 'btn-outline' }}">Male Only</a>
        </div>

        <div class="grid grid-2 mb-4">
            @foreach($accommodations as $acc)
                <article class="card card-hover" style="display:flex;flex-direction:column;justify-content:space-between;">
                    <div>
                        <img src="{{ asset($acc->image ?? 'images/campus/building.jpg') }}" alt="{{ $acc->name }}" class="card-media">
                        <div class="flex-between mb-2">
                            <div class="flex-center" style="gap:6px;">
                                <span class="badge badge-navy">{{ $acc->typeLabel() }}</span>
                                <span class="badge badge-slate">{{ ucfirst($acc->gender_policy) }}</span>
                                @if($acc->is_verified)<span class="badge badge-emerald">SONIT Verified</span>@endif
                            </div>
                            <span class="badge badge-gold mono">{{ $acc->available_rooms }} / {{ $acc->total_rooms }} spaces</span>
                        </div>
                        <h3 style="margin:0 0 6px;font-size:19px;font-weight:800;color:var(--navy-900);">{{ $acc->name }}</h3>
                        <div class="text-xs muted mb-2">{{ $acc->location }} · {{ $acc->distance_from_campus }}</div>
                        <p class="text-sm muted" style="margin:0 0 14px;">{{ $acc->description }}</p>
                        <div class="flex-center flex-wrap mb-3" style="gap:6px;">
                            @foreach($acc->amenitiesList() as $am)
                                <span class="badge badge-slate" style="font-size:11px;">{{ $am }}</span>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex-between" style="border-top:1px solid var(--border);padding-top:14px;">
                        <div>
                            <strong class="mono" style="font-size:18px;color:var(--navy-900);">TZS {{ number_format($acc->price_per_semester) }}</strong>
                            <span class="text-xs muted">/ semester</span>
                        </div>
                        <div class="text-xs muted" style="text-align:right;">
                            <div>{{ $acc->contact_person }}</div>
                            <strong class="mono" style="color:var(--navy-700);">{{ $acc->contact_phone }}</strong>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        @auth
            <div class="grid grid-2" style="align-items:start;" id="accommApply">
                <div class="card">
                    <span class="kicker">Placement Application</span>
                    <h2 style="margin:0 0 16px;font-size:21px;font-weight:800;color:var(--navy-900);">Request Accommodation Placement</h2>
                    <form method="POST" action="{{ route('accommodations.request') }}">
                        @csrf
                        <div class="form-group">
                            <label class="form-label">Preferred Residence</label>
                            <select name="accommodation_id" class="form-select" required>
                                @foreach($accommodations as $acc)
                                    <option value="{{ $acc->id }}">{{ $acc->name }} — TZS {{ number_format($acc->price_per_semester) }}/sem ({{ $acc->available_rooms }} open)</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="grid grid-2">
                            <div class="form-group">
                                <label class="form-label">Room Preference</label>
                                <select name="preferred_room_type" class="form-select" required>
                                    <option value="Shared (2 Students)">Shared (2 Students)</option>
                                    <option value="Shared (4 Students)">Shared (4 Students)</option>
                                    <option value="Single Self-Contained">Single Self-Contained</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Target Semester</label>
                                <input type="text" name="move_in_semester" class="form-control" value="2026/2027 Semester I" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Additional Welfare / Special Needs Notes</label>
                            <textarea name="notes" rows="3" class="form-control" placeholder="e.g. First-year priority, accessibility preference, budget note..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Submit Placement Application</button>
                    </form>
                </div>

                <div class="card">
                    <span class="kicker">Tracking</span>
                    <h2 style="margin:0 0 16px;font-size:21px;font-weight:800;color:var(--navy-900);">My Accommodation Applications</h2>
                    @forelse($myRequests as $req)
                        <div style="padding:12px 0;border-bottom:1px solid var(--border);">
                            <div class="flex-between mb-1">
                                <strong style="color:var(--navy-900);">{{ $req->accommodation->name }}</strong>
                                <span class="badge {{ $req->status === 'approved' ? 'badge-emerald' : ($req->status === 'declined' ? 'badge-crimson' : 'badge-amber') }}">{{ ucfirst($req->status) }}</span>
                            </div>
                            <div class="text-xs muted">{{ $req->preferred_room_type }} · {{ $req->move_in_semester }}</div>
                            @if($req->admin_response)<div class="text-xs mt-1" style="color:var(--navy-700);">Desk Response: {{ $req->admin_response }}</div>@endif
                        </div>
                    @empty
                        <p class="text-sm muted">No accommodation applications submitted yet.</p>
                    @endforelse
                </div>
            </div>
        @else
            <div class="card" style="text-align:center;">
                <p class="muted mb-3">Sign in with your NIT account to apply for on-campus hostels or SONIT-verified residences.</p>
                <a href="{{ route('login') }}" class="btn btn-primary">Sign In to Apply</a>
            </div>
        @endauth
    </div>
</section>
@endsection
