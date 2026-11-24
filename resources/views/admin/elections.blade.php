@extends('layouts.admin')

@section('title', 'Elections & Early Termination — SONIT Admin')
@section('page_title', 'Electoral Commission Controls & Candidate Vetting')

@section('content')
@if($activeElection)
    <div class="card card-accent-red mb-4">
        <div class="flex-between flex-wrap mb-3">
            <div>
                <span class="kicker">Active Election Cycle</span>
                <h2 style="margin:0;font-size:20px;font-weight:800;color:var(--nit-black);">{{ $activeElection->title }} ({{ $activeElection->academic_year }})</h2>
                @if($activeElection->isEnded())
                    <div class="text-xs mt-1" style="color:var(--nit-red-dark);font-weight:700;">
                        ENDED EARLY BY ADMIN on {{ $activeElection->ended_at->format('d M Y, H:i') }} (Countdown overridden — voting permanently closed).
                    </div>
                @elseif($activeElection->voting_ends_at)
                    <div class="text-xs muted mono mt-1">
                        Scheduled countdown end: {{ $activeElection->voting_ends_at->format('d M Y, H:i') }} (Admin can end immediately at any time)
                    </div>
                @endif
            </div>

            <div class="flex-center flex-wrap" style="gap:8px;">
                <a href="{{ route('elections.results') }}" class="btn btn-outline btn-sm">View Live Tally</a>

                @if(!$activeElection->isEnded())
                    <form method="POST" action="{{ route('admin.elections.end', $activeElection) }}" style="margin:0;" onsubmit="return confirm('END ELECTION IMMEDIATELY? This stops all voting right now even if the countdown timer is still running, and publishes official results.');">
                        @csrf
                        <button type="submit" class="btn btn-danger btn-sm">End Election Now (Stop Countdown)</button>
                    </form>
                @else
                    <form method="POST" action="{{ route('admin.elections.reopen', $activeElection) }}" style="margin:0;" onsubmit="return confirm('Reopen this election for voting?');">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-sm">Reopen Election</button>
                    </form>
                @endif
            </div>
        </div>

        <form method="POST" action="{{ route('admin.elections.flags', $activeElection) }}" class="flex-between flex-wrap" style="gap:14px;border-top:1px solid var(--border);padding-top:12px;">
            @csrf
            <div class="flex-center flex-wrap" style="gap:16px;">
                <label class="flex-center text-sm"><input type="checkbox" name="is_enabled" value="1" @checked($activeElection->is_enabled)> Election Enabled</label>
                <label class="flex-center text-sm"><input type="checkbox" name="applications_open" value="1" @checked($activeElection->applications_open)> Nominations Open</label>
                <label class="flex-center text-sm"><input type="checkbox" name="voting_open" value="1" @checked($activeElection->voting_open)> Voting Open</label>
                <label class="flex-center text-sm"><input type="checkbox" name="results_published" value="1" @checked($activeElection->results_published)> Publish Results Publicly</label>
            </div>
            <button type="submit" class="btn btn-black btn-sm">Save Flags</button>
        </form>
    </div>
@endif

<div class="card mb-4">
    <h2 style="margin:0 0 12px;font-size:17px;font-weight:800;color:var(--nit-black);">Candidate Vetting Queue ({{ $candidates->count() }})</h2>
    <div class="table-wrap">
        <table class="sonit-table">
            <thead>
                <tr>
                    <th>Candidate</th>
                    <th>Position & Course</th>
                    <th>Motto</th>
                    <th>Votes</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($candidates as $c)
                    <tr>
                        <td>
                            <strong>{{ $c->fullname }}</strong>
                            <div class="text-xs mono muted">{{ $c->reg_no }}</div>
                        </td>
                        <td>
                            <span class="badge badge-navy">{{ $c->position }}</span>
                            @if($c->course)<span class="badge badge-slate mono">{{ $c->course->code }}</span>@endif
                        </td>
                        <td class="text-xs">{{ $c->motto }}</td>
                        <td class="mono"><strong>{{ $c->votes_count }}</strong></td>
                        <td>
                            <span class="badge {{ $c->status === 'approved' ? 'badge-emerald' : ($c->status === 'declined' ? 'badge-crimson' : 'badge-amber') }}">
                                {{ ucfirst($c->status) }}
                            </span>
                        </td>
                        <td>
                            <form method="POST" action="{{ route('admin.candidates.status', $c) }}" class="flex-center" style="gap:6px;">
                                @csrf
                                <button type="submit" name="status" value="approved" class="btn btn-emerald btn-sm">Approve</button>
                                <button type="submit" name="status" value="declined" class="btn btn-danger btn-sm">Decline</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <h2 style="margin:0 0 12px;font-size:17px;font-weight:800;color:var(--nit-black);">Create New Election Cycle</h2>
    <form method="POST" action="{{ route('admin.elections.store') }}">
        @csrf
        <div class="grid grid-3">
            <div class="form-group">
                <label class="form-label">Election Title</label>
                <input type="text" name="title" class="form-control" placeholder="SONIT General Elections 2027/2028" required>
            </div>
            <div class="form-group">
                <label class="form-label">Academic Year</label>
                <input type="text" name="academic_year" class="form-control" placeholder="2027/2028" required>
            </div>
            <div class="form-group">
                <label class="form-label">Voting Ends At</label>
                <input type="datetime-local" name="voting_ends_at" class="form-control">
            </div>
        </div>
        <button type="submit" class="btn btn-primary btn-sm">Create Election Cycle</button>
    </form>
</div>
@endsection
