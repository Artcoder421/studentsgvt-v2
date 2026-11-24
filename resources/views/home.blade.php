@extends('layouts.app')

@section('title', ($siteSettings['org_short_name'] ?? 'SONIT') . ' — National Institute of Transport Portal')

@section('content')
    @php
        $president = $featuredLeaders->first(fn ($l) => $l->category === 'executive' && str_contains($l->position, 'President'))
            ?? $featuredLeaders->first();
    @endphp

    {{-- Compact Executive Hero: NIT Main Gate + President Portrait + Editable Motto --}}
    <div style="position:relative;background:var(--nit-black);color:#FFFFFF;border-top:4px solid var(--nit-blue);border-bottom:3px solid var(--nit-red);overflow:hidden;margin-bottom:18px;">
        <div style="position:absolute;inset:0;background-image:url('{{ asset('images/campus/hero_gate.jpg') }}');background-size:cover;background-position:center;opacity:0.22;"></div>
        <div style="position:relative;z-index:2;padding:22px 24px;display:grid;grid-template-columns:136px minmax(0,1fr) 260px;gap:20px;align-items:center;" class="home-hero-responsive">
            <img src="{{ asset($president?->photo ?? 'images/portraits/president.jpg') }}" alt="{{ $president?->name ?? 'SONIT President' }}" class="president-banner-photo" style="width:136px;height:152px;">

            <div>
                <div class="flex-center flex-wrap mb-1" style="gap:6px;">
                    <span class="badge" style="background:var(--nit-blue);color:#fff;">{{ $siteSettings['org_short_name'] ?? 'SONIT' }} · NIT</span>
                    <span class="badge badge-crimson">{{ $president?->position ?? 'SONIT President' }}</span>
                </div>
                <h1 style="margin:4px 0 6px;font-size:clamp(20px,2.4vw,27px);font-weight:800;line-height:1.15;color:#FFFFFF;">
                    "{{ $siteSettings['motto'] ?? 'Unity, Integrity, and Academic Excellence in Motion.' }}"
                </h1>
                <p style="margin:0 0 12px;font-size:13px;color:#D4DCE6;">
                    <strong>{{ $president?->name ?? 'Hon. Stavius Alkadi' }}</strong> — {{ $siteSettings['tagline'] ?? 'Empowering Tomorrow\'s Transport, Logistics, Aviation & Engineering Leaders.' }}
                </p>
                <div class="flex-center flex-wrap" style="gap:8px;">
                    <a href="{{ route('communities.index') }}" class="btn btn-primary btn-sm">Course Communities ({{ $stats['courses'] }})</a>
                    <a href="{{ route('elections.index') }}" class="btn btn-danger btn-sm">E-Elections Portal</a>
                    <a href="{{ route('help-desk.index') }}" class="btn btn-ghost-light btn-sm">Student Help Desk</a>
                    @guest
                        <a href="{{ route('login') }}" class="btn btn-ghost-light btn-sm">Sign In</a>
                    @else
                        <a href="{{ route('dashboard') }}" class="btn btn-ghost-light btn-sm">My Dashboard</a>
                    @endguest
                </div>
            </div>

            <div style="background:rgba(17,19,23,0.82);border:1px solid rgba(0,149,218,0.45);padding:12px 14px;">
                <div class="text-xs" style="color:var(--nit-blue);font-weight:800;text-transform:uppercase;letter-spacing:0.08em;margin-bottom:8px;">Campus Live Snapshot</div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                    <div style="background:rgba(255,255,255,0.06);padding:8px;">
                        <strong class="mono" style="font-size:18px;color:#fff;display:block;">{{ $stats['courses'] }}</strong>
                        <span class="text-xs" style="color:#9BA5B4;">NIT Courses</span>
                    </div>
                    <div style="background:rgba(255,255,255,0.06);padding:8px;">
                        <strong class="mono" style="font-size:18px;color:#fff;display:block;">{{ $stats['communities'] }}</strong>
                        <span class="text-xs" style="color:#9BA5B4;">Channels</span>
                    </div>
                    <div style="background:rgba(255,255,255,0.06);padding:8px;">
                        <strong class="mono" style="font-size:18px;color:#fff;display:block;">{{ $stats['venues'] }}</strong>
                        <span class="text-xs" style="color:#9BA5B4;">Venues</span>
                    </div>
                    <div style="background:rgba(255,255,255,0.06);padding:8px;">
                        <strong class="mono" style="font-size:18px;color:#fff;display:block;">{{ $stats['accommodations'] }}</strong>
                        <span class="text-xs" style="color:#9BA5B4;">Hostels</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Active Charity / Tour Reminder Spotlight (What, Where, When, Account & Contacts — No in-system payment) --}}
    @if($featuredCharity)
        <div class="card card-accent-red mb-4">
            <div class="flex-between flex-wrap mb-2">
                <div class="flex-center">
                    <span class="badge badge-crimson">Charity & Tour Reminder</span>
                    <strong style="font-size:16px;color:var(--nit-black);">{{ $featuredCharity->title }}</strong>
                </div>
                <div class="flex-center">
                    @if($featuredCharity->ends_at)
                        <div class="countdown-clock" data-countdown="{{ $featuredCharity->ends_at->toIso8601String() }}">
                            <span class="badge badge-black mono"><span data-cd-days>00</span>d : <span data-cd-hours>00</span>h : <span data-cd-mins>00</span>m : <span data-cd-secs>00</span>s</span>
                        </div>
                    @endif
                    <a href="{{ route('charity.index') }}" class="btn btn-outline btn-sm">All Charity & Tours</a>
                </div>
            </div>

            <div class="notice-grid">
                <div class="notice-box">
                    <div class="notice-label">What We Plan To Do</div>
                    <div class="text-xs" style="color:var(--ink-900);white-space:pre-line;">{{ $featuredCharity->planned_activities ?? $featuredCharity->description }}</div>
                </div>
                <div class="notice-box">
                    <div class="notice-label">Where & When</div>
                    <div class="text-xs" style="color:var(--ink-900);"><strong>When:</strong> {{ $featuredCharity->event_when ?? optional($featuredCharity->ends_at)->format('d M Y · H:i') }}</div>
                    <div class="text-xs mt-1" style="color:var(--ink-900);"><strong>Where:</strong> {{ $featuredCharity->event_where ?? $featuredCharity->beneficiary }}</div>
                </div>
                <div class="notice-box is-account">
                    <div class="notice-label">Official Contribution Account Number</div>
                    <div class="flex-between flex-wrap" style="gap:6px;">
                        <strong class="mono text-xs" style="color:var(--nit-black);">{{ $featuredCharity->account_number ?? $featuredCharity->payment_instructions }}</strong>
                        @if($featuredCharity->account_number)
                            <button type="button" class="btn btn-danger btn-sm" style="padding:2px 8px;font-size:11px;" data-copy-text="{{ $featuredCharity->account_number }}">Copy Account</button>
                        @endif
                    </div>
                </div>
                <div class="notice-box">
                    <div class="notice-label">Coordinator Contacts</div>
                    <div class="text-xs mono" style="color:var(--nit-black);font-weight:700;">{{ $featuredCharity->contact_info ?? ($siteSettings['phone'] ?? '+255 22 240 0148') }}</div>
                </div>
            </div>
        </div>
    @endif

    {{-- Compact Tabbed Hub (Eliminates Long Vertical Scrolling!) --}}
    <div class="card card-accent-blue" data-tab-group>
        <div class="flex-between flex-wrap mb-2">
            <div class="tab-bar" style="margin-bottom:0;border-bottom:none;">
                <button type="button" class="tab-btn active" data-tab-target="announcements">Latest Announcements ({{ $announcements->count() }})</button>
                <button type="button" class="tab-btn" data-tab-target="leadership">Executive Leadership</button>
                <button type="button" class="tab-btn" data-tab-target="identity">Motto, Vision & Mission</button>
                <button type="button" class="tab-btn" data-tab-target="courses">NIT Course Channels ({{ $stats['courses'] }})</button>
            </div>
            <div class="flex-center">
                @if($activeElection && $activeElection->isVotingActive())
                    <a href="{{ route('elections.index') }}" class="btn btn-danger btn-sm">Voting Open Now</a>
                @endif
            </div>
        </div>
        <div style="border-top:2px solid var(--border);padding-top:14px;">
            {{-- Tab 1: Latest Announcements & Quick Services --}}
            <div class="tab-panel active" data-tab-panel="announcements">
                <div class="grid grid-2">
                    <div style="display:flex;flex-direction:column;gap:10px;">
                        @foreach($announcements as $item)
                            <a href="{{ route('announcements.show', $item) }}" style="padding:12px 14px;border:1px solid var(--border);border-left:3px solid {{ $item->is_urgent ? 'var(--nit-red)' : 'var(--nit-blue)' }};background:#fff;display:block;">
                                <div class="flex-between mb-1">
                                    <div class="flex-center">
                                        @if($item->is_urgent)<span class="badge badge-crimson">Urgent</span>@endif
                                        <span class="badge badge-navy">{{ ucfirst($item->category) }}</span>
                                    </div>
                                    <span class="text-xs muted">{{ optional($item->published_at)->diffForHumans() }}</span>
                                </div>
                                <strong style="color:var(--nit-black);font-size:14px;display:block;">{{ $item->title }}</strong>
                                <span class="text-xs muted">{{ \Illuminate\Support\Str::limit($item->excerpt ?? strip_tags($item->content), 100) }}</span>
                            </a>
                        @endforeach
                        <a href="{{ route('announcements.index') }}" class="btn btn-outline btn-sm" style="align-self:flex-start;">Browse All Announcements</a>
                    </div>

                    <div>
                        <div class="kicker">Quick Access Student Services</div>
                        <div class="grid grid-2" style="gap:10px;">
                            <a href="{{ route('accommodations.index') }}" class="card card-hover" style="padding:12px;display:flex;align-items:center;gap:10px;">
                                <div class="icon-3d-tile sm"><img src="{{ asset('images/icons/accommodation.svg') }}" alt=""></div>
                                <div><strong style="color:var(--nit-black);font-size:13px;display:block;">Accommodation</strong><span class="text-xs muted">Hostels & Mabibo</span></div>
                            </a>
                            <a href="{{ route('venues.index') }}" class="card card-hover" style="padding:12px;display:flex;align-items:center;gap:10px;">
                                <div class="icon-3d-tile sm"><img src="{{ asset('images/icons/venues.jpg') }}" alt=""></div>
                                <div><strong style="color:var(--nit-black);font-size:13px;display:block;">Campus Venues</strong><span class="text-xs muted">Book MPH & Labs</span></div>
                            </a>
                            <a href="{{ route('help-desk.index') }}" class="card card-hover" style="padding:12px;display:flex;align-items:center;gap:10px;">
                                <div class="icon-3d-tile sm"><img src="{{ asset('images/icons/helpdesk.jpg') }}" alt=""></div>
                                <div><strong style="color:var(--nit-black);font-size:13px;display:block;">Help Desk</strong><span class="text-xs muted">HESLB, SIMS, Welfare</span></div>
                            </a>
                            <a href="{{ route('lost-found.index') }}" class="card card-hover" style="padding:12px;display:flex;align-items:center;gap:10px;">
                                <div class="icon-3d-tile sm"><img src="{{ asset('images/icons/lostfound.svg') }}" alt=""></div>
                                <div><strong style="color:var(--nit-black);font-size:13px;display:block;">Lost & Found</strong><span class="text-xs muted">IDs & Campus Items</span></div>
                            </a>
                            <a href="{{ route('sports.index') }}" class="card card-hover" style="padding:12px;display:flex;align-items:center;gap:10px;">
                                <div class="icon-3d-tile sm"><img src="{{ asset('images/icons/sports.svg') }}" alt=""></div>
                                <div><strong style="color:var(--nit-black);font-size:13px;display:block;">Sports & Clubs</strong><span class="text-xs muted">Fixtures & Trials</span></div>
                            </a>
                            <a href="{{ route('events.index') }}" class="card card-hover" style="padding:12px;display:flex;align-items:center;gap:10px;">
                                <div class="icon-3d-tile sm"><img src="{{ asset('images/icons/events.svg') }}" alt=""></div>
                                <div><strong style="color:var(--nit-black);font-size:13px;display:block;">Events Calendar</strong><span class="text-xs muted">{{ $upcomingEvents->count() }} Upcoming</span></div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tab 2: Official Leadership Portraits (Rector, President, Vice President) --}}
            <div class="tab-panel" data-tab-panel="leadership">
                <div class="grid grid-3">
                    @foreach($featuredLeaders as $leader)
                        <article class="portrait-card">
                            <div class="portrait-frame">
                                <img src="{{ asset($leader->photo ?? 'images/portraits/president.jpg') }}" alt="{{ $leader->name }}">
                            </div>
                            <div class="portrait-body">
                                <div class="portrait-role">{{ $leader->position }}</div>
                                <h3 class="portrait-name">{{ $leader->name }}</h3>
                                @if($leader->quote)<p class="portrait-quote">"{{ $leader->quote }}"</p>@endif
                                <p class="text-xs muted" style="margin:0;">{{ \Illuminate\Support\Str::limit($leader->bio, 110) }}</p>
                            </div>
                        </article>
                    @endforeach
                </div>
                <div class="mt-3"><a href="{{ route('leadership') }}" class="btn btn-outline btn-sm">View Full Cabinet & Leadership Directory</a></div>
            </div>

            {{-- Tab 3: Editable Motto, Vision & Mission --}}
            <div class="tab-panel" data-tab-panel="identity">
                <div class="grid grid-3">
                    <div class="card card-accent-red">
                        <span class="kicker">Motto</span>
                        <h3 style="margin:0 0 8px;font-size:16px;color:var(--nit-black);">"{{ $siteSettings['motto'] ?? '' }}"</h3>
                        <p class="text-xs muted" style="margin:0;">{{ $siteSettings['history'] ?? '' }}</p>
                    </div>
                    <div class="card card-accent-blue">
                        <span class="kicker">Vision</span>
                        <p class="text-sm" style="margin:0;color:var(--ink-700);">{{ $siteSettings['vision'] ?? '' }}</p>
                    </div>
                    <div class="card card-accent-black">
                        <span class="kicker">Mission</span>
                        <p class="text-sm" style="margin:0;color:var(--ink-700);">{{ $siteSettings['mission'] ?? '' }}</p>
                    </div>
                </div>
            </div>

            {{-- Tab 4: NIT Programmes / My Course Channel --}}
            <div class="tab-panel" data-tab-panel="courses">
                @auth
                    <div class="grid grid-4">
                        @foreach($coursesPreview as $course)
                            @php $comm = $course->defaultCommunity; @endphp
                            <div class="card" style="padding:12px;">
                                <div class="flex-between mb-1">
                                    <span class="badge badge-black mono">{{ $course->code }}</span>
                                    <span class="text-xs muted">{{ $course->levelLabel() }}</span>
                                </div>
                                <strong style="font-size:13px;color:var(--nit-black);display:block;margin-bottom:6px;">{{ \Illuminate\Support\Str::limit($course->name, 52) }}</strong>
                                <div class="text-xs mb-2" style="color:{{ ($comm && $comm->cr) ? 'var(--emerald-700)' : 'var(--nit-red-dark)' }};font-weight:700;">
                                    {{ ($comm && $comm->cr) ? 'CR: '.$comm->cr->name : 'CR Vacant · Admin Managed' }}
                                </div>
                                @if($comm)
                                    <a href="{{ route('communities.show', $comm) }}" class="btn btn-primary btn-sm" style="width:100%;">Open Channel</a>
                                @endif
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-3"><a href="{{ route('communities.index') }}" class="btn btn-primary btn-sm">Open My Course Communities</a></div>
                @else
                    <div class="flash flash-info mb-3">
                        <span>Course communities are private to enrolled NIT students. Sign in to automatically enter your course's official channel.</span>
                        <a href="{{ route('login') }}" class="btn btn-primary btn-sm">Sign In</a>
                    </div>
                    <div class="grid grid-4">
                        @foreach($coursesPreview as $course)
                            <div class="card" style="padding:12px;">
                                <div class="flex-between mb-1">
                                    <span class="badge badge-black mono">{{ $course->code }}</span>
                                    <span class="text-xs muted">{{ $course->levelLabel() }}</span>
                                </div>
                                <strong style="font-size:13px;color:var(--nit-black);display:block;margin-bottom:4px;">{{ \Illuminate\Support\Str::limit($course->name, 52) }}</strong>
                                <div class="text-xs muted">{{ $course->faculty }}</div>
                            </div>
                        @endforeach
                    </div>
                @endauth
            </div>
        </div>
    </div>

    <style>
        @media (max-width: 960px) {
            .home-hero-responsive { grid-template-columns: 1fr !important; }
        }
    </style>
@endsection
