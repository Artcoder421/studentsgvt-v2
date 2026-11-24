@extends('layouts.app')

@section('title', 'Official Digital Ballot — SONIT Elections')

@section('content')
<div class="page-banner">
    <div class="container">
        <span class="badge badge-gold mb-2">Verified Single-Ballot Booth</span>
        <h1>Cast Your Vote: {{ $position }}</h1>
        <p>Voter: {{ $user->name }} ({{ $user->reg_no }}) {{ $user->course ? '· '.$user->course->code : '' }}. Once submitted, your ballot is permanently locked and cannot be altered.</p>
    </div>
</div>

<section class="section section-tight">
    <div class="container" style="max-width:920px;">
        @if($candidates->isEmpty())
            <div class="card" style="text-align:center;padding:42px;">
                <h3>No Eligible Candidates Available</h3>
                <p class="muted">There are currently no approved candidates for {{ $position }} in your constituency.</p>
                <a href="{{ route('elections.index') }}" class="btn btn-primary">Return to Elections Hub</a>
            </div>
        @else
            <form method="POST" action="{{ route('elections.vote') }}" onsubmit="return confirm('Confirm your official ballot selection? You can only vote once for {{ $position }}.');">
                @csrf
                <input type="hidden" name="position" value="{{ $position }}">

                <div class="grid grid-2 mb-4">
                    @foreach($candidates as $cand)
                        <label class="card card-hover" style="cursor:pointer;display:block;position:relative;">
                            <div class="flex-between mb-3">
                                <span class="badge badge-navy mono">{{ $cand->course?->code ?? 'NIT' }}</span>
                                <input type="radio" name="candidate_id" value="{{ $cand->id }}" required style="width:20px;height:20px;accent-color:var(--navy-900);">
                            </div>
                            <div class="flex-center mb-3" style="align-items:flex-start;gap:14px;">
                                <img src="{{ asset($cand->photo ?? 'images/portraits/president.jpg') }}" alt="{{ $cand->fullname }}" style="width:76px;height:90px;object-fit:cover;object-position:top;border-radius:10px;background:var(--navy-900);">
                                <div>
                                    <h3 style="margin:0 0 4px;font-size:18px;font-weight:800;color:var(--navy-900);">{{ $cand->fullname }}</h3>
                                    <div class="text-xs mono muted">{{ $cand->reg_no }}</div>
                                    <div class="text-xs muted">{{ $cand->academic_level }}</div>
                                </div>
                            </div>
                            <div style="font-size:13px;font-weight:700;color:var(--gold-600);margin-bottom:6px;">"{{ $cand->motto }}"</div>
                            <p class="text-xs muted" style="margin:0;">{{ \Illuminate\Support\Str::limit($cand->manifesto, 180) }}</p>
                        </label>
                    @endforeach
                </div>

                <div class="flex-between flex-wrap" style="gap:12px;">
                    <a href="{{ route('elections.index') }}" class="btn btn-outline">Cancel & Return</a>
                    <button type="submit" class="btn btn-gold btn-lg">Submit Official Verified Ballot</button>
                </div>
            </form>
        @endif
    </div>
</section>
@endsection
