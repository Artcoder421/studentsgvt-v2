@extends('layouts.app')

@section('title', $community->name . ' — SONIT Communities')

@section('content')
<div class="page-banner">
    <div class="container page-banner-inner">
        <div>
            <div class="flex-center flex-wrap mb-2">
                <span class="badge badge-gold">{{ $community->typeLabel() }}</span>
                @if($community->course)<span class="badge badge-navy mono">{{ $community->course->code }}</span>@endif
                @if($community->is_locked)<span class="badge badge-crimson">Broadcast Mode (CR/Admin Only)</span>@endif
            </div>
            <h1>{{ $community->name }}</h1>
            <p>{{ $community->description }}</p>
        </div>

        <div class="flex-center flex-wrap">
            @auth
                @if($canModerate)
                    <form method="POST" action="{{ route('communities.toggle-lock', $community) }}" style="margin:0;">
                        @csrf
                        <button type="submit" class="btn btn-ghost-light btn-sm">
                            {{ $community->is_locked ? 'Unlock Student Chat' : 'Switch to Broadcast Mode' }}
                        </button>
                    </form>
                @endif

                @if($community->type === 'course_default' && (int)$community->course_id === (int)auth()->user()->course_id)
                    <span class="badge badge-emerald">Automatic Course Member</span>
                @elseif($isMember)
                    <form method="POST" action="{{ route('communities.leave', $community) }}" style="margin:0;">
                        @csrf
                        <button type="submit" class="btn btn-ghost-light btn-sm">Leave Channel</button>
                    </form>
                @endif
            @endauth
            <a href="{{ route('communities.index') }}" class="btn btn-ghost-light btn-sm">All Channels</a>
        </div>
    </div>
</div>

