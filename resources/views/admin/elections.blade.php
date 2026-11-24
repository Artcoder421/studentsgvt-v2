@extends('layouts.admin')

@section('title', 'Elections, Contestant Editor & Auto-Promotion — SONIT Admin')
@section('page_title', 'Electoral Commission Controls, Screening & Contestants')

@section('content')
@if($activeElection)
    <div class="card card-accent-red mb-4">
        <div class="flex-between flex-wrap mb-3">
            <div>
                <span class="kicker">Active Election Cycle</span>
                <h2 style="margin:0;font-size:20px;font-weight:800;color:var(--nit-black);">{{ $activeElection->title }} ({{ $activeElection->academic_year }})</h2>
                @if($activeElection->isEnded())
                    <div class="text-xs mt-1" style="color:var(--nit-red-dark);font-weight:700;">
                        ENDED on {{ $activeElection->ended_at->format('d M Y, H:i') }} — Winners automatically promoted to Homepage, Leadership Directory & Student SONIT Titles.
                    </div>
                @elseif($activeElection->voting_ends_at)
                    <div class="text-xs muted mono mt-1">
                        Scheduled countdown end: {{ $activeElection->voting_ends_at->format('d M Y, H:i') }}
                    </div>
                @endif
            </div>

            <div class="flex-center flex-wrap" style="gap:8px;">
                <a href="{{ route('elections.results') }}" class="btn btn-outline btn-sm">Live Tally</a>

                @if(!$activeElection->isVotingActive())
                    <form method="POST" action="{{ route('admin.elections.open', $activeElection) }}" style="margin:0;">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-sm">Open Election Now</button>
                    </form>
                @endif

                @if(!$activeElection->isEnded())
                    <form method="POST" action="{{ route('admin.elections.end', $activeElection) }}" style="margin:0;" onsubmit="return confirm('END ELECTION NOW? Voting stops immediately, results publish, and winning candidates are AUTOMATICALLY promoted to Homepage & SONIT Leadership.');">
                        @csrf
                        <button type="submit" class="btn btn-danger btn-sm">End Election & Auto-Promote Winners</button>
                    </form>
                @else
                    <form method="POST" action="{{ route('admin.elections.reopen', $activeElection) }}" style="margin:0;">
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
                <label class="flex-center text-sm"><input type="checkbox" name="applications_open" value="1" @checked($activeElection->applications_open)> Applications Open</label>
                <label class="flex-center text-sm"><input type="checkbox" name="voting_open" value="1" @checked($activeElection->voting_open)> Voting Open</label>
                <label class="flex-center text-sm"><input type="checkbox" name="results_published" value="1" @checked($activeElection->results_published)> Publish Results</label>
            </div>
            <button type="submit" class="btn btn-black btn-sm">Save Flags</button>
        </form>

        <div class="flex-between flex-wrap mt-2" style="gap:8px;border-top:1px solid var(--border);padding-top:10px;">
            <details class="admin-details" style="flex:1;">
                <summary>Edit Election Title, Year & Countdown End Date</summary>
                <form method="POST" action="{{ route('admin.elections.update', $activeElection) }}" class="admin-details-body">
                    @csrf
                    @method('PUT')
                    <div class="grid grid-3" style="gap:8px;">
                        <div class="form-group" style="margin:0;">
                            <label class="form-label">Election Title</label>
                            <input type="text" name="title" value="{{ $activeElection->title }}" class="form-control" required>
                        </div>
                        <div class="form-group" style="margin:0;">
                            <label class="form-label">Academic Year</label>
                            <input type="text" name="academic_year" value="{{ $activeElection->academic_year }}" class="form-control" required>
                        </div>
                        <div class="form-group" style="margin:0;">
                            <label class="form-label">Voting Ends At</label>
                            <input type="datetime-local" name="voting_ends_at" value="{{ $activeElection->voting_ends_at ? $activeElection->voting_ends_at->format('Y-m-d\TH:i') : '' }}" class="form-control">
                        </div>
                    </div>
                    <div class="form-group mt-2" style="margin-bottom:8px;">
                        <label class="form-label">Description</label>
                        <input type="text" name="description" value="{{ $activeElection->description }}" class="form-control">
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm">Save Election Details</button>
                </form>
            </details>

            <form method="POST" action="{{ route('admin.elections.delete', $activeElection) }}" style="margin:0;" onsubmit="return confirm('Delete election cycle {{ $activeElection->title }} permanently?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm">Delete Election</button>
            </form>
        </div>
    </div>
