@extends('layouts.admin')

@section('title', 'Activity Audit Logs & Visitor Analytics — SONIT Admin')
@section('page_title', 'System Activity Logs & Visitor Analytics')

@section('content')
<div class="grid grid-4 mb-4">
    <div class="card card-accent-blue" style="padding:14px;">
        <span class="kicker">Total Page Visits</span>
        <strong class="mono" style="font-size:26px;color:var(--nit-black);">{{ number_format($visitorStats['total_visits']) }}</strong>
        <div class="text-xs muted">{{ number_format($visitorStats['today_visits']) }} visits today</div>
    </div>
    <div class="card card-accent-black" style="padding:14px;">
        <span class="kicker">Unique Visitors (IP)</span>
        <strong class="mono" style="font-size:26px;color:var(--nit-black);">{{ number_format($visitorStats['unique_ips']) }}</strong>
        <div class="text-xs muted">Distinct visitor addresses</div>
    </div>
    <div class="card card-accent-red" style="padding:14px;">
        <span class="kicker">Guest vs Logged-In</span>
        <strong class="mono" style="font-size:20px;color:var(--nit-black);">{{ number_format($visitorStats['guest_visits']) }} / {{ number_format($visitorStats['member_visits']) }}</strong>
        <div class="text-xs muted">Guests / Authenticated</div>
    </div>
    <div class="card card-accent-blue" style="padding:14px;">
        <span class="kicker">Recorded Activities</span>
        <strong class="mono" style="font-size:26px;color:var(--nit-blue-dark);">{{ number_format($visitorStats['total_activities']) }}</strong>
        <div class="text-xs muted">Auth, elections, admin & student actions</div>
    </div>
</div>

{{-- Activity Audit Log Table --}}
<div class="card mb-4">
    <div class="flex-between flex-wrap mb-3">
        <h2 style="margin:0;font-size:17px;font-weight:800;color:var(--nit-black);">Activity Audit Trail</h2>
        <form method="GET" action="{{ route('admin.logs') }}" class="flex-center flex-wrap" style="gap:8px;">
            <input type="text" name="q" value="{{ $search }}" class="form-control" style="width:220px;padding:6px 10px;" placeholder="Search user, action...">
            <select name="module" class="form-select" style="width:160px;padding:6px 10px;">
                <option value="">All Modules</option>
                @foreach($modules as $m)
                    <option value="{{ $m }}" @selected($module === $m)>{{ ucfirst($m) }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-primary btn-sm">Filter</button>
        </form>
    </div>

    <div class="table-wrap">
        <table class="sonit-table">
            <thead>
                <tr>
                    <th>Time</th>
                    <th>Actor & Role</th>
                    <th>Module</th>
                    <th>Action</th>
                    <th>Description</th>
                    <th>IP</th>
                </tr>
            </thead>
            <tbody>
                @forelse($activities as $act)
                    <tr>
                        <td class="mono text-xs" style="white-space:nowrap;">{{ $act->created_at->format('d M H:i:s') }}</td>
                        <td>
                            <strong>{{ $act->user_name ?? 'System' }}</strong>
                            <div class="text-xs muted">{{ strtoupper($act->user_role ?? 'guest') }}</div>
                        </td>
                        <td><span class="badge badge-navy">{{ strtoupper($act->module) }}</span></td>
                        <td class="mono text-xs">{{ $act->action }}</td>
                        <td class="text-xs">{{ $act->description }}</td>
                        <td class="mono text-xs muted">{{ $act->ip_address }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="muted" style="text-align:center;">No activities logged yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $activities->links() }}</div>
</div>

{{-- Visitor Traffic Log Table --}}
<div class="card">
    <h2 style="margin:0 0 12px;font-size:17px;font-weight:800;color:var(--nit-black);">Recent Portal Visitors</h2>
    <div class="table-wrap">
        <table class="sonit-table">
            <thead>
                <tr>
                    <th>Visited At</th>
                    <th>Visitor</th>
                    <th>Path</th>
                    <th>Device</th>
                    <th>IP Address</th>
                </tr>
            </thead>
            <tbody>
                @forelse($visitors as $v)
                    <tr>
                        <td class="mono text-xs" style="white-space:nowrap;">{{ $v->visited_at->format('d M H:i:s') }}</td>
                        <td>
                            @if($v->user)
                                <strong>{{ $v->user->name }}</strong> <span class="badge badge-navy mono">{{ $v->user->reg_no ?? $v->user->role }}</span>
                            @else
                                <span class="badge badge-slate">Guest Visitor</span>
                            @endif
                        </td>
                        <td class="mono text-xs">{{ $v->path }}</td>
                        <td><span class="badge badge-black">{{ strtoupper($v->device_type) }}</span></td>
                        <td class="mono text-xs muted">{{ $v->ip_address }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="muted" style="text-align:center;">No visitors logged yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $visitors->links() }}</div>
</div>
@endsection
