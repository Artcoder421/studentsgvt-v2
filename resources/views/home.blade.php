@extends('layouts.app')

@section('title', ($siteSettings['org_short_name'] ?? 'SONIT') . ' — Digital Student Governance Portal | NIT')

@section('content')
    {{-- Cinematic Hero Section featuring edited NIT Main Gate --}}
    <section class="hero">
        <div class="hero-bg" style="background-image:url('{{ asset('images/campus/hero_gate.jpg') }}');"></div>
        <div class="hero-overlay"></div>
        <div class="container hero-grid">
            <div>
                <div class="hero-eyebrow">{{ $siteSettings['institution_name'] ?? 'National Institute of Transport (NIT)' }} · Est. {{ $siteSettings['founded_year'] ?? '1983' }}</div>
                <h1>{{ $siteSettings['org_name'] ?? 'Students Organization of the National Institute of Transport' }}</h1>
                <p class="hero-lead">{{ $siteSettings['tagline'] ?? 'Empowering Tomorrow\'s Transport, Logistics, Aviation & Engineering Leaders.' }}</p>

                <div class="hero-actions">
                    <a href="{{ route('communities.index') }}" class="btn btn-gold btn-lg">Explore Course Communities</a>
                    <a href="{{ route('elections.index') }}" class="btn btn-ghost-light btn-lg">E-Elections Portal</a>
                    @guest
                        <a href="{{ route('login') }}" class="btn btn-ghost-light btn-lg">Student Sign In</a>
                    @endguest
                </div>

                <div class="hero-metrics">
                    <div class="hero-metric">
                        <strong>{{ $stats['courses'] }}</strong>
                        <span>NIT Programmes</span>
                    </div>
                    <div class="hero-metric">
                        <strong>{{ $stats['communities'] }}</strong>
                        <span>Active Channels</span>
                    </div>
                    <div class="hero-metric">
                        <strong>{{ $stats['venues'] }}</strong>
                        <span>Campus Venues</span>
                    </div>
                    <div class="hero-metric">
                        <strong>{{ $stats['accommodations'] }}</strong>
                        <span>Verified Hostels</span>
                    </div>
                </div>
            </div>

            <div class="hero-card">
                <img src="{{ asset('images/campus/coeato.jpg') }}" alt="NIT Center of Excellence in Aviation and Transport Operations" class="hero-card-photo">
                <div class="flex-between mb-2">
                    <span class="badge badge-gold">Live Governance Hub</span>
                    @if($activeElection && $activeElection->isVotingActive())
                        <span class="badge badge-emerald">Voting Open</span>
                    @endif
                </div>
                <h3 style="margin:0 0 8px;font-size:19px;font-weight:800;color:#FFFFFF;">
                    {{ $siteSettings['motto'] ?? 'Unity, Integrity, and Academic Excellence in Motion.' }}
                </h3>
                <p style="margin:0 0 18px;font-size:13.5px;color:#CBD5E1;">
                    Unified digital governance for Mabibo Campus: course-scoped communities, transparent single-ballot elections, verified accommodation, campus venue booking, and 24/7 welfare support.
                </p>
                <div class="flex-between flex-wrap" style="gap:10px;">
                    <a href="{{ route('help-desk.index') }}" class="btn btn-gold btn-sm">Open Help Desk</a>
                    <a href="{{ route('accommodations.index') }}" class="btn btn-ghost-light btn-sm">Find Accommodation</a>
                </div>
            </div>
        </div>
    </section>

    {{-- Editable Motto, Vision & Mission Pillars --}}
    <section class="section section-alt">
        <div class="container">
            <div class="section-header">
                <div>
                    <span class="kicker">Institutional Identity</span>
                    <h2 class="section-title">Motto, Vision & Mission</h2>
                    <p class="section-subtitle">Guided by transparent student governance and continuously maintained by the SONIT Executive Cabinet.</p>
                </div>
                @auth
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.settings') }}" class="btn btn-outline btn-sm">Edit Identity in Admin</a>
                    @endif
                @endauth
            </div>

            <div class="grid grid-3">
                <article class="card card-hover">
                    <div class="flex-center mb-3">
                        <div class="icon-3d-tile sm"><img src="{{ asset('images/icons/leadership.svg') }}" alt="Motto"></div>
                        <div>
                            <span class="kicker" style="margin:0;">Guiding Principle</span>
                            <h3 style="margin:0;font-size:18px;font-weight:800;color:var(--navy-900);">Our Motto</h3>
                        </div>
                    </div>
                    <p style="font-size:16px;font-weight:700;color:var(--navy-700);margin:0 0 10px;">
                        "{{ $siteSettings['motto'] ?? 'Unity, Integrity, and Academic Excellence in Motion.' }}"
                    </p>
                    <p class="text-sm muted" style="margin:0;">{{ $siteSettings['history'] ?? '' }}</p>
                </article>

                <article class="card card-hover">
                    <div class="flex-center mb-3">
                        <div class="icon-3d-tile sm"><img src="{{ asset('images/icons/security.svg') }}" alt="Vision"></div>
                        <div>
                            <span class="kicker" style="margin:0;">Strategic Horizon</span>
                            <h3 style="margin:0;font-size:18px;font-weight:800;color:var(--navy-900);">Our Vision</h3>
                        </div>
                    </div>
                    <p style="font-size:14.5px;color:var(--ink-700);margin:0;">
                        {{ $siteSettings['vision'] ?? '' }}
                    </p>
                </article>

                <article class="card card-hover">
                    <div class="flex-center mb-3">
                        <div class="icon-3d-tile sm"><img src="{{ asset('images/icons/announcements.jpg') }}" alt="Mission"></div>
                        <div>
                            <span class="kicker" style="margin:0;">Daily Mandate</span>
                            <h3 style="margin:0;font-size:18px;font-weight:800;color:var(--navy-900);">Our Mission</h3>
                        </div>
                    </div>
                    <p style="font-size:14.5px;color:var(--ink-700);margin:0;">
                        {{ $siteSettings['mission'] ?? '' }}
                    </p>
                </article>
            </div>
        </div>
    </section>

    {{-- Executive Leadership Showcase (Edited Cinematic Portraits: Rector, President, Vice President) --}}
    <section class="section">
        <div class="container">
            <div class="section-header">
                <div>
                    <span class="kicker">Leadership & Governance</span>
                    <h2 class="section-title">Institute & SONIT Executive Leadership</h2>
                    <p class="section-subtitle">Working in partnership with National Institute of Transport management to deliver world-class student services.</p>
                </div>
                <a href="{{ route('leadership') }}" class="btn btn-outline">Full Leadership Directory</a>
            </div>

            <div class="grid grid-3">
                @foreach($featuredLeaders as $leader)
                    <article class="portrait-card">
                        <div class="portrait-frame">
                            <img src="{{ asset($leader->photo ?? 'images/portraits/president.jpg') }}" alt="{{ $leader->name }}">
                        </div>
                        <div class="portrait-body">
                            <div class="portrait-role">{{ $leader->position }}</div>
                            <h3 class="portrait-name">{{ $leader->name }}</h3>
                            @if($leader->quote)
                                <p class="portrait-quote">"{{ $leader->quote }}"</p>
                            @endif
                            <p class="text-sm muted" style="margin:0;">{{ \Illuminate\Support\Str::limit($leader->bio, 130) }}</p>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Smart Digital Ecosystem Grid (100% Custom 3D Studio Icons, Zero Emojis) --}}
    <section class="section section-alt">
        <div class="container">
            <div class="section-header">
                <div>
                    <span class="kicker">Unified Campus Platform</span>
                    <h2 class="section-title">Every Service an NIT Student Needs</h2>
                    <p class="section-subtitle">Engineered with strict role-based security, real-time transparency, and mobile-first ergonomics.</p>
                </div>
            </div>

            <div class="grid grid-4">
                <a href="{{ route('communities.index') }}" class="card card-hover" style="display:block;">
                    <div class="icon-3d-tile mb-3"><img src="{{ asset('images/icons/communities.jpg') }}" alt="Communities"></div>
                    <h3 style="margin:0 0 8px;font-size:17px;font-weight:800;color:var(--navy-900);">Course Communities</h3>
                    <p class="text-sm muted" style="margin:0;">Default official channels for all {{ $stats['courses'] }} NIT courses plus CR-created study groups and general campus squares.</p>
                </a>

                <a href="{{ route('elections.index') }}" class="card card-hover" style="display:block;">
                    <div class="icon-3d-tile mb-3"><img src="{{ asset('images/icons/elections.jpg') }}" alt="Elections"></div>
                    <h3 style="margin:0 0 8px;font-size:17px;font-weight:800;color:var(--navy-900);">E-Elections</h3>
                    <p class="text-sm muted" style="margin:0;">Verified single-ballot voting for President & Course Parliament Representatives with live transparent tallying.</p>
                </a>

                <a href="{{ route('accommodations.index') }}" class="card card-hover" style="display:block;">
                    <div class="icon-3d-tile mb-3"><img src="{{ asset('images/icons/accommodation.svg') }}" alt="Accommodation"></div>
                    <h3 style="margin:0 0 8px;font-size:17px;font-weight:800;color:var(--navy-900);">Accommodation</h3>
                    <p class="text-sm muted" style="margin:0;">Browse on-campus hostels and SONIT-verified Mabibo/Ubungo residences with direct placement applications.</p>
                </a>

                <a href="{{ route('help-desk.index') }}" class="card card-hover" style="display:block;">
                    <div class="icon-3d-tile mb-3"><img src="{{ asset('images/icons/helpdesk.jpg') }}" alt="Help Desk"></div>
                    <h3 style="margin:0 0 8px;font-size:17px;font-weight:800;color:var(--navy-900);">Student Help Desk</h3>
                    <p class="text-sm muted" style="margin:0;">Trackable tickets for HESLB loans, SIMS clearance, welfare, IT support, and confidential grievances.</p>
                </a>

                <a href="{{ route('venues.index') }}" class="card card-hover" style="display:block;">
                    <div class="icon-3d-tile mb-3"><img src="{{ asset('images/icons/venues.jpg') }}" alt="Venues"></div>
                    <h3 style="margin:0 0 8px;font-size:17px;font-weight:800;color:var(--navy-900);">Campus Venues</h3>
                    <p class="text-sm muted" style="margin:0;">Explore MPH, CoEATO Auditorium, ICT Labs, and lecture theatres with online reservation approval.</p>
                </a>

                <a href="{{ route('lost-found.index') }}" class="card card-hover" style="display:block;">
                    <div class="icon-3d-tile mb-3"><img src="{{ asset('images/icons/lostfound.svg') }}" alt="Lost and Found"></div>
                    <h3 style="margin:0 0 8px;font-size:17px;font-weight:800;color:var(--navy-900);">Lost & Found</h3>
                    <p class="text-sm muted" style="margin:0;">Report misplaced ID cards, electronics, or documents and verify handover cleanly through SONIT Desk.</p>
                </a>

                <a href="{{ route('sports.index') }}" class="card card-hover" style="display:block;">
                    <div class="icon-3d-tile mb-3"><img src="{{ asset('images/icons/sports.svg') }}" alt="Sports"></div>
                    <h3 style="margin:0 0 8px;font-size:17px;font-weight:800;color:var(--navy-900);">Sports & Athletics</h3>
                    <p class="text-sm muted" style="margin:0;">Join NIT varsity squads, follow Inter-Faculty Transport Cup fixtures, and view match standings.</p>
                </a>

                <a href="{{ route('charity.index') }}" class="card card-hover" style="display:block;">
                    <div class="icon-3d-tile mb-3"><img src="{{ asset('images/icons/charity.svg') }}" alt="Charity"></div>
                    <h3 style="margin:0 0 8px;font-size:17px;font-weight:800;color:var(--navy-900);">Charity & Welfare</h3>
                    <p class="text-sm muted" style="margin:0;">Support student solidarity funds and Mabibo community drives with live verified fundraising totals.</p>
                </a>
            </div>
        </div>
    </section>

    {{-- Real NIT Course Communities Preview --}}
    <section class="section">
        <div class="container">
            <div class="section-header">
                <div>
                    <span class="kicker">Academic Communities</span>
                    <h2 class="section-title">Default Channels for Every NIT Programme</h2>
                    <p class="section-subtitle">Every NIT course automatically holds an official community with a dedicated Class Representative seat. Even before a CR registers, SONIT Admins govern the channel seamlessly.</p>
                </div>
                <a href="{{ route('communities.index') }}" class="btn btn-primary">Browse All {{ $stats['courses'] }} Courses</a>
            </div>

            <div class="grid grid-4">
                @foreach($coursesPreview as $course)
                    @php $comm = $course->defaultCommunity; @endphp
                    <div class="card card-hover" style="display:flex;flex-direction:column;justify-content:space-between;">
                        <div>
                            <div class="flex-between mb-2">
                                <span class="badge badge-navy mono">{{ $course->code }}</span>
                                <span class="badge badge-slate">{{ $course->levelLabel() }}</span>
                            </div>
                            <h3 style="margin:0 0 8px;font-size:15.5px;font-weight:800;color:var(--navy-900);line-height:1.35;">{{ $course->name }}</h3>
                            <p class="text-xs muted" style="margin:0 0 14px;">{{ $course->faculty }}</p>
                        </div>
                        <div style="border-top:1px solid var(--border);padding-top:12px;">
                            @if($comm && $comm->cr)
                                <div class="text-xs" style="color:var(--emerald-700);font-weight:700;margin-bottom:8px;">
                                    CR Assigned: {{ $comm->cr->name }}
                                </div>
                            @else
                                <div class="text-xs" style="color:var(--amber-700);font-weight:700;margin-bottom:8px;">
                                    CR Seat Reserved · Admin Managed
                                </div>
                            @endif
                            @if($comm)
                                <a href="{{ route('communities.show', $comm) }}" class="btn btn-outline btn-sm" style="width:100%;">Enter Channel</a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Active Charity & Countdown Highlight + Latest Announcements --}}
    <section class="section section-alt">
        <div class="container">
            <div class="grid grid-2" style="align-items:start;">
                <div>
                    <div class="flex-between mb-3">
                        <div>
                            <span class="kicker">Campus Broadcasts</span>
                            <h2 class="section-title" style="font-size:26px;">Latest Announcements</h2>
                        </div>
                        <a href="{{ route('announcements.index') }}" class="btn btn-outline btn-sm">View All</a>
                    </div>

                    <div style="display:flex;flex-direction:column;gap:14px;">
                        @foreach($announcements as $item)
                            <a href="{{ route('announcements.show', $item) }}" class="card card-hover" style="padding:18px;display:block;">
                                <div class="flex-between mb-2">
                                    <div class="flex-center">
                                        @if($item->is_urgent)
                                            <span class="badge badge-crimson">Urgent</span>
                                        @endif
                                        <span class="badge badge-navy">{{ ucfirst($item->category) }}</span>
                                    </div>
                                    <span class="text-xs muted">{{ optional($item->published_at)->diffForHumans() }}</span>
                                </div>
                                <h3 style="margin:0 0 6px;font-size:16px;font-weight:800;color:var(--navy-900);">{{ $item->title }}</h3>
                                <p class="text-sm muted" style="margin:0;">{{ \Illuminate\Support\Str::limit($item->excerpt ?? strip_tags($item->content), 120) }}</p>
                            </a>
                        @endforeach
                    </div>
                </div>

                <div>
                    <div class="flex-between mb-3">
                        <div>
                            <span class="kicker">Social Impact & Events</span>
                            <h2 class="section-title" style="font-size:26px;">Featured Initiative</h2>
                        </div>
                        <a href="{{ route('charity.index') }}" class="btn btn-outline btn-sm">All Charity Drives</a>
                    </div>

                    @if($featuredCharity)
                        <div class="card" style="overflow:hidden;padding:0;">
                            <img src="{{ asset($featuredCharity->image ?? 'images/campus/students.jpg') }}" alt="{{ $featuredCharity->title }}" style="width:100%;height:220px;object-fit:cover;">
                            <div style="padding:22px;">
                                <div class="flex-between mb-2">
                                    <span class="badge badge-gold">Active Charity Drive</span>
                                    <span class="mono text-xs" style="font-weight:700;color:var(--navy-700);">{{ $featuredCharity->progressPercent() }}% Funded</span>
                                </div>
                                <h3 style="margin:0 0 8px;font-size:19px;font-weight:800;color:var(--navy-900);">{{ $featuredCharity->title }}</h3>
                                <p class="text-sm muted" style="margin:0 0 14px;">{{ $featuredCharity->description }}</p>

                                <div class="progress-track mb-2">
                                    <div class="progress-fill" style="width:{{ $featuredCharity->progressPercent() }}%;"></div>
                                </div>
                                <div class="flex-between text-sm mb-3">
                                    <strong class="mono" style="color:var(--navy-900);">TZS {{ number_format($featuredCharity->raised_amount) }}</strong>
                                    <span class="muted">Goal: TZS {{ number_format($featuredCharity->target_amount) }}</span>
                                </div>

                                <div class="flex-between flex-wrap" style="gap:10px;">
                                    <a href="{{ route('charity.index') }}" class="btn btn-primary">Support Campaign</a>
                                    <a href="{{ route('events.index') }}" class="btn btn-outline">Upcoming Events ({{ $upcomingEvents->count() }})</a>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    {{-- Institutional CTA Banner --}}
    <section class="section section-navy">
        <div class="container flex-between flex-wrap" style="gap:24px;">
            <div style="max-width:680px;">
                <span class="kicker">Built for Transparency & Scale</span>
                <h2 class="section-title">Ready to Experience Next-Generation Student Governance?</h2>
                <p class="section-subtitle">Join your official NIT course community, participate in verified electronic elections, or explore the Super Admin Role Governance Console.</p>
            </div>
            <div class="flex-center flex-wrap">
                @auth
                    <a href="{{ route('dashboard') }}" class="btn btn-gold btn-lg">Open My Portal</a>
                @else
                    <a href="{{ route('register') }}" class="btn btn-gold btn-lg">Register as NIT Student</a>
                    <a href="{{ route('login') }}" class="btn btn-ghost-light btn-lg">Sign In</a>
                @endauth
            </div>
        </div>
    </section>
@endsection
