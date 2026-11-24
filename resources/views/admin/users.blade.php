@extends('layouts.admin')

@section('title', 'Users & RBAC Governance — SONIT Admin')
@section('page_title', 'Users & Role-Based Access Control (RBAC)')

@section('content')
<div class="card mb-4">
    <form method="GET" action="{{ route('admin.users') }}" class="grid grid-3" style="gap:12px;align-items:end;">
        <div class="form-group" style="margin:0;">
            <label class="form-label">Search Name, Reg No or Email</label>
            <input type="text" name="q" value="{{ $search }}" class="form-control" placeholder="e.g. NIT/BLTM/2022/4805">
        </div>
        <div class="form-group" style="margin:0;">
            <label class="form-label">Filter Role</label>
            <select name="role" class="form-select">
                <option value="">All Roles</option>
                @foreach(\App\Models\User::ROLES as $rKey => $rLbl)
                    <option value="{{ $rKey }}" @selected($roleFilter === $rKey)>{{ $rLbl }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex-center">
            <button type="submit" class="btn btn-primary" style="flex:1;">Search</button>
            <a href="{{ route('admin.users') }}" class="btn btn-outline">Reset</a>
        </div>
    </form>
</div>

@if(!auth()->user()->isSuperAdmin())
    <div class="flash flash-info mb-4">
        Read-only view: Only <strong>Super Administrators</strong> hold privilege to assign or revoke user roles.
    </div>
@endif

<div class="table-wrap">
    <table class="sonit-table">
        <thead>
            <tr>
                <th>Student / Official</th>
                <th>Reg No</th>
                <th>Course</th>
                <th>Current Role</th>
                <th>Status</th>
                @if(auth()->user()->isSuperAdmin())
                    <th>Assign Role & Course (Super Admin)</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach($users as $u)
                <tr>
                    <td>
                        <strong style="color:var(--navy-900);">{{ $u->name }}</strong>
                        <div class="text-xs muted">{{ $u->email }}</div>
                    </td>
                    <td class="mono text-xs">{{ $u->reg_no ?? '—' }}</td>
                    <td><span class="badge badge-navy mono">{{ $u->course?->code ?? 'N/A' }}</span></td>
                    <td>
                        <span class="badge {{ $u->role === 'super_admin' ? 'badge-crimson' : ($u->role === 'admin' ? 'badge-gold' : ($u->role === 'class_rep' ? 'badge-emerald' : 'badge-slate')) }}">
                            {{ $u->roleLabel() }}
                        </span>
                    </td>
                    <td><span class="badge {{ $u->is_active ? 'badge-emerald' : 'badge-crimson' }}">{{ $u->is_active ? 'Active' : 'Suspended' }}</span></td>
                    @if(auth()->user()->isSuperAdmin())
                        <td>
                            <form method="POST" action="{{ route('admin.users.role', $u) }}" class="flex-center flex-wrap" style="gap:6px;">
                                @csrf
                                <select name="role" class="form-select" style="width:auto;padding:6px 10px;font-size:12px;">
                                    @foreach(\App\Models\User::ROLES as $rKey => $rLbl)
                                        <option value="{{ $rKey }}" @selected($u->role === $rKey)>{{ $rLbl }}</option>
                                    @endforeach
                                </select>
                                <select name="course_id" class="form-select" style="width:auto;padding:6px 10px;font-size:12px;">
                                    <option value="">No Course</option>
                                    @foreach($courses as $c)
                                        <option value="{{ $c->id }}" @selected($u->course_id === $c->id)>{{ $c->code }}</option>
                                    @endforeach
                                </select>
                                <input type="hidden" name="is_active" value="1">
                                <button type="submit" class="btn btn-primary btn-sm">Save</button>
                            </form>
                        </td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="mt-3">{{ $users->links() }}</div>
@endsection