@endif

{{-- Contestants Management: Edit Name/Details, Verify Fee, Grade Interview, Delete --}}
<div class="card mb-4">
    <h2 style="margin:0 0 12px;font-size:17px;font-weight:800;color:var(--nit-black);">Contestants, Fee Verification & Interview Screening ({{ $candidates->count() }})</h2>
    <p class="text-xs muted mb-3">Edit any contestant's name, contact details, photo, fee payment status, or interview result. Setting Interview to <strong>Passed</strong> places them on the ballot; setting <strong>Failed</strong> displays <em>"Did Not Pass Interview"</em> in their student account.</p>

    <div style="display:flex;flex-direction:column;gap:14px;">
        @forelse($candidates as $c)
            <div style="border:1px solid var(--border);border-left:4px solid {{ $c->status === 'approved' ? 'var(--emerald-700)' : ($c->status === 'declined' ? 'var(--nit-red)' : 'var(--nit-blue)') }};padding:14px;background:var(--canvas);">
                <div class="flex-between flex-wrap mb-2" style="gap:10px;">
                    <div class="flex-center">
                        @if($c->photo)
                            <img src="{{ asset($c->photo) }}" alt="{{ $c->fullname }}" style="width:42px;height:48px;object-fit:cover;object-position:top;border-radius:2px;border:1px solid var(--border);">
                        @else
                            <div class="avatar-tile avatar-fallback" style="width:42px;height:48px;">{{ strtoupper(substr($c->fullname, 0, 2)) }}</div>
                        @endif
                        <div>
                            <strong style="font-size:15px;color:var(--nit-black);">{{ $c->fullname }}</strong>
                            <span class="badge badge-navy">{{ $c->position }}</span>
                            @if($c->course)<span class="badge badge-black mono">{{ $c->course->code }}</span>@endif
                            <span class="badge {{ $c->status === 'approved' ? 'badge-emerald' : ($c->status === 'declined' ? 'badge-crimson' : 'badge-amber') }}">
                                {{ $c->stageLabel() }}
                            </span>
                            <div class="text-xs mono muted mt-1">
                                {{ $c->reg_no }} · Votes: <strong>{{ $c->votes_count }}</strong> ·
                                Fee: TZS {{ number_format($c->application_fee_amount) }} ({{ strtoupper($c->payment_status) }}{{ $c->payment_reference ? ' · Ref: '.$c->payment_reference : '' }})
                            </div>
                            <div class="text-xs muted">
                                Contact: {{ $c->contact_email ?? 'No email' }} | {{ $c->contact_phone ?? 'No phone' }}
                            </div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('admin.candidates.delete', $c) }}" style="margin:0;" onsubmit="return confirm('Delete contestant {{ $c->fullname }} permanently from this election?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm">Delete Contestant</button>
                    </form>
                </div>

                {{-- Inline Edit Form for Name, Details, Fee & Interview --}}
                <details class="admin-details">
                    <summary>Edit Contestant Name, Photo, Fee Payment & Interview Result</summary>
                    <form method="POST" action="{{ route('admin.candidates.update', $c) }}" enctype="multipart/form-data" class="admin-details-body">
                        @csrf
                        @method('PUT')
                        <div class="grid grid-3" style="gap:10px;">
                            <div class="form-group" style="margin:0;">
                                <label class="form-label">Full Name</label>
                                <input type="text" name="fullname" value="{{ $c->fullname }}" class="form-control" required>
                            </div>
                            <div class="form-group" style="margin:0;">
                                <label class="form-label">Reg No</label>
                                <input type="text" name="reg_no" value="{{ $c->reg_no }}" class="form-control mono" required>
                            </div>
                            <div class="form-group" style="margin:0;">
                                <label class="form-label">Position</label>
                                <select name="position" class="form-select">
                                    @foreach(\App\Models\Candidate::POSITIONS as $pos)
                                        <option value="{{ $pos }}" @selected($c->position === $pos)>{{ $pos }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-3 mt-2" style="gap:10px;">
                            <div class="form-group" style="margin:0;">
                                <label class="form-label">Contact Email</label>
                                <input type="email" name="contact_email" value="{{ $c->contact_email }}" class="form-control">
                            </div>
                            <div class="form-group" style="margin:0;">
                                <label class="form-label">Contact Phone</label>
                                <input type="text" name="contact_phone" value="{{ $c->contact_phone }}" class="form-control mono">
                            </div>
                            <div class="form-group" style="margin:0;">
                                <label class="form-label">Update Photo (Optional)</label>
                                <input type="file" name="photo" accept="image/*" class="form-control">
                            </div>
                        </div>

                        @if($c->photo)
                            <label class="flex-center text-xs mt-2" style="gap:6px;color:var(--nit-red-dark);font-weight:700;cursor:pointer;">
                                <input type="checkbox" name="remove_photo" value="1"> Remove current contestant photo
                            </label>
                        @endif

                        <div class="grid grid-3 mt-2" style="gap:10px;">
                            <div class="form-group" style="margin:0;">
                                <label class="form-label">Application Fee Payment</label>
                                <select name="payment_status" class="form-select">
                                    <option value="unpaid" @selected($c->payment_status === 'unpaid')>Unpaid</option>
                                    <option value="submitted" @selected($c->payment_status === 'submitted')>Submitted (Pending Check)</option>
                                    <option value="verified" @selected($c->payment_status === 'verified')>Verified Paid</option>
                                </select>
                            </div>
                            <div class="form-group" style="margin:0;">
                                <label class="form-label">Interview Screening Outcome</label>
                                <select name="interview_status" class="form-select">
                                    <option value="pending_payment" @selected($c->interview_status === 'pending_payment')>Awaiting Fee Payment</option>
                                    <option value="scheduled" @selected($c->interview_status === 'scheduled')>Interview Scheduled</option>
                                    <option value="passed" @selected($c->interview_status === 'passed')>Passed Interview (Approve on Ballot)</option>
                                    <option value="failed" @selected($c->interview_status === 'failed')>Did Not Pass Interview (Decline)</option>
                                </select>
                            </div>
                            <div class="form-group" style="margin:0;">
                                <label class="form-label">Interview Date / Venue</label>
                                <input type="text" name="interview_date" value="{{ $c->interview_date }}" class="form-control" placeholder="e.g. Fri 10:00 AM · SONIT Room 104">
                            </div>
                        </div>

                        <div class="grid grid-2 mt-2" style="gap:10px;">
                            <div class="form-group" style="margin:0;">
                                <label class="form-label">Campaign Motto</label>
                                <input type="text" name="motto" value="{{ $c->motto }}" class="form-control" required>
                            </div>
                            <div class="form-group" style="margin:0;">
                                <label class="form-label">Interview Remarks (Shown to Applicant)</label>
                                <input type="text" name="interview_notes" value="{{ $c->interview_notes }}" class="form-control" placeholder="Feedback or reason if did not pass...">
                            </div>
                        </div>

                        <div class="mt-2">
                            <button type="submit" class="btn btn-primary btn-sm">Save Contestant Changes</button>
                        </div>
                    </form>
                </details>
            </div>
        @empty
            <p class="text-sm muted">No contestants applied yet.</p>
        @endforelse
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
