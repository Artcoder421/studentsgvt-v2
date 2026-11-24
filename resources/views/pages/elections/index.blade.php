@extends('layouts.app')

@section('title', 'SONIT E-Elections Portal — NIT')

@section('content')
<div class="page-banner">
    <div class="container page-banner-inner">
        <div class="page-banner-left">
            <div class="icon-3d-tile lg"><img src="{{ asset('images/icons/elections.jpg') }}" alt="Elections"></div>
            <div>
                <h1>{{ $election->title ?? 'SONIT E-Elections Portal' }}</h1>
                <p>{{ $election->description ?? 'Verified, single-ballot electronic democracy for the National Institute of Transport.' }}</p>
            </div>
        </div>
        <div class="flex-center flex-wrap">
            @if($election && $election->applications_open)
                <a href="{{ route('elections.apply') }}" class="btn btn-ghost-light">Apply as Candidate</a>
            @endif
            <a href="{{ route('elections.results') }}" class="btn btn-gold">View Live Results</a>
        </div>
    </div>
</div>

<section class="section section-tight">
    <div class="container">
        @if(!$election || !$election->is_enabled)
            <div class="card" style="text-align:center;padding:48px;">
                <h2 style="color:var(--navy-900);">No Active Election Cycle</h2>
                <p class="muted">The SONIT Electoral Commission has not opened voting at this time. Please watch official announcements.</p>
            </div>
        @else
            {{-- Election Status Bar --}}
            <div class="card mb-4" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px;">
                <div class="flex-center flex-wrap" style="gap:10px;">
                    <span class="badge {{ $election->isVotingActive() ? 'badge-emerald' : 'badge-crimson' }}">
                        {{ $election->isVotingActive() ? 'Polls Open Now' : 'Voting Closed' }}
                    </span>
                    <span class="badge {{ $election->applications_open ? 'badge-navy' : 'badge-slate' }}">
                        Nominations: {{ $election->applications_open ? 'Open' : 'Closed' }}
                    </span>
                    <span class="badge badge-gold">Academic Year {{ $election->academic_year }}</span>
                </div>
                @if($election->voting_ends_at && $election->isVotingActive())
                    <div class="flex-center">
                        <span class="text-xs muted" style="font-weight:700;">POLLS CLOSE IN:</span>
                        <div class="countdown-clock" data-countdown="{{ $election->voting_ends_at->toIso8601String() }}" style="color:var(--navy-900);">
                            <span class="badge badge-navy mono"><span data-cd-days>00</span>d : <span data-cd-hours>00</span>h : <span data-cd-mins>00</span>m : <span data-cd-secs>00</span>s</span>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Presidential Race --}}
            <div class="flex-between flex-wrap mb-3">
                <div>
                    <span class="kicker">Campus-Wide Ballot</span>
                    <h2 class="section-title" style="font-size:25px;">Presidential Candidates</h2>
                </div>
                @auth
                    @if(in_array('President', $votedPositions))
                        <span class="badge badge-emerald" style="padding:8px 14px;font-size:13px;">Presidential Ballot Recorded</span>
                    @elseif($election->isVotingActive())
                        <a href="{{ route('elections.ballot', ['position' => 'President']) }}" class="btn btn-primary">Vote for President</a>
                    @endif
                @else
                    <a href="{{ route('login') }}" class="btn btn-primary">Sign In to Vote</a>
                @endauth
            </div>

            <div class="grid grid-2 mb-4">
                @forelse($presidentialCandidates as $cand)
                    <div class="card card-hover" style="display:flex;gap:20px;align-items:flex-start;">
                        <img src="{{ asset($cand->photo ?? 'images/portraits/president.jpg') }}" alt="{{ $cand->fullname }}" style="width:110px;height:132px;object-fit:cover;object-position:top;border-radius:14px;flex-shrink:0;background:var(--navy-900);">
                        <div>
                            <div class="flex-center mb-1">
                                <span class="badge badge-gold">President</span>
                                @if($cand->course)<span class="badge badge-navy mono">{{ $cand->course->code }}</span>@endif
                            </div>
                            <h3 style="margin:4px 0;font-size:19px;font-weight:800;color:var(--navy-900);">{{ $cand->fullname }}</h3>
                            <div class="text-xs muted mono mb-2">{{ $cand->reg_no }} · {{ $cand->academic_level }}</div>
                            <div style="font-size:13.5px;font-weight:700;color:var(--navy-700);margin-bottom:8px;">"{{ $cand->motto }}"</div>
                            <p class="text-sm muted" style="margin:0;white-space:pre-line;">{{ $cand->manifesto }}</p>
                        </div>
                    </div>
                @empty
                    <div class="card" style="grid-column:1/-1;"><p class="muted" style="margin:0;">No approved Presidential candidates yet.</p></div>
                @endforelse
            </div>

            {{-- Course-Scoped Parliament Representative Race --}}
            <div class="flex-between flex-wrap mb-3 mt-4" style="padding-top:16px;">
                <div>
                    <span class="kicker">Course-Scoped Representation</span>
                    <h2 class="section-title" style="font-size:25px;">
                        Parliament Representatives
                        @auth
                            @if(auth()->user()->course)
                                ({{ auth()->user()->course->code }})
                            @endif
                        @endauth
                    </h2>
                </div>
                @auth
                    @if(in_array('Parliament Representative', $votedPositions))
                        <span class="badge badge-emerald" style="padding:8px 14px;font-size:13px;">Course PR Ballot Recorded</span>
                    @elseif($election->isVotingActive() && $prCandidates->isNotEmpty())
                        <a href="{{ route('elections.ballot', ['position' => 'Parliament Representative']) }}" class="btn btn-gold">Vote for Your Course PR</a>
                    @endif
                @endauth
            </div>

            <div class="grid grid-3">
                @forelse($prCandidates as $cand)
                    <div class="card card-hover">
                        <div class="flex-between mb-2">
                            <span class="badge badge-navy mono">{{ $cand->course?->code ?? 'Course' }} PR</span>
                            <span class="text-xs muted">{{ $cand->academic_level }}</span>
                        </div>
                        <h3 style="margin:0 0 4px;font-size:17.5px;font-weight:800;color:var(--navy-900);">{{ $cand->fullname }}</h3>
                        <div class="text-xs mono muted mb-2">{{ $cand->reg_no }}</div>
                        <div style="font-size:13px;font-weight:700;color:var(--gold-600);margin-bottom:8px;">"{{ $cand->motto }}"</div>
                        <p class="text-sm muted" style="margin:0;">{{ $cand->manifesto }}</p>
                    </div>
                @empty
                    <div class="card" style="grid-column:1/-1;">
                        <p class="muted" style="margin:0;">
                            No approved Parliament Representative candidates for your course yet.
                            @if($election->applications_open)
                                <a href="{{ route('elections.apply') }}" style="font-weight:700;">Submit your nomination now.</a>
                            @endif
                        </p>
                    </div>
                @endforelse
            </div>
        @endif
    </div>
</section>
@endsection
