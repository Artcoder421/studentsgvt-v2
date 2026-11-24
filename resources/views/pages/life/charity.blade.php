@extends('layouts.app')

@section('title', 'Charity & Student Welfare Drives — SONIT | NIT')

@section('content')
<div class="page-banner">
    <div class="container page-banner-inner">
        <div class="page-banner-left">
            <div class="icon-3d-tile lg"><img src="{{ asset('images/icons/charity.svg') }}" alt="Charity"></div>
            <div>
                <h1>Charity & Student Solidarity Initiatives</h1>
                <p>Admin-audited community outreach drives and student emergency welfare funds with transparent live fundraising tallies.</p>
            </div>
        </div>
    </div>
</div>

<section class="section section-tight">
    <div class="container">
        <div style="display:flex;flex-direction:column;gap:28px;">
            @foreach($campaigns as $campaign)
                <article class="card" style="padding:28px;">
                    <div class="grid grid-2" style="align-items:start;">
                        <div>
                            <img src="{{ asset($campaign->image ?? 'images/campus/students.jpg') }}" alt="{{ $campaign->title }}" style="width:100%;height:240px;object-fit:cover;border-radius:14px;margin-bottom:16px;">
                            <div class="flex-between mb-2">
                                <span class="badge {{ $campaign->status === 'active' ? 'badge-emerald' : 'badge-slate' }}">{{ ucfirst($campaign->status) }} Drive</span>
                                @if($campaign->ends_at)
                                    <div class="countdown-clock" data-countdown="{{ $campaign->ends_at->toIso8601String() }}">
                                        <span class="badge badge-gold mono"><span data-cd-days>00</span>d : <span data-cd-hours>00</span>h : <span data-cd-mins>00</span>m : <span data-cd-secs>00</span>s left</span>
                                    </div>
                                @endif
                            </div>
                            <h2 style="margin:0 0 6px;font-size:23px;font-weight:800;color:var(--navy-900);">{{ $campaign->title }}</h2>
                            <div class="text-xs muted mb-2">Beneficiary: <strong>{{ $campaign->beneficiary }}</strong></div>
                            <p class="text-sm muted" style="margin:0 0 16px;">{{ $campaign->description }}</p>

                            <div class="progress-track mb-2"><div class="progress-fill" style="width:{{ $campaign->progressPercent() }}%;"></div></div>
                            <div class="flex-between mb-3">
                                <strong class="mono" style="font-size:19px;color:var(--navy-900);">TZS {{ number_format($campaign->raised_amount) }}</strong>
                                <span class="text-sm muted">Target: TZS {{ number_format($campaign->target_amount) }} ({{ $campaign->progressPercent() }}%)</span>
                            </div>

                            @if($campaign->payment_instructions)
                                <div style="background:var(--gold-100);border:1px solid #E7CE93;border-radius:12px;padding:14px;font-size:13px;color:var(--navy-900);">
                                    <strong>Official Payment Channels:</strong><br>{{ $campaign->payment_instructions }}
                                </div>
                            @endif
                        </div>

                        <div>
                            <div style="background:var(--canvas);border:1px solid var(--border);border-radius:14px;padding:20px;margin-bottom:18px;">
                                <h3 style="margin:0 0 12px;font-size:18px;font-weight:800;color:var(--navy-900);">Log Your Contribution / Pledge</h3>
                                @auth
                                    <form method="POST" action="{{ route('charity.pledge', $campaign) }}">
                                        @csrf
                                        <div class="form-group">
                                            <label class="form-label">Donor / Class Collective Name</label>
                                            <input type="text" name="donor_name" value="{{ auth()->user()->name }}" class="form-control" required>
                                        </div>
                                        <div class="grid grid-2">
                                            <div class="form-group">
                                                <label class="form-label">Amount (TZS)</label>
                                                <input type="number" name="amount" min="1000" step="1000" value="10000" class="form-control mono" required>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Payment Reference</label>
                                                <input type="text" name="payment_reference" class="form-control mono" placeholder="e.g. MPESA-994X">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Encouragement Note (Optional)</label>
                                            <input type="text" name="message" class="form-control" placeholder="Together for Mabibo community!">
                                        </div>
                                        <div class="form-group">
                                            <label class="flex-center text-xs" style="cursor:pointer;">
                                                <input type="checkbox" name="is_anonymous" value="1"> Display publicly as Anonymous Well-Wisher
                                            </label>
                                        </div>
                                        <button type="submit" class="btn btn-gold" style="width:100%;">Submit Contribution</button>
                                    </form>
                                @else
                                    <p class="text-sm muted mb-3">Sign in with your NIT account to log a verified contribution or class pledge.</p>
                                    <a href="{{ route('login') }}" class="btn btn-primary btn-sm">Sign In to Contribute</a>
                                @endauth
                            </div>

                            <h4 style="margin:0 0 10px;font-size:14px;font-weight:800;color:var(--navy-900);text-transform:uppercase;letter-spacing:0.06em;">Recent Verified Contributions</h4>
                            @forelse($campaign->contributions as $c)
                                <div class="flex-between text-xs" style="padding:8px 0;border-bottom:1px solid var(--border);">
                                    <div>
                                        <strong style="color:var(--navy-900);">{{ $c->donor_name }}</strong>
                                        @if($c->message)<div class="muted">"{{ $c->message }}"</div>@endif
                                    </div>
                                    <span class="badge badge-emerald mono">TZS {{ number_format($c->amount) }}</span>
                                </div>
                            @empty
                                <p class="text-xs muted">Be the first to log a verified contribution.</p>
                            @endforelse
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
@endsection
