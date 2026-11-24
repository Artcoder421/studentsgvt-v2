@extends('layouts.app')

@section('title', 'Student Registration — SONIT Portal')

@section('content')
<section class="section" style="padding:48px 0 72px;">
    <div class="container" style="max-width:740px;">
        <div class="card" style="padding:34px;">
            <div class="flex-center mb-3">
                <div class="icon-3d-tile sm"><img src="{{ asset('images/icons/communities.jpg') }}" alt="Register"></div>
                <div>
                    <h1 style="margin:0;font-size:25px;font-weight:800;color:var(--navy-900);">NIT Student Registration</h1>
                    <p class="text-sm muted" style="margin:0;">Registering automatically connects you to your official NIT course community.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('register.submit') }}" class="mt-3">
                @csrf
                <div class="grid grid-2">
                    <div class="form-group">
                        <label for="name" class="form-label">Full Name</label>
                        <input id="name" type="text" name="name" value="{{ old('name') }}" class="form-control" placeholder="e.g. Amina Hassan Mwinyi" required>
                    </div>

                    <div class="form-group">
                        <label for="reg_no" class="form-label">NIT Registration Number</label>
                        <input id="reg_no" type="text" name="reg_no" value="{{ old('reg_no') }}" class="form-control mono" placeholder="NIT/BLTM/2025/1234" required>
                        <div class="form-hint">Format: NIT/COURSE/YEAR/NUMBER</div>
                    </div>
                </div>

                <div class="grid grid-2">
                    <div class="form-group">
                        <label for="email" class="form-label">Email Address</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" class="form-control" placeholder="name@student.nit.ac.tz" required>
                    </div>

                    <div class="form-group">
                        <label for="phone" class="form-label">Phone Number (Optional)</label>
                        <input id="phone" type="text" name="phone" value="{{ old('phone') }}" class="form-control" placeholder="+255 7XX XXX XXX">
                    </div>
                </div>

                <div class="form-group">
                    <label for="course_id" class="form-label">NIT Academic Programme / Course</label>
                    <select id="course_id" name="course_id" class="form-select" required>
                        <option value="">-- Select your NIT Programme --</option>
                        @foreach($courses as $course)
                            <option value="{{ $course->id }}" @selected(old('course_id') == $course->id)>
                                [{{ $course->code }}] {{ $course->name }} ({{ $course->levelLabel() }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-2">
                    <div class="form-group">
                        <label for="year_joined" class="form-label">Year Joined</label>
                        <input id="year_joined" type="number" name="year_joined" value="{{ old('year_joined', date('Y')) }}" min="2018" max="2030" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="academic_level" class="form-label">Current Academic Level</label>
                        <select id="academic_level" name="academic_level" class="form-select" required>
                            @foreach(['First Year', 'Second Year', 'Third Year', 'Fourth Year', 'Postgraduate'] as $lvl)
                                <option value="{{ $lvl }}" @selected(old('academic_level') === $lvl)>{{ $lvl }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-2">
                    <div class="form-group">
                        <label for="password" class="form-label">Password (min 8 chars)</label>
                        <input id="password" type="password" name="password" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="password_confirmation" class="form-label">Confirm Password</label>
                        <input id="password_confirmation" type="password" name="password_confirmation" class="form-control" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-lg" style="width:100%;">Create Student Account</button>
            </form>

            <div style="margin-top:20px;text-align:center;" class="text-sm">
                Already registered? <a href="{{ route('login') }}" style="font-weight:700;">Sign in here</a>
            </div>
        </div>
    </div>
</section>
@endsection
