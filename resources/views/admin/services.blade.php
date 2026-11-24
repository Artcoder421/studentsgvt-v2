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

{{-- Manage Existing Venues & Accommodations (Edit / Photo Replace-Remove / Delete) --}}
<div class="grid grid-2 mb-4" style="align-items:start;">
    <div class="card">
        <h2 style="margin:0 0 12px;font-size:17px;font-weight:800;color:var(--nit-black);">Manage Campus Venues ({{ $venues->count() }})</h2>
        @forelse($venues as $v)
            <div style="padding:10px 0;border-bottom:1px solid var(--border);">
                <div class="flex-between" style="gap:8px;">
                    <div class="flex-center" style="gap:8px;">
                        @if($v->image)
                            <img src="{{ asset($v->image) }}" alt="{{ $v->name }}" style="width:44px;height:34px;object-fit:cover;border:1px solid var(--border);border-radius:2px;">
                        @endif
                        <div>
                            <strong style="color:var(--nit-black);">{{ $v->name }}</strong>
                            <span class="badge badge-navy mono">{{ $v->code }}</span>
                            <div class="text-xs muted">{{ $v->building }} · Cap: {{ $v->capacity }} · {{ ucfirst($v->status) }}</div>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('admin.venues.delete', $v) }}" style="margin:0;" onsubmit="return confirm('Delete venue {{ $v->name }}?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm" style="padding:4px 8px;font-size:11px;">Delete</button>
                    </form>
                </div>

                <details class="admin-details mt-1">
                    <summary>Edit Venue & Photo</summary>
                    <form method="POST" action="{{ route('admin.venues.update', $v) }}" enctype="multipart/form-data" class="admin-details-body">
                        @csrf
                        @method('PUT')
                        <div class="grid grid-2" style="gap:8px;">
                            <div class="form-group" style="margin:0;"><label class="form-label">Name</label><input type="text" name="name" value="{{ $v->name }}" class="form-control" required></div>
                            <div class="form-group" style="margin:0;"><label class="form-label">Code</label><input type="text" name="code" value="{{ $v->code }}" class="form-control mono" required></div>
                            <div class="form-group" style="margin:0;"><label class="form-label">Building</label><input type="text" name="building" value="{{ $v->building }}" class="form-control" required></div>
                            <div class="form-group" style="margin:0;"><label class="form-label">Capacity</label><input type="number" name="capacity" value="{{ $v->capacity }}" min="5" class="form-control" required></div>
                            <div class="form-group" style="margin:0;">
                                <label class="form-label">Category</label>
                                <select name="category" class="form-select">
                                    @foreach(['lecture_hall' => 'Lecture Hall', 'auditorium' => 'Auditorium', 'lab' => 'Lab', 'hangar' => 'Hangar', 'meeting' => 'Boardroom', 'sports' => 'Sports'] as $vk => $vl)
                                        <option value="{{ $vk }}" @selected($v->category === $vk)>{{ $vl }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group" style="margin:0;">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="available" @selected($v->status === 'available')>Available</option>
                                    <option value="booked" @selected($v->status === 'booked')>Booked</option>
                                    <option value="maintenance" @selected($v->status === 'maintenance')>Maintenance</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group mt-2" style="margin-bottom:8px;"><label class="form-label">Amenities</label><input type="text" name="amenities" value="{{ $v->amenities }}" class="form-control"></div>
                        <div class="form-group" style="margin-bottom:8px;"><label class="form-label">Upload / Replace Photo</label><input type="file" name="image" accept="image/*" class="form-control"></div>
                        @if($v->image)
                            <label class="flex-center text-xs mb-2" style="gap:6px;color:var(--nit-red-dark);font-weight:700;cursor:pointer;">
                                <input type="checkbox" name="remove_image" value="1"> Remove current venue photo
                            </label>
                        @endif
                        <button type="submit" class="btn btn-primary btn-sm">Save Venue</button>
                    </form>
                </details>
            </div>
        @empty
            <p class="text-sm muted">No venues added yet.</p>
        @endforelse
    </div>

    <div class="card">
        <h2 style="margin:0 0 12px;font-size:17px;font-weight:800;color:var(--nit-black);">Manage Verified Accommodations ({{ $accommodations->count() }})</h2>
        @forelse($accommodations as $acc)
            <div style="padding:10px 0;border-bottom:1px solid var(--border);">
                <div class="flex-between" style="gap:8px;">
                    <div class="flex-center" style="gap:8px;">
                        @if($acc->image)
                            <img src="{{ asset($acc->image) }}" alt="{{ $acc->name }}" style="width:44px;height:34px;object-fit:cover;border:1px solid var(--border);border-radius:2px;">
                        @endif
                        <div>
                            <strong style="color:var(--nit-black);">{{ $acc->name }}</strong>
                            <div class="text-xs muted">{{ $acc->location }} · TZS {{ number_format($acc->price_per_semester) }}/sem · {{ $acc->available_rooms }}/{{ $acc->total_rooms }} rooms</div>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('admin.accommodations.delete', $acc) }}" style="margin:0;" onsubmit="return confirm('Delete residence {{ $acc->name }}?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm" style="padding:4px 8px;font-size:11px;">Delete</button>
                    </form>
                </div>

                <details class="admin-details mt-1">
                    <summary>Edit Residence & Photo</summary>
                    <form method="POST" action="{{ route('admin.accommodations.update', $acc) }}" enctype="multipart/form-data" class="admin-details-body">
                        @csrf
                        @method('PUT')
                        <div class="grid grid-2" style="gap:8px;">
                            <div class="form-group" style="margin:0;"><label class="form-label">Name</label><input type="text" name="name" value="{{ $acc->name }}" class="form-control" required></div>
                            <div class="form-group" style="margin:0;"><label class="form-label">Location</label><input type="text" name="location" value="{{ $acc->location }}" class="form-control" required></div>
                            <div class="form-group" style="margin:0;">
                                <label class="form-label">Type</label>
                                <select name="type" class="form-select">
                                    <option value="on_campus_hostel" @selected($acc->type === 'on_campus_hostel')>On-Campus Hostel</option>
                                    <option value="verified_off_campus" @selected($acc->type === 'verified_off_campus')>Verified Off-Campus</option>
                                    <option value="shared_apartment" @selected($acc->type === 'shared_apartment')>Student Apartment</option>
                                </select>
                            </div>
                            <div class="form-group" style="margin:0;">
                                <label class="form-label">Gender Policy</label>
                                <select name="gender_policy" class="form-select">
                                    <option value="mixed" @selected($acc->gender_policy === 'mixed')>Mixed</option>
                                    <option value="female" @selected($acc->gender_policy === 'female')>Female</option>
                                    <option value="male" @selected($acc->gender_policy === 'male')>Male</option>
                                </select>
                            </div>
                            <div class="form-group" style="margin:0;"><label class="form-label">Price/Sem (TZS)</label><input type="number" name="price_per_semester" value="{{ $acc->price_per_semester }}" class="form-control mono" required></div>
                            <div class="form-group" style="margin:0;"><label class="form-label">Available / Total Rooms</label>
                                <div class="flex-center" style="gap:4px;">
                                    <input type="number" name="available_rooms" value="{{ $acc->available_rooms }}" class="form-control" required>
                                    <input type="number" name="total_rooms" value="{{ $acc->total_rooms }}" class="form-control" required>
                                </div>
                            </div>
                            <div class="form-group" style="margin:0;"><label class="form-label">Contact Person</label><input type="text" name="contact_person" value="{{ $acc->contact_person }}" class="form-control"></div>
                            <div class="form-group" style="margin:0;"><label class="form-label">Contact Phone</label><input type="text" name="contact_phone" value="{{ $acc->contact_phone }}" class="form-control mono"></div>
                        </div>
                        <div class="form-group mt-2" style="margin-bottom:8px;"><label class="form-label">Upload / Replace Photo</label><input type="file" name="image" accept="image/*" class="form-control"></div>
                        @if($acc->image)
                            <label class="flex-center text-xs mb-2" style="gap:6px;color:var(--nit-red-dark);font-weight:700;cursor:pointer;">
                                <input type="checkbox" name="remove_image" value="1"> Remove current residence photo
                            </label>
                        @endif
                        <button type="submit" class="btn btn-primary btn-sm">Save Residence</button>
                    </form>
                </details>
            </div>
        @empty
            <p class="text-sm muted">No accommodations listed yet.</p>
        @endforelse
    </div>
</div>

{{-- Help Desk & Lost/Found Moderation --}}
<div class="grid grid-2" style="align-items:start;">
    <div class="card">
        <h2 style="margin:0 0 14px;font-size:18px;font-weight:800;color:var(--navy-900);">Student Help Desk Queue</h2>
        @forelse($tickets as $t)
            <div class="flex-between" style="padding:10px 0;border-bottom:1px solid var(--border);gap:8px;">
                <div>
                    <a href="{{ route('help-desk.show', $t) }}" style="font-weight:700;color:var(--navy-900);">{{ $t->subject }}</a>
                    <div class="text-xs mono muted">{{ $t->ticket_number }} · {{ $t->categoryLabel() }}</div>
                </div>
                <div class="flex-center" style="gap:4px;">
                    <a href="{{ route('help-desk.show', $t) }}" class="btn btn-outline btn-sm">Respond</a>
                    <form method="POST" action="{{ route('admin.tickets.delete', $t) }}" style="margin:0;" onsubmit="return confirm('Delete support ticket {{ $t->ticket_number }}?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                    </form>
                </div>
            </div>
        @empty
            <p class="text-sm muted">No support tickets found.</p>
        @endforelse
    </div>

    <div class="card">
        <h2 style="margin:0 0 14px;font-size:18px;font-weight:800;color:var(--navy-900);">Lost & Found Moderation</h2>
        @forelse($lostItems as $item)
            <div class="flex-between" style="padding:10px 0;border-bottom:1px solid var(--border);gap:10px;">
                <div>
                    <strong>{{ $item->item_name }}</strong> <span class="badge badge-slate">{{ strtoupper($item->type) }}</span>
                    <div class="text-xs muted">{{ $item->location }} · Status: {{ ucfirst($item->status) }}</div>
                </div>
                <div class="flex-center" style="gap:4px;">
                    <form method="POST" action="{{ route('admin.lost-found.status', $item) }}" style="margin:0;">
                        @csrf
                        <button type="submit" name="status" value="resolved" class="btn btn-emerald btn-sm">Resolve</button>
                    </form>
                    <form method="POST" action="{{ route('admin.lost-found.delete', $item) }}" style="margin:0;" onsubmit="return confirm('Delete Lost & Found item {{ $item->item_name }}?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                    </form>
                </div>
            </div>
        @empty
            <p class="text-sm muted">No Lost & Found reports.</p>
        @endforelse
    </div>
</div>
@endsection
