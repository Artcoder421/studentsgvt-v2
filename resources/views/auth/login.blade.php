@extends('layouts.app')

@section('title', 'Sign In — SONIT Student Portal')

@section('content')
<section class="section" style="padding:54px 0 74px;">
    <div class="container" style="max-width:980px;">
        <div class="grid grid-2" style="align-items:stretch;">
            <div class="card" style="background:linear-gradient(155deg, var(--navy-950) 0%, var(--navy-800) 100%);color:#FFFFFF;border:none;display:flex;flex-direction:column;justify-content:space-between;padding:34px;">
                <div>
                    <div class="flex-center mb-3">
                        <img src="{{ asset('images/icons/sonit_crest.svg') }}" alt="SONIT Crest" style="width:46px;height:46px;border-radius:12px;">
                        <div>
                            <strong style="font-size:18px;display:block;">SONIT Portal</strong>
                            <span style="font-size:11.5px;color:var(--gold-400);text-transform:uppercase;letter-spacing:0.08em;">Role-Based Access Control</span>
                        </div>
                    </div>
                    <h2 style="margin:12px 0 12px;font-size:26px;font-weight:800;line-height:1.2;">Secure Digital Access for NIT Students & Leaders</h2>
                    <p style="font-size:14px;color:#CBD5E1;margin:0 0 22px;">
                        Sign in using your official NIT Registration Number (e.g. <span class="mono" style="color:var(--gold-400);">NIT/BLTM/2022/4805</span>) or institutional email address.
                    </p>

                    <div style="background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.14);border-radius:14px;padding:16px;">
                        <div style="font-size:11.5px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:var(--gold-400);margin-bottom:10px;">
                            Quick-Fill Evaluation Accounts
                        </div>
                        <div style="display:flex;flex-direction:column;gap:8px;">
                            <button type="button" class="btn btn-ghost-light btn-sm" style="justify-content:space-between;" data-demo-login data-demo-id="superadmin@sonit.nit.ac.tz" data-demo-pass="SuperAdmin@2026">
                                <span>Super Admin (Full RBAC)</span>
                                <span class="mono text-xs">superadmin</span>
                            </button>
                            <button type="button" class="btn btn-ghost-light btn-sm" style="justify-content:space-between;" data-demo-login data-demo-id="admin@sonit.nit.ac.tz" data-demo-pass="Admin@2026">
                                <span>SONIT Cabinet Admin</span>
                                <span class="mono text-xs">admin</span>
                            </button>
                            <button type="button" class="btn btn-ghost-light btn-sm" style="justify-content:space-between;" data-demo-login data-demo-id="NIT/BLTM/2022/4805" data-demo-pass="ClassRep@2026">
                                <span>BLTM Class Rep (CR)</span>
                                <span class="mono text-xs">BLTM CR</span>
                            </button>
                            <button type="button" class="btn btn-ghost-light btn-sm" style="justify-content:space-between;" data-demo-login data-demo-id="NIT/BLTM/2023/7658" data-demo-pass="Student@2026">
                                <span>BLTM Student</span>
                                <span class="mono text-xs">Student</span>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="text-xs" style="color:#94A3B8;margin-top:20px;">
                    Protected by Bcrypt password hashing, CSRF tokens, session regeneration, and brute-force rate limiting.
                </div>
            </div>

            <div class="card" style="padding:34px;">
                <div class="flex-center mb-3">
                    <div class="icon-3d-tile sm"><img src="{{ asset('images/icons/security.svg') }}" alt="Security"></div>
                    <div>
                        <h1 style="margin:0;font-size:24px;font-weight:800;color:var(--navy-900);">Sign In</h1>
                        <p class="text-sm muted" style="margin:0;">Enter your NIT credentials below</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('login.submit') }}" class="mt-3">
                    @csrf
                    <div class="form-group">
                        <label for="identifier" class="form-label">Registration Number or Email</label>
                        <input id="identifier" type="text" name="identifier" value="{{ old('identifier') }}" class="form-control" placeholder="e.g. NIT/BLTM/2022/4805 or email" required autofocus>
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <input id="password" type="password" name="password" class="form-control" placeholder="Enter your password" required>
                    </div>

                    <div class="flex-between mb-3">
                        <label class="flex-center text-sm" style="cursor:pointer;">
                            <input type="checkbox" name="remember" value="1"> Remember me on this device
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg" style="width:100%;">Sign In to Portal</button>
                </form>

                <div style="margin-top:22px;padding-top:18px;border-top:1px solid var(--border);text-align:center;" class="text-sm">
                    New NIT student? <a href="{{ route('register') }}" style="font-weight:700;">Create your verified student account</a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
