<?php

namespace App\Http\Controllers;

use App\Models\Accommodation;
use App\Models\Announcement;
use App\Models\CampusEvent;
use App\Models\CharityCampaign;
use App\Models\Community;
use App\Models\Course;
use App\Models\Election;
use App\Models\Leader;
use App\Models\SportsActivity;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $featuredLeaders = Leader::where('is_active', true)
            ->where('is_featured', true)
            ->orderBy('sort_order')
            ->take(3)
            ->get();

        $announcements = Announcement::with('author')
            ->where('is_published', true)
            ->orderByDesc('is_urgent')
            ->orderByDesc('is_pinned')
            ->latest('published_at')
            ->take(4)
            ->get();

        $activeElection = Election::where('is_enabled', true)->latest()->first();

        $upcomingEvents = CampusEvent::where('is_published', true)
            ->where('starts_at', '>=', now())
            ->orderBy('starts_at')
            ->take(3)
            ->get();

        $featuredCharity = CharityCampaign::where('status', 'active')
            ->latest()
            ->first();

        $coursesPreview = Course::with(['defaultCommunity.cr'])
            ->where('is_active', true)
            ->orderBy('level')
            ->orderBy('code')
            ->take(8)
            ->get();

        $stats = [
            'courses' => Course::where('is_active', true)->count(),
            'communities' => Community::where('is_active', true)->count(),
            'venues' => Venue::count(),
            'accommodations' => Accommodation::where('is_active', true)->count(),
            'students' => User::where('is_active', true)->count(),
            'sports' => SportsActivity::where('is_active', true)->count(),
        ];

        return view('home', compact(
            'featuredLeaders',
            'announcements',
            'activeElection',
            'upcomingEvents',
            'featuredCharity',
            'coursesPreview',
            'stats'
        ));
    }

    public function dashboard(): View
    {
        $user = Auth::user()->load('course');

        $myCourseCommunities = Community::with(['course', 'cr'])
            ->withCount(['members', 'messages'])
            ->where('is_active', true)
            ->where('course_id', $user->course_id)
            ->orderByDesc('is_official')
            ->get();

        $generalCommunities = Community::withCount(['members', 'messages'])
            ->where('is_active', true)
            ->where('type', 'general')
            ->take(4)
            ->get();

        $activeElection = Election::where('is_enabled', true)->latest()->first();
        $votedPresident = $activeElection ? $activeElection->hasUserVotedForPosition($user, 'President') : false;
        $votedPr = $activeElection ? $activeElection->hasUserVotedForPosition($user, 'Parliament Representative') : false;

        $myTickets = $user->helpTickets()->latest()->take(4)->get();
        $myAccommRequests = $user->accommodationRequests()->with('accommodation')->latest()->take(3)->get();
        $myVenueBookings = $user->venueBookings()->with('venue')->latest()->take(3)->get();

        $latestAnnouncements = Announcement::where('is_published', true)
            ->orderByDesc('is_urgent')
            ->latest('published_at')
            ->take(4)
            ->get();

        $upcomingEvents = CampusEvent::where('is_published', true)
            ->where('starts_at', '>=', now())
            ->orderBy('starts_at')
            ->take(3)
            ->get();

        return view('portal.dashboard', compact(
            'user',
            'myCourseCommunities',
            'generalCommunities',
            'activeElection',
            'votedPresident',
            'votedPr',
            'myTickets',
            'myAccommRequests',
            'myVenueBookings',
            'latestAnnouncements',
            'upcomingEvents'
        ));
    }

    public function leadership(): View
    {
        $instituteLeaders = Leader::where('is_active', true)->where('category', 'institute')->orderBy('sort_order')->get();
        $executives = Leader::where('is_active', true)->where('category', 'executive')->orderBy('sort_order')->get();
        $ministries = Leader::where('is_active', true)->whereIn('category', ['ministry', 'parliament'])->orderBy('sort_order')->get();

        return view('pages.leadership', compact('instituteLeaders', 'executives', 'ministries'));
    }
}
