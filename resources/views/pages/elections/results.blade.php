@extends('layouts.app')

@section('title', 'Official Election Results — SONIT')

@section('content')
<div class="page-banner">
    <div class="container page-banner-inner">
        <div class="page-banner-left">
            <div class="icon-3d-tile lg"><img src="{{ asset('images/icons/elections.jpg') }}" alt="Results"></div>
            <div>
                <h1>Official Election Tally & Turnout</h1>
                <p>{{ $election->title }} · Real-time audited vote breakdown.</p>
            </div>
        </div>
        <a href="{{ route('elections.index') }}" class="btn btn-ghost-light">Back to Elections</a>
    </div>
</div>

<section class="section section-tight">
    <div class="container">
        <div class="grid grid-3 mb-4">
            <div class="card">
                <span class="kicker">Verified Participation</span>
                <strong style="font-size:30px;color:var(--navy-900);display:block;">{{ $totalUniqueVoters }}</strong>
                <span class="text-xs muted">Unique Verified Voters</span>
            </div>
            <div class="card">
                <span class="kicker">Ballots Recorded</span>
                <strong style="font-size:30px;color:var(--navy-900);display:block;">{{ $totalBallotsCast }}</strong>
                <span class="text-xs muted">Total Valid Ballots Cast</span>
            </div>
            <div class="card">
                <span class="kicker">Integrity Status</span>
                <strong style="font-size:20px;color:var(--emerald-700);display:block;margin-top:6px;">Single-Ballot Verified</strong>
                <span class="text-xs muted">Database-locked unique voter constraints</span>
            </div>
        </div>

        <div class="card mb-4">
            <span class="kicker">Campus-Wide Race</span>
            <h2 style="margin:0 0 18px;font-size:22px;font-weight:800;color:var(--navy-900);">Presidential Results</h2>

            <div style="display:flex;flex-direction:column;gap:18px;">
                @forelse($presidentialResults as $idx => $cand)
                    @php $pct = $presidentialResults->sum('votes_count') > 0 ? round(($cand->votes_count / $totalPresidentialVotes) * 100, 1) : 0; @endphp
                    <div style="padding:16px;border:1px solid var(--border);border-radius:14px;">
                        <div class="flex-between mb-2">
                            <div class="flex-center">
                                <img src="{{ asset($cand->photo ?? 'images/portraits/president.jpg') }}" alt="{{ $cand->fullname }}" style="width:48px;height:48px;border-radius:10px;object-fit:cover;object-position:top;">
                                <div>
                                    <strong style="font-size:16px;color:var(--navy-900);">{{ $cand->fullname }}</strong>
                                    @if($idx === 0 && $cand->votes_count > 0)<span class="badge badge-gold" style="margin-left:6px;">Leading</span>@endif
                                    <div class="text-xs muted mono">{{ $cand->reg_no }} · {{ $cand->course?->code }}</div>
                                </div>
                            </div>
                            <div style="text-align:right;">
                                <strong class="mono" style="font-size:18px;color:var(--navy-900);">{{ $cand->votes_count }} votes</strong>
                                <div class="text-xs muted mono">{{ $pct }}%</div>
                            </div>
                        </div>
                        <div class="progress-track">
                            <div class="progress-fill" style="width:{{ $pct }}%;"></div>
                        </div>
                    </div>
                @empty
                    <p class="muted">No Presidential candidates tallied yet.</p>
                @endforelse
            </div>
        </div>

        <div class="card">
            <span class="kicker">Programme Constituencies</span>
            <h2 style="margin:0 0 18px;font-size:22px;font-weight:800;color:var(--navy-900);">Course Parliament Representative Results</h2>

            <div class="grid grid-2">
                @forelse($prCandidates as $courseCode => $list)
                    @php $courseTotal = max(1, $list->sum('votes_count')); @endphp
                    <div style="padding:18px;border:1px solid var(--border);border-radius:14px;background:var(--canvas);">
                        <div class="flex-between mb-3">
                            <span class="badge badge-navy mono">{{ $courseCode }} Constituency</span>
                            <span class="text-xs muted mono">{{ $list->sum('votes_count') }} total votes</span>
                        </div>
                        @foreach($list as $cand)
                            @php $p = $list->sum('votes_count') > 0 ? round(($cand->votes_count / $courseTotal) * 100, 1) : 0; @endphp
                            <div class="mb-2">
                                <div class="flex-between text-sm mb-1">
                                    <strong>{{ $cand->fullname }}</strong>
                                    <span class="mono">{{ $cand->votes_count }} ({{ $p }}%)</span>
                                </div>
                                <div class="progress-track"><div class="progress-fill" style="width:{{ $p }}%;"></div></div>
                            </div>
                        @endforeach
                    </div>
                @empty
                    <p class="muted">No PR candidates tallied yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</section>
@endsection
