@extends('layouts.admin')

@section('title', 'Elections & Contestants — SONIT Admin')
@section('page_title', 'Electoral Commission Controls & Candidate Vetting')

@section('content')
@if($activeElection)
    <div class="card mb-4" style="border-top:3px solid var(--gold-500);">
        <div class="flex-between flex-wrap mb-3">
            <div>
                <span class="kicker">Active Election Cycle</span>
                <h2 style="margin:0;font-size:21px;font-weight:800;color:var(--navy-900);">{{ $activeElection->title }} ({{ $activeElection->academic_year }})</h2>
            </div>
            <a href="{{ route('elections.results') }}" class="btn btn-outline btn-sm">View Live Tally</a>
        </div>

        <form method="POST" action="{{ route('admin.elections.flags', $activeElection) }}" class="flex-between flex-wrap" style="gap:16px;">
            @csrf
            <div class="flex-center flex-wrap" style="gap:18px;">
                <label class="flex-center text-sm"><input type="checkbox" name="is_enabled" value="1" @checked($activeElection->is_enabled)> Election Enabled</label>
                <label class="flex-center text-sm"><input type="checkbox" name="applications_open" value="1" @checked($activeElection->applications_open)> Nominations Open</label>
                <label class="flex-center text-sm"><input type="checkbox" name="voting_open" value="1" @checked($activeElection->voting_open)> Voting Open</label>
                <label class="flex-center text-sm"><input type="checkbox" name="results_published" value="1" @checked($activeElection->results_published)> Publish Results Publicly</label>
            </div>
            <button type="submit" class="btn btn-gold">Save Election Controls</button>
        </form>
    </div>
@endif

<div class="card mb-4">
    <h2 style="margin:0 0 14px;font-size:18px;font-weight:800;color:var(--navy-900);">Candidate Vetting Queue ({{ $candidates->count() }})</h2>
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
    <h2 style="margin:0 0 14px;font-size:18px;font-weight:800;color:var(--navy-900);">Create New Election Cycle</h2>
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
        <button type="submit" class="btn btn-primary">Create Election Cycle</button>
    </form>
</div>
@endsection
