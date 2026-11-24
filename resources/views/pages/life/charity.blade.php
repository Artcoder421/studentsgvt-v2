@extends('layouts.app')

@section('title', 'Charity Tours & Contribution Reminders — SONIT | NIT')

@section('content')
<div class="page-banner">
    <div class="page-banner-inner">
        <div class="page-banner-left">
            <div class="icon-3d-tile"><img src="{{ asset('images/icons/charity.svg') }}" alt="Charity"></div>
            <div>
                <h1>Charity Tours & Contribution Reminders</h1>
                <p>Official SONIT notices showing what is planned, where, when, and the official bank/mobile money account numbers & coordinator contacts.</p>
            </div>
        </div>
    </div>
</div>

<div style="display:flex;flex-direction:column;gap:18px;">
    @forelse($campaigns as $campaign)
        <article class="card card-accent-red">
            <div class="grid grid-2" style="align-items:start;">
                <div>
                    <img src="{{ asset($campaign->image ?? 'images/campus/students.jpg') }}" alt="{{ $campaign->title }}" style="width:100%;height:210px;object-fit:cover;border-radius:2px;margin-bottom:12px;border:1px solid var(--border);">
                    <div class="flex-between flex-wrap mb-2">
                        <span class="badge badge-crimson">{{ strtoupper($campaign->status) }} NOTICE</span>
                        @if($campaign->ends_at && $campaign->ends_at->isFuture())
                            <div class="countdown-clock" data-countdown="{{ $campaign->ends_at->toIso8601String() }}">
                                <span class="badge badge-black mono"><span data-cd-days>00</span>d : <span data-cd-hours>00</span>h : <span data-cd-mins>00</span>m : <span data-cd-secs>00</span>s left</span>
                            </div>
                        @endif
                    </div>
                    <h2 style="margin:0 0 6px;font-size:20px;font-weight:800;color:var(--nit-black);">{{ $campaign->title }}</h2>
                    <div class="text-xs muted mb-2">Target Group / Beneficiary: <strong>{{ $campaign->beneficiary }}</strong></div>
                    <p class="text-sm muted" style="margin:0;">{{ $campaign->description }}</p>
                </div>

                <div>
                    <div class="notice-box mb-2">
                        <div class="notice-label">What We Are Planning To Do</div>
                        <div class="text-sm" style="color:var(--ink-900);white-space:pre-line;">{{ $campaign->planned_activities ?? $campaign->description }}</div>
                    </div>

                    <div class="grid grid-2 mb-2" style="gap:10px;">
                        <div class="notice-box">
                            <div class="notice-label">When (Date & Time)</div>
                            <strong class="text-xs" style="color:var(--nit-black);">{{ $campaign->event_when ?? optional($campaign->ends_at)->format('d M Y · H:i') ?? 'TBA' }}</strong>
                        </div>
                        <div class="notice-box">
                            <div class="notice-label">Where (Venue / Destination)</div>
                            <strong class="text-xs" style="color:var(--nit-black);">{{ $campaign->event_where ?? $campaign->beneficiary }}</strong>
                        </div>
                    </div>

                    <div class="notice-box is-account mb-2">
                        <div class="notice-label">Official Account Number / Lipa Namba for Contributions</div>
                        <div class="flex-between flex-wrap" style="gap:8px;">
                            <strong class="mono text-sm" style="color:var(--nit-black);">{{ $campaign->account_number ?? $campaign->payment_instructions ?? 'Contact SONIT Finance Office' }}</strong>
                            @if($campaign->account_number)
                                <button type="button" class="btn btn-danger btn-sm" data-copy-text="{{ $campaign->account_number }}">Copy Account</button>
                            @endif
                        </div>
                        @if($campaign->payment_instructions)
                            <div class="text-xs muted mt-1">{{ $campaign->payment_instructions }}</div>
                        @endif
                    </div>

                    <div class="notice-box">
                        <div class="notice-label">Official Coordinator Contacts</div>
                        <strong class="mono text-xs" style="color:var(--nit-blue-dark);">{{ $campaign->contact_info ?? ($siteSettings['phone'] ?? '+255 22 240 0148') }}</strong>
                    </div>
                </div>
            </div>
        </article>
    @empty
        <div class="card"><p class="muted" style="margin:0;">No active charity or tour reminders published right now.</p></div>
    @endforelse
</div>
@endsection
