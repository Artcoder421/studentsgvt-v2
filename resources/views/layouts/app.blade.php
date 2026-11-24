<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', ($siteSettings['org_short_name'] ?? 'SONIT') . ' — National Institute of Transport')</title>
    <meta name="description" content="@yield('meta_description', $siteSettings['tagline'] ?? 'Official Digital Governance & Student Services Portal for the National Institute of Transport (NIT)')">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@500;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/sonit.css') }}">
</head>
<body>
    {{-- Top-of-Page Countdown Timer Offer / Initiative Strip --}}
    @if($liveTimers->isNotEmpty())
        @php $topTimer = $liveTimers->first(); @endphp
        <div class="timer-strip" role="region" aria-label="Time-sensitive student initiative">
            <div class="container timer-strip-inner">
                <div class="timer-strip-meta">
                    <span class="timer-cat {{ $topTimer->category === 'urgent' ? 'is-urgent' : '' }}">{{ strtoupper($topTimer->category) }}</span>
                    <div style="min-width:0;">
                        <span class="timer-title">{{ $topTimer->title }}</span>
                        @if($topTimer->progressPercent() !== null)
                            <span class="timer-sub mono">TZS {{ number_format($topTimer->raised_amount) }} / {{ number_format($topTimer->target_amount) }} ({{ $topTimer->progressPercent() }}%)</span>
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
                        <a href="{{ url($topTimer->cta_url) }}" class="timer-cta">
                            {{ $topTimer->cta_label }}
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- Urgent Broadcast Ribbon --}}
    @if($urgentAlerts->isNotEmpty())
        @php $latestUrgent = $urgentAlerts->first(); @endphp
        <div class="urgent-ribbon">
            <div class="container urgent-ribbon-inner">
                <div class="flex-center" style="min-width:0;">
                    <span class="urgent-tag">Urgent Notice</span>
                    <a href="{{ route('announcements.show', $latestUrgent) }}" style="color:#991B1B;font-weight:700;">
                        {{ $latestUrgent->title }}
                    </a>
                </div>
                <a href="{{ route('announcements.index', ['urgent' => 1]) }}" style="color:#B91C1C;font-weight:700;font-size:12.5px;white-space:nowrap;">
                    View all urgent alerts ({{ $urgentAlerts->count() }})
                </a>
            </div>
        </div>
    @endif

    {{-- Main Sticky Navigation --}}
    <header class="site-header">
        <div class="container nav-inner">
            <a href="{{ route('home') }}" class="brand">
                <img src="{{ asset('images/icons/sonit_crest.svg') }}" alt="SONIT Crest" class="brand-crest">
                <div class="brand-text">
                    <strong>{{ $siteSettings['org_short_name'] ?? 'SONIT' }}</strong>
                    <span>NIT Student Government</span>
                </div>
            </a>

            <ul class="nav-links" id="primaryNavLinks">
                <li><a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}">Home</a></li>
                <li><a href="{{ route('communities.index') }}" class="nav-link {{ request()->routeIs('communities.*') ? 'active' : '' }}">Communities</a></li>
                <li><a href="{{ route('elections.index') }}" class="nav-link {{ request()->routeIs('elections.*') ? 'active' : '' }}">Elections</a></li>
                <li class="nav-dropdown">
                    <a href="{{ route('venues.index') }}" class="nav-link {{ request()->routeIs('venues.*','accommodations.*','help-desk.*','lost-found.*') ? 'active' : '' }}">
                        Student Services
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="m6 9 6 6 6-6"/></svg>
                    </a>
                    <div class="nav-dropdown-menu">
                        <a href="{{ route('accommodations.index') }}" class="nav-dropdown-item">
                            <img src="{{ asset('images/icons/accommodation.svg') }}" alt="Accommodation"> Accommodation Services
                        </a>
                        <a href="{{ route('help-desk.index') }}" class="nav-dropdown-item">
                            <img src="{{ asset('images/icons/helpdesk.jpg') }}" alt="Help Desk"> Student Help Desk
                        </a>
                        <a href="{{ route('venues.index') }}" class="nav-dropdown-item">
                            <img src="{{ asset('images/icons/venues.jpg') }}" alt="Venues"> Campus Venues & Booking
                        </a>
                        <a href="{{ route('lost-found.index') }}" class="nav-dropdown-item">
                            <img src="{{ asset('images/icons/lostfound.svg') }}" alt="Lost & Found"> Lost & Found Desk
                        </a>
                    </div>
                </li>
                <li class="nav-dropdown">
                    <a href="{{ route('events.index') }}" class="nav-link {{ request()->routeIs('events.*','sports.*','charity.*','leadership') ? 'active' : '' }}">
                        Campus Life
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="m6 9 6 6 6-6"/></svg>
                    </a>
                    <div class="nav-dropdown-menu">
                        <a href="{{ route('events.index') }}" class="nav-dropdown-item">
                            <img src="{{ asset('images/icons/events.svg') }}" alt="Events"> Events & Calendar
                        </a>
                        <a href="{{ route('sports.index') }}" class="nav-dropdown-item">
                            <img src="{{ asset('images/icons/sports.svg') }}" alt="Sports"> Sports & Recreation
                        </a>
                        <a href="{{ route('charity.index') }}" class="nav-dropdown-item">
                            <img src="{{ asset('images/icons/charity.svg') }}" alt="Charity"> Charity & Welfare Drives
                        </a>
                        <a href="{{ route('leadership') }}" class="nav-dropdown-item">
                            <img src="{{ asset('images/icons/leadership.svg') }}" alt="Leadership"> Leadership Directory
                        </a>
                    </div>
                </li>
                <li><a href="{{ route('announcements.index') }}" class="nav-link {{ request()->routeIs('announcements.*') ? 'active' : '' }}">Announcements</a></li>
            </ul>

            <div class="nav-actions">
                <a href="{{ route('announcements.index') }}" class="notif-btn" title="Notifications & Urgent Announcements">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
                    @if($unreadNotificationCount > 0)
                        <span class="notif-badge" title="{{ $unreadUrgentCount }} urgent">{{ $unreadNotificationCount }}</span>
                    @endif
                </a>

                @auth
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-gold btn-sm">Admin Console</a>
                    @endif
                    <a href="{{ route('dashboard') }}" class="btn btn-outline btn-sm">My Portal</a>
                    <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-sm">Sign Out</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="btn btn-outline btn-sm">Sign In</a>
                    <a href="{{ route('register') }}" class="btn btn-primary btn-sm">Student Register</a>
                @endauth

                <button type="button" class="mobile-menu-btn" id="mobileMenuBtn" aria-label="Toggle Menu">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
                </button>
            </div>
        </div>
    </header>

    {{-- Global Flash Feedback --}}
    @if(session('success') || session('error') || session('info') || $errors->any())
        <div class="container" style="margin-top:18px;">
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
                    <div>
                        <strong>Please check the form fields:</strong>
                        <ul style="margin:6px 0 0 18px;padding:0;">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif
        </div>
    @endif

    <main>
        @yield('content')
    </main>

    {{-- Executive Institutional Footer --}}
    <footer class="site-footer">
        <div class="container">
            <div class="footer-grid">
                <div>
                    <div class="flex-center mb-2">
                        <img src="{{ asset('images/icons/sonit_crest.svg') }}" alt="SONIT Crest" style="width:40px;height:40px;border-radius:10px;">
                        <div>
                            <strong style="color:#FFFFFF;font-size:16px;display:block;">{{ $siteSettings['org_short_name'] ?? 'SONIT' }}</strong>
                            <span style="font-size:11.5px;color:#94A3B8;">{{ $siteSettings['institution_name'] ?? 'National Institute of Transport' }}</span>
                        </div>
                    </div>
                    <p style="font-size:13.5px;color:#94A3B8;margin:12px 0;">
                        {{ $siteSettings['motto'] ?? 'Unity, Integrity, and Academic Excellence in Motion.' }}
                    </p>
                    <p style="font-size:12.5px;color:#94A3B8;">
                        {{ $siteSettings['address'] ?? 'Mabibo Campus, Dar es Salaam, Tanzania' }}
                    </p>
                </div>

                <div>
                    <h4 class="footer-heading">Governance & Channels</h4>
                    <ul class="footer-links">
                        <li><a href="{{ route('communities.index') }}">Course Communities & Channels</a></li>
                        <li><a href="{{ route('elections.index') }}">E-Elections & Voting</a></li>
                        <li><a href="{{ route('elections.results') }}">Election Results</a></li>
                        <li><a href="{{ route('leadership') }}">Executive Leadership</a></li>
                        <li><a href="{{ route('announcements.index') }}">Official Announcements</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="footer-heading">Student Services</h4>
                    <ul class="footer-links">
                        <li><a href="{{ route('accommodations.index') }}">Accommodation & Hostels</a></li>
                        <li><a href="{{ route('help-desk.index') }}">Student Help Desk</a></li>
                        <li><a href="{{ route('venues.index') }}">Campus Venues & Booking</a></li>
                        <li><a href="{{ route('lost-found.index') }}">Lost & Found Desk</a></li>
                        <li><a href="{{ route('sports.index') }}">Sports & Recreation</a></li>
                        <li><a href="{{ route('charity.index') }}">Charity & Welfare Drives</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="footer-heading">Official Secretariat</h4>
                    <ul class="footer-links">
                        <li>Email: <strong style="color:#FFFFFF;">{{ $siteSettings['email'] ?? 'office@sonit.nit.ac.tz' }}</strong></li>
                        <li>Office: <strong style="color:#FFFFFF;">{{ $siteSettings['phone'] ?? '+255 22 240 0148' }}</strong></li>
                        <li>24/7 Welfare Hotline: <strong style="color:var(--gold-400);">{{ $siteSettings['emergency_phone'] ?? '+255 754 000 911' }}</strong></li>
                        <li style="color:#94A3B8;font-size:12.5px;">{{ $siteSettings['office_hours'] ?? 'Mon–Fri 08:00–17:00 EAT' }}</li>
                    </ul>
                </div>
            </div>

            <div class="footer-bottom">
                <span>&copy; {{ date('Y') }} {{ $siteSettings['org_name'] ?? 'Students Organization of the National Institute of Transport' }}. All rights reserved.</span>
                <span>Built on Laravel · Role-Based Governance · Mabibo, Dar es Salaam</span>
            </div>
        </div>
    </footer>

    <script src="{{ asset('js/sonit.js') }}"></script>
</body>
</html>
