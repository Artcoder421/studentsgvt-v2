@extends('layouts.admin')

@section('title', 'Events, Sports, Charity Tours & Leaders — SONIT Admin')
@section('page_title', 'Events, Sports, Charity Tours & Leadership')

@section('content')
<div class="grid grid-2 mb-4" style="align-items:start;">
    {{-- Publish Charity / Tour Reminder --}}
    <div class="card card-accent-red">
        <h2 style="margin:0 0 12px;font-size:17px;font-weight:800;color:var(--nit-black);">Post Charity / Tour Contribution Reminder</h2>
        <form method="POST" action="{{ route('admin.charity.store') }}">
            @csrf
            <div class="grid grid-2">
                <div class="form-group"><label class="form-label">Campaign / Tour Title</label><input type="text" name="title" class="form-control" required></div>
                <div class="form-group"><label class="form-label">Beneficiary / Destination</label><input type="text" name="beneficiary" class="form-control" required></div>
            </div>
            <div class="grid grid-2">
                <div class="form-group"><label class="form-label">When (Date & Time)</label><input type="text" name="event_when" class="form-control" placeholder="e.g. Saturday, 12 Dec · 08:30 AM" required></div>
                <div class="form-group"><label class="form-label">Where (Meeting Point / Location)</label><input type="text" name="event_where" class="form-control" placeholder="e.g. NIT Main Gate → Mabibo" required></div>
            </div>
            <div class="form-group"><label class="form-label">What We Plan To Do</label><textarea name="planned_activities" rows="2" class="form-control" placeholder="Key planned activities..." required></textarea></div>
            <div class="form-group"><label class="form-label">Contribution Account Number / Lipa Namba</label><input type="text" name="account_number" class="form-control mono" placeholder="e.g. CRDB: 0150442198800 | M-Pesa Lipa: 5589021" required></div>
            <div class="grid grid-2">
                <div class="form-group"><label class="form-label">Coordinator Contacts</label><input type="text" name="contact_info" class="form-control" placeholder="+255 7XX XXX XXX" required></div>
                <div class="form-group"><label class="form-label">Countdown Deadline</label><input type="datetime-local" name="ends_at" class="form-control"></div>
            </div>
            <div class="form-group"><label class="form-label">Short Overview</label><textarea name="description" rows="2" class="form-control" required></textarea></div>
            <button type="submit" class="btn btn-danger btn-sm">Publish Reminder Notice</button>
        </form>
    </div>

    {{-- Publish Campus Event --}}
    <div class="card card-accent-blue">
        <h2 style="margin:0 0 12px;font-size:17px;font-weight:800;color:var(--nit-black);">Publish Campus Event</h2>
        <form method="POST" action="{{ route('admin.events.store') }}">
            @csrf
            <div class="form-group"><label class="form-label">Event Title</label><input type="text" name="title" class="form-control" required></div>
            <div class="grid grid-2">
                <div class="form-group">
                    <label class="form-label">Category</label>
                    <select name="category" class="form-select">
                        <option value="academic">Academic</option>
                        <option value="governance">Governance</option>
                        <option value="sports">Sports</option>
                        <option value="charity">Charity</option>
                        <option value="social">Social</option>
                    </select>
                </div>
                <div class="form-group"><label class="form-label">Venue</label><input type="text" name="venue_name" class="form-control" required></div>
            </div>
            <div class="form-group"><label class="form-label">Starts At</label><input type="datetime-local" name="starts_at" class="form-control" required></div>
            <div class="form-group"><label class="form-label">Description</label><textarea name="description" rows="2" class="form-control" required></textarea></div>
            <button type="submit" class="btn btn-primary btn-sm">Publish Event</button>
        </form>
    </div>
</div>

<div class="grid grid-2" style="align-items:start;">
    {{-- Add Sports Team --}}
    <div class="card">
        <h2 style="margin:0 0 12px;font-size:17px;font-weight:800;color:var(--nit-black);">Add Sports Club / Fixture</h2>
        <form method="POST" action="{{ route('admin.sports.store') }}">
            @csrf
            <div class="grid grid-2">
                <div class="form-group"><label class="form-label">Team / Club Name</label><input type="text" name="name" class="form-control" required></div>
                <div class="form-group">
                    <label class="form-label">Sport</label>
                    <select name="sport_type" class="form-select">
                        <option value="football">Football</option>
                        <option value="basketball">Basketball</option>
                        <option value="volleyball">Volleyball</option>
                        <option value="netball">Netball</option>
                        <option value="athletics">Athletics</option>
                        <option value="chess">Chess</option>
                    </select>
                </div>
            </div>
            <div class="grid grid-2">
                <div class="form-group"><label class="form-label">Training Schedule</label><input type="text" name="schedule" class="form-control" placeholder="Mon & Thu 16:30" required></div>
                <div class="form-group"><label class="form-label">Venue</label><input type="text" name="venue" class="form-control" value="NIT Main Pitch" required></div>
            </div>
            <div class="form-group"><label class="form-label">Upcoming Match (Optional)</label><input type="text" name="upcoming_match" class="form-control"></div>
            <button type="submit" class="btn btn-primary btn-sm">Save Sports Club</button>
        </form>
    </div>

    {{-- Add Leader --}}
    <div class="card">
        <h2 style="margin:0 0 12px;font-size:17px;font-weight:800;color:var(--nit-black);">Add Cabinet / Institute Leader</h2>
        <form method="POST" action="{{ route('admin.leaders.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="grid grid-2">
                <div class="form-group"><label class="form-label">Full Name</label><input type="text" name="name" class="form-control" required></div>
                <div class="form-group"><label class="form-label">Official Position</label><input type="text" name="position" class="form-control" required></div>
            </div>
            <div class="grid grid-2">
                <div class="form-group">
                    <label class="form-label">Category</label>
                    <select name="category" class="form-select">
                        <option value="executive">SONIT Executive</option>
                        <option value="ministry">Cabinet Minister</option>
                        <option value="parliament">Parliament</option>
                        <option value="institute">Institute Leadership</option>
                    </select>
                </div>
                <div class="form-group"><label class="form-label">Portrait Photo</label><input type="file" name="photo" accept="image/*" class="form-control"></div>
            </div>
            <div class="form-group"><label class="form-label">Short Bio</label><textarea name="bio" rows="2" class="form-control"></textarea></div>
            <div class="form-group"><label class="flex-center text-sm"><input type="checkbox" name="is_featured" value="1"> Feature on Homepage</label></div>
            <button type="submit" class="btn btn-black btn-sm">Add Leader</button>
        </form>
    </div>
</div>
@endsection
