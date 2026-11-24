<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Accommodation;
use App\Models\AccommodationRequest;
use App\Models\Announcement;
use App\Models\CampusEvent;
use App\Models\Candidate;
use App\Models\CharityCampaign;
use App\Models\CharityContribution;
use App\Models\Election;
use App\Models\HelpTicket;
use App\Models\Leader;
use App\Models\LostFoundItem;
use App\Models\SportsActivity;
use App\Models\Venue;
use App\Models\VenueBooking;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminModulesController extends Controller
{
    // --- ANNOUNCEMENTS ---
    public function announcements(): View
    {
        $announcements = Announcement::with('author')->withCount('comments')->latest()->paginate(15);

        return view('admin.announcements', compact('announcements'));
    }

    public function storeAnnouncement(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'excerpt' => ['nullable', 'string', 'max:400'],
            'content' => ['required', 'string'],
            'category' => ['required', 'in:general,academic,welfare,election,sports,charity,urgent'],
            'is_urgent' => ['nullable', 'boolean'],
            'is_pinned' => ['nullable', 'boolean'],
            'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
        ]);

        $coverPath = null;
        if ($request->hasFile('cover_image')) {
            $file = $request->file('cover_image');
            $filename = 'ann_'.time().'_'.Str::random(4).'.'.$file->getClientOriginalExtension();
            $file->move(public_path('uploads/announcements'), $filename);
            $coverPath = 'uploads/announcements/'.$filename;
        }

        Announcement::create([
            'title' => $data['title'],
            'slug' => Str::slug($data['title']).'-'.Str::lower(Str::random(4)),
            'excerpt' => $data['excerpt'] ?? Str::limit(strip_tags($data['content']), 160),
            'content' => $data['content'],
            'category' => $data['category'],
            'is_urgent' => $request->boolean('is_urgent'),
            'is_pinned' => $request->boolean('is_pinned'),
            'is_published' => true,
            'cover_image' => $coverPath,
            'published_at' => now(),
            'author_id' => $request->user()->id,
        ]);

        return back()->with('success', 'Announcement published and student notification badges updated.');
    }

    public function deleteAnnouncement(Announcement $announcement): RedirectResponse
    {
        $announcement->delete();

        return back()->with('success', 'Announcement deleted.');
    }

    // --- ELECTIONS & CONTESTANTS ---
    public function elections(): View
    {
        $elections = Election::withCount(['candidates', 'votes'])->latest()->get();
        $activeElection = $elections->first();
        $candidates = $activeElection
            ? Candidate::with(['course', 'election'])->withCount('votes')->where('election_id', $activeElection->id)->latest()->get()
            : collect();

        return view('admin.elections', compact('elections', 'activeElection', 'candidates'));
    }

    public function storeElection(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'academic_year' => ['required', 'string', 'max:20'],
            'description' => ['nullable', 'string', 'max:1000'],
            'voting_starts_at' => ['nullable', 'date'],
            'voting_ends_at' => ['nullable', 'date'],
        ]);

        Election::create([
            ...$data,
            'is_enabled' => true,
            'applications_open' => true,
            'voting_open' => false,
            'results_published' => false,
        ]);

        return back()->with('success', 'New election cycle created.');
    }

    public function updateElectionFlags(Request $request, Election $election): RedirectResponse
    {
        $election->update([
            'is_enabled' => $request->boolean('is_enabled'),
            'applications_open' => $request->boolean('applications_open'),
            'voting_open' => $request->boolean('voting_open'),
            'results_published' => $request->boolean('results_published'),
        ]);

        return back()->with('success', 'Election controls updated successfully.');
    }

    public function updateCandidateStatus(Request $request, Candidate $candidate): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:pending,approved,declined'],
        ]);

        $candidate->update(['status' => $data['status']]);

        return back()->with('success', "{$candidate->fullname}'s candidacy marked as {$data['status']}.");
    }

    // --- CAMPUS SERVICES (VENUES, ACCOMMODATION, HELP DESK, LOST & FOUND) ---
    public function services(): View
    {
        $venues = Venue::orderBy('name')->get();
        $bookings = VenueBooking::with(['venue', 'user'])->latest()->take(20)->get();
        $accommodations = Accommodation::orderBy('name')->get();
        $accommRequests = AccommodationRequest::with(['accommodation', 'user'])->latest()->take(20)->get();
        $tickets = HelpTicket::with('user')->latest()->take(20)->get();
        $lostItems = LostFoundItem::with('reporter')->latest()->take(20)->get();

        return view('admin.services', compact('venues', 'bookings', 'accommodations', 'accommRequests', 'tickets', 'lostItems'));
    }

    public function storeVenue(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:140'],
            'code' => ['required', 'string', 'max:30', 'unique:venues,code'],
            'building' => ['required', 'string', 'max:140'],
            'capacity' => ['required', 'integer', 'min:5'],
            'category' => ['required', 'in:auditorium,lecture_hall,lab,sports,meeting,hangar'],
            'amenities' => ['nullable', 'string', 'max:400'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        Venue::create([...$data, 'code' => strtoupper($data['code']), 'status' => 'available']);

        return back()->with('success', 'Campus venue added.');
    }

    public function updateBookingStatus(Request $request, VenueBooking $booking): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:pending,approved,declined'],
            'admin_notes' => ['nullable', 'string', 'max:400'],
        ]);
        $booking->update($data);

        return back()->with('success', 'Venue booking updated.');
    }

    public function storeAccommodation(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'type' => ['required', 'in:on_campus_hostel,verified_off_campus,shared_apartment'],
            'gender_policy' => ['required', 'in:mixed,male,female'],
            'location' => ['required', 'string', 'max:180'],
            'distance_from_campus' => ['nullable', 'string', 'max:100'],
            'price_per_semester' => ['required', 'integer', 'min:0'],
            'available_rooms' => ['required', 'integer', 'min:0'],
            'total_rooms' => ['required', 'integer', 'min:1'],
            'amenities' => ['nullable', 'string', 'max:400'],
            'contact_person' => ['nullable', 'string', 'max:120'],
            'contact_phone' => ['nullable', 'string', 'max:30'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        Accommodation::create([
            ...$data,
            'slug' => Str::slug($data['name']).'-'.Str::lower(Str::random(4)),
            'is_verified' => true,
            'is_active' => true,
        ]);

        return back()->with('success', 'Accommodation residence published.');
    }

    public function updateAccommodationRequest(Request $request, AccommodationRequest $accommRequest): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:pending,approved,waitlisted,declined'],
            'admin_response' => ['nullable', 'string', 'max:400'],
        ]);
        $accommRequest->update($data);

        return back()->with('success', 'Accommodation placement request updated.');
    }

    public function updateLostFoundStatus(Request $request, LostFoundItem $item): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:open,claimed,resolved'],
        ]);
        $item->update($data);

        return back()->with('success', 'Lost & Found item status updated.');
    }

    // --- CAMPUS LIFE (EVENTS, SPORTS, CHARITY, LEADERS) ---
    public function campusLife(): View
    {
        $events = CampusEvent::withCount('rsvps')->orderByDesc('starts_at')->get();
        $sports = SportsActivity::withCount('registrations')->orderBy('name')->get();
        $campaigns = CharityCampaign::with('contributions')->latest()->get();
        $leaders = Leader::orderBy('sort_order')->get();

        return view('admin.campus_life', compact('events', 'sports', 'campaigns', 'leaders'));
    }

    public function storeEvent(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'category' => ['required', 'in:academic,social,sports,charity,governance,holiday'],
            'venue_name' => ['required', 'string', 'max:140'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date'],
            'description' => ['required', 'string', 'max:2000'],
        ]);

        CampusEvent::create([
            ...$data,
            'slug' => Str::slug($data['title']).'-'.Str::lower(Str::random(4)),
            'is_published' => true,
            'created_by' => $request->user()->id,
        ]);

        return back()->with('success', 'Campus event published.');
    }

    public function storeSport(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:140'],
            'sport_type' => ['required', 'in:football,basketball,volleyball,athletics,netball,chess,other'],
            'captain_name' => ['nullable', 'string', 'max:120'],
            'schedule' => ['required', 'string', 'max:140'],
            'venue' => ['required', 'string', 'max:140'],
            'upcoming_match' => ['nullable', 'string', 'max:180'],
            'last_result' => ['nullable', 'string', 'max:140'],
            'description' => ['nullable', 'string', 'max:800'],
        ]);

        SportsActivity::create([...$data, 'is_open_for_joining' => true, 'is_active' => true]);

        return back()->with('success', 'Sports club / team published.');
    }

    public function storeCharity(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'beneficiary' => ['required', 'string', 'max:180'],
            'target_amount' => ['required', 'integer', 'min:10000'],
            'raised_amount' => ['nullable', 'integer', 'min:0'],
            'ends_at' => ['nullable', 'date'],
            'payment_instructions' => ['nullable', 'string', 'max:1000'],
            'description' => ['required', 'string', 'max:2000'],
        ]);

        CharityCampaign::create([
            ...$data,
            'slug' => Str::slug($data['title']).'-'.Str::lower(Str::random(4)),
            'raised_amount' => $data['raised_amount'] ?? 0,
            'status' => 'active',
            'created_by' => $request->user()->id,
        ]);

        return back()->with('success', 'Charity campaign launched.');
    }

    public function verifyCharityContribution(CharityContribution $contribution): RedirectResponse
    {
        if ($contribution->status !== 'verified') {
            $contribution->update(['status' => 'verified']);
            $contribution->campaign->increment('raised_amount', $contribution->amount);
        }

        return back()->with('success', 'Contribution verified and added to live raised total.');
    }

    public function storeLeader(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:140'],
            'position' => ['required', 'string', 'max:140'],
            'category' => ['required', 'in:institute,executive,ministry,parliament'],
            'department' => ['nullable', 'string', 'max:140'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'quote' => ['nullable', 'string', 'max:300'],
            'email' => ['nullable', 'email', 'max:120'],
            'phone' => ['nullable', 'string', 'max:30'],
            'office_location' => ['nullable', 'string', 'max:140'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $filename = 'leader_'.time().'_'.Str::random(4).'.'.$file->getClientOriginalExtension();
            $file->move(public_path('uploads/leaders'), $filename);
            $photoPath = 'uploads/leaders/'.$filename;
        }

        Leader::create([
            ...$data,
            'photo' => $photoPath,
            'is_featured' => $request->boolean('is_featured'),
            'is_active' => true,
        ]);

        return back()->with('success', 'Leadership profile added.');
    }
}