<section class="section section-tight">
    <div class="container channel-layout">
        {{-- Main Live Feed --}}
        <div>
            @if($pinnedMessages->isNotEmpty())
                <div class="card mb-3" style="background:var(--gold-100);border-color:#E7CE93;padding:16px;">
                    <div class="kicker" style="margin-bottom:6px;">Pinned Official Notices</div>
                    @foreach($pinnedMessages as $pin)
                        <div style="padding:8px 0;border-top:1px solid rgba(165,124,36,0.2);">
                            <strong style="font-size:13px;color:var(--navy-900);">{{ $pin->user->name }}:</strong>
                            <span style="font-size:13.5px;color:var(--ink-700);">{{ $pin->body }}</span>
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="card" style="padding:20px;">
                <div class="chat-feed" id="communityChatFeed">
                    @forelse($messages as $msg)
                        @php
                            $isMine = auth()->check() && (int)auth()->id() === (int)$msg->user_id;
                            $initials = strtoupper(substr($msg->user->name, 0, 2));
                            $fallbackClass = $msg->user->isAdmin() ? 'is-admin' : ($msg->user->isClassRep() ? 'is-cr' : '');
                        @endphp
                        <div class="chat-msg {{ $isMine ? 'is-mine' : '' }} {{ $msg->is_pinned ? 'is-pinned' : '' }}">
                            <div class="chat-msg-row">
                                @if($msg->user->avatar)
                                    <img src="{{ asset($msg->user->avatar) }}" alt="{{ $msg->user->name }}" class="avatar-tile">
                                @else
                                    <div class="avatar-tile avatar-fallback {{ $fallbackClass }}" title="{{ $msg->user->name }}">{{ $initials }}</div>
                                @endif

                                <div class="chat-msg-body">
                                    <div class="flex-between mb-1">
                                        <div class="flex-center flex-wrap" style="gap:6px;">
                                            <strong style="font-size:14px;color:var(--navy-900);">{{ $msg->user->name }}</strong>
                                            @if($msg->user->sonit_title)
                                                <span class="badge badge-crimson">{{ $msg->user->sonit_title }}</span>
                                            @endif
                                            @if($msg->user->isAdmin())
                                                <span class="badge badge-gold">SONIT Admin</span>
                                            @elseif($msg->user->isClassRep())
                                                <span class="badge badge-emerald">{{ $msg->user->course?->code }} CR</span>
                                            @elseif($msg->user->course)
                                                <span class="badge badge-slate mono">{{ $msg->user->course->code }}</span>
                                            @endif
                                            @if($msg->is_pinned)<span class="badge badge-amber">Pinned</span>@endif
                                        </div>
                                        <span class="text-xs muted">{{ $msg->created_at->diffForHumans() }}</span>
                                    </div>

                                    @if($msg->replyTo)
                                        <div class="chat-quote">
                                            Replying to <strong>{{ $msg->replyTo->user->name }}</strong>: "{{ \Illuminate\Support\Str::limit($msg->replyTo->body, 90) }}"
                                        </div>
                                    @endif

                                    <div style="font-size:14.5px;color:var(--ink-900);white-space:pre-line;">{{ $msg->body }}</div>

                                    @if($msg->attachment)
                                        <img src="{{ asset($msg->attachment) }}" alt="Attachment" style="max-width:340px;border-radius:4px;margin-top:10px;border:1px solid var(--border);">
                                    @endif

                                    @auth
                                        <div class="flex-center mt-2" style="gap:12px;">
                                            <button type="button" class="btn btn-outline btn-sm" style="padding:3px 10px;font-size:11.5px;"
                                                data-reply-id="{{ $msg->id }}"
                                                data-reply-preview="{{ $msg->user->name }}: {{ \Illuminate\Support\Str::limit($msg->body, 70) }}">
                                                Reply
                                            </button>
                                            @if($canModerate)
                                                <form method="POST" action="{{ route('communities.messages.pin', [$community, $msg]) }}" style="margin:0;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-outline btn-sm" style="padding:3px 10px;font-size:11.5px;">
                                                        {{ $msg->is_pinned ? 'Unpin' : 'Pin Notice' }}
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    @endauth
                                </div>
                            </div>
                        </div>
                    @empty
                        <div style="text-align:center;padding:42px 16px;" class="muted">
                            No messages in this channel yet. Post the first update below.
                        </div>
                    @endforelse
                </div>

                @auth
                    @if($community->is_locked && !$canModerate)
                        <div class="flash flash-info mt-3" style="margin-bottom:0;">
                            This channel is in Broadcast Mode. Only the Class Representative and SONIT Admins can post messages right now.
                        </div>
                    @else
                        <form method="POST" action="{{ route('communities.messages.store', $community) }}" enctype="multipart/form-data" class="mt-3">
                            @csrf
                            <input type="hidden" name="reply_to_id" id="replyToInput">
                            <div id="replyPreviewBanner" class="flash flash-info mb-2" style="display:none;padding:8px 12px;font-size:12.5px;">
                                <span>Replying to <strong id="replyPreviewText"></strong></span>
                                <button type="button" id="cancelReplyBtn" class="btn btn-outline btn-sm" style="padding:2px 8px;">Cancel</button>
                            </div>
                            <div class="form-group mb-2">
                                <textarea id="communityMessageInput" name="body" rows="3" class="form-control" placeholder="Write a message to {{ $community->name }}..." required></textarea>
                            </div>
                            <div class="flex-between flex-wrap" style="gap:10px;">
                                <input type="file" name="attachment" accept="image/*" class="text-xs muted">
                                <button type="submit" class="btn btn-primary">Send Message</button>
                            </div>
                        </form>
                    @endif
                @else
                    <div class="flash flash-info mt-3" style="margin-bottom:0;">
                        <span>Sign in with your NIT student account to join and message inside this community.</span>
                        <a href="{{ route('login') }}" class="btn btn-primary btn-sm">Sign In</a>
                    </div>
                @endauth
            </div>
        </div>

        {{-- Right Governance & Members Sidebar --}}
        <aside style="display:flex;flex-direction:column;gap:18px;">
            <div class="card">
                <span class="kicker">Channel Governance</span>
                <h3 style="margin:0 0 12px;font-size:17px;font-weight:800;color:var(--navy-900);">Leadership Seat</h3>

                @if($community->course)
                    <div class="text-xs muted mb-2">
                        Programme: <strong style="color:var(--navy-900);">{{ $community->course->name }}</strong>
                    </div>
                @endif

                @if($community->cr)
                    <div style="padding:12px;border-radius:4px;background:var(--emerald-50);border:1px solid #A7F3D0;">
                        <div class="text-xs" style="color:var(--emerald-700);font-weight:700;text-transform:uppercase;">Assigned Class Rep (CR)</div>
                        <div class="flex-center mt-1" style="gap:8px;">
                            @if($community->cr->avatar)
                                <img src="{{ asset($community->cr->avatar) }}" alt="{{ $community->cr->name }}" class="avatar-tile sm">
                            @else
                                <div class="avatar-tile sm avatar-fallback is-cr">{{ strtoupper(substr($community->cr->name, 0, 2)) }}</div>
                            @endif
                            <div>
                                <div style="font-weight:800;color:var(--navy-900);">{{ $community->cr->name }}</div>
                                <div class="text-xs mono muted">{{ $community->cr->reg_no }}</div>
                            </div>
                        </div>
                    </div>
                @elseif($community->type === 'course_default')
                    <div style="padding:12px;border-radius:4px;background:var(--amber-50);border:1px solid #FDE68A;">
                        <div class="text-xs" style="color:var(--amber-700);font-weight:700;text-transform:uppercase;">CR Seat Reserved (Vacant)</div>
                        <div class="text-xs muted mt-1">No CR registered yet for {{ $community->course?->code }}. SONIT Admins govern this channel until a CR is appointed.</div>
                    </div>
                @else
                    <div class="text-sm muted">Moderated by SONIT Administration.</div>
                @endif
            </div>

            @if($relatedCourseGroups->isNotEmpty())
                <div class="card">
                    <span class="kicker">Same Course</span>
                    <h3 style="margin:0 0 10px;font-size:16px;font-weight:800;color:var(--navy-900);">Related {{ $community->course?->code }} Channels</h3>
                    <div style="display:flex;flex-direction:column;gap:8px;">
                        @foreach($relatedCourseGroups as $rel)
                            <a href="{{ route('communities.show', $rel) }}" class="btn btn-outline btn-sm" style="justify-content:flex-start;">{{ $rel->name }}</a>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="card">
                <div class="flex-between mb-2">
                    <h3 style="margin:0;font-size:16px;font-weight:800;color:var(--navy-900);">Members</h3>
                    <span class="badge badge-navy mono">{{ $community->members_count }}</span>
                </div>
                <div style="display:flex;flex-direction:column;gap:8px;max-height:260px;overflow-y:auto;">
                    @foreach($members as $member)
                        <div class="flex-between text-xs" style="padding:6px 0;border-bottom:1px solid var(--border);gap:8px;">
                            <div class="flex-center" style="gap:8px;min-width:0;">
                                @if($member->avatar)
                                    <img src="{{ asset($member->avatar) }}" alt="{{ $member->name }}" class="avatar-tile sm">
                                @else
                                    <div class="avatar-tile sm avatar-fallback {{ $member->isAdmin() ? 'is-admin' : ($member->isClassRep() ? 'is-cr' : '') }}">
                                        {{ strtoupper(substr($member->name, 0, 2)) }}
                                    </div>
                                @endif
                                <strong style="color:var(--ink-900);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $member->name }}</strong>
                            </div>
                            <span class="badge badge-slate">{{ strtoupper($member->pivot->member_role) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </aside>
    </div>
</section>
@endsection
