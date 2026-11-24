@extends('layouts.app')

@section('title', 'Ticket ' . $ticket->ticket_number . ' — SONIT Help Desk')

@section('content')
<div class="page-banner">
    <div class="container page-banner-inner">
        <div>
            <div class="flex-center mb-2">
                <span class="badge badge-gold mono">{{ $ticket->ticket_number }}</span>
                <span class="badge badge-navy">{{ $ticket->categoryLabel() }}</span>
                <span class="badge {{ $ticket->status === 'resolved' ? 'badge-emerald' : 'badge-amber' }}">{{ ucfirst(str_replace('_', ' ', $ticket->status)) }}</span>
            </div>
            <h1>{{ $ticket->subject }}</h1>
            <p>Opened {{ $ticket->created_at->format('d M Y, H:i') }} · Submitted by {{ $ticket->is_anonymous ? 'Anonymous Student (Protected)' : $ticket->user->name }}</p>
        </div>
        <a href="{{ route('help-desk.index') }}" class="btn btn-ghost-light">Back to Help Desk</a>
    </div>
</div>

<section class="section section-tight">
    <div class="container" style="max-width:860px;">
        <div class="card mb-4">
            <span class="kicker">Initial Case Description</span>
            <p style="font-size:15px;color:var(--ink-900);white-space:pre-line;margin:0;">{{ $ticket->description }}</p>
        </div>

        <div class="card mb-4">
            <h2 style="margin:0 0 16px;font-size:19px;font-weight:800;color:var(--navy-900);">Case Conversation ({{ $ticket->replies->count() }})</h2>
            <div style="display:flex;flex-direction:column;gap:12px;">
                @forelse($ticket->replies as $reply)
                    <div style="padding:14px 16px;border-radius:12px;border:1px solid var(--border);background:{{ $reply->is_staff_reply ? 'var(--blue-100)' : 'var(--canvas)' }};">
                        <div class="flex-between mb-1">
                            <div class="flex-center">
                                <strong style="color:var(--navy-900);">{{ $reply->is_staff_reply ? $reply->user->name . ' (SONIT Staff)' : ($ticket->is_anonymous ? 'Student' : $reply->user->name) }}</strong>
                                @if($reply->is_staff_reply)<span class="badge badge-gold">Official Response</span>@endif
                            </div>
                            <span class="text-xs muted">{{ $reply->created_at->diffForHumans() }}</span>
                        </div>
                        <p style="margin:0;font-size:14.5px;color:var(--ink-900);white-space:pre-line;">{{ $reply->message }}</p>
                    </div>
                @empty
                    <p class="text-sm muted">Awaiting initial review from the SONIT Help Desk Officer.</p>
                @endforelse
            </div>
        </div>

        <div class="card">
            <h3 style="margin:0 0 12px;font-size:17px;font-weight:800;color:var(--navy-900);">Add Follow-Up Reply</h3>
            <form method="POST" action="{{ route('help-desk.reply', $ticket) }}">
                @csrf
                @if(auth()->user()->isAdmin())
                    <div class="form-group">
                        <label class="form-label">Update Ticket Status</label>
                        <select name="status" class="form-select">
                            @foreach(['open','in_progress','resolved','closed'] as $st)
                                <option value="{{ $st }}" @selected($ticket->status === $st)>{{ ucfirst(str_replace('_', ' ', $st)) }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="form-group">
                    <textarea name="message" rows="3" class="form-control" placeholder="Write your update or official resolution..." required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Send Reply</button>
            </form>
        </div>
    </div>
</section>
@endsection
