@extends('layouts.admin')

@section('title', 'Campus Services Control — SONIT Admin')
@section('page_title', 'Venues, Accommodation, Help Desk & Lost & Found')

@section('content')
<div class="grid grid-2 mb-4" style="align-items:start;">
    {{-- Venue Bookings Approval --}}
    <div class="card">
        <h2 style="margin:0 0 14px;font-size:18px;font-weight:800;color:var(--navy-900);">Venue Booking Requests</h2>
        @forelse($bookings as $b)
            <div style="padding:12px 0;border-bottom:1px solid var(--border);">
                <div class="flex-between mb-1">
                    <strong>{{ $b->event_title }}</strong>
                    <span class="badge {{ $b->status === 'approved' ? 'badge-emerald' : ($b->status === 'declined' ? 'badge-crimson' : 'badge-amber') }}">{{ ucfirst($b->status) }}</span>
                </div>
                <div class="text-xs muted mb-2">{{ $b->venue->name }} · {{ $b->booking_date->format('d M Y') }} ({{ $b->start_time }}–{{ $b->end_time }}) · By {{ $b->user->name }}</div>
                <form method="POST" action="{{ route('admin.bookings.status', $b) }}" class="flex-center flex-wrap" style="gap:6px;">
                    @csrf
                    <input type="text" name="admin_notes" value="{{ $b->admin_notes }}" class="form-control" style="flex:1;padding:6px 10px;font-size:12px;" placeholder="Admin note...">
                    <button type="submit" name="status" value="approved" class="btn btn-emerald btn-sm">Approve</button>
                    <button type="submit" name="status" value="declined" class="btn btn-danger btn-sm">Decline</button>
                </form>
            </div>
        @empty
            <p class="text-sm muted">No venue bookings yet.</p>
        @endforelse
    </div>

    {{-- Accommodation Requests --}}
    <div class="card">
        <h2 style="margin:0 0 14px;font-size:18px;font-weight:800;color:var(--navy-900);">Accommodation Placement Requests</h2>
        @forelse($accommRequests as $req)
            <div style="padding:12px 0;border-bottom:1px solid var(--border);">
                <div class="flex-between mb-1">
                    <strong>{{ $req->user->name }} → {{ $req->accommodation->name }}</strong>
                    <span class="badge {{ $req->status === 'approved' ? 'badge-emerald' : 'badge-amber' }}">{{ ucfirst($req->status) }}</span>
                </div>
                <div class="text-xs muted mb-2">{{ $req->preferred_room_type }} · {{ $req->move_in_semester }}</div>
                <form method="POST" action="{{ route('admin.accommodation-requests.status', $req) }}" class="flex-center flex-wrap" style="gap:6px;">
                    @csrf
                    <input type="text" name="admin_response" value="{{ $req->admin_response }}" class="form-control" style="flex:1;padding:6px 10px;font-size:12px;" placeholder="Room allocation response...">
                    <button type="submit" name="status" value="approved" class="btn btn-emerald btn-sm">Approve</button>
                    <button type="submit" name="status" value="waitlisted" class="btn btn-outline btn-sm">Waitlist</button>
                </form>
            </div>
        @empty
            <p class="text-sm muted">No placement requests yet.</p>
        @endforelse
    </div>
</div>

