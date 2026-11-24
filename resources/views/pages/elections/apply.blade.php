@extends('layouts.app')

@section('title', 'Candidate Nomination & Application — SONIT Elections')

@section('content')
<div class="page-banner">
    <div class="page-banner-inner">
        <div class="page-banner-left">
            <div class="icon-3d-tile"><img src="{{ asset('images/icons/elections.jpg') }}" alt="Apply"></div>
            <div>
                <h1>Apply for SONIT Election Position ({{ $election->academic_year }})</h1>
                <p>Submit your contact details, pay the official nomination fee, and wait for your Electoral Commission interview schedule.</p>
            </div>
        </div>
        <a href="{{ route('elections.index') }}" class="btn btn-ghost-light btn-sm">Back to Elections</a>
    </div>
</div>

<div class="grid grid-2" style="align-items:start;">
    <div class="card card-accent-blue">
        <h2 style="margin:0 0 14px;font-size:18px;font-weight:800;color:var(--nit-black);">Nomination Form</h2>

        <form method="POST" action="{{ route('elections.apply.submit') }}" enctype="multipart/form-data">
            @csrf
            <div class="grid grid-2">
                <div class="form-group">
                    <label class="form-label">Applicant Name</label>
                    <input type="text" class="form-control" value="{{ $user->name }}" disabled>
                </div>
                <div class="form-group">
                    <label class="form-label">Reg No & Course</label>
                    <input type="text" class="form-control mono" value="{{ $user->reg_no }} ({{ $user->course?->code ?? 'N/A' }})" disabled>
                </div>
            </div>

            <div class="grid grid-2">
                <div class="form-group">
                    <label class="form-label">Contact Email (For Interview Notices)</label>
                    <input type="email" name="contact_email" value="{{ old('contact_email', $user->email) }}" class="form-control" required placeholder="your.email@nit.ac.tz">
                </div>
                <div class="form-group">
                    <label class="form-label">Active Phone Number (SMS / Call)</label>
                    <input type="text" name="contact_phone" value="{{ old('contact_phone', $user->phone) }}" class="form-control mono" required placeholder="+255 7XX XXX XXX">
                </div>
            </div>

            <div class="grid grid-2">
                <div class="form-group">
                    <label class="form-label">Position Sought</label>
                    <select name="position" class="form-select" required>
                        @foreach(\App\Models\Candidate::POSITIONS as $pos)
                            <option value="{{ $pos }}" @selected(old('position') === $pos)>
                                {{ $pos }} (Fee: TZS {{ number_format(\App\Models\Candidate::feeForPosition($pos)) }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Academic Level</label>
                    <select name="academic_level" class="form-select" required>
                        @foreach(['First Year', 'Second Year', 'Third Year', 'Fourth Year', 'Postgraduate'] as $lvl)
                            <option value="{{ $lvl }}" @selected(old('academic_level', $user->academic_level) === $lvl)>{{ $lvl }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Campaign Motto</label>
                <input type="text" name="motto" value="{{ old('motto') }}" class="form-control" placeholder="e.g. Accountable Representation & Student Welfare" required>
            </div>

            <div class="form-group">
                <label class="form-label">Official Manifesto & Key Plans</label>
                <textarea name="manifesto" rows="4" class="form-control" placeholder="Outline your measurable agenda for NIT students..." required>{{ old('manifesto') }}</textarea>
            </div>

            <div class="grid grid-2">
                <div class="form-group">
                    <label class="form-label">Fee Payment Reference (Optional Now)</label>
                    <input type="text" name="payment_reference" value="{{ old('payment_reference') }}" class="form-control mono" placeholder="e.g. MPESA-994X21 (or add later)">
                </div>
                <div class="form-group">
                    <label class="form-label">Campaign Portrait (JPG/PNG/WebP)</label>
                    <input type="file" name="photo" accept="image/*" class="form-control">
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-lg" style="width:100%;">Submit Nomination Application</button>
        </form>
    </div>

    <div class="card card-accent-red">
        <span class="kicker">Screening Process</span>
        <h2 style="margin:0 0 12px;font-size:18px;font-weight:800;color:var(--nit-black);">Application Fee & Interview Steps</h2>

        <div class="notice-box is-account mb-3">
            <div class="notice-label">Official Nomination Fee Payment Account</div>
            <strong class="mono text-sm" style="color:var(--nit-black);display:block;">CRDB Bank: 0150442198800 (SONIT ELECTORAL COMMISSION)</strong>
            <strong class="mono text-sm" style="color:var(--nit-black);display:block;">M-Pesa / Mixx Lipa Namba: 5589021</strong>
            <div class="text-xs muted mt-1">Reference: Your NIT Registration Number</div>
        </div>

        <div style="display:flex;flex-direction:column;gap:10px;" class="text-xs">
            <div class="notice-box">
                <div class="notice-label">Step 1 · Submit Nomination & Contacts</div>
                Provide a valid email and phone number where the Electoral Commission can reach you for vetting updates.
            </div>
            <div class="notice-box">
                <div class="notice-label">Step 2 · Pay Application Fee</div>
                President: <strong>TZS 25,000</strong> · Vice President: <strong>TZS 20,000</strong> · Cabinet: <strong>TZS 15,000</strong> · Parliament Rep: <strong>TZS 10,000</strong>. Submit your payment reference now or later from your Dashboard.
            </div>
            <div class="notice-box">
                <div class="notice-label">Step 3 · Attend Screening Interview</div>
                Once your payment is verified, your interview date will appear in your Dashboard. Only candidates who pass the interview appear on the official ballot; if you do not pass, the result and remarks will display clearly on your account.
            </div>
        </div>
    </div>
</div>
@endsection
