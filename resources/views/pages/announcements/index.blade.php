@extends('layouts.app')

@section('title', 'Official Announcements — SONIT')

@section('content')
<div class="page-banner">
    <div class="container page-banner-inner">
        <div class="page-banner-left">
            <div class="icon-3d-tile lg"><img src="{{ asset('images/icons/announcements.jpg') }}" alt="Announcements"></div>
            <div>
                <h1>Official Announcements & Urgent Alerts</h1>
                <p>Verified circulars from the SONIT Cabinet, Academic Ministry, and Electoral Commission.</p>
            </div>
        </div>
        <div class="flex-center flex-wrap">
            @auth
                @if($unreadNotificationCount > 0)
                    <form method="POST" action="{{ route('announcements.mark-all-read') }}" style="margin:0;">
                        @csrf
                        <button type="submit" class="btn btn-gold">Mark All Read ({{ $unreadNotificationCount }})</button>
                    </form>
                @endif
            @endauth
        </div>
    </div>
</div>

<section class="section section-tight">
    <div class="container">
        <div class="flex-between flex-wrap mb-4" style="gap:10px;">
            <div class="flex-center flex-wrap" style="gap:8px;">
                <a href="{{ route('announcements.index') }}" class="btn btn-sm {{ !$category && !$urgentOnly ? 'btn-primary' : 'btn-outline' }}">All</a>
                <a href="{{ route('announcements.index', ['urgent' => 1]) }}" class="btn btn-sm {{ $urgentOnly ? 'btn-danger' : 'btn-outline' }}">Urgent Only</a>
                @foreach(['academic','election','welfare','sports','charity','general'] as $cat)
                    <a href="{{ route('announcements.index', ['category' => $cat]) }}" class="btn btn-sm {{ $category === $cat ? 'btn-primary' : 'btn-outline' }}">{{ ucfirst($cat) }}</a>
                @endforeach
            </div>
        </div>

        <div class="grid grid-3">
            @forelse($announcements as $item)
                @php $isUnread = auth()->check() && !in_array($item->id, $readIds); @endphp
                <article class="card card-hover" style="display:flex;flex-direction:column;justify-content:space-between;">
                    <div>
                        @if($item->cover_image)
                            <img src="{{ asset($item->cover_image) }}" alt="{{ $item->title }}" class="card-media">
                        @endif
                        <div class="flex-between mb-2">
                            <div class="flex-center" style="gap:6px;">
                                @if($item->is_urgent)<span class="badge badge-crimson">Urgent</span>@endif
                                @if($isUnread)<span class="badge badge-gold">New</span>@endif
                                <span class="badge badge-navy">{{ ucfirst($item->category) }}</span>
                            </div>
                            <span class="text-xs muted">{{ optional($item->published_at)->format('d M Y') }}</span>
                        </div>
                        <h2 style="margin:0 0 8px;font-size:18px;font-weight:800;color:var(--navy-900);line-height:1.3;">
                            <a href="{{ route('announcements.show', $item) }}" style="color:inherit;">{{ $item->title }}</a>
                        </h2>
                        <p class="text-sm muted" style="margin:0 0 16px;">{{ \Illuminate\Support\Str::limit($item->excerpt ?? strip_tags($item->content), 130) }}</p>
                    </div>
                    <div class="flex-between" style="border-top:1px solid var(--border);padding-top:12px;">
                        <span class="text-xs muted">{{ $item->comments_count }} comments · {{ $item->author?->name ?? 'SONIT Office' }}</span>
                        <a href="{{ route('announcements.show', $item) }}" class="btn btn-outline btn-sm">Read Full</a>
                    </div>
                </article>
            @empty
                <div class="card" style="grid-column:1/-1;text-align:center;padding:40px;">
                    <p class="muted" style="margin:0;">No announcements match the selected filter.</p>
                </div>
            @endforelse
        </div>

        <div class="mt-4">{{ $announcements->links() }}</div>
    </div>
</section>
@endsection
