<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SONIT Admin Console — NIT')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@500;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/sonit.css') }}">
</head>
<body>
<div class="admin-shell">
    <aside class="admin-sidebar">
        <a href="{{ route('home') }}" class="flex-center" style="padding:6px 8px 14px;border-bottom:2px solid var(--nit-blue);margin-bottom:10px;">
            <img src="{{ asset('images/nit_logo_badge.png') }}" alt="NIT Logo" style="width:40px;height:40px;background:#fff;padding:2px;border-radius:2px;">
            <div>
                <strong style="color:#fff;display:block;font-size:15px;">SONIT Admin</strong>
                <span style="font-size:10.5px;color:var(--nit-blue);text-transform:uppercase;letter-spacing:0.06em;font-weight:700;">{{ auth()->user()->roleLabel() }}</span>
            </div>
        </a>

        <a href="{{ route('admin.dashboard') }}" class="admin-nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">Overview & KPIs</a>
        <a href="{{ route('admin.users') }}" class="admin-nav-link {{ request()->routeIs('admin.users*') ? 'active' : '' }}">Users & RBAC Roles</a>
        <a href="{{ route('admin.courses') }}" class="admin-nav-link {{ request()->routeIs('admin.courses*') ? 'active' : '' }}">NIT Courses & CR Seats</a>
        <a href="{{ route('admin.settings') }}" class="admin-nav-link {{ request()->routeIs('admin.settings*') ? 'active' : '' }}">Motto, Vision & Identity</a>
        <a href="{{ route('admin.timers') }}" class="admin-nav-link {{ request()->routeIs('admin.timers*') ? 'active' : '' }}">Countdown Notices</a>
        <a href="{{ route('admin.announcements') }}" class="admin-nav-link {{ request()->routeIs('admin.announcements*') ? 'active' : '' }}">Announcements & Alerts</a>
        <a href="{{ route('admin.elections') }}" class="admin-nav-link {{ request()->routeIs('admin.elections*') ? 'active' : '' }}">Elections & Early End</a>
        <a href="{{ route('admin.services') }}" class="admin-nav-link {{ request()->routeIs('admin.services*') ? 'active' : '' }}">Venues, Housing & Help</a>
        <a href="{{ route('admin.campus-life') }}" class="admin-nav-link {{ request()->routeIs('admin.campus-life*') ? 'active' : '' }}">Events, Sports & Charity</a>

        <div style="margin-top:auto;padding-top:14px;border-top:1px solid rgba(255,255,255,0.12);display:flex;flex-direction:column;gap:6px;">
            <a href="{{ route('dashboard') }}" class="admin-nav-link">Student Portal</a>
            <a href="{{ route('home') }}" class="admin-nav-link">Public Homepage</a>
            <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                @csrf
                <button type="submit" class="btn btn-danger btn-sm" style="width:100%;">Sign Out</button>
            </form>
        </div>
    </aside>

    <div class="admin-main">
        <div class="flex-between mb-3" style="padding-bottom:12px;border-bottom:2px solid var(--nit-black);flex-wrap:wrap;">
            <div>
                <span class="kicker" style="margin-bottom:2px;">NIT Governance Console</span>
                <h1 style="margin:0;font-size:22px;font-weight:800;color:var(--nit-black);">@yield('page_title', 'Command Center')</h1>
            </div>
            <div class="flex-center">
                <span class="badge badge-navy">{{ auth()->user()->name }}</span>
                <span class="badge badge-crimson">{{ auth()->user()->roleLabel() }}</span>
            </div>
        </div>

        @if(session('success'))
            <div class="flash flash-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="flash flash-error">{{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div class="flash flash-error">
                <ul style="margin:0 0 0 16px;padding:0;">
                    @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </div>
</div>
<script src="{{ asset('js/sonit.js') }}"></script>
</body>
</html>
