@extends('layouts.app')

@section('title', 'Course Communities & Campus Channels — SONIT')

@section('content')
<div class="page-banner">
    <div class="container page-banner-inner">
        <div class="page-banner-left">
            <div class="icon-3d-tile lg"><img src="{{ asset('images/icons/communities.jpg') }}" alt="Communities"></div>
            <div>
                <h1>NIT Course Communities & Channels</h1>
                <p>Default official communities for every NIT course with dedicated CR seats (Admin-governed even while vacant), CR-led study groups, and general campus squares.</p>
            </div>
        </div>
        @auth
            @if(auth()->user()->isAdmin() || auth()->user()->isClassRep())
                <a href="#createCommunityCard" class="btn btn-gold">Create Community Channel</a>
            @endif
        @endauth
    </div>
</div>

<section class="section section-tight">
    <div class="container">
        {{-- Search & Faculty Filter --}}
        <form method="GET" action="{{ route('communities.index') }}" class="card mb-4" style="padding:16px;">
            <div class="grid grid-3" style="gap:12px;align-items:end;">
                <div class="form-group" style="margin:0;">
                    <label class="form-label">Search Course or Channel</label>
                    <input type="text" name="q" value="{{ $search }}" class="form-control" placeholder="e.g. BLTM, BIT, Aviation, Careers...">
                </div>
                <div class="form-group" style="margin:0;">
                    <label class="form-label">Filter by NIT Faculty</label>
                    <select name="faculty" class="form-select">
                        <option value="">All Faculties</option>
                        @foreach($faculties as $fac)
                            <option value="{{ $fac }}" @selected($faculty === $fac)>{{ $fac }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex-center">
                    <button type="submit" class="btn btn-primary" style="flex:1;">Filter Channels</button>
                    <a href="{{ route('communities.index') }}" class="btn btn-outline">Reset</a>
                </div>
            </div>
        </form>

        {{-- General Campus Channels --}}
        <div class="mb-4">
            <span class="kicker">Open To All NIT Students</span>
            <h2 class="section-title mb-3" style="font-size:24px;">General Campus Channels</h2>
            <div class="grid grid-3">
                @foreach($generalChannels as $channel)
                    <div class="card card-hover" style="display:flex;flex-direction:column;justify-content:space-between;">
                        <div>
                            <div class="flex-between mb-2">
                                <span class="badge badge-gold">General Square</span>
                                <span class="text-xs muted mono">{{ $channel->members_count }} members</span>
                            </div>
                            <h3 style="margin:0 0 6px;font-size:17px;font-weight:800;color:var(--navy-900);">{{ $channel->name }}</h3>
                            <p class="text-sm muted" style="margin:0 0 14px;">{{ $channel->description }}</p>
                        </div>
                        <div class="flex-between" style="border-top:1px solid var(--border);padding-top:12px;">
                            <span class="text-xs muted">{{ $channel->messages_count }} messages</span>
                            <a href="{{ route('communities.show', $channel) }}" class="btn btn-primary btn-sm">Open Channel</a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Default Official Course Communities --}}
        <div class="mb-4" style="padding-top:18px;">
            <span class="kicker">Permanent Programme Channels ({{ $defaultCourseCommunities->count() }})</span>
            <h2 class="section-title mb-2" style="font-size:24px;">Official NIT Course Communities</h2>
            <p class="text-sm muted mb-3">Every course maintains an official community by default. Where a Class Representative is not yet registered, the CR seat remains reserved and SONIT Admins retain full governance control.</p>

            <div class="grid grid-3">
                @foreach($defaultCourseCommunities as $comm)
                    <div class="card card-hover" style="display:flex;flex-direction:column;justify-content:space-between;">
                        <div>
                            <div class="flex-between mb-2">
                                <span class="badge badge-navy mono">{{ $comm->course?->code }}</span>
                                <span class="badge badge-slate">{{ $comm->course?->levelLabel() }}</span>
                            </div>
                            <h3 style="margin:0 0 4px;font-size:16.5px;font-weight:800;color:var(--navy-900);">{{ $comm->name }}</h3>
                            <div class="text-xs muted mb-2">{{ $comm->course?->name }}</div>
                            <p class="text-xs muted" style="margin:0 0 12px;">{{ \Illuminate\Support\Str::limit($comm->description, 115) }}</p>
                        </div>

                        <div style="border-top:1px solid var(--border);padding-top:12px;">
                            <div class="flex-between mb-2">
                                @if($comm->cr)
                                    <span class="badge badge-emerald">CR: {{ $comm->cr->name }}</span>
                                @else
                                    <span class="badge badge-amber">CR Vacant · Admin Controlled</span>
                                @endif
                                <span class="text-xs muted mono">{{ $comm->members_count }} mbrs</span>
                            </div>
                            <a href="{{ route('communities.show', $comm) }}" class="btn btn-outline btn-sm" style="width:100%;">Enter Course Channel</a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- CR-Created Course Sub-Communities --}}
        @if($courseSubGroups->isNotEmpty())
            <div class="mb-4" style="padding-top:18px;">
                <span class="kicker">Specialized Course Study Groups</span>
                <h2 class="section-title mb-3" style="font-size:24px;">Course Sub-Communities (CR & Admin Led)</h2>
                <div class="grid grid-3">
                    @foreach($courseSubGroups as $group)
                        <div class="card card-hover" style="display:flex;flex-direction:column;justify-content:space-between;">
                            <div>
                                <div class="flex-between mb-2">
                                    <span class="badge badge-navy mono">{{ $group->course?->code }} Sub-Group</span>
                                    <span class="text-xs muted">{{ $group->members_count }} members</span>
                                </div>
                                <h3 style="margin:0 0 6px;font-size:16.5px;font-weight:800;color:var(--navy-900);">{{ $group->name }}</h3>
                                <p class="text-sm muted" style="margin:0 0 12px;">{{ $group->description }}</p>
                            </div>
                            <div class="flex-between" style="border-top:1px solid var(--border);padding-top:12px;">
                                <span class="text-xs muted">Created by {{ $group->creator?->name ?? 'CR' }}</span>
                                <a href="{{ route('communities.show', $group) }}" class="btn btn-primary btn-sm">Open Group</a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Role-Scoped Create Community Form (Only Class Reps for own course & Admins) --}}
        @auth
            @if(auth()->user()->isAdmin() || auth()->user()->isClassRep())
                <div class="card mt-4" id="createCommunityCard" style="border-top:3px solid var(--gold-500);">
                    <div class="flex-between flex-wrap mb-3">
                        <div>
                            <span class="kicker">Role-Scoped Governance</span>
                            <h2 style="margin:0;font-size:21px;font-weight:800;color:var(--navy-900);">Create a New Community Channel</h2>
                        </div>
                        <span class="badge badge-gold">
                            {{ auth()->user()->isAdmin() ? 'Admin Scope: All NIT Courses & General' : 'CR Scope: Restricted strictly to ' . (auth()->user()->course?->code ?? 'Your Course') }}
                        </span>
                    </div>

                    <form method="POST" action="{{ route('communities.store') }}">
                        @csrf
                        <div class="grid grid-2">
                            <div class="form-group">
                                <label class="form-label">Community / Channel Name</label>
                                <input type="text" name="name" class="form-control" placeholder="e.g. BLTM Year 3 Port Operations Group" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Channel Type</label>
                                <select name="type" class="form-select" required>
                                    <option value="course_group">Course Sub-Community</option>
                                    @if(auth()->user()->isAdmin())
                                        <option value="general">General Campus Channel</option>
                                    @endif
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Target NIT Course</label>
                            <select name="course_id" class="form-select">
                                @if(auth()->user()->isAdmin())
                                    <option value="">-- Select Course (Optional for General Channels) --</option>
                                @endif
                                @foreach($creatableCourses as $c)
                                    <option value="{{ $c->id }}">[{{ $c->code }}] {{ $c->name }}</option>
                                @endforeach
                            </select>
                            @if(auth()->user()->isClassRep())
                                <div class="form-hint">Security policy: Class Representatives can only create sub-communities for their own assigned course ({{ auth()->user()->course?->code }}).</div>
                            @endif
                        </div>

                        <div class="form-group">
                            <label class="form-label">Channel Purpose & Guidelines</label>
                            <textarea name="description" rows="3" class="form-control" placeholder="Describe what students will discuss or collaborate on in this channel..." required></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">Launch Community Channel</button>
                    </form>
                </div>
            @endif
        @endauth
    </div>
</section>
@endsection
