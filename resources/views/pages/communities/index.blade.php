@extends('layouts.app')

@section('title', 'My Course Communities & Channels — SONIT | NIT')

@section('content')
@php $user = auth()->user(); @endphp

<div class="page-banner">
    <div class="page-banner-inner">
        <div class="page-banner-left">
            <div class="icon-3d-tile"><img src="{{ asset('images/icons/communities.jpg') }}" alt="Communities"></div>
            <div>
                <h1>
                    {{ $user->isAdmin() ? 'All NIT Course Communities & Campus Channels' : ($user->course ? $user->course->code . ' Course Communities & General Channels' : 'Campus Communities') }}
                </h1>
                <p>
                    @if($user->isAdmin())
                        Administrator governance view across all NIT courses (including vacant CR seats) and general campus squares.
                    @else
                        You are automatically placed in your enrolled course ({{ $user->course?->name ?? 'NIT' }}) communities and general campus channels. Other courses' channels are private.
                    @endif
                </p>
            </div>
        </div>
        @if($user->isAdmin() || $user->isClassRep())
            <a href="#createCommunityCard" class="btn btn-danger btn-sm">Create Community Channel</a>
        @endif
    </div>
</div>

{{-- Search & Faculty Filter (Faculty filter only shown to Admins) --}}
@if($user->isAdmin())
    <form method="GET" action="{{ route('communities.index') }}" class="card mb-3" style="padding:14px;">
        <div class="grid grid-3" style="gap:10px;align-items:end;">
            <div class="form-group" style="margin:0;">
                <label class="form-label">Search Course or Channel</label>
                <input type="text" name="q" value="{{ $search }}" class="form-control" placeholder="e.g. BLTM, BIT, Aviation...">
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
                <button type="submit" class="btn btn-primary" style="flex:1;">Filter</button>
                <a href="{{ route('communities.index') }}" class="btn btn-outline">Reset</a>
            </div>
        </div>
    </form>
@endif

{{-- My Course Official Community & Sub-Groups FIRST --}}
<div class="mb-4">
    <span class="kicker">{{ $user->isAdmin() ? 'Official Programme Channels (' . $defaultCourseCommunities->count() . ')' : 'Automatic Course Membership' }}</span>
    <h2 class="section-title mb-2">
        {{ $user->isAdmin() ? 'All NIT Default Course Communities' : 'My ' . ($user->course?->code ?? 'Course') . ' Communities' }}
    </h2>

    <div class="grid grid-3">
        @foreach($defaultCourseCommunities as $comm)
            <div class="card card-accent-blue card-hover" style="display:flex;flex-direction:column;justify-content:space-between;">
                <div>
                    <div class="flex-between mb-2">
                        <span class="badge badge-black mono">{{ $comm->course?->code }}</span>
                        <span class="badge badge-emerald">Automatic Member</span>
                    </div>
                    <h3 style="margin:0 0 4px;font-size:16px;font-weight:800;color:var(--nit-black);">{{ $comm->name }}</h3>
                    <div class="text-xs muted mb-2">{{ $comm->course?->name }}</div>
                    <p class="text-xs muted" style="margin:0 0 12px;">{{ \Illuminate\Support\Str::limit($comm->description, 110) }}</p>
                </div>

                <div style="border-top:1px solid var(--border);padding-top:10px;">
                    <div class="flex-between mb-2">
                        @if($comm->cr)
                            <span class="badge badge-emerald">CR: {{ $comm->cr->name }}</span>
                        @else
                            <span class="badge badge-amber">CR Vacant · Admin Controlled</span>
                        @endif
                        <span class="text-xs muted mono">{{ $comm->members_count }} mbrs</span>
                    </div>
                    <a href="{{ route('communities.show', $comm) }}" class="btn btn-primary btn-sm" style="width:100%;">Open Course Channel</a>
                </div>
            </div>
        @endforeach

        @foreach($courseSubGroups as $group)
            <div class="card card-accent-red card-hover" style="display:flex;flex-direction:column;justify-content:space-between;">
                <div>
                    <div class="flex-between mb-2">
                        <span class="badge badge-navy mono">{{ $group->course?->code }} Sub-Group</span>
                        <span class="text-xs muted mono">{{ $group->members_count }} mbrs</span>
                    </div>
                    <h3 style="margin:0 0 4px;font-size:16px;font-weight:800;color:var(--nit-black);">{{ $group->name }}</h3>
                    <p class="text-xs muted" style="margin:0 0 12px;">{{ $group->description }}</p>
                </div>
                <div class="flex-between" style="border-top:1px solid var(--border);padding-top:10px;">
                    <span class="text-xs muted">By {{ $group->creator?->name ?? 'CR' }}</span>
                    <a href="{{ route('communities.show', $group) }}" class="btn btn-outline btn-sm">Open Group</a>
                </div>
            </div>
        @endforeach
    </div>
</div>

{{-- General Campus Channels --}}
<div class="mb-4">
    <span class="kicker">Open To All NIT Students</span>
    <h2 class="section-title mb-2">General Campus Channels</h2>
    <div class="grid grid-3">
        @foreach($generalChannels as $channel)
            <div class="card card-hover" style="display:flex;flex-direction:column;justify-content:space-between;">
                <div>
                    <div class="flex-between mb-2">
                        <span class="badge badge-gold">General Square</span>
                        <span class="text-xs muted mono">{{ $channel->members_count }} members</span>
                    </div>
                    <h3 style="margin:0 0 6px;font-size:16px;font-weight:800;color:var(--nit-black);">{{ $channel->name }}</h3>
                    <p class="text-xs muted" style="margin:0 0 12px;">{{ $channel->description }}</p>
                </div>
                <div class="flex-between" style="border-top:1px solid var(--border);padding-top:10px;">
                    <span class="text-xs muted">{{ $channel->messages_count }} messages</span>
                    <a href="{{ route('communities.show', $channel) }}" class="btn btn-black btn-sm">Open Channel</a>
                </div>
            </div>
        @endforeach
    </div>
</div>

{{-- Role-Scoped Create Community Form (Only Class Reps for own course & Admins) --}}
@if($user->isAdmin() || $user->isClassRep())
    <div class="card card-accent-red" id="createCommunityCard">
        <div class="flex-between flex-wrap mb-3">
            <div>
                <span class="kicker">Channel Creation</span>
                <h2 style="margin:0;font-size:18px;font-weight:800;color:var(--nit-black);">Create a New Community Channel</h2>
            </div>
            <span class="badge badge-black">
                {{ $user->isAdmin() ? 'Admin Scope: All NIT Courses & General' : 'CR Scope: Restricted to ' . ($user->course?->code ?? 'Your Course') . ' Only' }}
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
                        @if($user->isAdmin())
                            <option value="general">General Campus Channel</option>
                        @endif
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Target NIT Course</label>
                <select name="course_id" class="form-select">
                    @if($user->isAdmin())
                        <option value="">-- Select Course (Optional for General Channels) --</option>
                    @endif
                    @foreach($creatableCourses as $c)
                        <option value="{{ $c->id }}">[{{ $c->code }}] {{ $c->name }}</option>
                    @endforeach
                </select>
                @if($user->isClassRep())
                    <div class="form-hint">Class Representatives can only create sub-communities for {{ $user->course?->code }}. Students in {{ $user->course?->code }} are enrolled automatically.</div>
                @endif
            </div>

            <div class="form-group">
                <label class="form-label">Channel Purpose & Guidelines</label>
                <textarea name="description" rows="2" class="form-control" placeholder="Describe what students will discuss in this channel..." required></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Launch Community Channel</button>
        </form>
    </div>
@endif
@endsection
