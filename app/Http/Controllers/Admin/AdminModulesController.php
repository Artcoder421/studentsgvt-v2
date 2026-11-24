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

    protected function safeRemoveUpload(?string $path): void
    {
        if ($path && str_starts_with($path, 'uploads/') && file_exists(public_path($path))) {
            @unlink(public_path($path));
        }
    }

    public function updateAnnouncement(Request $request, Announcement $announcement): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'excerpt' => ['nullable', 'string', 'max:400'],
            'content' => ['required', 'string'],
            'category' => ['required', 'in:general,academic,welfare,election,sports,charity,urgent'],
            'is_urgent' => ['nullable', 'boolean'],
            'is_pinned' => ['nullable', 'boolean'],
            'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
            'remove_image' => ['nullable', 'boolean'],
        ]);

        if ($request->boolean('remove_image')) {
            $this->safeRemoveUpload($announcement->cover_image);
            $data['cover_image'] = null;
        } elseif ($request->hasFile('cover_image')) {
            $this->safeRemoveUpload($announcement->cover_image);
            $file = $request->file('cover_image');
            $filename = 'ann_'.time().'_'.Str::random(4).'.'.$file->getClientOriginalExtension();
            $file->move(public_path('uploads/announcements'), $filename);
            $data['cover_image'] = 'uploads/announcements/'.$filename;
        } else {
            unset($data['cover_image']);
        }

        unset($data['remove_image']);
        $data['is_urgent'] = $request->boolean('is_urgent');
        $data['is_pinned'] = $request->boolean('is_pinned');

        $announcement->update($data);
        \App\Models\ActivityLog::record('announcements', 'announcement_updated', "Updated announcement: {$announcement->title}.");

        return back()->with('success', 'Announcement updated.');
    }

    public function deleteAnnouncement(Announcement $announcement): RedirectResponse
    {
        $title = $announcement->title;
        $this->safeRemoveUpload($announcement->cover_image);
        $announcement->delete();
        \App\Models\ActivityLog::record('announcements', 'announcement_deleted', "Deleted announcement: {$title}.");

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

        \App\Models\ActivityLog::record('elections', 'election_flags_updated', "Updated election flags for {$election->title}.");

        return back()->with('success', 'Election controls updated successfully.');
    }

    public function openElection(Request $request, Election $election): RedirectResponse
    {
        $election->update([
            'is_enabled' => true,
            'applications_open' => true,
            'voting_open' => true,
            'ended_at' => null,
            'ended_by' => null,
        ]);

        \App\Models\ActivityLog::record('elections', 'election_opened', "Opened election cycle {$election->title} for nominations and voting.");

        return back()->with('success', 'Election is now OPEN for applications and voting.');
    }

    public function endElection(Request $request, Election $election): RedirectResponse
    {
        $election->update([
            'voting_open' => false,
            'applications_open' => false,
            'ended_at' => now(),
            'ended_by' => $request->user()->id,
            'results_published' => true,
        ]);

        // Also deactivate any running election countdown timers immediately
        \App\Models\TimerAnnouncement::where('category', 'election')
            ->where('is_active', true)
            ->update(['is_active' => false]);

        // Automatically promote elected winners to Leaders (Homepage/Dashboard) & User SONIT Titles!
        $promoted = $this->promoteElectionWinners($election);

        \App\Models\ActivityLog::record(
            'elections',
            'election_ended',
            "Ended election {$election->title} and auto-promoted ".count($promoted).' elected leader(s): '.implode(', ', $promoted)
        );

        $msg = 'Election ended immediately and official results published.';
        if (! empty($promoted)) {
            $msg .= ' Auto-promoted winners to Homepage & Leadership: '.implode(', ', $promoted).'.';
        }

        return back()->with('success', $msg);
    }

    protected function promoteElectionWinners(Election $election): array
    {
        $promoted = [];

        // 1. Campus-wide Executive & Cabinet positions
        $positionMeta = [
            'President' => ['title' => 'President, SONIT Student Government', 'user_title' => 'SONIT President', 'category' => 'executive', 'sort' => 2, 'featured' => true, 'dept' => 'SONIT Executive Office'],
            'Vice President' => ['title' => 'Vice President, SONIT Student Government', 'user_title' => 'SONIT Vice President', 'category' => 'executive', 'sort' => 3, 'featured' => true, 'dept' => 'SONIT Executive Office'],
            'General Secretary' => ['title' => 'General Secretary, SONIT', 'user_title' => 'SONIT General Secretary', 'category' => 'ministry', 'sort' => 4, 'featured' => false, 'dept' => 'SONIT Secretariat'],
            'Treasurer' => ['title' => 'Minister of Finance & Treasurer, SONIT', 'user_title' => 'SONIT Treasurer', 'category' => 'ministry', 'sort' => 5, 'featured' => false, 'dept' => 'Ministry of Finance'],
        ];

        foreach ($positionMeta as $position => $meta) {
            $winner = $election->approvedCandidates()
                ->with(['user', 'course'])
                ->withCount('votes')
                ->where('position', $position)
                ->orderByDesc('votes_count')
                ->first();

            if (! $winner || $winner->votes_count <= 0) {
                continue;
            }

            // Clear old user's title for this exact office
            \App\Models\User::where('sonit_title', $meta['user_title'])->update(['sonit_title' => null]);

            if ($winner->user) {
                $winner->user->update(['sonit_title' => $meta['user_title']]);
            }

            // Replace/update the office leader card automatically so Homepage & Leadership reflect the newly elected leader
            $existingOfficeLeader = Leader::where('category', $meta['category'])
                ->where('position', $meta['title'])
                ->first();

            $leaderData = [
                'user_id' => $winner->user_id,
                'election_id' => $election->id,
                'name' => 'Hon. '.$winner->fullname,
                'position' => $meta['title'],
                'category' => $meta['category'],
                'department' => $meta['dept'],
                'bio' => \Illuminate\Support\Str::limit($winner->manifesto, 400),
                'quote' => $winner->motto,
                'email' => $winner->contact_email ?: $winner->user?->email,
                'phone' => $winner->contact_phone ?: $winner->user?->phone,
                'sort_order' => $meta['sort'],
                'is_featured' => $meta['featured'],
                'is_active' => true,
            ];

            if ($winner->photo) {
                $leaderData['photo'] = $winner->photo;
            }

            if ($existingOfficeLeader) {
                $existingOfficeLeader->update($leaderData);
            } else {
                Leader::create($leaderData);
            }

            $promoted[] = "{$meta['user_title']} ({$winner->fullname})";
        }

        // 2. Per-Course Parliament Representative winners
        $prGroups = $election->approvedCandidates()
            ->with(['user', 'course'])
            ->withCount('votes')
            ->where('position', 'Parliament Representative')
            ->orderByDesc('votes_count')
            ->get()
            ->groupBy('course_id');

        foreach ($prGroups as $courseId => $candidates) {
            $winner = $candidates->first();
            if (! $winner || $winner->votes_count <= 0) {
                continue;
            }

            $courseCode = $winner->course?->code ?? 'NIT';
            $prTitle = "SONIT Parliament Representative ({$courseCode})";

            \App\Models\User::where('sonit_title', $prTitle)->update(['sonit_title' => null]);
            if ($winner->user) {
                $winner->user->update(['sonit_title' => $prTitle]);
            }

            Leader::updateOrCreate(
                ['category' => 'parliament', 'department' => "{$courseCode} Constituency"],
                [
                    'user_id' => $winner->user_id,
                    'election_id' => $election->id,
                    'name' => 'Hon. '.$winner->fullname,
                    'position' => $prTitle,
                    'bio' => \Illuminate\Support\Str::limit($winner->manifesto, 300),
                    'quote' => $winner->motto,
                    'photo' => $winner->photo,
                    'email' => $winner->contact_email ?: $winner->user?->email,
                    'phone' => $winner->contact_phone ?: $winner->user?->phone,
                    'sort_order' => 20,
                    'is_featured' => false,
                    'is_active' => true,
                ]
            );

            $promoted[] = "{$prTitle} ({$winner->fullname})";
        }

        return $promoted;
    }

    public function reopenElection(Request $request, Election $election): RedirectResponse
    {
        $election->update([
            'ended_at' => null,
            'ended_by' => null,
            'is_enabled' => true,
            'voting_open' => true,
        ]);

        \App\Models\ActivityLog::record('elections', 'election_reopened', "Reopened election {$election->title}.");

        return back()->with('success', 'Election has been reopened for voting.');
    }

    public function updateCandidateStatus(Request $request, Candidate $candidate): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:pending,approved,declined'],
        ]);

        $interviewStatus = match ($data['status']) {
            'approved' => 'passed',
            'declined' => 'failed',
            default => $candidate->interview_status,
        };

        $candidate->update([
            'status' => $data['status'],
            'interview_status' => $interviewStatus,
        ]);

        \App\Models\ActivityLog::record('elections', 'candidate_status_changed', "Set {$candidate->fullname} ({$candidate->position}) status to {$data['status']}.");

        return back()->with('success', "{$candidate->fullname}'s candidacy marked as {$data['status']}.");
    }

    public function updateCandidate(Request $request, Candidate $candidate): RedirectResponse
    {
        $data = $request->validate([
            'fullname' => ['required', 'string', 'max:140'],
            'reg_no' => ['required', 'string', 'max:50'],
            'position' => ['required', 'string', 'in:'.implode(',', Candidate::POSITIONS)],
            'contact_email' => ['nullable', 'email', 'max:120'],
            'contact_phone' => ['nullable', 'string', 'max:40'],
            'motto' => ['required', 'string', 'max:180'],
            'manifesto' => ['nullable', 'string', 'max:3000'],
            'payment_status' => ['required', 'in:unpaid,submitted,verified'],
            'interview_status' => ['required', 'in:pending_payment,scheduled,passed,failed'],
            'interview_date' => ['nullable', 'string', 'max:100'],
            'interview_notes' => ['nullable', 'string', 'max:1000'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_photo' => ['nullable', 'boolean'],
        ]);

        if ($request->boolean('remove_photo')) {
            $this->safeRemoveUpload($candidate->photo);
            $data['photo'] = null;
        } elseif ($request->hasFile('photo')) {
            $this->safeRemoveUpload($candidate->photo);
            $file = $request->file('photo');
            $filename = 'cand_edit_'.$candidate->id.'_'.time().'.'.$file->getClientOriginalExtension();
            $file->move(public_path('uploads/candidates'), $filename);
            $data['photo'] = 'uploads/candidates/'.$filename;
        } else {
            unset($data['photo']);
        }

        unset($data['remove_photo']);

        // Sync ballot eligibility status cleanly with interview outcome
        $data['status'] = match ($data['interview_status']) {
            'passed' => 'approved',
            'failed' => 'declined',
            default => 'pending',
        };

        $candidate->update($data);

        \App\Models\ActivityLog::record(
            'elections',
            'candidate_updated',
            "Updated contestant {$candidate->fullname} ({$candidate->position}) — Payment: {$candidate->payment_status}, Interview: {$candidate->interview_status}."
        );

        return back()->with('success', "Contestant {$candidate->fullname} updated.");
    }

    public function deleteCandidate(Candidate $candidate): RedirectResponse
    {
        $name = $candidate->fullname;
        $pos = $candidate->position;
        $this->safeRemoveUpload($candidate->photo);
        $candidate->delete();

        \App\Models\ActivityLog::record('elections', 'candidate_deleted', "Deleted contestant {$name} ({$pos}) from election.");

        return back()->with('success', "Contestant {$name} removed from the election.");
    }

    public function updateElection(Request $request, Election $election): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'academic_year' => ['required', 'string', 'max:20'],
            'description' => ['nullable', 'string', 'max:1000'],
            'voting_ends_at' => ['nullable', 'date'],
        ]);

        $election->update($data);
        \App\Models\ActivityLog::record('elections', 'election_updated', "Updated election details: {$election->title}.");

        return back()->with('success', 'Election details updated.');
    }

    public function deleteElection(Election $election): RedirectResponse
    {
        $title = $election->title;
        $election->delete();
        \App\Models\ActivityLog::record('elections', 'election_deleted', "Deleted election cycle: {$title}.");

        return back()->with('success', "Election cycle {$title} deleted.");
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
            'event_when' => ['nullable', 'string', 'max:140'],
            'event_where' => ['nullable', 'string', 'max:180'],
            'planned_activities' => ['nullable', 'string', 'max:1500'],
            'account_number' => ['nullable', 'string', 'max:180'],
            'contact_info' => ['nullable', 'string', 'max:180'],
            'target_amount' => ['nullable', 'integer', 'min:0'],
            'raised_amount' => ['nullable', 'integer', 'min:0'],
            'ends_at' => ['nullable', 'date'],
            'payment_instructions' => ['nullable', 'string', 'max:1000'],
            'description' => ['required', 'string', 'max:2000'],
        ]);

        CharityCampaign::create([
            ...$data,
            'slug' => Str::slug($data['title']).'-'.Str::lower(Str::random(4)),
            'target_amount' => $data['target_amount'] ?? 0,
            'raised_amount' => $data['raised_amount'] ?? 0,
            'status' => 'active',
            'created_by' => $request->user()->id,
        ]);

        return back()->with('success', 'Charity / Tour notice published.');
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

        \App\Models\ActivityLog::record('leadership', 'leader_created', "Added leader profile: {$data['name']} ({$data['position']}).");

        return back()->with('success', 'Leadership profile added.');
    }

    public function updateLeader(Request $request, Leader $leader): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:140'],
            'position' => ['required', 'string', 'max:140'],
            'category' => ['nullable', 'in:institute,executive,ministry,parliament'],
            'department' => ['nullable', 'string', 'max:140'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'quote' => ['nullable', 'string', 'max:300'],
            'email' => ['nullable', 'email', 'max:120'],
            'phone' => ['nullable', 'string', 'max:30'],
            'office_location' => ['nullable', 'string', 'max:140'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_photo' => ['nullable', 'boolean'],
        ]);

        if ($request->boolean('remove_photo')) {
            $this->safeRemoveUpload($leader->photo);
            $data['photo'] = null;
        } elseif ($request->hasFile('photo')) {
            $this->safeRemoveUpload($leader->photo);
            $file = $request->file('photo');
            $filename = 'leader_edit_'.$leader->id.'_'.time().'.'.$file->getClientOriginalExtension();
            $file->move(public_path('uploads/leaders'), $filename);
            $data['photo'] = 'uploads/leaders/'.$filename;
        } else {
            unset($data['photo']);
        }

        unset($data['remove_photo']);
        $leader->update(array_filter($data, fn ($v, $k) => $v !== null || $k === 'photo', ARRAY_FILTER_USE_BOTH));

        \App\Models\ActivityLog::record('leadership', 'leader_updated', "Updated leader profile: {$leader->name} ({$leader->position}).");

        return back()->with('success', "Updated {$leader->name}'s official leadership profile.");
    }

    public function deleteLeader(Leader $leader): RedirectResponse
    {
        $name = $leader->name;
        $this->safeRemoveUpload($leader->photo);
        $leader->delete();
        \App\Models\ActivityLog::record('leadership', 'leader_deleted', "Deleted leader profile: {$name}.");

        return back()->with('success', "Leader {$name} removed.");
    }

    public function updateVenue(Request $request, Venue $venue): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:140'],
            'code' => ['required', 'string', 'max:30', 'unique:venues,code,'.$venue->id],
            'building' => ['required', 'string', 'max:140'],
            'capacity' => ['required', 'integer', 'min:5'],
            'category' => ['required', 'in:auditorium,lecture_hall,lab,sports,meeting,hangar'],
            'status' => ['required', 'in:available,booked,maintenance'],
            'amenities' => ['nullable', 'string', 'max:400'],
            'description' => ['nullable', 'string', 'max:1000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
            'remove_image' => ['nullable', 'boolean'],
        ]);

        if ($request->boolean('remove_image')) {
            $this->safeRemoveUpload($venue->image);
            $data['image'] = null;
        } elseif ($request->hasFile('image')) {
            $this->safeRemoveUpload($venue->image);
            $file = $request->file('image');
            $filename = 'venue_'.$venue->id.'_'.time().'.'.$file->getClientOriginalExtension();
            $file->move(public_path('uploads/venues'), $filename);
            $data['image'] = 'uploads/venues/'.$filename;
        } else {
            unset($data['image']);
        }
        unset($data['remove_image']);

        $venue->update([...$data, 'code' => strtoupper($data['code'])]);
        \App\Models\ActivityLog::record('services', 'venue_updated', "Updated venue {$venue->name}.");

        return back()->with('success', "Venue {$venue->name} updated.");
    }

    public function deleteVenue(Venue $venue): RedirectResponse
    {
        $name = $venue->name;
        $this->safeRemoveUpload($venue->image);
        $venue->delete();
        \App\Models\ActivityLog::record('services', 'venue_deleted', "Deleted venue {$name}.");

        return back()->with('success', "Venue {$name} deleted.");
    }

    public function updateAccommodation(Request $request, Accommodation $accommodation): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'type' => ['required', 'in:on_campus_hostel,verified_off_campus,shared_apartment'],
            'gender_policy' => ['required', 'in:mixed,male,female'],
            'location' => ['required', 'string', 'max:180'],
            'price_per_semester' => ['required', 'integer', 'min:0'],
            'available_rooms' => ['required', 'integer', 'min:0'],
            'total_rooms' => ['required', 'integer', 'min:1'],
            'contact_person' => ['nullable', 'string', 'max:120'],
            'contact_phone' => ['nullable', 'string', 'max:30'],
            'description' => ['nullable', 'string', 'max:1000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
            'remove_image' => ['nullable', 'boolean'],
        ]);

        if ($request->boolean('remove_image')) {
            $this->safeRemoveUpload($accommodation->image);
            $data['image'] = null;
        } elseif ($request->hasFile('image')) {
            $this->safeRemoveUpload($accommodation->image);
            $file = $request->file('image');
            $filename = 'acc_'.$accommodation->id.'_'.time().'.'.$file->getClientOriginalExtension();
            $file->move(public_path('uploads/accommodations'), $filename);
            $data['image'] = 'uploads/accommodations/'.$filename;
        } else {
            unset($data['image']);
        }
        unset($data['remove_image']);

        $accommodation->update($data);
        \App\Models\ActivityLog::record('services', 'accommodation_updated', "Updated accommodation {$accommodation->name}.");

        return back()->with('success', "Accommodation {$accommodation->name} updated.");
    }

    public function deleteAccommodation(Accommodation $accommodation): RedirectResponse
    {
        $name = $accommodation->name;
        $this->safeRemoveUpload($accommodation->image);
        $accommodation->delete();
        \App\Models\ActivityLog::record('services', 'accommodation_deleted', "Deleted accommodation {$name}.");

        return back()->with('success', "Accommodation {$name} deleted.");
    }

    public function deleteLostFound(LostFoundItem $item): RedirectResponse
    {
        $name = $item->item_name;
        $this->safeRemoveUpload($item->image);
        $item->delete();
        \App\Models\ActivityLog::record('services', 'lost_found_deleted', "Deleted Lost & Found item {$name}.");

        return back()->with('success', "Lost & Found item {$name} deleted.");
    }

    public function deleteTicket(HelpTicket $ticket): RedirectResponse
    {
        $num = $ticket->ticket_number;
        $ticket->delete();
        \App\Models\ActivityLog::record('services', 'ticket_deleted', "Deleted help ticket {$num}.");

        return back()->with('success', "Support ticket {$num} deleted.");
    }

    public function updateEvent(Request $request, CampusEvent $event): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'category' => ['required', 'in:academic,social,sports,charity,governance,holiday'],
            'venue_name' => ['required', 'string', 'max:140'],
            'starts_at' => ['required', 'date'],
            'description' => ['required', 'string', 'max:2000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
            'remove_image' => ['nullable', 'boolean'],
        ]);

        if ($request->boolean('remove_image')) {
            $this->safeRemoveUpload($event->image);
            $data['image'] = null;
        } elseif ($request->hasFile('image')) {
            $this->safeRemoveUpload($event->image);
            $file = $request->file('image');
            $filename = 'ev_'.$event->id.'_'.time().'.'.$file->getClientOriginalExtension();
            $file->move(public_path('uploads/events'), $filename);
            $data['image'] = 'uploads/events/'.$filename;
        } else {
            unset($data['image']);
        }
        unset($data['remove_image']);

        $event->update($data);
        \App\Models\ActivityLog::record('campus_life', 'event_updated', "Updated event {$event->title}.");

        return back()->with('success', "Event {$event->title} updated.");
    }

    public function deleteEvent(CampusEvent $event): RedirectResponse
    {
        $title = $event->title;
        $this->safeRemoveUpload($event->image);
        $event->delete();
        \App\Models\ActivityLog::record('campus_life', 'event_deleted', "Deleted event {$title}.");

        return back()->with('success', "Event {$title} deleted.");
    }

    public function updateSport(Request $request, SportsActivity $sport): RedirectResponse
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

        $sport->update($data);
        \App\Models\ActivityLog::record('campus_life', 'sport_updated', "Updated sports club {$sport->name}.");

        return back()->with('success', "Sports club {$sport->name} updated.");
    }

    public function deleteSport(SportsActivity $sport): RedirectResponse
    {
        $name = $sport->name;
        $sport->delete();
        \App\Models\ActivityLog::record('campus_life', 'sport_deleted', "Deleted sports club {$name}.");

        return back()->with('success', "Sports club {$name} deleted.");
    }

    public function updateCharity(Request $request, CharityCampaign $campaign): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'beneficiary' => ['required', 'string', 'max:180'],
            'event_when' => ['nullable', 'string', 'max:140'],
            'event_where' => ['nullable', 'string', 'max:180'],
            'planned_activities' => ['nullable', 'string', 'max:1500'],
            'account_number' => ['nullable', 'string', 'max:180'],
            'contact_info' => ['nullable', 'string', 'max:180'],
            'ends_at' => ['nullable', 'date'],
            'description' => ['required', 'string', 'max:2000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
            'remove_image' => ['nullable', 'boolean'],
        ]);

        if ($request->boolean('remove_image')) {
            $this->safeRemoveUpload($campaign->image);
            $data['image'] = null;
        } elseif ($request->hasFile('image')) {
            $this->safeRemoveUpload($campaign->image);
            $file = $request->file('image');
            $filename = 'charity_'.$campaign->id.'_'.time().'.'.$file->getClientOriginalExtension();
            $file->move(public_path('uploads/charity'), $filename);
            $data['image'] = 'uploads/charity/'.$filename;
        } else {
            unset($data['image']);
        }
        unset($data['remove_image']);

        $campaign->update($data);
        \App\Models\ActivityLog::record('campus_life', 'charity_updated', "Updated charity notice {$campaign->title}.");

        return back()->with('success', "Charity notice {$campaign->title} updated.");
    }

    public function deleteCharity(CharityCampaign $campaign): RedirectResponse
    {
        $title = $campaign->title;
        $this->safeRemoveUpload($campaign->image);
        $campaign->delete();
        \App\Models\ActivityLog::record('campus_life', 'charity_deleted', "Deleted charity notice {$title}.");

        return back()->with('success', "Charity notice {$title} deleted.");
    }
}
