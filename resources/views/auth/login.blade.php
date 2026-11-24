@extends('layouts.app')

@section('title', 'Sign In — SONIT | NIT')

@section('content')
<div style="max-width:860px;margin:10px auto;">
    <div class="grid grid-2" style="align-items:stretch;">
        <div class="card" style="background:var(--nit-black);color:#FFFFFF;border:none;border-top:4px solid var(--nit-blue);border-bottom:3px solid var(--nit-red);display:flex;flex-direction:column;justify-content:space-between;padding:24px;">
            <div>
                <div class="flex-center mb-3">
                    <img src="{{ asset('images/nit_logo_badge.png') }}" alt="NIT Official Logo" style="width:48px;height:48px;background:#fff;padding:3px;border-radius:2px;">
                    <div>
                        <strong style="font-size:17px;display:block;color:#fff;">SONIT · NIT Portal</strong>
                        <span style="font-size:10.5px;color:var(--nit-blue);text-transform:uppercase;letter-spacing:0.08em;font-weight:700;">National Institute of Transport</span>
                    </div>
                </div>
                <h2 style="margin:6px 0 8px;font-size:21px;font-weight:800;line-height:1.2;">Secure Role-Based Student Access</h2>
                <p style="font-size:13px;color:#CBD5E1;margin:0 0 16px;">
                    Sign in with your NIT Registration Number (e.g. <span class="mono" style="color:var(--nit-blue);">NIT/BLTM/2022/4805</span>) or institutional email.
                </p>

                <div style="background:rgba(255,255,255,0.06);border:1px solid rgba(0,149,218,0.4);padding:12px;">
                    <div style="font-size:10.5px;font-weight:800;letter-spacing:0.08em;text-transform:uppercase;color:var(--nit-blue);margin-bottom:8px;">
                        Quick-Fill Evaluation Accounts
                    </div>
                    <div style="display:flex;flex-direction:column;gap:6px;">
                        <button type="button" class="btn btn-ghost-light btn-sm" style="justify-content:space-between;" data-demo-login data-demo-id="superadmin@sonit.nit.ac.tz" data-demo-pass="SuperAdmin@2026">
                            <span>Super Admin</span><span class="mono text-xs">superadmin</span>
                        </button>
                        <button type="button" class="btn btn-ghost-light btn-sm" style="justify-content:space-between;" data-demo-login data-demo-id="admin@sonit.nit.ac.tz" data-demo-pass="Admin@2026">
                            <span>SONIT Admin</span><span class="mono text-xs">admin</span>
                        </button>
                        <button type="button" class="btn btn-ghost-light btn-sm" style="justify-content:space-between;" data-demo-login data-demo-id="NIT/BLTM/2022/4805" data-demo-pass="ClassRep@2026">
                            <span>BLTM Class Rep (CR)</span><span class="mono text-xs">BLTM CR</span>
                        </button>
                        <button type="button" class="btn btn-ghost-light btn-sm" style="justify-content:space-between;" data-demo-login data-demo-id="NIT/BLTM/2023/7658" data-demo-pass="Student@2026">
                            <span>BLTM Student</span><span class="mono text-xs">Student</span>
                        </button>
                    </div>
                </div>
            </div>
            <div class="text-xs" style="color:#8F99A8;margin-top:14px;">Bcrypt hashed passwords · CSRF · Session regeneration · Rate limiting</div>
        </div>

        <div class="card card-accent-blue" style="padding:24px;">
            <div class="flex-center mb-3">
                <div class="icon-3d-tile sm"><img src="{{ asset('images/icons/security.svg') }}" alt="Security"></div>
                <div>
                    <h1 style="margin:0;font-size:21px;font-weight:800;color:var(--nit-black);">Sign In</h1>
                    <p class="text-xs muted" style="margin:0;">Enter your NIT credentials</p>
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
