@extends('layouts.admin')

@section('title', 'Countdown Timer Banners — SONIT Admin')
@section('page_title', 'Top-of-Homepage Countdown Timer Announcements')

@section('content')
<div class="grid grid-2" style="align-items:start;">
    <div class="card">
        <h2 style="margin:0 0 14px;font-size:18px;font-weight:800;color:var(--navy-900);">Launch New Countdown Banner</h2>
        <form method="POST" action="{{ route('admin.timers.store') }}">
            @csrf
            <div class="form-group">
                <label class="form-label">Headline (Shown at very top of Homepage)</label>
                <input type="text" name="title" class="form-control" placeholder="e.g. Charity Contribution Drive — Closing Soon!" required>
            </div>
            <div class="form-group">
                <label class="form-label">Subtitle (Optional)</label>
                <input type="text" name="subtitle" class="form-control" placeholder="Short motivating description...">
            </div>
            <div class="grid grid-2">
                <div class="form-group">
                    <label class="form-label">Category</label>
                    <select name="category" class="form-select" required>
                        <option value="charity">Charity Drive</option>
                        <option value="election">Election Countdown</option>
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
                    <label class="form-label">CTA Button Text</label>
                    <input type="text" name="cta_label" class="form-control" value="Contribute Now">
                </div>
                <div class="form-group">
                    <label class="form-label">CTA Target URL</label>
                    <input type="text" name="cta_url" class="form-control" value="/charity">
                </div>
            </div>
            <div class="grid grid-3">
                <div class="form-group">
                    <label class="form-label">Target TZS (Optional)</label>
                    <input type="number" name="target_amount" class="form-control mono" placeholder="5000000">
                </div>
                <div class="form-group">
                    <label class="form-label">Raised TZS</label>
                    <input type="number" name="raised_amount" class="form-control mono" value="0">
                </div>
                <div class="form-group">
                    <label class="form-label">Priority</label>
                    <input type="number" name="priority" class="form-control" value="10">
                </div>
            </div>
            <button type="submit" class="btn btn-gold">Publish Top Countdown Banner</button>
        </form>
    </div>

    <div class="card">
        <h2 style="margin:0 0 14px;font-size:18px;font-weight:800;color:var(--navy-900);">Active & Scheduled Timers</h2>
        @forelse($timers as $t)
            <div style="padding:14px;border:1px solid var(--border);border-radius:12px;margin-bottom:12px;">
                <div class="flex-between mb-1">
                    <span class="badge badge-navy">{{ strtoupper($t->category) }}</span>
                    <span class="badge {{ $t->is_active && $t->ends_at->isFuture() ? 'badge-emerald' : 'badge-slate' }}">
                        {{ $t->is_active && $t->ends_at->isFuture() ? 'Live on Homepage' : 'Inactive / Expired' }}
                    </span>
                </div>
                <strong style="color:var(--navy-900);display:block;">{{ $t->title }}</strong>
                <div class="text-xs muted mono mt-1">Ends: {{ $t->ends_at->format('d M Y H:i') }} · Priority {{ $t->priority }}</div>
                <div class="flex-center mt-2">
                    <form method="POST" action="{{ route('admin.timers.toggle', $t) }}" style="margin:0;">
                        @csrf
                        <button type="submit" class="btn btn-outline btn-sm">{{ $t->is_active ? 'Pause' : 'Activate' }}</button>
                    </form>
                    <form method="POST" action="{{ route('admin.timers.delete', $t) }}" style="margin:0;" onsubmit="return confirm('Delete this countdown timer?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                    </form>
                </div>
            </div>
        @empty
            <p class="text-sm muted">No timer announcements configured yet.</p>
        @endforelse
    </div>
</div>
@endsection
