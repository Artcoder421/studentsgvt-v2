@extends('layouts.app')

@section('title', 'Leadership Directory — SONIT | NIT')

@section('content')
<div class="page-banner">
    <div class="container page-banner-inner">
        <div class="page-banner-left">
            <div class="icon-3d-tile lg"><img src="{{ asset('images/icons/leadership.svg') }}" alt="Leadership"></div>
            <div>
                <h1>Institute & SONIT Leadership Directory</h1>
                <p>Executive leadership of the National Institute of Transport and the Students Organization Cabinet.</p>
            </div>
        </div>
    </div>
</div>

<section class="section">
    <div class="container">
        @if($instituteLeaders->isNotEmpty() || $executives->isNotEmpty())
            <span class="kicker">Executive Gallery</span>
            <h2 class="section-title mb-4">Office of the Rector & SONIT Presidency</h2>
            <div class="grid grid-3 mb-4">
                @foreach($instituteLeaders->concat($executives) as $leader)
                    <article class="portrait-card">
                        <div class="portrait-frame">
                            <img src="{{ asset($leader->photo ?? 'images/portraits/president.jpg') }}" alt="{{ $leader->name }}">
                        </div>
                        <div class="portrait-body">
                            <div class="portrait-role">{{ $leader->position }}</div>
                            <h3 class="portrait-name">{{ $leader->name }}</h3>
                            @if($leader->quote)<p class="portrait-quote">"{{ $leader->quote }}"</p>@endif
                            <p class="text-sm muted" style="margin:0 0 12px;">{{ $leader->bio }}</p>
                            <div class="text-xs muted" style="border-top:1px solid var(--border);padding-top:10px;">
                                @if($leader->email)<div>Email: <strong>{{ $leader->email }}</strong></div>@endif
                                @if($leader->office_location)<div>Office: {{ $leader->office_location }}</div>@endif
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif

        @if($ministries->isNotEmpty())
            <div class="mt-4" style="padding-top:24px;">
                <span class="kicker">Cabinet & Parliament</span>
                <h2 class="section-title mb-4">SONIT Ministers & Secretariat</h2>
                <div class="grid grid-3">
                    @foreach($ministries as $leader)
                        <div class="card">
                            <span class="badge badge-navy mb-2">{{ $leader->department ?? ucfirst($leader->category) }}</span>
                            <h3 style="margin:0 0 4px;font-size:18px;font-weight:800;color:var(--navy-900);">{{ $leader->name }}</h3>
                            <div class="text-sm" style="color:var(--gold-600);font-weight:700;margin-bottom:10px;">{{ $leader->position }}</div>
                            <p class="text-sm muted" style="margin:0 0 12px;">{{ $leader->bio }}</p>
                            <div class="text-xs muted">
                                @if($leader->email)<div>{{ $leader->email }}</div>@endif
                                @if($leader->phone)<div>{{ $leader->phone }}</div>@endif
                                @if($leader->office_location)<div>{{ $leader->office_location }}</div>@endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</section>
@endsection
