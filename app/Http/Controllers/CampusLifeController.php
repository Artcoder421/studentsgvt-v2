<?php

namespace App\Http\Controllers;

use App\Models\CampusEvent;
use App\Models\CharityCampaign;
use App\Models\CharityContribution;
use App\Models\EventRsvp;
use App\Models\SportsActivity;
use App\Models\SportsRegistration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CampusLifeController extends Controller
{
    public function events(Request $request): View
    {
        $category = $request->query('category');
        $events = CampusEvent::withCount([
            'rsvps as going_count' => fn ($q) => $q->where('status', 'going'),
            'rsvps as interested_count' => fn ($q) => $q->where('status', 'interested'),
        ])
            ->where('is_published', true)
            ->when($category, fn ($q) => $q->where('category', $category))
            ->orderBy('starts_at')
            ->get();

        $userRsvps = Auth::check()
            ? EventRsvp::where('user_id', Auth::id())->pluck('status', 'campus_event_id')->toArray()
            : [];

        return view('pages.life.events', compact('events', 'category', 'userRsvps'));
    }

    public function rsvpEvent(Request $request, CampusEvent $event): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:going,interested,not_going'],
        ]);

        EventRsvp::updateOrCreate(
            ['campus_event_id' => $event->id, 'user_id' => $request->user()->id],
            ['status' => $data['status']]
        );

        return back()->with('success', "RSVP updated for {$event->title}.");
    }

    public function sports(): View
    {
        $activities = SportsActivity::withCount('registrations')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $myRegistrations = Auth::check()
            ? SportsRegistration::where('user_id', Auth::id())->pluck('status', 'sports_activity_id')->toArray()
            : [];

        return view('pages.life.sports', compact('activities', 'myRegistrations'));
    }

    public function joinSport(Request $request, SportsActivity $sport): RedirectResponse
    {
        $data = $request->validate([
            'position_or_skill' => ['nullable', 'string', 'max:100'],
        ]);

        SportsRegistration::updateOrCreate(
            ['sports_activity_id' => $sport->id, 'user_id' => $request->user()->id],
            ['position_or_skill' => $data['position_or_skill'] ?? 'General Squad', 'status' => 'pending']
        );

        return back()->with('success', "You have registered for {$sport->name} trials!");
    }

    public function charity(): View
    {
        $campaigns = CharityCampaign::with(['contributions' => fn ($q) => $q->where('status', 'verified')->take(6)])
            ->orderByDesc('created_at')
            ->get();

        return view('pages.life.charity', compact('campaigns'));
    }

    public function pledgeCharity(Request $request, CharityCampaign $campaign): RedirectResponse
    {
        $data = $request->validate([
            'donor_name' => ['required', 'string', 'max:120'],
            'amount' => ['required', 'integer', 'min:1000', 'max:100000000'],
            'payment_reference' => ['nullable', 'string', 'max:80'],
            'message' => ['nullable', 'string', 'max:240'],
            'is_anonymous' => ['nullable', 'boolean'],
        ]);

        CharityContribution::create([
            'charity_campaign_id' => $campaign->id,
            'user_id' => Auth::id(),
            'donor_name' => $request->boolean('is_anonymous') ? 'Anonymous Well-Wisher' : $data['donor_name'],
            'amount' => $data['amount'],
            'payment_reference' => $data['payment_reference'] ?? null,
            'message' => $data['message'] ?? null,
            'is_anonymous' => $request->boolean('is_anonymous'),
            'status' => 'pledged',
        ]);

        return back()->with('success', 'Thank you for your generous pledge! Once verified by the SONIT Finance Ministry, it will reflect in the live tally.');
    }
}
