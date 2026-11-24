@extends('layouts.admin')

@section('title', 'Countdown Notices & Ads — SONIT Admin')
@section('page_title', 'Top Countdown Reminders (Charity, Tours, Deadlines)')

@section('content')
<div class="grid grid-2" style="align-items:start;">
    <div class="card card-accent-blue">
        <h2 style="margin:0 0 12px;font-size:17px;font-weight:800;color:var(--nit-black);">Create Countdown Notice Banner</h2>
        <form method="POST" action="{{ route('admin.timers.store') }}">
            @csrf
            <div class="form-group">
                <label class="form-label">Notice Title (Shown at top of portal)</label>
                <input type="text" name="title" class="form-control" placeholder="e.g. SONIT Charity Tour to Mabibo Orphanage" required>
            </div>
            <div class="grid grid-2">
                <div class="form-group">
                    <label class="form-label">Category</label>
                    <select name="category" class="form-select" required>
                        <option value="charity">Charity / Tour</option>
                        <option value="election">Election</option>
                        <option value="event">Major Event</option>
                        <option value="deadline">Academic Deadline</option>
                        <option value="urgent">Urgent Notice</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Countdown Ends At</label>
                    <input type="datetime-local" name="ends_at" class="form-control" required>
                </div>
            </div>
            <div class="grid grid-2">
                <div class="form-group">
                    <label class="form-label">When (Event Date & Time)</label>
                    <input type="text" name="event_when" class="form-control" placeholder="e.g. Sat 12 Dec · 08:30 AM">
                </div>
                <div class="form-group">
                    <label class="form-label">Where (Location / Destination)</label>
                    <input type="text" name="event_where" class="form-control" placeholder="e.g. Mabibo Children's Centre">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">What We Plan To Do</label>
                <textarea name="planned_activities" rows="2" class="form-control" placeholder="Describe planned activities..."></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Contribution Account Number / Lipa Namba</label>
                <input type="text" name="account_number" class="form-control mono" placeholder="e.g. CRDB: 0150442198800 | M-Pesa Lipa: 5589021">
            </div>
            <div class="form-group">
                <label class="form-label">Coordinator Contacts</label>
                <input type="text" name="contact_info" class="form-control" placeholder="e.g. Hon. Robert Nziku: +255 788 112 404">
            </div>
            <div class="grid grid-3">
                <div class="form-group"><label class="form-label">CTA Label</label><input type="text" name="cta_label" class="form-control" value="View Details"></div>
                <div class="form-group"><label class="form-label">CTA URL</label><input type="text" name="cta_url" class="form-control" value="/charity"></div>
                <div class="form-group"><label class="form-label">Priority</label><input type="number" name="priority" class="form-control" value="10"></div>
            </div>
            <button type="submit" class="btn btn-primary">Publish Countdown Notice</button>
        </form>
    </div>

    <div class="card">
        <h2 style="margin:0 0 12px;font-size:17px;font-weight:800;color:var(--nit-black);">Active & Scheduled Countdown Notices</h2>
        @forelse($timers as $t)
            <div style="padding:12px;border:1px solid var(--border);margin-bottom:10px;">
                <div class="flex-between mb-1">
                    <span class="badge badge-black">{{ strtoupper($t->category) }}</span>
                    <span class="badge {{ $t->is_active && $t->ends_at->isFuture() ? 'badge-emerald' : 'badge-slate' }}">
                        {{ $t->is_active && $t->ends_at->isFuture() ? 'Live' : 'Paused / Ended' }}
                    </span>
                </div>
                <strong style="color:var(--nit-black);display:block;">{{ $t->title }}</strong>
                @if($t->account_number)<div class="text-xs mono mt-1" style="color:var(--nit-red-dark);">Account: {{ $t->account_number }}</div>@endif
                @if($t->event_when)<div class="text-xs muted">When: {{ $t->event_when }} · Where: {{ $t->event_where }}</div>@endif
                <div class="text-xs muted mono mt-1">Expires: {{ $t->ends_at->format('d M Y H:i') }}</div>
                <div class="flex-center mt-2">
                    <form method="POST" action="{{ route('admin.timers.toggle', $t) }}" style="margin:0;">
                        @csrf
                        <button type="submit" class="btn btn-outline btn-sm">{{ $t->is_active ? 'Pause' : 'Activate' }}</button>
                    </form>
                    <form method="POST" action="{{ route('admin.timers.delete', $t) }}" style="margin:0;" onsubmit="return confirm('Delete notice?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                    </form>
                </div>
            </div>
        @empty
            <p class="text-sm muted">No countdown notices yet.</p>
        @endforelse
    </div>
</div>
@endsection