<div class="grid grid-2 mb-4" style="align-items:start;">
    {{-- Add Venue --}}
    <div class="card">
        <h2 style="margin:0 0 14px;font-size:18px;font-weight:800;color:var(--navy-900);">Add Campus Venue</h2>
        <form method="POST" action="{{ route('admin.venues.store') }}">
            @csrf
            <div class="grid grid-2">
                <div class="form-group"><label class="form-label">Venue Name</label><input type="text" name="name" class="form-control" required></div>
                <div class="form-group"><label class="form-label">Code</label><input type="text" name="code" class="form-control mono" placeholder="LBB-02" required></div>
            </div>
            <div class="grid grid-3">
                <div class="form-group"><label class="form-label">Building</label><input type="text" name="building" class="form-control" required></div>
                <div class="form-group"><label class="form-label">Capacity</label><input type="number" name="capacity" class="form-control" value="100" required></div>
                <div class="form-group">
                    <label class="form-label">Category</label>
                    <select name="category" class="form-select">
                        <option value="lecture_hall">Lecture Hall</option>
                        <option value="auditorium">Auditorium</option>
                        <option value="lab">Lab</option>
                        <option value="hangar">Hangar</option>
                        <option value="meeting">Boardroom</option>
                        <option value="sports">Sports</option>
                    </select>
                </div>
            </div>
            <div class="form-group"><label class="form-label">Amenities (comma separated)</label><input type="text" name="amenities" class="form-control" placeholder="Projector, Wi-Fi, AC"></div>
            <button type="submit" class="btn btn-primary btn-sm">Add Venue</button>
        </form>
    </div>

    {{-- Add Accommodation --}}
    <div class="card">
        <h2 style="margin:0 0 14px;font-size:18px;font-weight:800;color:var(--navy-900);">Add Verified Accommodation</h2>
        <form method="POST" action="{{ route('admin.accommodations.store') }}">
            @csrf
            <div class="grid grid-2">
                <div class="form-group"><label class="form-label">Residence Name</label><input type="text" name="name" class="form-control" required></div>
                <div class="form-group"><label class="form-label">Location</label><input type="text" name="location" class="form-control" required></div>
            </div>
            <div class="grid grid-3">
                <div class="form-group">
                    <label class="form-label">Type</label>
                    <select name="type" class="form-select">
                        <option value="on_campus_hostel">On-Campus Hostel</option>
                        <option value="verified_off_campus">Verified Off-Campus</option>
                        <option value="shared_apartment">Student Apartment</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Gender Policy</label>
                    <select name="gender_policy" class="form-select">
                        <option value="mixed">Mixed</option>
                        <option value="female">Female</option>
                        <option value="male">Male</option>
                    </select>
                </div>
                <div class="form-group"><label class="form-label">Price/Sem (TZS)</label><input type="number" name="price_per_semester" class="form-control mono" value="250000" required></div>
            </div>
            <div class="grid grid-2">
                <div class="form-group"><label class="form-label">Available Rooms</label><input type="number" name="available_rooms" class="form-control" value="10" required></div>
                <div class="form-group"><label class="form-label">Total Rooms</label><input type="number" name="total_rooms" class="form-control" value="40" required></div>
            </div>
            <button type="submit" class="btn btn-primary btn-sm">Add Residence</button>
        </form>
    </div>
</div>

{{-- Help Desk & Lost/Found Moderation --}}
<div class="grid grid-2" style="align-items:start;">
    <div class="card">
        <h2 style="margin:0 0 14px;font-size:18px;font-weight:800;color:var(--navy-900);">Student Help Desk Queue</h2>
        @foreach($tickets as $t)
            <div class="flex-between" style="padding:10px 0;border-bottom:1px solid var(--border);">
                <div>
                    <a href="{{ route('help-desk.show', $t) }}" style="font-weight:700;color:var(--navy-900);">{{ $t->subject }}</a>
                    <div class="text-xs mono muted">{{ $t->ticket_number }} · {{ $t->categoryLabel() }}</div>
                </div>
                <a href="{{ route('help-desk.show', $t) }}" class="btn btn-outline btn-sm">Respond</a>
            </div>
        @endforeach
    </div>

    <div class="card">
        <h2 style="margin:0 0 14px;font-size:18px;font-weight:800;color:var(--navy-900);">Lost & Found Moderation</h2>
        @foreach($lostItems as $item)
            <div class="flex-between" style="padding:10px 0;border-bottom:1px solid var(--border);gap:10px;">
                <div>
                    <strong>{{ $item->item_name }}</strong> <span class="badge badge-slate">{{ strtoupper($item->type) }}</span>
                    <div class="text-xs muted">{{ $item->location }} · Status: {{ ucfirst($item->status) }}</div>
                </div>
                <form method="POST" action="{{ route('admin.lost-found.status', $item) }}" class="flex-center" style="gap:4px;">
                    @csrf
                    <button type="submit" name="status" value="resolved" class="btn btn-emerald btn-sm">Resolve</button>
                </form>
            </div>
        @endforeach
    </div>
</div>
@endsection
