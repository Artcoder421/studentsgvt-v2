@extends('layouts.app')

@section('title', 'Sports & Athletics — SONIT | NIT')

@section('content')
<div class="page-banner">
    <div class="container page-banner-inner">
        <div class="page-banner-left">
            <div class="icon-3d-tile lg"><img src="{{ asset('images/icons/sports.svg') }}" alt="Sports"></div>
            <div>
                <h1>NIT Sports, Athletics & Varsity Teams</h1>
                <p>Managed by the SONIT Ministry of Sports — follow Inter-Faculty Transport Cup fixtures, results, and register for team trials.</p>
            </div>
        </div>
    </div>
</div>

<section class="section section-tight">
    <div class="container">
        <div class="grid grid-2">
            @foreach($activities as $sport)
                @php $joinedStatus = $myRegistrations[$sport->id] ?? null; @endphp
                <article class="card card-hover" style="display:flex;flex-direction:column;justify-content:space-between;">
                    <div>
                        <div class="flex-between mb-2">
                            <span class="badge badge-navy">{{ ucfirst($sport->sport_type) }}</span>
                            <span class="badge badge-gold mono">{{ $sport->registrations_count }} Registered</span>
                        </div>
                        <h3 style="margin:0 0 6px;font-size:20px;font-weight:800;color:var(--navy-900);">{{ $sport->name }}</h3>
                        <div class="text-xs muted mb-2">
                            Captain: <strong>{{ $sport->captain_name ?? 'SONIT Sports Desk' }}</strong> · Venue: {{ $sport->venue }}
                        </div>
                        <div class="text-xs mono mb-2" style="color:var(--navy-700);font-weight:700;">Training: {{ $sport->schedule }}</div>
                        <p class="text-sm muted" style="margin:0 0 14px;">{{ $sport->description }}</p>

                        <div style="background:var(--canvas);border:1px solid var(--border);border-radius:10px;padding:12px;margin-bottom:14px;">
                            @if($sport->upcoming_match)
                                <div class="text-xs" style="font-weight:700;color:var(--navy-900);">Next Fixture: {{ $sport->upcoming_match }}</div>
                                @if($sport->match_date)<div class="text-xs muted mono">{{ $sport->match_date->format('d M Y · H:i') }}</div>@endif
                            @endif
                            @if($sport->last_result)
                                <div class="text-xs mt-1" style="color:var(--emerald-700);font-weight:700;">Last Result: {{ $sport->last_result }}</div>
                            @endif
                        </div>
                    </div>

                    <div style="border-top:1px solid var(--border);padding-top:12px;">
                        @auth
                            @if($joinedStatus)
                                <span class="badge badge-emerald">Registered for Trials ({{ ucfirst($joinedStatus) }})</span>
                            @elseif($sport->is_open_for_joining)
                                <form method="POST" action="{{ route('sports.join', $sport) }}" style="display:flex;gap:8px;">
                                    @csrf
                                    <input type="text" name="position_or_skill" class="form-control" style="padding:8px 10px;font-size:12.5px;" placeholder="Position / specialty (e.g. Striker, Point Guard)">
                                    <button type="submit" class="btn btn-primary btn-sm">Join Squad</button>
                                </form>
                            @endif
                        @else
                            <a href="{{ route('login') }}" class="btn btn-outline btn-sm">Sign In to Join Team</a>
                        @endauth
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
@endsection
