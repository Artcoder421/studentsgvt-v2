@extends('layouts.admin')

@section('title', 'Events, Sports, Charity & Leaders — SONIT Admin')
@section('page_title', 'Events, Sports, Charity & Leadership Management')

@section('content')
<div class="grid grid-2 mb-4" style="align-items:start;">
    {{-- Add Event --}}
    <div class="card">
        <h2 style="margin:0 0 14px;font-size:18px;font-weight:800;color:var(--navy-900);">Publish Campus Event</h2>
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

    {{-- Add Sports Team --}}
    <div class="card">
        <h2 style="margin:0 0 14px;font-size:18px;font-weight:800;color:var(--navy-900);">Add Sports Club / Fixture</h2>
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
</div>

<div class="grid grid-2 mb-4" style="align-items:start;">
    {{-- Launch Charity Campaign + Verify Pledges --}}
    <div class="card">
        <h2 style="margin:0 0 14px;font-size:18px;font-weight:800;color:var(--navy-900);">Launch Charity Drive</h2>
        <form method="POST" action="{{ route('admin.charity.store') }}" class="mb-4">
            @csrf
            <div class="grid grid-2">
                <div class="form-group"><label class="form-label">Campaign Title</label><input type="text" name="title" class="form-control" required></div>
                <div class="form-group"><label class="form-label">Beneficiary</label><input type="text" name="beneficiary" class="form-control" required></div>
            </div>
            <div class="grid grid-2">
                <div class="form-group"><label class="form-label">Target Amount (TZS)</label><input type="number" name="target_amount" class="form-control mono" value="3000000" required></div>
                <div class="form-group"><label class="form-label">Ends At</label><input type="datetime-local" name="ends_at" class="form-control"></div>
            </div>
            <div class="form-group"><label class="form-label">Description</label><textarea name="description" rows="2" class="form-control" required></textarea></div>
            <button type="submit" class="btn btn-gold btn-sm">Launch Charity Drive</button>
        </form>

        <h3 style="margin:0 0 10px;font-size:15px;font-weight:800;color:var(--navy-900);">Verify Pending Student Pledges</h3>
        @foreach($campaigns as $camp)
            @foreach($camp->contributions->where('status', 'pledged') as $pledge)
                <div class="flex-between text-xs" style="padding:8px 0;border-bottom:1px solid var(--border);">
                    <div><strong>{{ $pledge->donor_name }}</strong> · TZS {{ number_format($pledge->amount) }} ({{ $pledge->payment_reference ?? 'No Ref' }})</div>
                    <form method="POST" action="{{ route('admin.charity.verify', $pledge) }}" style="margin:0;">
                        @csrf
                        <button type="submit" class="btn btn-emerald btn-sm">Verify & Add</button>
                    </form>
                </div>
            @endforeach
        @endforeach
    </div>

    {{-- Add Leader --}}
    <div class="card">
        <h2 style="margin:0 0 14px;font-size:18px;font-weight:800;color:var(--navy-900);">Add Cabinet / Institute Leader</h2>
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
            <button type="submit" class="btn btn-primary btn-sm">Add Leader</button>
        </form>
    </div>
</div>
@endsection
