@extends('layouts.app')

@section('title', 'Sign In — SONIT | NIT')

@section('content')
<div style="max-width:860px;margin:10px auto;">
    <div class="grid grid-2" style="align-items:stretch;">
        <div class="card" style="background:var(--nit-black);color:#FFFFFF;border:none;border-top:4px solid var(--nit-blue);border-bottom:3px solid var(--nit-red);display:flex;flex-direction:column;justify-content:space-between;padding:26px;">
            <div>
                <div class="flex-center mb-3">
                    <img src="{{ asset('images/nit_logo_badge.png') }}" alt="NIT Official Logo" style="width:52px;height:52px;background:#fff;padding:3px;border-radius:2px;">
                    <div>
                        <strong style="font-size:18px;display:block;color:#fff;">{{ $siteSettings['org_short_name'] ?? 'SONIT' }} · NIT Portal</strong>
                        <span style="font-size:10.5px;color:var(--nit-blue);text-transform:uppercase;letter-spacing:0.08em;font-weight:700;">National Institute of Transport</span>
                    </div>
                </div>

                <h2 style="margin:8px 0 8px;font-size:21px;font-weight:800;line-height:1.25;color:#FFFFFF;">
                    Welcome to the Official SONIT Portal
                </h2>
                <p style="font-size:13px;color:#CBD5E1;margin:0 0 16px;">
                    Sign in using your NIT Registration Number (e.g. <span class="mono" style="color:var(--nit-blue);">NIT/BLTM/2022/4805</span>) or institutional email to access your course communities, elections, accommodation, and student services.
                </p>

                <div style="border-left:3px solid var(--nit-red);background:rgba(255,255,255,0.06);padding:12px 14px;margin-bottom:16px;">
                    <div style="font-size:10px;font-weight:800;letter-spacing:0.08em;text-transform:uppercase;color:var(--nit-blue);margin-bottom:4px;">Organization Motto</div>
                    <div style="font-size:13.5px;font-weight:700;color:#FFFFFF;">
                        "{{ $siteSettings['motto'] ?? 'Unity, Integrity, and Academic Excellence in Motion.' }}"
                    </div>
                </div>
            </div>

            <div class="text-xs" style="color:#9BA5B4;border-top:1px solid rgba(255,255,255,0.1);padding-top:12px;">
                Need account assistance? Contact SONIT Secretariat at <strong style="color:#FFFFFF;">{{ $siteSettings['email'] ?? 'office@sonit.nit.ac.tz' }}</strong>
            </div>
        </div>

        <div class="card card-accent-blue" style="padding:26px;">
            <div class="flex-center mb-3">
                <div class="icon-3d-tile sm"><img src="{{ asset('images/icons/security.svg') }}" alt="Sign In"></div>
                <div>
                    <h1 style="margin:0;font-size:21px;font-weight:800;color:var(--nit-black);">Sign In</h1>
                    <p class="text-xs muted" style="margin:0;">Enter your NIT registration number or email</p>
                </div>
            </div>

            <form method="POST" action="{{ route('login.submit') }}" class="mt-2">
                @csrf
                <div class="form-group">
                    <label for="identifier" class="form-label">Registration Number or Email</label>
                    <input id="identifier" type="text" name="identifier" value="{{ old('identifier') }}" class="form-control" placeholder="NIT/BLTM/2022/4805 or email" required autofocus>
                </div>
                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input id="password" type="password" name="password" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="flex-center text-xs" style="cursor:pointer;">
                        <input type="checkbox" name="remember" value="1"> Keep me signed in
                    </label>
                </div>
                <button type="submit" class="btn btn-primary btn-lg" style="width:100%;">Sign In to Portal</button>
            </form>

            <div style="margin-top:16px;padding-top:14px;border-top:1px solid var(--border);text-align:center;" class="text-xs">
                New NIT student? <a href="{{ route('register') }}" style="font-weight:700;">Create Student Account</a>
            </div>
        </div>
    </div>
</div>
@endsection
