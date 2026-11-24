@extends('layouts.app')

@section('title', 'Campus Events & Calendar — SONIT | NIT')

@section('content')
<div class="page-banner">
    <div class="container page-banner-inner">
        <div class="page-banner-left">
            <div class="icon-3d-tile lg"><img src="{{ asset('images/icons/events.svg') }}" alt="Events"></div>
            <div>
                <h1>Campus Events & Academic Calendar</h1>
                <p>Symposiums, SONIT debates, sports fixtures, charity outreach days, and academic deadlines curated by SONIT Administration.</p>
            </div>
        </div>
    </div>
</div>

<section class="section section-tight">
    <div class="container">
        <div class="flex-center flex-wrap mb-4" style="gap:8px;">
            <a href="{{ route('events.index') }}" class="btn btn-sm {{ !$category ? 'btn-primary' : 'btn-outline' }}">All Events</a>
            @foreach(['academic','governance','sports','charity','social'] as $cat)
                <a href="{{ route('events.index', ['category' => $cat]) }}" class="btn btn-sm {{ $category === $cat ? 'btn-primary' : 'btn-outline' }}">{{ ucfirst($cat) }}</a>
            @endforeach
        </div>

        <div class="grid grid-2">
            @forelse($events as $event)
                @php $myStatus = $userRsvps[$event->id] ?? null; @endphp
                <article class="card card-hover" style="display:flex;flex-direction:column;justify-content:space-between;">
                    <div>
                        <img src="{{ asset($event->image ?? 'images/campus/coeato.jpg') }}" alt="{{ $event->title }}" class="card-media">
                        <div class="flex-between mb-2">
                            <span class="badge badge-navy">{{ ucfirst($event->category) }}</span>
                            <span class="badge badge-gold mono">{{ $event->starts_at->format('d M Y · H:i') }}</span>
                        </div>
                        <h3 style="margin:0 0 6px;font-size:19px;font-weight:800;color:var(--navy-900);">{{ $event->title }}</h3>
                        <div class="text-xs muted mb-2">Venue: <strong>{{ $event->venue_name }}</strong></div>
                        <p class="text-sm muted" style="margin:0 0 14px;">{{ $event->description }}</p>
                    </div>
                    <div class="flex-between flex-wrap" style="border-top:1px solid var(--border);padding-top:12px;gap:10px;">
                        <span class="text-xs muted mono">{{ $event->going_count }} Going · {{ $event->interested_count }} Interested</span>
                        @auth
                            <div class="flex-center" style="gap:6px;">
                                <form method="POST" action="{{ route('events.rsvp', $event) }}" style="margin:0;">
                                    @csrf
                                    <input type="hidden" name="status" value="going">
                                    <button type="submit" class="btn btn-sm {{ $myStatus === 'going' ? 'btn-emerald' : 'btn-primary' }}">
                                        {{ $myStatus === 'going' ? 'Going' : 'RSVP Going' }}
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('events.rsvp', $event) }}" style="margin:0;">
                                    @csrf
                                    <input type="hidden" name="status" value="interested">
                                    <button type="submit" class="btn btn-sm {{ $myStatus === 'interested' ? 'btn-gold' : 'btn-outline' }}">Interested</button>
                                </form>
                            </div>
                        @else
                            <a href="{{ route('login') }}" class="btn btn-outline btn-sm">Sign In to RSVP</a>
                        @endauth
                    </div>
                </article>
            @empty
                <div class="card" style="grid-column:1/-1;text-align:center;"><p class="muted">No upcoming events published in this category.</p></div>
            @endforelse
        </div>
    </div>
</section>
@endsection
