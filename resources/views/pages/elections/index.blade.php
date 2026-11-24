@extends('layouts.app')

@section('title', 'SONIT E-Elections Portal — NIT')

@section('content')
<div class="page-banner">
    <div class="page-banner-inner">
        <div class="page-banner-left">
            <div class="icon-3d-tile"><img src="{{ asset('images/icons/elections.jpg') }}" alt="Elections"></div>
            <div>
                <h1>{{ $election->title ?? 'SONIT E-Elections Portal' }}</h1>
                <p>{{ $election->description ?? 'Verified, single-ballot electronic democracy for the National Institute of Transport.' }}</p>
            </div>
        </div>
        <div class="flex-center flex-wrap">
            @if($election && !$election->isEnded() && $election->applications_open)
                <a href="{{ route('elections.apply') }}" class="btn btn-ghost-light btn-sm">Apply as Candidate</a>
            @endif
            <a href="{{ route('elections.results') }}" class="btn btn-danger btn-sm">View Live Results</a>
        </div>
    </div>
</div>

@if(!$election || !$election->is_enabled)
    <div class="card" style="text-align:center;padding:34px;">
        <h2 style="color:var(--nit-black);">No Active Election Cycle</h2>
        <p class="muted">The SONIT Electoral Commission has not opened voting at this time.</p>
    </div>
