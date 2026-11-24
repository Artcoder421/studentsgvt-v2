@extends('layouts.app')

@section('title', 'Campus Venues & Booking — SONIT | NIT')

@section('content')
<div class="page-banner">
    <div class="container page-banner-inner">
        <div class="page-banner-left">
            <div class="icon-3d-tile lg"><img src="{{ asset('images/icons/venues.jpg') }}" alt="Venues"></div>
            <div>
                <h1>NIT Campus Venues & Reservation Desk</h1>
                <p>Auditoriums, lecture theatres, CoEATO aviation hangars, ICT labs, and sports grounds available for student clubs and academic seminars.</p>
            </div>
        </div>
        @auth
            <a href="#bookVenueForm" class="btn btn-gold">Reserve a Venue</a>
        @endauth
    </div>
</div>

<section class="section section-tight">
    <div class="container">
        <div class="flex-center flex-wrap mb-4" style="gap:8px;">
            <a href="{{ route('venues.index') }}" class="btn btn-sm {{ !$category ? 'btn-primary' : 'btn-outline' }}">All Venues</a>
            @foreach(['auditorium' => 'Auditoriums', 'lecture_hall' => 'Lecture Halls', 'lab' => 'ICT Labs', 'hangar' => 'Aviation Hangar', 'meeting' => 'Boardrooms', 'sports' => 'Sports Arenas'] as $k => $lbl)
                <a href="{{ route('venues.index', ['category' => $k]) }}" class="btn btn-sm {{ $category === $k ? 'btn-primary' : 'btn-outline' }}">{{ $lbl }}</a>
            @endforeach
        </div>

        <div class="grid grid-3 mb-4">
            @foreach($venues as $venue)
                <article class="card card-hover" style="display:flex;flex-direction:column;justify-content:space-between;">
                    <div>
                        <img src="{{ asset($venue->image ?? 'images/campus/building.jpg') }}" alt="{{ $venue->name }}" class="card-media">
                        <div class="flex-between mb-2">
                            <span class="badge badge-navy mono">{{ $venue->code }}</span>
                            <span class="badge {{ $venue->status === 'available' ? 'badge-emerald' : ($venue->status === 'booked' ? 'badge-amber' : 'badge-crimson') }}">
                                {{ ucfirst($venue->status) }}
                            </span>
                        </div>
                        <h3 style="margin:0 0 4px;font-size:18px;font-weight:800;color:var(--navy-900);">{{ $venue->name }}</h3>
                        <div class="text-xs muted mb-2">{{ $venue->building }} · Capacity: <strong>{{ number_format($venue->capacity) }}</strong></div>
                        <p class="text-sm muted" style="margin:0 0 12px;">{{ $venue->description }}</p>
                    </div>
                    <div style="border-top:1px solid var(--border);padding-top:12px;">
                        <div class="flex-center flex-wrap" style="gap:5px;">
                            @foreach($venue->amenitiesList() as $am)
                                <span class="badge badge-slate" style="font-size:10.5px;">{{ $am }}</span>
                            @endforeach
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        @auth
            <div class="grid grid-2" style="align-items:start;" id="bookVenueForm">
                <div class="card">
                    <span class="kicker">Online Reservation</span>
                    <h2 style="margin:0 0 16px;font-size:21px;font-weight:800;color:var(--navy-900);">Request a Campus Venue</h2>
                    <form method="POST" action="{{ route('venues.book') }}">
                        @csrf
                        <div class="form-group">
                            <label class="form-label">Select Venue</label>
                            <select name="venue_id" class="form-select" required>
                                @foreach($venues as $v)
                                    <option value="{{ $v->id }}">[{{ $v->code }}] {{ $v->name }} (Cap: {{ $v->capacity }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="grid grid-2">
                            <div class="form-group">
                                <label class="form-label">Event / Seminar Title</label>
                                <input type="text" name="event_title" class="form-control" required placeholder="e.g. BLTM Logistics Career Seminar">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Course / Student Organization</label>
                                <input type="text" name="organization_or_course" class="form-control" required placeholder="e.g. BIT Innovation Club">
                            </div>
                        </div>
                        <div class="grid grid-3">
                            <div class="form-group">
                                <label class="form-label">Date</label>
                                <input type="date" name="booking_date" class="form-control" min="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Start Time</label>
                                <input type="time" name="start_time" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">End Time</label>
                                <input type="time" name="end_time" class="form-control" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Expected Attendees</label>
                            <input type="number" name="expected_attendees" class="form-control" min="5" value="60" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Purpose & Equipment Needs</label>
                            <textarea name="purpose" rows="3" class="form-control" required placeholder="Specify agenda, projector/PA needs..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Submit Venue Booking Request</button>
                    </form>
                </div>

                <div class="card">
                    <span class="kicker">My Reservations</span>
                    <h2 style="margin:0 0 16px;font-size:21px;font-weight:800;color:var(--navy-900);">Recent Booking Requests</h2>
                    @forelse($myBookings as $b)
                        <div style="padding:12px 0;border-bottom:1px solid var(--border);">
                            <div class="flex-between mb-1">
                                <strong style="color:var(--navy-900);">{{ $b->event_title }}</strong>
                                <span class="badge {{ $b->status === 'approved' ? 'badge-emerald' : ($b->status === 'declined' ? 'badge-crimson' : 'badge-amber') }}">{{ ucfirst($b->status) }}</span>
                            </div>
                            <div class="text-xs muted">{{ $b->venue->name }} · {{ $b->booking_date->format('d M Y') }} ({{ $b->start_time }} – {{ $b->end_time }})</div>
                            @if($b->admin_notes)<div class="text-xs mt-1" style="color:var(--navy-700);">Note: {{ $b->admin_notes }}</div>@endif
                        </div>
                    @empty
                        <p class="text-sm muted">You haven't requested any campus venues yet.</p>
                    @endforelse
                </div>
            </div>
        @else
            <div class="card" style="text-align:center;">
                <p class="muted mb-3">Sign in with your NIT account to reserve campus auditoriums, lecture halls, or boardrooms.</p>
                <a href="{{ route('login') }}" class="btn btn-primary">Sign In to Book a Venue</a>
            </div>
        @endauth
    </div>
</section>
@endsection
