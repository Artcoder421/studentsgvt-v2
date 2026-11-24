@extends('layouts.app')

@section('title', 'Student Help Desk & Welfare — SONIT | NIT')

@section('content')
<div class="page-banner">
    <div class="container page-banner-inner">
        <div class="page-banner-left">
            <div class="icon-3d-tile lg"><img src="{{ asset('images/icons/helpdesk.jpg') }}" alt="Help Desk"></div>
            <div>
                <h1>SONIT Student Help Desk & Welfare</h1>
                <p>Fast, trackable resolutions for HESLB loans, SIMS portal clearance, hostel queries, academic appeals, and confidential student welfare.</p>
            </div>
        </div>
        <div class="badge badge-gold" style="padding:10px 16px;font-size:13px;">24/7 Hotline: {{ $siteSettings['emergency_phone'] ?? '+255 754 000 911' }}</div>
    </div>
</div>

<section class="section section-tight">
    <div class="container">
        @auth
            <div class="grid grid-2" style="align-items:start;">
                <div class="card">
                    <span class="kicker">Open Support Ticket</span>
                    <h2 style="margin:0 0 16px;font-size:21px;font-weight:800;color:var(--navy-900);">Submit a New Case</h2>
                    <form method="POST" action="{{ route('help-desk.store') }}">
                        @csrf
                        <div class="grid grid-2">
                            <div class="form-group">
                                <label class="form-label">Support Category</label>
                                <select name="category" class="form-select" required>
                                    @foreach(\App\Models\HelpTicket::CATEGORIES as $k => $lbl)
                                        <option value="{{ $k }}">{{ $lbl }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Urgency Priority</label>
                                <select name="priority" class="form-select" required>
                                    <option value="normal">Normal (24–48h)</option>
                                    <option value="high">High (Same Day)</option>
                                    <option value="urgent">Urgent (Immediate Escalation)</option>
                                    <option value="low">Low</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Subject</label>
                            <input type="text" name="subject" class="form-control" placeholder="Brief summary of your issue" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Detailed Description</label>
                            <textarea name="description" rows="4" class="form-control" placeholder="Include relevant module codes, HESLB batch info, or room details..." required></textarea>
                        </div>
                        <div class="form-group">
                            <label class="flex-center text-sm" style="cursor:pointer;">
                                <input type="checkbox" name="is_anonymous" value="1"> Submit as Confidential / Anonymous Welfare Report
                            </label>
                        </div>
                        <button type="submit" class="btn btn-primary">Create Trackable Ticket</button>
                    </form>
                </div>

                <div class="card">
                    <span class="kicker">Case History</span>
                    <h2 style="margin:0 0 16px;font-size:21px;font-weight:800;color:var(--navy-900);">
                        {{ auth()->user()->isAdmin() ? 'All Active Campus Tickets' : 'My Support Tickets' }}
                    </h2>

                    @forelse($tickets as $ticket)
                        <div style="padding:14px 0;border-bottom:1px solid var(--border);">
                            <div class="flex-between mb-1">
                                <a href="{{ route('help-desk.show', $ticket) }}" style="font-weight:800;color:var(--navy-900);">{{ $ticket->subject }}</a>
                                <span class="badge {{ $ticket->status === 'resolved' ? 'badge-emerald' : ($ticket->priority === 'urgent' ? 'badge-crimson' : 'badge-navy') }}">
                                    {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                                </span>
                            </div>
                            <div class="flex-between text-xs muted">
                                <span class="mono">{{ $ticket->ticket_number }} · {{ $ticket->categoryLabel() }}</span>
                                <span>{{ $ticket->replies->count() }} replies · {{ $ticket->created_at->diffForHumans() }}</span>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm muted">No support tickets logged yet.</p>
                    @endforelse
                    @if(method_exists($tickets, 'links'))
                        <div class="mt-3">{{ $tickets->links() }}</div>
                    @endif
                </div>
            </div>
        @else
            <div class="grid grid-3 mb-4">
                @foreach(['Academic & SIMS Clearance', 'HESLB Loans & Tuition Desk', 'Confidential Welfare & Counseling'] as $pillar)
                    <div class="card">
                        <span class="badge badge-navy mb-2">SONIT Support SLA</span>
                        <h3 style="margin:0 0 8px;font-size:18px;font-weight:800;color:var(--navy-900);">{{ $pillar }}</h3>
                        <p class="text-sm muted" style="margin:0;">Direct routing to the responsible SONIT Ministry with trackable ticket IDs and documented responses.</p>
                    </div>
                @endforeach
            </div>
            <div class="card" style="text-align:center;padding:36px;">
                <h2 style="margin:0 0 8px;color:var(--navy-900);">Need Direct Student Assistance?</h2>
                <p class="muted mb-3">Sign in with your NIT credentials to open a private, trackable support case.</p>
                <a href="{{ route('login') }}" class="btn btn-primary">Sign In to Open Ticket</a>
            </div>
        @endauth
    </div>
</section>
@endsection
