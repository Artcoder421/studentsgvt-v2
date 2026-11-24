@extends('layouts.app')

@section('title', 'Lost & Found Desk — SONIT | NIT')

@section('content')
<div class="page-banner">
    <div class="container page-banner-inner">
        <div class="page-banner-left">
            <div class="icon-3d-tile lg"><img src="{{ asset('images/icons/lostfound.svg') }}" alt="Lost and Found"></div>
            <div>
                <h1>Campus Lost & Found Registry</h1>
                <p>Report misplaced IDs, laptops, calculators, or documents and reunite found property transparently through the SONIT Secretariat Desk.</p>
            </div>
        </div>
        @auth
            <a href="#reportLostFound" class="btn btn-gold">Report Lost or Found Item</a>
        @endauth
    </div>
</div>

<section class="section section-tight">
    <div class="container">
        <div class="flex-center flex-wrap mb-4" style="gap:8px;">
            <a href="{{ route('lost-found.index') }}" class="btn btn-sm {{ !$type ? 'btn-primary' : 'btn-outline' }}">All Listings</a>
            <a href="{{ route('lost-found.index', ['type' => 'found']) }}" class="btn btn-sm {{ $type === 'found' ? 'btn-emerald' : 'btn-outline' }}">Found Items</a>
            <a href="{{ route('lost-found.index', ['type' => 'lost']) }}" class="btn btn-sm {{ $type === 'lost' ? 'btn-danger' : 'btn-outline' }}">Lost Items</a>
        </div>

        <div class="grid grid-3 mb-4">
            @forelse($items as $item)
                <article class="card card-hover" style="display:flex;flex-direction:column;justify-content:space-between;">
                    <div>
                        @if($item->image)
                            <img src="{{ asset($item->image) }}" alt="{{ $item->item_name }}" class="card-media">
                        @endif
                        <div class="flex-between mb-2">
                            <span class="badge {{ $item->type === 'found' ? 'badge-emerald' : 'badge-crimson' }}">{{ strtoupper($item->type) }}</span>
                            <span class="badge {{ $item->status === 'open' ? 'badge-navy' : ($item->status === 'claimed' ? 'badge-amber' : 'badge-slate') }}">{{ ucfirst($item->status) }}</span>
                        </div>
                        <h3 style="margin:0 0 6px;font-size:17.5px;font-weight:800;color:var(--navy-900);">{{ $item->item_name }}</h3>
                        <div class="text-xs muted mb-2">Location: <strong>{{ $item->location }}</strong> · {{ optional($item->date_occurred)->format('d M Y') }}</div>
                        <p class="text-sm muted" style="margin:0 0 12px;">{{ $item->description }}</p>
                    </div>
                    <div style="border-top:1px solid var(--border);padding-top:12px;">
                        <div class="text-xs muted mb-2">Contact: <strong>{{ $item->contact_info }}</strong></div>
                        @if($item->status === 'open' && auth()->check())
                            <form method="POST" action="{{ route('lost-found.claim', $item) }}" style="display:flex;gap:6px;">
                                @csrf
                                <input type="text" name="claimed_by_info" class="form-control" style="padding:7px 10px;font-size:12px;" placeholder="Your phone / proof note..." required>
                                <button type="submit" class="btn btn-outline btn-sm">Claim</button>
                            </form>
                        @endif
                    </div>
                </article>
            @empty
                <div class="card" style="grid-column:1/-1;text-align:center;"><p class="muted">No items listed yet.</p></div>
            @endforelse
        </div>

        <div class="mb-4">{{ $items->links() }}</div>

        @auth
            <div class="card" id="reportLostFound" style="max-width:780px;">
                <span class="kicker">Submit Listing</span>
                <h2 style="margin:0 0 16px;font-size:21px;font-weight:800;color:var(--navy-900);">Report a Lost or Found Item</h2>
                <form method="POST" action="{{ route('lost-found.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="grid grid-3">
                        <div class="form-group">
                            <label class="form-label">Report Type</label>
                            <select name="type" class="form-select" required>
                                <option value="found">I Found an Item</option>
                                <option value="lost">I Lost an Item</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Item Category</label>
                            <select name="category" class="form-select" required>
                                <option value="id_card">Student ID / Bank Card</option>
                                <option value="electronics">Electronics / Charger</option>
                                <option value="documents">Documents / Books</option>
                                <option value="keys">Keys</option>
                                <option value="bags">Bags / Backpacks</option>
                                <option value="accessories">Calculator / Watch</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Date</label>
                            <input type="date" name="date_occurred" value="{{ date('Y-m-d') }}" class="form-control">
                        </div>
                    </div>
                    <div class="grid grid-2">
                        <div class="form-group">
                            <label class="form-label">Item Name</label>
                            <input type="text" name="item_name" class="form-control" required placeholder="e.g. NIT Student ID / Laptop Charger">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Campus Location</label>
                            <input type="text" name="location" class="form-control" required placeholder="e.g. Lecture Block A / Main Library">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Description</label>
                        <textarea name="description" rows="3" class="form-control" required placeholder="Identifying details (avoid posting sensitive PINs or full card numbers)..."></textarea>
                    </div>
                    <div class="grid grid-2">
                        <div class="form-group">
                            <label class="form-label">Contact Info / Handover Desk</label>
                            <input type="text" name="contact_info" value="{{ auth()->user()->phone ?? auth()->user()->email }}" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Photo (Optional, max 2MB)</label>
                            <input type="file" name="image" accept="image/*" class="form-control">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Publish Listing</button>
                </form>
            </div>
        @endauth
    </div>
</section>
@endsection
