<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SONIT Admin Command Center')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@500;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/sonit.css') }}">
</head>
<body>
<div class="admin-shell">
    <aside class="admin-sidebar">
        <a href="{{ route('home') }}" class="flex-center" style="padding:8px 10px 18px;border-bottom:1px solid rgba(255,255,255,0.1);margin-bottom:10px;">
            <img src="{{ asset('images/icons/sonit_crest.svg') }}" alt="SONIT" style="width:38px;height:38px;border-radius:10px;">
            <div>
                <strong style="color:#fff;display:block;font-size:15px;">SONIT Admin</strong>
                <span style="font-size:11px;color:var(--gold-400);text-transform:uppercase;letter-spacing:0.06em;">{{ auth()->user()->roleLabel() }}</span>
            </div>
        </a>

        <a href="{{ route('admin.dashboard') }}" class="admin-nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">Overview & KPIs</a>
        <a href="{{ route('admin.users') }}" class="admin-nav-link {{ request()->routeIs('admin.users*') ? 'active' : '' }}">Users & RBAC Roles</a>
        <a href="{{ route('admin.courses') }}" class="admin-nav-link {{ request()->routeIs('admin.courses*') ? 'active' : '' }}">NIT Courses & CR Seats</a>
        <a href="{{ route('admin.settings') }}" class="admin-nav-link {{ request()->routeIs('admin.settings*') ? 'active' : '' }}">Motto, Vision & Identity</a>
        <a href="{{ route('admin.timers') }}" class="admin-nav-link {{ request()->routeIs('admin.timers*') ? 'active' : '' }}">Countdown Banners</a>
        <a href="{{ route('admin.announcements') }}" class="admin-nav-link {{ request()->routeIs('admin.announcements*') ? 'active' : '' }}">Announcements & Alerts</a>
        <a href="{{ route('admin.elections') }}" class="admin-nav-link {{ request()->routeIs('admin.elections*') ? 'active' : '' }}">Elections & Contestants</a>
        <a href="{{ route('admin.services') }}" class="admin-nav-link {{ request()->routeIs('admin.services*') ? 'active' : '' }}">Venues, Housing & Help</a>
        <a href="{{ route('admin.campus-life') }}" class="admin-nav-link {{ request()->routeIs('admin.campus-life*') ? 'active' : '' }}">Events, Sports & Charity</a>

        <div style="margin-top:auto;padding-top:16px;border-top:1px solid rgba(255,255,255,0.1);display:flex;flex-direction:column;gap:8px;">
            <a href="{{ route('communities.index') }}" class="admin-nav-link">Course Communities</a>
            <a href="{{ route('home') }}" class="admin-nav-link">View Public Website</a>
            <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                @csrf
                <button type="submit" class="btn btn-gold btn-sm" style="width:100%;">Sign Out</button>
            </form>
        </div>
    </aside>

    <div class="admin-main">
        <div class="flex-between mb-4" style="padding-bottom:16px;border-bottom:1px solid var(--border);flex-wrap:wrap;">
            <div>
                <span class="kicker" style="margin-bottom:4px;">Governance Console</span>
                <h1 style="margin:0;font-size:26px;font-weight:800;color:var(--navy-900);">@yield('page_title', 'Command Center')</h1>
            </div>
            <div class="flex-center">
                <span class="badge badge-navy">{{ auth()->user()->name }}</span>
                <span class="badge badge-gold">{{ auth()->user()->roleLabel() }}</span>
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
                <ul style="margin:0 0 0 18px;padding:0;">
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
