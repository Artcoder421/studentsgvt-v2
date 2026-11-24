@extends('layouts.admin')

@section('title', 'Announcements & Urgent Alerts — SONIT Admin')
@section('page_title', 'Announcements & Urgent Student Broadcasts')

@section('content')
<div class="grid grid-2" style="align-items:start;">
    <div class="card">
        <h2 style="margin:0 0 14px;font-size:18px;font-weight:800;color:var(--navy-900);">Publish New Announcement</h2>
        <form method="POST" action="{{ route('admin.announcements.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label class="form-label">Announcement Title</label>
                <input type="text" name="title" class="form-control" required placeholder="e.g. URGENT: HESLB Verification Window">
            </div>
            <div class="grid grid-2">
                <div class="form-group">
                    <label class="form-label">Category</label>
                    <select name="category" class="form-select" required>
                        @foreach(['general','academic','welfare','election','sports','charity','urgent'] as $c)
                            <option value="{{ $c }}">{{ ucfirst($c) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Cover Photo (Optional)</label>
                    <input type="file" name="cover_image" accept="image/*" class="form-control">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Short Excerpt</label>
                <input type="text" name="excerpt" class="form-control" placeholder="One-sentence summary shown on cards...">
            </div>
            <div class="form-group">
                <label class="form-label">Full Circular Content</label>
                <textarea name="content" rows="5" class="form-control" required></textarea>
            </div>
            <div class="flex-center flex-wrap mb-3" style="gap:18px;">
                <label class="flex-center text-sm" style="cursor:pointer;">
                    <input type="checkbox" name="is_urgent" value="1"> Mark as <strong>Urgent Alert</strong> (Shows top red ribbon & increments urgent badge)
                </label>
                <label class="flex-center text-sm" style="cursor:pointer;">
                    <input type="checkbox" name="is_pinned" value="1"> Pin to Top
                </label>
            </div>
            <button type="submit" class="btn btn-primary">Publish Broadcast</button>
        </form>
    </div>

    <div class="card">
        <h2 style="margin:0 0 14px;font-size:18px;font-weight:800;color:var(--navy-900);">Published Announcements</h2>
        @foreach($announcements as $a)
            <div style="padding:12px 0;border-bottom:1px solid var(--border);">
                <div class="flex-between mb-1">
                    <div class="flex-center" style="gap:6px;">
                        @if($a->is_urgent)<span class="badge badge-crimson">Urgent</span>@endif
                        <span class="badge badge-navy">{{ ucfirst($a->category) }}</span>
                    </div>
                    <form method="POST" action="{{ route('admin.announcements.delete', $a) }}" style="margin:0;" onsubmit="return confirm('Delete announcement?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm" style="padding:4px 10px;font-size:11.5px;">Delete</button>
                    </form>
                </div>
                <a href="{{ route('announcements.show', $a) }}" style="font-weight:800;color:var(--navy-900);">{{ $a->title }}</a>
                <div class="text-xs muted">{{ optional($a->published_at)->format('d M Y H:i') }} · {{ $a->comments_count }} comments</div>
            </div>
        @endforeach
        <div class="mt-3">{{ $announcements->links() }}</div>
    </div>
</div>
@endsection
