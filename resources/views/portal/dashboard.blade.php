@extends('layouts.app')

@section('title', 'My Student Portal — SONIT')

@section('content')
<div class="page-banner">
    <div class="container page-banner-inner">
        <div class="page-banner-left">
            <div class="icon-3d-tile lg"><img src="{{ asset('images/icons/leadership.svg') }}" alt="Portal"></div>
            <div>
                <div class="flex-center mb-1">
                    <span class="badge badge-gold">{{ $user->roleLabel() }}</span>
                    @if($user->course)
                        <span class="badge badge-navy mono">{{ $user->course->code }}</span>
                    @endif
                </div>
                <h1>Welcome, {{ $user->name }}</h1>
                <p>{{ $user->reg_no ?? $user->email }} · {{ $user->course?->name ?? 'SONIT Governance Member' }}</p>
            </div>
        </div>
        <div class="flex-center flex-wrap">
            <a href="{{ route('profile.edit') }}" class="btn btn-ghost-light">Edit Profile & Password</a>
            @if($user->isAdmin())
                <a href="{{ route('admin.dashboard') }}" class="btn btn-gold">Open Admin Console</a>
            @endif
        </div>
    </div>
</div>

<section class="section section-tight">
    <div class="container">
        {{-- Quick Status Row --}}
        <div class="grid grid-4 mb-4">
            <div class="card">
                <span class="kicker">Notifications</span>
                <strong style="font-size:28px;color:var(--navy-900);display:block;">{{ $unreadNotificationCount }}</strong>
                <span class="text-xs muted">Unread announcements ({{ $unreadUrgentCount }} urgent)</span>
            </div>
            <div class="card">
                <span class="kicker">My Course Channels</span>
                <strong style="font-size:28px;color:var(--navy-900);display:block;">{{ $myCourseCommunities->count() }}</strong>
                <span class="text-xs muted">{{ $user->course?->code ?? 'All' }} active communities</span>
            </div>
            <div class="card">
                <span class="kicker">Election Status</span>
                <strong style="font-size:20px;color:{{ ($votedPresident && $votedPr) ? 'var(--emerald-700)' : 'var(--navy-700)' }};display:block;margin-top:4px;">
                    {{ ($votedPresident && $votedPr) ? 'All Ballots Cast' : 'Voting Pending' }}
                </strong>
                <span class="text-xs muted">President: {{ $votedPresident ? 'Voted' : 'Open' }} · PR: {{ $votedPr ? 'Voted' : 'Open' }}</span>
            </div>
            <div class="card">
                <span class="kicker">Support Tickets</span>
                <strong style="font-size:28px;color:var(--navy-900);display:block;">{{ $myTickets->count() }}</strong>
                <span class="text-xs muted"><a href="{{ route('help-desk.index') }}">Open Help Desk</a></span>
            </div>
        </div>

        <div class="grid grid-2" style="align-items:start;">
            {{-- My Course Communities --}}
            <div class="card">
                <div class="flex-between mb-3">
                    <div>
                        <span class="kicker">Course Communities</span>
                        <h2 style="margin:0;font-size:20px;font-weight:800;color:var(--navy-900);">{{ $user->course?->code ?? 'Campus' }} Channels</h2>
                    </div>
                    <a href="{{ route('communities.index') }}" class="btn btn-outline btn-sm">All Channels</a>
                </div>

                @if($user->isClassRep())
                    <div class="flash flash-info" style="font-size:13px;">
                        As {{ $user->course?->code }} Class Representative, you can create and moderate sub-communities exclusively for {{ $user->course?->code }}.
                    </div>
                @endif

                <div style="display:flex;flex-direction:column;gap:12px;">
                    @forelse($myCourseCommunities as $comm)
                        <div style="padding:14px;border:1px solid var(--border);border-radius:12px;display:flex;justify-content:space-between;align-items:center;gap:12px;">
                            <div>
                                <div class="flex-center mb-1">
                                    <strong style="color:var(--navy-900);">{{ $comm->name }}</strong>
                                    @if($comm->is_official)<span class="badge badge-gold">Official</span>@endif
                                </div>
                                <div class="text-xs muted">
                                    {{ $comm->members_count }} members · {{ $comm->messages_count }} messages ·
                                    {{ $comm->cr ? 'CR: '.$comm->cr->name : 'CR Seat Vacant (Admin Managed)' }}
                                </div>
                            </div>
                            <a href="{{ route('communities.show', $comm) }}" class="btn btn-primary btn-sm">Open</a>
                        </div>
                    @empty
                        <p class="text-sm muted">Explore all 23 NIT course communities and general campus squares.</p>
                    @endforelse
                </div>
            </div>

            {{-- Elections & Latest Notices --}}
            <div style="display:flex;flex-direction:column;gap:20px;">
                @if($activeElection)
                    <div class="card" style="background:linear-gradient(135deg, var(--navy-950), var(--navy-800));color:#fff;border:none;">
                        <div class="flex-between mb-2">
                            <span class="badge badge-gold">{{ $activeElection->academic_year }} Elections</span>
                            <span class="badge {{ $activeElection->isVotingActive() ? 'badge-emerald' : 'badge-amber' }}">
                                {{ $activeElection->isVotingActive() ? 'Polls Open' : 'Polls Closed' }}
                            </span>
                        </div>
                        <h3 style="margin:0 0 8px;font-size:19px;">{{ $activeElection->title }}</h3>
                        <p style="margin:0 0 16px;font-size:13.5px;color:#CBD5E1;">Cast your verified vote for President and your {{ $user->course?->code ?? 'Course' }} Parliament Representative.</p>
                        <div class="flex-center flex-wrap">
                            <a href="{{ route('elections.index') }}" class="btn btn-gold btn-sm">Go to Election Hub</a>
                            <a href="{{ route('elections.results') }}" class="btn btn-ghost-light btn-sm">View Results</a>
                        </div>
                    </div>
                @endif

                <div class="card">
                    <div class="flex-between mb-3">
                        <h3 style="margin:0;font-size:18px;font-weight:800;color:var(--navy-900);">Recent Help Desk & Bookings</h3>
                        <a href="{{ route('help-desk.index') }}" class="btn btn-outline btn-sm">New Ticket</a>
                    </div>
                    @forelse($myTickets as $ticket)
                        <div class="flex-between" style="padding:10px 0;border-bottom:1px solid var(--border);">
                            <div>
                                <a href="{{ route('help-desk.show', $ticket) }}" style="font-weight:700;color:var(--navy-900);">{{ $ticket->subject }}</a>
                                <div class="text-xs muted mono">{{ $ticket->ticket_number }} · {{ $ticket->categoryLabel() }}</div>
                            </div>
                            <span class="badge badge-navy">{{ ucfirst(str_replace('_', ' ', $ticket->status)) }}</span>
                        </div>
                    @empty
                        <p class="text-sm muted" style="margin:0;">No active support tickets. Need assistance with HESLB, SIMS, or accommodation? Open a Help Desk ticket anytime.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
