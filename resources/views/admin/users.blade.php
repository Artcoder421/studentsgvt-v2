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
        Note: You can edit student/user profiles, update or remove profile photos, and delete accounts. Only <strong>Super Administrators</strong> can assign or revoke system roles.
    </div>
@endif

<div class="table-wrap">
    <table class="sonit-table">
        <thead>
            <tr>
                <th>Student / Official</th>
                <th>Reg No & Course</th>
                <th>Role & Status</th>
                <th>Edit / Photo / Delete</th>
                @if(auth()->user()->isSuperAdmin())
                    <th>Assign Role (Super Admin)</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach($users as $u)
                <tr>
                    <td>
                        <div class="flex-center" style="gap:10px;">
                            @if($u->avatar)
                                <img src="{{ asset($u->avatar) }}" alt="{{ $u->name }}" class="avatar-tile">
                            @else
                                <div class="avatar-tile avatar-fallback {{ $u->isAdmin() ? 'is-admin' : ($u->isClassRep() ? 'is-cr' : '') }}">
                                    {{ strtoupper(substr($u->name, 0, 2)) }}
                                </div>
                            @endif
                            <div>
                                <strong style="color:var(--navy-900);">{{ $u->name }}</strong>
                                @if($u->sonit_title)
                                    <div><span class="badge badge-crimson" style="font-size:10px;">{{ $u->sonit_title }}</span></div>
                                @endif
                                <div class="text-xs muted">{{ $u->email }}</div>
                                @if($u->phone)<div class="text-xs mono muted">{{ $u->phone }}</div>@endif
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="mono text-xs" style="font-weight:700;">{{ $u->reg_no ?? '—' }}</div>
                        <div class="mt-1">
                            <span class="badge badge-navy mono">{{ $u->course?->code ?? 'N/A' }}</span>
                            @if($u->academic_level)<span class="text-xs muted">{{ $u->academic_level }}</span>@endif
                        </div>
                    </td>
                    <td>
                        <span class="badge {{ $u->role === 'super_admin' ? 'badge-crimson' : ($u->role === 'admin' ? 'badge-gold' : ($u->role === 'class_rep' ? 'badge-emerald' : 'badge-slate')) }}">
                            {{ $u->roleLabel() }}
                        </span>
                        <div class="mt-1">
                            <span class="badge {{ $u->is_active ? 'badge-emerald' : 'badge-crimson' }}">{{ $u->is_active ? 'Active' : 'Suspended' }}</span>
                        </div>
                    </td>
                    <td style="min-width:250px;">
                        <div class="flex-center flex-wrap" style="gap:6px;">
                            @if($u->avatar)
                                <form method="POST" action="{{ route('admin.users.remove-avatar', $u) }}" style="margin:0;" onsubmit="return confirm('Remove profile photo for {{ $u->name }}?');">
                                    @csrf
                                    <button type="submit" class="btn btn-outline btn-sm" style="padding:4px 8px;font-size:11px;color:var(--nit-red-dark);border-color:var(--nit-red);">Remove Photo</button>
                                </form>
                            @endif

                            @if((int)$u->id !== (int)auth()->id() && (!$u->isSuperAdmin() || auth()->user()->isSuperAdmin()))
                                <form method="POST" action="{{ route('admin.users.delete', $u) }}" style="margin:0;" onsubmit="return confirm('Delete user account {{ $u->name }} permanently?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" style="padding:4px 8px;font-size:11px;">Delete</button>
                                </form>
                            @endif
                        </div>

                        <details class="admin-details mt-1">
                            <summary>Edit Details & Photo</summary>
                            <form method="POST" action="{{ route('admin.users.update', $u) }}" enctype="multipart/form-data" class="admin-details-body">
                                @csrf
                                @method('PUT')
                                <div class="grid grid-2" style="gap:8px;">
                                    <div class="form-group" style="margin:0;">
                                        <label class="form-label">Full Name</label>
                                        <input type="text" name="name" value="{{ $u->name }}" class="form-control" required>
                                    </div>
                                    <div class="form-group" style="margin:0;">
                                        <label class="form-label">Reg No</label>
                                        <input type="text" name="reg_no" value="{{ $u->reg_no }}" class="form-control mono">
                                    </div>
                                    <div class="form-group" style="margin:0;">
                                        <label class="form-label">Email</label>
                                        <input type="email" name="email" value="{{ $u->email }}" class="form-control" required>
                                    </div>
                                    <div class="form-group" style="margin:0;">
                                        <label class="form-label">Phone</label>
                                        <input type="text" name="phone" value="{{ $u->phone }}" class="form-control mono">
                                    </div>
                                    <div class="form-group" style="margin:0;">
                                        <label class="form-label">Course</label>
                                        <select name="course_id" class="form-select">
                                            <option value="">No Course</option>
                                            @foreach($courses as $c)
                                                <option value="{{ $c->id }}" @selected($u->course_id === $c->id)>{{ $c->code }} — {{ $c->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group" style="margin:0;">
                                        <label class="form-label">Academic Level</label>
                                        <input type="text" name="academic_level" value="{{ $u->academic_level }}" class="form-control" placeholder="e.g. Year 2">
                                    </div>
                                    <div class="form-group" style="margin:0;">
                                        <label class="form-label">SONIT Official Title</label>
                                        <input type="text" name="sonit_title" value="{{ $u->sonit_title }}" class="form-control" placeholder="e.g. SONIT President">
                                    </div>
                                    <div class="form-group" style="margin:0;">
                                        <label class="form-label">Account Status</label>
                                        <select name="is_active" class="form-select">
                                            <option value="1" @selected($u->is_active)>Active</option>
                                            <option value="0" @selected(!$u->is_active)>Suspended</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group mt-2" style="margin-bottom:8px;">
                                    <label class="form-label">Upload / Replace Profile Photo</label>
                                    <input type="file" name="avatar" accept="image/*" class="form-control">
                                </div>
                                @if($u->avatar)
                                    <label class="flex-center text-xs mb-2" style="gap:6px;color:var(--nit-red-dark);font-weight:700;cursor:pointer;">
                                        <input type="checkbox" name="remove_avatar" value="1"> Remove current profile photo
                                    </label>
                                @endif
                                <button type="submit" class="btn btn-primary btn-sm">Save User Changes</button>
                            </form>
                        </details>
                    </td>
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
                                <input type="hidden" name="is_active" value="{{ $u->is_active ? 1 : 0 }}">
                                <button type="submit" class="btn btn-black btn-sm">Set Role</button>
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
