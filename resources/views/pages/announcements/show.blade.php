@extends('layouts.app')

@section('title', $announcement->title . ' — SONIT Announcements')

@section('content')
<div class="page-banner">
    <div class="container">
        <div class="flex-center mb-2">
            @if($announcement->is_urgent)<span class="badge badge-crimson">Urgent Broadcast</span>@endif
            <span class="badge badge-gold">{{ ucfirst($announcement->category) }}</span>
            <span class="text-xs" style="color:#CBD5E1;">Published {{ optional($announcement->published_at)->format('d M Y, H:i') }}</span>
        </div>
        <h1>{{ $announcement->title }}</h1>
        <p>Issued by {{ $announcement->author?->name ?? 'SONIT Secretariat' }}</p>
    </div>
</div>

<section class="section section-tight">
    <div class="container" style="max-width:880px;">
        <div class="card mb-4">
            @if($announcement->cover_image)
                <img src="{{ asset($announcement->cover_image) }}" alt="{{ $announcement->title }}" style="width:100%;max-height:380px;object-fit:cover;border-radius:14px;margin-bottom:22px;">
            @endif
            <div style="font-size:15.5px;line-height:1.8;color:var(--ink-700);white-space:pre-line;">{{ $announcement->content }}</div>
            <div class="mt-4" style="border-top:1px solid var(--border);padding-top:14px;">
                <a href="{{ route('announcements.index') }}" class="btn btn-outline btn-sm">Back to All Announcements</a>
            </div>
        </div>

        <div class="card">
            <h2 style="margin:0 0 16px;font-size:19px;font-weight:800;color:var(--navy-900);">Student Discussion ({{ $announcement->comments->count() }})</h2>
            @auth
                <form method="POST" action="{{ route('announcements.comment', $announcement) }}" class="mb-4">
                    @csrf
                    <div class="form-group">
                        <textarea name="body" rows="3" class="form-control" placeholder="Share a respectful question or comment..." required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm">Post Comment</button>
                </form>
            @else
                <div class="flash flash-info mb-3">
                    <span>Sign in with your NIT account to participate in announcement discussions.</span>
                    <a href="{{ route('login') }}" class="btn btn-primary btn-sm">Sign In</a>
                </div>
            @endauth

            <div style="display:flex;flex-direction:column;gap:12px;">
                @forelse($announcement->comments as $comment)
                    <div style="padding:14px;border:1px solid var(--border);border-radius:12px;background:var(--canvas);">
                        <div class="flex-between mb-1">
                            <div>
                                <strong style="color:var(--navy-900);font-size:14px;">{{ $comment->user->name }}</strong>
                                @if($comment->user->course)<span class="badge badge-navy mono" style="margin-left:6px;">{{ $comment->user->course->code }}</span>@endif
                            </div>
                            <span class="text-xs muted">{{ $comment->created_at->diffForHumans() }}</span>
                        </div>
                        <p style="margin:0;font-size:14px;color:var(--ink-700);">{{ $comment->body }}</p>
                    </div>
                @empty
                    <p class="text-sm muted" style="margin:0;">No comments yet. Start the conversation.</p>
                @endforelse
            </div>
        </div>
    </div>
</section>
@endsection
