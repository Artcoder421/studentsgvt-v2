@extends('layouts.app')

@section('title', 'Candidate Nomination — SONIT Elections')

@section('content')
<div class="page-banner">
    <div class="container">
        <span class="badge badge-gold mb-2">{{ $election->academic_year }} Nominations</span>
        <h1>Apply to Contest in SONIT Elections</h1>
        <p>Submit your official manifesto and campaign details for vetting by the SONIT Electoral Commission.</p>
    </div>
</div>

<section class="section section-tight">
    <div class="container" style="max-width:740px;">
        <div class="card" style="padding:30px;">
            <form method="POST" action="{{ route('elections.apply.submit') }}" enctype="multipart/form-data">
                @csrf
                <div class="grid grid-2">
                    <div class="form-group">
                        <label class="form-label">Applicant Name</label>
                        <input type="text" class="form-control" value="{{ $user->name }}" disabled>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Registration Number & Course</label>
                        <input type="text" class="form-control mono" value="{{ $user->reg_no }} ({{ $user->course?->code ?? 'N/A' }})" disabled>
                    </div>
                </div>

                <div class="grid grid-2">
                    <div class="form-group">
                        <label class="form-label">Position Sought</label>
                        <select name="position" class="form-select" required>
                            @foreach(\App\Models\Candidate::POSITIONS as $pos)
                                <option value="{{ $pos }}">{{ $pos }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Academic Level</label>
                        <select name="academic_level" class="form-select" required>
                            @foreach(['First Year', 'Second Year', 'Third Year', 'Fourth Year', 'Postgraduate'] as $lvl)
                                <option value="{{ $lvl }}" @selected($user->academic_level === $lvl)>{{ $lvl }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Campaign Motto</label>
                    <input type="text" name="motto" value="{{ old('motto') }}" class="form-control" placeholder="e.g. Accountable Representation & Better Student Hostels" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Official Manifesto & Policy Priorities</label>
                    <textarea name="manifesto" rows="5" class="form-control" placeholder="Outline your concrete, measurable plans for NIT students..." required>{{ old('manifesto') }}</textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Official Campaign Portrait (JPG/PNG/WebP, max 2MB)</label>
                    <input type="file" name="photo" accept="image/*" class="form-control">
                </div>

                <div class="flex-between">
                    <a href="{{ route('elections.index') }}" class="btn btn-outline">Back</a>
                    <button type="submit" class="btn btn-primary btn-lg">Submit Nomination</button>
                </div>
            </form>
        </div>
    </div>
</section>
@endsection
