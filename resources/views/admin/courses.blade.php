@extends('layouts.admin')

@section('title', 'NIT Courses & CR Seats — SONIT Admin')
@section('page_title', 'NIT Courses, Default Communities & CR Seat Governance')

@section('content')
<div class="card mb-4">
    <h2 style="margin:0 0 14px;font-size:18px;font-weight:800;color:var(--navy-900);">Add New NIT Course (Auto-Creates Default Course Community)</h2>
    <form method="POST" action="{{ route('admin.courses.store') }}">
        @csrf
        <div class="grid grid-4" style="gap:12px;">
            <div class="form-group">
                <label class="form-label">Course Code</label>
                <input type="text" name="code" class="form-control mono" placeholder="e.g. BRME" required>
            </div>
            <div class="form-group" style="grid-column:span 2;">
                <label class="form-label">Full Programme Name</label>
                <input type="text" name="name" class="form-control" placeholder="e.g. Bachelor's Degree in Railway Mechanical Engineering" required>
            </div>
            <div class="form-group">
                <label class="form-label">Level</label>
                <select name="level" class="form-select" required>
                    <option value="bachelor">Bachelor's Degree</option>
                    <option value="diploma">Ordinary Diploma</option>
                    <option value="certificate">Technician Certificate</option>
                    <option value="masters">Master's Degree</option>
                </select>
            </div>
        </div>
        <div class="grid grid-3" style="gap:12px;">
            <div class="form-group">
                <label class="form-label">Faculty</label>
                <input type="text" name="faculty" class="form-control" value="Faculty of Logistics & Business Studies" required>
            </div>
            <div class="form-group">
                <label class="form-label">Duration (Years)</label>
                <input type="number" name="duration_years" class="form-control" value="3" min="1" max="6" required>
            </div>
            <div class="form-group">
                <label class="form-label">Short Description</label>
                <input type="text" name="description" class="form-control" placeholder="Programme focus...">
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Add Course & Provision Default Community</button>
    </form>
</div>

<div class="table-wrap">
    <table class="sonit-table">
        <thead>
            <tr>
                <th>Code</th>
                <th>Programme & Faculty</th>
                <th>Default Community</th>
                <th>CR Seat Status</th>
                <th>Assign / Vacate CR Seat</th>
            </tr>
        </thead>
        <tbody>
            @foreach($courses as $course)
                @php $comm = $course->defaultCommunity; @endphp
                <tr>
                    <td class="mono"><strong>{{ $course->code }}</strong></td>
                    <td>
                        <strong style="color:var(--navy-900);">{{ $course->name }}</strong>
                        <div class="text-xs muted">{{ $course->faculty }} · {{ $course->levelLabel() }}</div>
                    </td>
                    <td>
                        @if($comm)
                            <a href="{{ route('communities.show', $comm) }}" style="font-weight:700;">{{ $comm->name }}</a>
                            <div class="text-xs muted">{{ $comm->members_count }} members</div>
                        @else
                            <span class="badge badge-crimson">Missing</span>
                        @endif
                    </td>
                    <td>
                        @if($comm && $comm->cr)
                            <span class="badge badge-emerald">{{ $comm->cr->name }}</span>
                        @else
                            <span class="badge badge-amber">Vacant (Admin Controlled)</span>
                        @endif
                    </td>
                    <td>
                        <form method="POST" action="{{ route('admin.courses.assign-cr', $course) }}" class="flex-center" style="gap:6px;">
                            @csrf
                            <select name="cr_user_id" class="form-select" style="padding:6px 10px;font-size:12px;max-width:210px;">
                                <option value="">-- Leave Vacant (Admin Managed) --</option>
                                @foreach($eligibleCrs as $u)
                                    <option value="{{ $u->id }}" @selected($comm && $comm->cr_user_id === $u->id)>
                                        {{ $u->name }} ({{ $u->course?->code ?? 'N/A' }})
                                    </option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn btn-primary btn-sm">Update</button>
                        </form>

                        <div class="flex-between mt-2" style="gap:6px;">
                            <details class="admin-details" style="flex:1;">
                                <summary>Edit Course</summary>
                                <form method="POST" action="{{ route('admin.courses.update', $course) }}" class="admin-details-body">
                                    @csrf
                                    @method('PUT')
                                    <div class="grid grid-2" style="gap:8px;">
                                        <div class="form-group" style="margin:0;">
                                            <label class="form-label">Code</label>
                                            <input type="text" name="code" value="{{ $course->code }}" class="form-control mono" required>
                                        </div>
                                        <div class="form-group" style="margin:0;">
                                            <label class="form-label">Level</label>
                                            <select name="level" class="form-select" required>
                                                <option value="bachelor" @selected($course->level === 'bachelor')>Bachelor's Degree</option>
                                                <option value="diploma" @selected($course->level === 'diploma')>Ordinary Diploma</option>
                                                <option value="certificate" @selected($course->level === 'certificate')>Technician Certificate</option>
                                                <option value="masters" @selected($course->level === 'masters')>Master's Degree</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group mt-2" style="margin-bottom:8px;">
                                        <label class="form-label">Programme Name</label>
                                        <input type="text" name="name" value="{{ $course->name }}" class="form-control" required>
                                    </div>
                                    <div class="grid grid-2" style="gap:8px;">
                                        <div class="form-group" style="margin:0;">
                                            <label class="form-label">Faculty</label>
                                            <input type="text" name="faculty" value="{{ $course->faculty }}" class="form-control" required>
                                        </div>
                                        <div class="form-group" style="margin:0;">
                                            <label class="form-label">Duration (Years)</label>
                                            <input type="number" name="duration_years" value="{{ $course->duration_years }}" min="1" max="6" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="form-group mt-2" style="margin-bottom:8px;">
                                        <label class="form-label">Description</label>
                                        <input type="text" name="description" value="{{ $course->description }}" class="form-control">
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-sm">Save Course</button>
                                </form>
                            </details>

                            <form method="POST" action="{{ route('admin.courses.delete', $course) }}" style="margin:0;" onsubmit="return confirm('Delete course {{ $course->code }} and its communities?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" style="padding:4px 8px;font-size:11px;">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
