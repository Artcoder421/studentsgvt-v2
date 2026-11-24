@extends('layouts.app')

@section('title', 'Profile & Security — SONIT Portal')

@section('content')
<div class="page-banner">
    <div class="container page-banner-inner">
        <div class="page-banner-left">
            <div class="icon-3d-tile lg"><img src="{{ asset('images/icons/security.svg') }}" alt="Profile"></div>
            <div>
                <h1>Account Profile & Security</h1>
                <p>{{ $user->reg_no ?? $user->email }} · {{ $user->roleLabel() }} {{ $user->course ? '· '.$user->course->code : '' }}</p>
            </div>
        </div>
    </div>
</div>

<section class="section section-tight">
    <div class="container">
        <div class="grid grid-2" style="align-items:start;">
            <div class="card">
                <h2 style="margin:0 0 18px;font-size:20px;font-weight:800;color:var(--navy-900);">Personal Information</h2>
                <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="flex-center mb-3" style="gap:14px;padding:12px;background:var(--canvas);border:1px solid var(--border);">
                        @if($user->avatar)
                            <img src="{{ asset($user->avatar) }}" alt="{{ $user->name }}" class="avatar-tile lg">
                        @else
                            <div class="avatar-tile lg avatar-fallback">{{ strtoupper(substr($user->name, 0, 2)) }}</div>
                        @endif
                        <div style="flex:1;min-width:0;">
                            <div style="font-weight:800;font-size:15px;color:var(--nit-black);">{{ $user->name }}</div>
                            <div class="text-xs muted mb-1">Your profile photo appears in Course Community chats and your portal sidebar.</div>
                            @if($user->avatar)
                                <label class="flex-center text-xs" style="gap:6px;color:var(--nit-red-dark);font-weight:700;cursor:pointer;margin-top:6px;">
                                    <input type="checkbox" name="remove_avatar" value="1"> Remove current profile photo
                                </label>
                            @endif
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Registration Number</label>
                        <input type="text" value="{{ $user->reg_no ?? 'N/A' }}" class="form-control mono" disabled>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Course</label>
                        <input type="text" value="{{ $user->course ? '['.$user->course->code.'] '.$user->course->name : 'Administration' }}" class="form-control" disabled>
                    </div>
                    <div class="grid grid-2">
                        <div class="form-group">
                            <label class="form-label">Phone Number</label>
                            <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="form-control">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Academic Level</label>
                            <input type="text" name="academic_level" value="{{ old('academic_level', $user->academic_level) }}" class="form-control">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Short Bio</label>
                        <textarea name="bio" rows="3" class="form-control">{{ old('bio', $user->bio) }}</textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Upload / Replace Profile Photo (JPG/PNG/WebP, max 2MB)</label>
                        <input type="file" name="avatar" class="form-control" accept="image/*">
                    </div>
                    <button type="submit" class="btn btn-primary">Save Profile Changes</button>
                </form>
            </div>

            <div class="card">
                <h2 style="margin:0 0 18px;font-size:20px;font-weight:800;color:var(--navy-900);">Change Password</h2>
                <form method="POST" action="{{ route('profile.password') }}">
                    @csrf
                    <div class="form-group">
                        <label class="form-label">Current Password</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">New Password (minimum 8 characters)</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Confirm New Password</label>
                        <input type="password" name="password_confirmation" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-gold">Update Password</button>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection
