@extends('layouts.admin')

@section('title', 'Overview — SONIT Admin Console')
@section('page_title', 'Executive Overview & Live KPIs')

@section('content')
<div class="grid grid-4 mb-4">
    <div class="card">
        <span class="kicker">Registered Accounts</span>
        <strong style="font-size:30px;color:var(--navy-900);">{{ $metrics['users'] }}</strong>
        <div class="text-xs muted mt-1"><a href="{{ route('admin.users') }}">Manage RBAC Roles</a></div>
    </div>
    <div class="card">
        <span class="kicker">NIT Courses / Channels</span>
        <strong style="font-size:30px;color:var(--navy-900);">{{ $metrics['courses'] }} / {{ $metrics['communities'] }}</strong>
        <div class="text-xs muted mt-1">{{ $metrics['vacant_crs'] }} vacant CR seats (Admin governed)</div>
    </div>
    <div class="card">
        <span class="kicker">Open Help Tickets</span>
        <strong style="font-size:30px;color:var(--crimson-600);">{{ $metrics['open_tickets'] }}</strong>
        <div class="text-xs muted mt-1">{{ $metrics['pending_bookings'] }} venue · {{ $metrics['pending_accommodations'] }} housing pending</div>
    </div>
    <div class="card card-accent-blue">
        <span class="kicker">Portal Traffic (Admin Only)</span>
        <strong class="mono" style="font-size:28px;color:var(--nit-blue-dark);">{{ number_format($metrics['total_visits']) }}</strong>
        <div class="text-xs muted mt-1">
            {{ number_format($metrics['today_visits']) }} today · {{ number_format($metrics['unique_visitors']) }} unique IPs ·
            <a href="{{ route('admin.logs') }}">View Traffic & Logs</a>
        </div>
    </div>
</div>

<div class="grid grid-2" style="align-items:start;">
    <div class="card">
        <div class="flex-between mb-3">
            <h2 style="margin:0;font-size:18px;font-weight:800;color:var(--navy-900);">Recent Help Desk Tickets</h2>
            <a href="{{ route('admin.services') }}" class="btn btn-outline btn-sm">Manage All</a>
        </div>
        @forelse($recentTickets as $t)
            <div class="flex-between" style="padding:10px 0;border-bottom:1px solid var(--border);">
                <div>
                    <a href="{{ route('help-desk.show', $t) }}" style="font-weight:700;color:var(--navy-900);">{{ $t->subject }}</a>
                    <div class="text-xs muted mono">{{ $t->ticket_number }} · {{ $t->is_anonymous ? 'Anonymous' : $t->user->name }}</div>
                </div>
                <span class="badge badge-navy">{{ ucfirst(str_replace('_',' ',$t->status)) }}</span>
            </div>
        @empty
            <p class="text-sm muted">No tickets yet.</p>
        @endforelse
    </div>

    <div class="card">
        <div class="flex-between mb-3">
            <h2 style="margin:0;font-size:18px;font-weight:800;color:var(--navy-900);">Active Election & Pending Vetting</h2>
            <a href="{{ route('admin.elections') }}" class="btn btn-primary btn-sm">Election Controls</a>
        </div>
        @if($activeElection)
            <div class="mb-3">
                <strong style="font-size:16px;color:var(--navy-900);">{{ $activeElection->title }}</strong>
                <div class="flex-center flex-wrap mt-2" style="gap:6px;">
                    <span class="badge {{ $activeElection->is_enabled ? 'badge-emerald' : 'badge-crimson' }}">Enabled: {{ $activeElection->is_enabled ? 'Yes' : 'No' }}</span>
                    <span class="badge {{ $activeElection->voting_open ? 'badge-emerald' : 'badge-amber' }}">Voting: {{ $activeElection->voting_open ? 'Open' : 'Closed' }}</span>
                    <span class="badge badge-navy mono">{{ $activeElection->candidates_count }} candidates · {{ $activeElection->votes_count }} votes</span>
                </div>
            </div>
        @endif

        <h3 style="margin:16px 0 10px;font-size:15px;font-weight:800;color:var(--navy-900);">Recent Venue Bookings</h3>
        @forelse($recentBookings as $b)
            <div class="flex-between text-xs" style="padding:8px 0;border-bottom:1px solid var(--border);">
                <div><strong>{{ $b->event_title }}</strong> — {{ $b->venue->code }} ({{ $b->booking_date->format('d M') }})</div>
                <span class="badge {{ $b->status === 'approved' ? 'badge-emerald' : 'badge-amber' }}">{{ ucfirst($b->status) }}</span>
            </div>
        @empty
            <p class="text-sm muted">No venue booking requests.</p>
        @endforelse
    </div>
</div>
@endsection
