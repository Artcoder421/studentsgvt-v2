<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', ($siteSettings['org_short_name'] ?? 'SONIT') . ' — National Institute of Transport')</title>
    <meta name="description" content="@yield('meta_description', $siteSettings['tagline'] ?? 'Official Student Government Portal for the National Institute of Transport (NIT)')">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@500;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/sonit.css') }}">
</head>
<body>
<div class="sidebar-backdrop" id="sidebarBackdrop"></div>

<div class="portal-shell">
    {{-- Centralized Student Side Menu --}}
    <aside class="portal-sidebar" id="portalSidebar" aria-label="Student Portal Navigation">
        <a href="{{ route('home') }}" class="sidebar-brand">
            <img src="{{ asset('images/nit_logo_badge.png') }}" alt="NIT Official Logo" class="sidebar-logo">
            <div>
                <strong>{{ $siteSettings['org_short_name'] ?? 'SONIT' }}</strong>
                <span>National Institute of Transport</span>
            </div>
        </a>

        <div class="sidebar-section-label">Core Portal</div>
        <nav class="sidebar-nav">
            <a href="{{ route('home') }}" class="sidebar-link {{ request()->routeIs('home') ? 'active' : '' }}">
                <span class="sidebar-link-left">
                    <img src="{{ asset('images/icons/leadership.svg') }}" alt=""> Overview
                </span>
            </a>
            @auth
                <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <span class="sidebar-link-left">
                        <img src="{{ asset('images/icons/security.svg') }}" alt=""> Student Dashboard
                    </span>
                </a>
            @endauth
            <a href="{{ route('announcements.index') }}" class="sidebar-link {{ request()->routeIs('announcements.*') ? 'active' : '' }}">
                <span class="sidebar-link-left">
                    <img src="{{ asset('images/icons/announcements.jpg') }}" alt=""> Announcements
                </span>
                @if($unreadNotificationCount > 0)
                    <span class="sidebar-badge">{{ $unreadNotificationCount }}</span>
                @endif
            </a>
            <a href="{{ route('communities.index') }}" class="sidebar-link {{ request()->routeIs('communities.*') ? 'active' : '' }}">
                <span class="sidebar-link-left">
                    <img src="{{ asset('images/icons/communities.jpg') }}" alt=""> Course Communities
                </span>
            </a>
            <a href="{{ route('elections.index') }}" class="sidebar-link {{ request()->routeIs('elections.*') ? 'active' : '' }}">
                <span class="sidebar-link-left">
                    <img src="{{ asset('images/icons/elections.jpg') }}" alt=""> E-Elections
                </span>
            </a>
        </nav>

        <div class="sidebar-section-label">Student Services</div>
        <nav class="sidebar-nav">
            <a href="{{ route('accommodations.index') }}" class="sidebar-link {{ request()->routeIs('accommodations.*') ? 'active' : '' }}">
                <span class="sidebar-link-left">
                    <img src="{{ asset('images/icons/accommodation.svg') }}" alt=""> Accommodation
                </span>
            </a>
            <a href="{{ route('help-desk.index') }}" class="sidebar-link {{ request()->routeIs('help-desk.*') ? 'active' : '' }}">
                <span class="sidebar-link-left">
                    <img src="{{ asset('images/icons/helpdesk.jpg') }}" alt=""> Help Desk
                </span>
            </a>
            <a href="{{ route('venues.index') }}" class="sidebar-link {{ request()->routeIs('venues.*') ? 'active' : '' }}">
                <span class="sidebar-link-left">
                    <img src="{{ asset('images/icons/venues.jpg') }}" alt=""> Campus Venues
                </span>
            </a>
            <a href="{{ route('lost-found.index') }}" class="sidebar-link {{ request()->routeIs('lost-found.*') ? 'active' : '' }}">
                <span class="sidebar-link-left">
                    <img src="{{ asset('images/icons/lostfound.svg') }}" alt=""> Lost & Found
                </span>
            </a>
        </nav>

        <div class="sidebar-section-label">Campus Life</div>
        <nav class="sidebar-nav">
            <a href="{{ route('charity.index') }}" class="sidebar-link {{ request()->routeIs('charity.*') ? 'active' : '' }}">
                <span class="sidebar-link-left">
                    <img src="{{ asset('images/icons/charity.svg') }}" alt=""> Charity & Tours
                </span>
            </a>
            <a href="{{ route('events.index') }}" class="sidebar-link {{ request()->routeIs('events.*') ? 'active' : '' }}">
                <span class="sidebar-link-left">
                    <img src="{{ asset('images/icons/events.svg') }}" alt=""> Events & Calendar
                </span>
            </a>
            <a href="{{ route('sports.index') }}" class="sidebar-link {{ request()->routeIs('sports.*') ? 'active' : '' }}">
                <span class="sidebar-link-left">
                    <img src="{{ asset('images/icons/sports.svg') }}" alt=""> Sports & Clubs
                </span>
            </a>
            <a href="{{ route('leadership') }}" class="sidebar-link {{ request()->routeIs('leadership') ? 'active' : '' }}">
                <span class="sidebar-link-left">
                    <img src="{{ asset('images/icons/leadership.svg') }}" alt=""> Leadership
                </span>
            </a>
            @auth
                @if(auth()->user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="sidebar-link is-admin mt-2">
                        <span class="sidebar-link-left">
                            <img src="{{ asset('images/icons/security.svg') }}" alt=""> Admin Console
                        </span>
                    </a>
                @endif
            @endauth
        </nav>

        <div class="sidebar-footer">
            @auth
                <div style="font-size:12px;font-weight:700;color:#FFFFFF;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ auth()->user()->name }}</div>
                <div class="mono" style="font-size:10.5px;color:var(--nit-blue);margin-bottom:8px;">{{ auth()->user()->reg_no ?? auth()->user()->roleLabel() }}</div>
                <div class="flex-between" style="gap:6px;">
                    <a href="{{ route('profile.edit') }}" class="btn btn-ghost-light btn-sm" style="flex:1;">Profile</a>
                    <form method="POST" action="{{ route('logout') }}" style="margin:0;flex:1;">
                        @csrf
                        <button type="submit" class="btn btn-danger btn-sm" style="width:100%;">Sign Out</button>
                    </form>
                </div>
            @else
                <div style="display:flex;gap:6px;">
                    <a href="{{ route('login') }}" class="btn btn-primary btn-sm" style="flex:1;">Sign In</a>
                    <a href="{{ route('register') }}" class="btn btn-ghost-light btn-sm" style="flex:1;">Register</a>
                </div>
            @endauth
        </div>
    </aside>

    {{-- Main Workspace Column --}}
    <div class="portal-main">
        {{-- Top Countdown Timer Announcement Strip (Charity Tour / Election Reminder with Account Info) --}}
        @if($liveTimers->isNotEmpty())
            @php $topTimer = $liveTimers->first(); @endphp
            <div class="timer-strip" role="region" aria-label="Time-sensitive student notice">
                <div class="timer-strip-inner">
                    <div class="timer-strip-meta">
                        <span class="timer-cat {{ $topTimer->category === 'urgent' ? 'is-urgent' : '' }}">{{ strtoupper($topTimer->category) }}</span>
                        <div style="min-width:0;">
                            <span class="timer-title">{{ $topTimer->title }}</span>
                            @if($topTimer->account_number)
                                <span class="timer-sub mono">· {{ $topTimer->account_number }}</span>
                            @elseif($topTimer->event_when)
                                <span class="timer-sub">· {{ $topTimer->event_when }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="timer-right">
                        <div class="countdown-clock" data-countdown="{{ $topTimer->ends_at->toIso8601String() }}">
                            <div class="cd-unit"><span class="cd-num" data-cd-days>00</span><span class="cd-lbl">Days</span></div>
                            <div class="cd-unit"><span class="cd-num" data-cd-hours>00</span><span class="cd-lbl">Hrs</span></div>
                            <div class="cd-unit"><span class="cd-num" data-cd-mins>00</span><span class="cd-lbl">Min</span></div>
                            <div class="cd-unit"><span class="cd-num" data-cd-secs>00</span><span class="cd-lbl">Sec</span></div>
                        </div>
                        @if($topTimer->cta_label && $topTimer->cta_url)
                            <a href="{{ url($topTimer->cta_url) }}" class="timer-cta">{{ $topTimer->cta_label }}</a>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        {{-- Urgent Broadcast Ribbon --}}
        @if($urgentAlerts->isNotEmpty())
            @php $latestUrgent = $urgentAlerts->first(); @endphp
            <div class="urgent-ribbon">
                <div class="urgent-ribbon-inner">
                    <div class="flex-center" style="min-width:0;">
                        <span class="urgent-tag">Urgent</span>
                        <a href="{{ route('announcements.show', $latestUrgent) }}" style="color:var(--nit-red-dark);font-weight:700;">
                            {{ $latestUrgent->title }}
                        </a>
                    </div>
                    <a href="{{ route('announcements.index', ['urgent' => 1]) }}" style="color:var(--nit-red-dark);font-weight:700;font-size:12px;white-space:nowrap;">
                        All urgent ({{ $urgentAlerts->count() }})
                    </a>
                </div>
            </div>
        @endif

        {{-- Compact Top Utility Header --}}
        <header class="topbar">
            <div class="topbar-inner">
                <div class="topbar-left">
                    <button type="button" class="mobile-menu-btn" id="mobileMenuBtn" aria-label="Open Menu">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
                    </button>
                    <div class="topbar-inst">
                        Ministry of Transport · <span>National Institute of Transport (SONIT)</span>
                    </div>
                </div>

                <div class="topbar-actions">
                    <a href="{{ route('announcements.index') }}" class="notif-btn" title="Notifications & Urgent Alerts">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
                        @if($unreadNotificationCount > 0)
                            <span class="notif-badge">{{ $unreadNotificationCount }}</span>
                        @endif
                    </a>
                    @auth
                        @if(auth()->user()->course)
                            <span class="badge badge-navy mono">{{ auth()->user()->course->code }}</span>
                        @endif
                        <span class="badge badge-black">{{ auth()->user()->roleLabel() }}</span>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-primary btn-sm">Sign In</a>
                    @endauth
                </div>
            </div>
        </header>

        {{-- Main Content Area --}}
        <main class="page-content">
            @if(session('success'))
                <div class="flash flash-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="flash flash-error">{{ session('error') }}</div>
            @endif
            @if(session('info'))
                <div class="flash flash-info">{{ session('info') }}</div>
            @endif
            @if($errors->any())
                <div class="flash flash-error">
                    <ul style="margin:0 0 0 16px;padding:0;">
                        @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>

        {{-- Official NIT Red Bottom Bar (Matches NIT Institutional Brand Strip) --}}
        <footer class="site-footer-bar">
            <div class="flex-between flex-wrap" style="gap:10px;">
                <div>
                    <strong>{{ $siteSettings['org_short_name'] ?? 'SONIT' }}</strong> · {{ $siteSettings['institution_name'] ?? 'National Institute of Transport (NIT)' }} · {{ $siteSettings['address'] ?? 'Mabibo, Dar es Salaam' }}
                </div>
                <div class="mono text-xs">
                    {{ $siteSettings['email'] ?? 'office@sonit.nit.ac.tz' }} | {{ $siteSettings['phone'] ?? '+255 22 240 0148' }} | Welfare: {{ $siteSettings['emergency_phone'] ?? '+255 754 000 911' }}
                </div>
            </div>
        </footer>
    </div>
</div>

<script src="{{ asset('js/sonit.js') }}"></script>
</body>
</html>