@else
    @if($election->isEnded())
        <div class="flash flash-error mb-3">
            <span><strong>ELECTION ENDED:</strong> Polls were officially closed by the Electoral Commission on {{ $election->ended_at->format('d M Y, H:i') }}. Voting is no longer permitted.</span>
            <a href="{{ route('elections.results') }}" class="btn btn-danger btn-sm">View Official Results</a>
        </div>
    @endif

    @if($myApplication)
        <div class="card {{ ($myApplication->interview_status === 'failed' || $myApplication->status === 'declined') ? 'card-accent-red' : 'card-accent-blue' }} mb-3">
            <div class="flex-between flex-wrap mb-1">
                <div>
                    <strong>My Nomination ({{ $myApplication->position }}):</strong>
                    <span class="badge {{ ($myApplication->interview_status === 'failed' || $myApplication->status === 'declined') ? 'badge-crimson' : ($myApplication->status === 'approved' ? 'badge-emerald' : 'badge-navy') }}">
                        {{ $myApplication->stageLabel() }}
                    </span>
                </div>
                <span class="text-xs mono muted">Fee: TZS {{ number_format($myApplication->application_fee_amount) }} ({{ strtoupper($myApplication->payment_status) }})</span>
            </div>
            @if($myApplication->interview_status === 'failed' || $myApplication->status === 'declined')
                <div class="text-xs mt-1" style="color:var(--nit-red-dark);font-weight:700;">
                    Did Not Pass Interview{{ $myApplication->interview_notes ? ' — ' . $myApplication->interview_notes : '' }}
                </div>
            @elseif($myApplication->interview_date)
                <div class="text-xs mt-1"><strong>Scheduled Interview:</strong> {{ $myApplication->interview_date }}</div>
            @elseif($myApplication->payment_status === 'unpaid')
                <form method="POST" action="{{ route('elections.application.payment', $myApplication) }}" class="flex-center flex-wrap mt-2" style="gap:8px;">
                    @csrf
                    <input type="text" name="payment_reference" class="form-control mono" style="max-width:280px;" placeholder="Enter Fee Payment Reference..." required>
                    <button type="submit" class="btn btn-primary btn-sm">Submit Payment Ref</button>
                </form>
            @endif
        </div>
    @endif

    <div class="card mb-3 flex-between flex-wrap" style="padding:12px 16px;">
        <div class="flex-center flex-wrap" style="gap:8px;">
            <span class="badge {{ $election->isVotingActive() ? 'badge-emerald' : 'badge-crimson' }}">
                {{ $election->isEnded() ? 'Ended by Admin' : ($election->isVotingActive() ? 'Polls Open Now' : 'Voting Closed') }}
            </span>
            <span class="badge badge-navy">Nominations: {{ (!$election->isEnded() && $election->applications_open) ? 'Open' : 'Closed' }}</span>
            <span class="badge badge-black">{{ $election->academic_year }}</span>
        </div>
        @if($election->voting_ends_at && $election->isVotingActive())
            <div class="flex-center">
                <span class="text-xs muted" style="font-weight:700;">CLOSES IN:</span>
                <div class="countdown-clock" data-countdown="{{ $election->voting_ends_at->toIso8601String() }}">
                    <span class="badge badge-black mono"><span data-cd-days>00</span>d : <span data-cd-hours>00</span>h : <span data-cd-mins>00</span>m : <span data-cd-secs>00</span>s</span>
                </div>
            </div>
        @endif
    </div>

    <div class="grid grid-2" style="align-items:start;">
        {{-- Presidential Race --}}
        <div class="card card-accent-blue">
            <div class="flex-between flex-wrap mb-3">
                <div>
                    <span class="kicker">Campus-Wide Ballot</span>
                    <h2 class="section-title">Presidential Candidates</h2>
                </div>
                @auth
                    @if(in_array('President', $votedPositions))
                        <span class="badge badge-emerald">Ballot Cast</span>
                    @elseif($election->isVotingActive())
                        <a href="{{ route('elections.ballot', ['position' => 'President']) }}" class="btn btn-primary btn-sm">Vote for President</a>
                    @endif
                @else
                    <a href="{{ route('login') }}" class="btn btn-primary btn-sm">Sign In to Vote</a>
                @endauth
            </div>

            <div style="display:flex;flex-direction:column;gap:12px;">
                @forelse($presidentialCandidates as $cand)
                    <div style="display:flex;gap:14px;padding:12px;border:1px solid var(--border);background:var(--canvas);">
                        <img src="{{ asset($cand->photo ?? 'images/portraits/president.jpg') }}" alt="{{ $cand->fullname }}" style="width:82px;height:96px;object-fit:cover;object-position:top;border-radius:2px;background:var(--nit-black);flex-shrink:0;">
                        <div>
                            <div class="flex-center mb-1">
                                <strong style="font-size:15px;color:var(--nit-black);">{{ $cand->fullname }}</strong>
                                @if($cand->course)<span class="badge badge-navy mono">{{ $cand->course->code }}</span>@endif
                            </div>
                            <div class="text-xs mono muted mb-1">{{ $cand->reg_no }} · {{ $cand->academic_level }}</div>
                            <div class="text-xs" style="font-weight:700;color:var(--nit-red-dark);margin-bottom:4px;">"{{ $cand->motto }}"</div>
                            <p class="text-xs muted" style="margin:0;white-space:pre-line;">{{ $cand->manifesto }}</p>
                        </div>
                    </div>
                @empty
                    <p class="text-sm muted">No approved Presidential candidates yet.</p>
                @endforelse
            </div>
        </div>

        {{-- Course-Scoped PR Race --}}
        <div class="card card-accent-red">
            <div class="flex-between flex-wrap mb-3">
                <div>
                    <span class="kicker">Course-Scoped Ballot</span>
                    <h2 class="section-title">
                        Parliament Rep @auth @if(auth()->user()->course) ({{ auth()->user()->course->code }}) @endif @endauth
                    </h2>
                </div>
                @auth
                    @if(in_array('Parliament Representative', $votedPositions))
                        <span class="badge badge-emerald">PR Ballot Cast</span>
                    @elseif($election->isVotingActive() && $prCandidates->isNotEmpty())
                        <a href="{{ route('elections.ballot', ['position' => 'Parliament Representative']) }}" class="btn btn-danger btn-sm">Vote for Course PR</a>
                    @endif
                @endauth
            </div>

            <div style="display:flex;flex-direction:column;gap:10px;">
                @forelse($prCandidates as $cand)
                    <div style="padding:12px;border:1px solid var(--border);background:var(--canvas);">
                        <div class="flex-between mb-1">
                            <strong style="font-size:15px;color:var(--nit-black);">{{ $cand->fullname }}</strong>
                            <span class="badge badge-black mono">{{ $cand->course?->code ?? 'Course' }} PR</span>
                        </div>
                        <div class="text-xs mono muted mb-1">{{ $cand->reg_no }} · {{ $cand->academic_level }}</div>
                        <div class="text-xs" style="font-weight:700;color:var(--nit-blue-dark);margin-bottom:4px;">"{{ $cand->motto }}"</div>
                        <p class="text-xs muted" style="margin:0;">{{ $cand->manifesto }}</p>
                    </div>
                @empty
                    <p class="text-sm muted">No approved Parliament Representative candidates for your course yet.</p>
                @endforelse
            </div>
        </div>
    </div>
@endif
@endsection
