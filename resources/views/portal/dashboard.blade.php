@extends('layouts.app')

@section('title', 'Student Dashboard — SONIT | NIT')

@section('content')
    {{-- Prominent President Portrait + Editable Organization Motto Banner on Dashboard --}}
    <div class="president-banner">
        <img src="{{ asset($presidentLeader?->photo ?? 'images/portraits/president.jpg') }}"
             alt="{{ $presidentLeader?->name ?? 'SONIT President' }}"
             class="president-banner-photo">

        <div>
            <div class="flex-center flex-wrap mb-1" style="gap:6px;">
                <span class="badge" style="background:var(--nit-blue);color:#fff;">{{ $siteSettings['org_short_name'] ?? 'SONIT' }} Official Motto</span>
                <span class="badge badge-crimson">{{ $user->roleLabel() }}</span>
                @if($user->course)
                    <span class="badge" style="background:rgba(255,255,255,0.14);color:#fff;">{{ $user->course->code }}</span>
                @endif
            </div>
            <h1 style="margin:4px 0 6px;font-size:clamp(19px,2.2vw,24px);font-weight:800;color:#FFFFFF;line-height:1.2;">
                "{{ $siteSettings['motto'] ?? 'Unity, Integrity, and Academic Excellence in Motion.' }}"
            </h1>
            <div class="text-xs" style="color:#CBD5E1;margin-bottom:8px;">
                <strong>{{ $presidentLeader?->name ?? 'Hon. Stavius Alkadi' }}</strong> — {{ $presidentLeader?->position ?? 'President, SONIT Student Government' }}
            </div>
            <div class="text-xs" style="color:var(--nit-blue);font-weight:700;">
                Signed in as {{ $user->name }} ({{ $user->reg_no ?? $user->email }})
            </div>
        </div>

        <div style="display:flex;flex-direction:column;gap:6px;">
            <a href="{{ route('profile.edit') }}" class="btn btn-ghost-light btn-sm">Profile & Password</a>
            @if($user->isAdmin())
                <a href="{{ route('admin.dashboard') }}" class="btn btn-danger btn-sm">Admin Console</a>
            @endif
        </div>
    </div>

    {{-- Compact KPI Strip --}}
    <div class="grid grid-4 mb-4">
        <div class="card card-accent-red" style="padding:14px;">
            <span class="kicker">Unread Alerts</span>
            <strong class="mono" style="font-size:24px;color:var(--nit-black);">{{ $unreadNotificationCount }}</strong>
            <div class="text-xs muted">{{ $unreadUrgentCount }} urgent · <a href="{{ route('announcements.index') }}">Open</a></div>
        </div>
        <div class="card card-accent-blue" style="padding:14px;">
            <span class="kicker">My Course Channels</span>
            <strong class="mono" style="font-size:24px;color:var(--nit-black);">{{ $myCourseCommunities->count() }}</strong>
            <div class="text-xs muted">{{ $user->course?->code ?? 'Campus' }} · <a href="{{ route('communities.index') }}">Browse</a></div>
        </div>
        <div class="card card-accent-black" style="padding:14px;">
            <span class="kicker">Election Status</span>
            <strong style="font-size:15px;color:{{ ($votedPresident && $votedPr) ? 'var(--emerald-700)' : 'var(--nit-red)' }};display:block;margin:4px 0;">
                @if($activeElection && $activeElection->isEnded())
                    Ended by Commission
                @elseif($votedPresident && $votedPr)
                    All Ballots Cast
                @else
                    Voting Open
                @endif
            </strong>
            <div class="text-xs muted"><a href="{{ route('elections.index') }}">Go to Elections</a></div>
        </div>
        <div class="card card-accent-blue" style="padding:14px;">
            <span class="kicker">Help Desk Tickets</span>
            <strong class="mono" style="font-size:24px;color:var(--nit-black);">{{ $myTickets->count() }}</strong>
            <div class="text-xs muted"><a href="{{ route('help-desk.index') }}">Open Support Desk</a></div>
        </div>
    </div>

    {{-- Compact 2-Column Command Workspace --}}
    <div class="grid grid-2" style="align-items:start;">
        <div class="card">
            <div class="flex-between mb-2">
                <h2 style="margin:0;font-size:16px;font-weight:800;color:var(--nit-black);">My {{ $user->course?->code ?? 'Campus' }} Communities</h2>
                <a href="{{ route('communities.index') }}" class="btn btn-outline btn-sm">All Channels</a>
            </div>
            @if($user->isClassRep())
                <div class="flash flash-info mb-2" style="padding:8px 10px;font-size:12px;">
                    CR Privileges active for <strong>{{ $user->course?->code }}</strong> only.
                </div>
            @endif
            @forelse($myCourseCommunities as $comm)
                <div class="flex-between" style="padding:10px 0;border-bottom:1px solid var(--border);">
                    <div>
                        <strong style="font-size:13.5px;color:var(--nit-black);">{{ $comm->name }}</strong>
                        <div class="text-xs muted">{{ $comm->members_count }} members · {{ $comm->cr ? 'CR: '.$comm->cr->name : 'CR Vacant (Admin Controlled)' }}</div>
                    </div>
                    <a href="{{ route('communities.show', $comm) }}" class="btn btn-primary btn-sm">Open</a>
                </div>
            @empty
                <p class="text-xs muted">Browse all NIT course communities from the left side menu.</p>
            @endforelse
        </div>

        <div class="card">
            <div class="flex-between mb-2">
                <h2 style="margin:0;font-size:16px;font-weight:800;color:var(--nit-black);">Latest Campus Notices & Tickets</h2>
                <a href="{{ route('help-desk.index') }}" class="btn btn-outline btn-sm">New Ticket</a>
            </div>
            @foreach($latestAnnouncements->take(3) as $a)
                <div class="flex-between" style="padding:8px 0;border-bottom:1px solid var(--border);">
                    <div style="min-width:0;">
                        <a href="{{ route('announcements.show', $a) }}" style="font-weight:700;color:var(--nit-black);font-size:13px;">{{ \Illuminate\Support\Str::limit($a->title, 58) }}</a>
                        <div class="text-xs muted">{{ optional($a->published_at)->diffForHumans() }}</div>
                    </div>
                    @if($a->is_urgent)<span class="badge badge-crimson">Urgent</span>@else<span class="badge badge-navy">{{ ucfirst($a->category) }}</span>@endif
                </div>
            @endforeach
            @foreach($myTickets->take(2) as $t)
                <div class="flex-between" style="padding:8px 0;border-bottom:1px solid var(--border);">
                    <div>
                        <a href="{{ route('help-desk.show', $t) }}" style="font-weight:700;font-size:13px;color:var(--nit-blue-dark);">{{ $t->subject }}</a>
                        <div class="text-xs mono muted">{{ $t->ticket_number }}</div>
                    </div>
                    <span class="badge badge-slate">{{ ucfirst(str_replace('_',' ',$t->status)) }}</span>
                </div>
            @endforeach
        </div>
    </div>
@endsection
