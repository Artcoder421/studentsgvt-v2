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

{{-- Manage Current & Auto-Elected Leaders (Edit Everything, Update/Remove Photo, Delete) --}}
<div class="card mt-4">
    <h2 style="margin:0 0 12px;font-size:17px;font-weight:800;color:var(--nit-black);">Manage Current & Auto-Elected Leaders ({{ $leaders->count() }})</h2>
    <p class="text-xs muted mb-3">Elected winners appear here automatically when an election ends. Edit any leader's name, position, category, quote, bio, portrait image, remove their photo, or remove the leader profile entirely.</p>

    <div style="display:flex;flex-direction:column;gap:10px;">
        @foreach($leaders as $leader)
            <div style="border:1px solid var(--border);padding:12px;background:var(--canvas);">
                <div class="flex-between flex-wrap mb-2" style="gap:10px;">
                    <div class="flex-center" style="gap:10px;">
                        @if($leader->photo)
                            <img src="{{ asset($leader->photo) }}" alt="{{ $leader->name }}" style="width:44px;height:48px;object-fit:cover;object-position:top;border-radius:2px;border:1px solid var(--border);">
                        @else
                            <div class="avatar-tile avatar-fallback" style="width:44px;height:48px;">{{ strtoupper(substr($leader->name, 0, 2)) }}</div>
                        @endif
                        <div>
                            <strong style="color:var(--nit-black);font-size:14.5px;">{{ $leader->name }}</strong>
                            <span class="badge badge-navy">{{ ucfirst($leader->category) }}</span>
                            @if($leader->is_featured)<span class="badge badge-crimson">Homepage Featured</span>@endif
                            <div class="text-xs muted">{{ $leader->position }} {{ $leader->department ? '· '.$leader->department : '' }}</div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('admin.leaders.delete', $leader) }}" style="margin:0;" onsubmit="return confirm('Delete leader {{ $leader->name }} permanently?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm">Delete Leader</button>
                    </form>
                </div>

                <details class="admin-details">
                    <summary>Edit Leader Name, Position, Details & Portrait Photo</summary>
                    <form method="POST" action="{{ route('admin.leaders.update', $leader) }}" enctype="multipart/form-data" class="admin-details-body">
                        @csrf
                        @method('PUT')
                        <div class="grid grid-3" style="gap:8px;">
                            <div class="form-group" style="margin:0;">
                                <label class="form-label">Full Name</label>
                                <input type="text" name="name" value="{{ $leader->name }}" class="form-control" required>
                            </div>
                            <div class="form-group" style="margin:0;">
                                <label class="form-label">Official Position</label>
                                <input type="text" name="position" value="{{ $leader->position }}" class="form-control" required>
                            </div>
                            <div class="form-group" style="margin:0;">
                                <label class="form-label">Category</label>
                                <select name="category" class="form-select">
                                    <option value="executive" @selected($leader->category === 'executive')>SONIT Executive</option>
                                    <option value="ministry" @selected($leader->category === 'ministry')>Cabinet Minister</option>
                                    <option value="parliament" @selected($leader->category === 'parliament')>Parliament</option>
                                    <option value="institute" @selected($leader->category === 'institute')>Institute Leadership</option>
                                </select>
                            </div>
                        </div>
                        <div class="grid grid-3 mt-2" style="gap:8px;">
                            <div class="form-group" style="margin:0;">
                                <label class="form-label">Department / Constituency</label>
                                <input type="text" name="department" value="{{ $leader->department }}" class="form-control">
                            </div>
                            <div class="form-group" style="margin:0;">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" value="{{ $leader->email }}" class="form-control">
                            </div>
                            <div class="form-group" style="margin:0;">
                                <label class="form-label">Phone</label>
                                <input type="text" name="phone" value="{{ $leader->phone }}" class="form-control mono">
                            </div>
                        </div>
                        <div class="grid grid-2 mt-2" style="gap:8px;">
                            <div class="form-group" style="margin:0;">
                                <label class="form-label">Quote / Motto</label>
                                <input type="text" name="quote" value="{{ $leader->quote }}" class="form-control">
                            </div>
                            <div class="form-group" style="margin:0;">
                                <label class="form-label">Upload / Replace Portrait Photo</label>
                                <input type="file" name="photo" accept="image/*" class="form-control">
                            </div>
                        </div>
                        @if($leader->photo)
                            <label class="flex-center text-xs mt-2" style="gap:6px;color:var(--nit-red-dark);font-weight:700;cursor:pointer;">
                                <input type="checkbox" name="remove_photo" value="1"> Remove current portrait photo
                            </label>
                        @endif
                        <div class="form-group mt-2" style="margin-bottom:8px;">
                            <label class="form-label">Official Biography</label>
                            <textarea name="bio" rows="2" class="form-control">{{ $leader->bio }}</textarea>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm">Save Leader Changes</button>
                    </form>
                </details>
            </div>
        @endforeach
    </div>
</div>

{{-- Manage Existing Charity Notices, Events & Sports Clubs --}}
<div class="grid grid-3 mt-4" style="align-items:start;">
    {{-- Manage Charity / Tour Reminders --}}
    <div class="card">
        <h2 style="margin:0 0 12px;font-size:16px;font-weight:800;color:var(--nit-black);">Manage Charity & Tours ({{ $campaigns->count() }})</h2>
        @forelse($campaigns as $camp)
            <div style="padding:10px 0;border-bottom:1px solid var(--border);">
                <div class="flex-between" style="gap:6px;">
                    <strong style="font-size:13.5px;color:var(--nit-black);">{{ $camp->title }}</strong>
                    <form method="POST" action="{{ route('admin.charity.delete', $camp) }}" style="margin:0;" onsubmit="return confirm('Delete charity notice {{ $camp->title }}?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm" style="padding:3px 7px;font-size:11px;">Delete</button>
                    </form>
                </div>
                <div class="text-xs muted">{{ $camp->beneficiary }}</div>
                @if($camp->account_number)<div class="text-xs mono" style="color:var(--nit-red-dark);">{{ $camp->account_number }}</div>@endif

                <details class="admin-details mt-1">
                    <summary>Edit Notice & Photo</summary>
                    <form method="POST" action="{{ route('admin.charity.update', $camp) }}" enctype="multipart/form-data" class="admin-details-body">
                        @csrf
                        @method('PUT')
                        <div class="form-group"><label class="form-label">Title</label><input type="text" name="title" value="{{ $camp->title }}" class="form-control" required></div>
                        <div class="form-group"><label class="form-label">Beneficiary</label><input type="text" name="beneficiary" value="{{ $camp->beneficiary }}" class="form-control" required></div>
                        <div class="form-group"><label class="form-label">When</label><input type="text" name="event_when" value="{{ $camp->event_when }}" class="form-control"></div>
                        <div class="form-group"><label class="form-label">Where</label><input type="text" name="event_where" value="{{ $camp->event_where }}" class="form-control"></div>
                        <div class="form-group"><label class="form-label">Planned Activities</label><textarea name="planned_activities" rows="2" class="form-control">{{ $camp->planned_activities }}</textarea></div>
                        <div class="form-group"><label class="form-label">Account Number / Lipa Namba</label><input type="text" name="account_number" value="{{ $camp->account_number }}" class="form-control mono"></div>
                        <div class="form-group"><label class="form-label">Contacts</label><input type="text" name="contact_info" value="{{ $camp->contact_info }}" class="form-control"></div>
                        <div class="form-group"><label class="form-label">Overview</label><textarea name="description" rows="2" class="form-control" required>{{ $camp->description }}</textarea></div>
                        <div class="form-group"><label class="form-label">Upload / Replace Photo</label><input type="file" name="image" accept="image/*" class="form-control"></div>
                        @if($camp->image)
                            <label class="flex-center text-xs mb-2" style="gap:6px;color:var(--nit-red-dark);font-weight:700;cursor:pointer;">
                                <input type="checkbox" name="remove_image" value="1"> Remove current image
                            </label>
                        @endif
                        <button type="submit" class="btn btn-primary btn-sm">Save Notice</button>
                    </form>
                </details>
            </div>
        @empty
            <p class="text-sm muted">No charity notices yet.</p>
        @endforelse
    </div>

    {{-- Manage Events --}}
    <div class="card">
        <h2 style="margin:0 0 12px;font-size:16px;font-weight:800;color:var(--nit-black);">Manage Campus Events ({{ $events->count() }})</h2>
        @forelse($events as $ev)
            <div style="padding:10px 0;border-bottom:1px solid var(--border);">
                <div class="flex-between" style="gap:6px;">
                    <strong style="font-size:13.5px;color:var(--nit-black);">{{ $ev->title }}</strong>
                    <form method="POST" action="{{ route('admin.events.delete', $ev) }}" style="margin:0;" onsubmit="return confirm('Delete event {{ $ev->title }}?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm" style="padding:3px 7px;font-size:11px;">Delete</button>
                    </form>
                </div>
                <div class="text-xs muted">{{ $ev->venue_name }} · {{ optional($ev->starts_at)->format('d M Y H:i') }}</div>

                <details class="admin-details mt-1">
                    <summary>Edit Event & Photo</summary>
                    <form method="POST" action="{{ route('admin.events.update', $ev) }}" enctype="multipart/form-data" class="admin-details-body">
                        @csrf
                        @method('PUT')
                        <div class="form-group"><label class="form-label">Title</label><input type="text" name="title" value="{{ $ev->title }}" class="form-control" required></div>
                        <div class="form-group">
                            <label class="form-label">Category</label>
                            <select name="category" class="form-select">
                                @foreach(['academic','governance','sports','charity','social','holiday'] as $ec)
                                    <option value="{{ $ec }}" @selected($ev->category === $ec)>{{ ucfirst($ec) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group"><label class="form-label">Venue</label><input type="text" name="venue_name" value="{{ $ev->venue_name }}" class="form-control" required></div>
                        <div class="form-group"><label class="form-label">Starts At</label><input type="datetime-local" name="starts_at" value="{{ optional($ev->starts_at)->format('Y-m-d\TH:i') }}" class="form-control" required></div>
                        <div class="form-group"><label class="form-label">Description</label><textarea name="description" rows="2" class="form-control" required>{{ $ev->description }}</textarea></div>
                        <div class="form-group"><label class="form-label">Upload / Replace Photo</label><input type="file" name="image" accept="image/*" class="form-control"></div>
                        @if($ev->image)
                            <label class="flex-center text-xs mb-2" style="gap:6px;color:var(--nit-red-dark);font-weight:700;cursor:pointer;">
                                <input type="checkbox" name="remove_image" value="1"> Remove current image
                            </label>
                        @endif
                        <button type="submit" class="btn btn-primary btn-sm">Save Event</button>
                    </form>
                </details>
            </div>
        @empty
            <p class="text-sm muted">No campus events yet.</p>
        @endforelse
    </div>

    {{-- Manage Sports Clubs --}}
    <div class="card">
        <h2 style="margin:0 0 12px;font-size:16px;font-weight:800;color:var(--nit-black);">Manage Sports Clubs ({{ $sports->count() }})</h2>
        @forelse($sports as $sp)
            <div style="padding:10px 0;border-bottom:1px solid var(--border);">
                <div class="flex-between" style="gap:6px;">
                    <strong style="font-size:13.5px;color:var(--nit-black);">{{ $sp->name }}</strong>
                    <form method="POST" action="{{ route('admin.sports.delete', $sp) }}" style="margin:0;" onsubmit="return confirm('Delete sports club {{ $sp->name }}?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm" style="padding:3px 7px;font-size:11px;">Delete</button>
                    </form>
                </div>
                <div class="text-xs muted">{{ ucfirst($sp->sport_type) }} · {{ $sp->schedule }} · {{ $sp->venue }}</div>

                <details class="admin-details mt-1">
                    <summary>Edit Club & Fixture</summary>
                    <form method="POST" action="{{ route('admin.sports.update', $sp) }}" class="admin-details-body">
                        @csrf
                        @method('PUT')
                        <div class="form-group"><label class="form-label">Club Name</label><input type="text" name="name" value="{{ $sp->name }}" class="form-control" required></div>
                        <div class="form-group">
                            <label class="form-label">Sport</label>
                            <select name="sport_type" class="form-select">
                                @foreach(['football','basketball','volleyball','netball','athletics','chess','other'] as $st)
                                    <option value="{{ $st }}" @selected($sp->sport_type === $st)>{{ ucfirst($st) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group"><label class="form-label">Captain</label><input type="text" name="captain_name" value="{{ $sp->captain_name }}" class="form-control"></div>
                        <div class="form-group"><label class="form-label">Schedule</label><input type="text" name="schedule" value="{{ $sp->schedule }}" class="form-control" required></div>
                        <div class="form-group"><label class="form-label">Venue</label><input type="text" name="venue" value="{{ $sp->venue }}" class="form-control" required></div>
                        <div class="form-group"><label class="form-label">Upcoming Match</label><input type="text" name="upcoming_match" value="{{ $sp->upcoming_match }}" class="form-control"></div>
                        <div class="form-group"><label class="form-label">Last Result</label><input type="text" name="last_result" value="{{ $sp->last_result }}" class="form-control"></div>
                        <button type="submit" class="btn btn-primary btn-sm">Save Club</button>
                    </form>
                </details>
            </div>
        @empty
            <p class="text-sm muted">No sports clubs yet.</p>
        @endforelse
    </div>
</div>
@endsection
