<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminModulesController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CampusLifeController;
use App\Http\Controllers\CampusServiceController;
use App\Http\Controllers\CommunityController;
use App\Http\Controllers\ElectionController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

// Public & Investor-Ready Landing Pages
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/leadership', [HomeController::class, 'leadership'])->name('leadership');

// Announcements (Public browsing; comments & read tracking require auth)
Route::get('/announcements', [AnnouncementController::class, 'index'])->name('announcements.index');
Route::get('/announcements/{announcement:slug}', [AnnouncementController::class, 'show'])->name('announcements.show');

// Communities (Public directory preview; channel interaction requires auth)
Route::get('/communities', [CommunityController::class, 'index'])->name('communities.index');
Route::get('/communities/{community:slug}', [CommunityController::class, 'show'])->name('communities.show');

// Elections (Public overview & published results; ballot & nominations require auth)
Route::get('/elections', [ElectionController::class, 'index'])->name('elections.index');
Route::get('/elections/results', [ElectionController::class, 'results'])->name('elections.results');

// Campus Services & Campus Life (Publicly browsable; submissions require auth)
Route::get('/venues', [CampusServiceController::class, 'venues'])->name('venues.index');
Route::get('/accommodation', [CampusServiceController::class, 'accommodations'])->name('accommodations.index');
Route::get('/help-desk', [CampusServiceController::class, 'helpDesk'])->name('help-desk.index');
Route::get('/lost-found', [CampusServiceController::class, 'lostFound'])->name('lost-found.index');
Route::get('/events', [CampusLifeController::class, 'events'])->name('events.index');
Route::get('/sports', [CampusLifeController::class, 'sports'])->name('sports.index');
Route::get('/charity', [CampusLifeController::class, 'charity'])->name('charity.index');

// Authentication
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.submit');
});

// Authenticated Student, Class Rep & Admin Portal Actions
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [HomeController::class, 'dashboard'])->name('dashboard');

    // Profile & Password
    Route::get('/profile', [AuthController::class, 'profile'])->name('profile.edit');
    Route::post('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');
    Route::post('/profile/password', [AuthController::class, 'updatePassword'])->name('profile.password');

    // Announcements interactions
    Route::post('/announcements/mark-all-read', [AnnouncementController::class, 'markAllRead'])->name('announcements.mark-all-read');
    Route::post('/announcements/{announcement:slug}/comment', [AnnouncementController::class, 'comment'])->name('announcements.comment');

    // Communities interactions (CR course-scoping & Admin moderation enforced in controller)
    Route::post('/communities', [CommunityController::class, 'store'])->name('communities.store');
    Route::post('/communities/{community:slug}/join', [CommunityController::class, 'join'])->name('communities.join');
    Route::post('/communities/{community:slug}/leave', [CommunityController::class, 'leave'])->name('communities.leave');
    Route::post('/communities/{community:slug}/messages', [CommunityController::class, 'postMessage'])->name('communities.messages.store');
    Route::post('/communities/{community:slug}/messages/{message}/pin', [CommunityController::class, 'togglePinMessage'])->name('communities.messages.pin');
    Route::post('/communities/{community:slug}/toggle-lock', [CommunityController::class, 'toggleLock'])->name('communities.toggle-lock');

    // Elections voting & nominations
    Route::get('/elections/ballot', [ElectionController::class, 'ballot'])->name('elections.ballot');
    Route::post('/elections/vote', [ElectionController::class, 'vote'])->name('elections.vote');
    Route::get('/elections/apply', [ElectionController::class, 'showApply'])->name('elections.apply');
    Route::post('/elections/apply', [ElectionController::class, 'apply'])->name('elections.apply.submit');

    // Campus Services submissions
    Route::post('/venues/book', [CampusServiceController::class, 'bookVenue'])->name('venues.book');
    Route::post('/accommodation/request', [CampusServiceController::class, 'requestAccommodation'])->name('accommodations.request');
    Route::post('/help-desk', [CampusServiceController::class, 'storeTicket'])->name('help-desk.store');
    Route::get('/help-desk/{ticket:ticket_number}', [CampusServiceController::class, 'showTicket'])->name('help-desk.show');
    Route::post('/help-desk/{ticket:ticket_number}/reply', [CampusServiceController::class, 'replyTicket'])->name('help-desk.reply');
    Route::post('/lost-found', [CampusServiceController::class, 'storeLostFound'])->name('lost-found.store');
    Route::post('/lost-found/{item}/claim', [CampusServiceController::class, 'claimLostFound'])->name('lost-found.claim');

    // Campus Life interactions
    Route::post('/events/{event:slug}/rsvp', [CampusLifeController::class, 'rsvpEvent'])->name('events.rsvp');
    Route::post('/sports/{sport}/join', [CampusLifeController::class, 'joinSport'])->name('sports.join');
    Route::post('/charity/{campaign:slug}/pledge', [CampusLifeController::class, 'pledgeCharity'])->name('charity.pledge');
});

// Admin Command Center (SONIT Admin & Super Admin)
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:super_admin,admin'])->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

    // User & Role Governance (Viewable by Admin; role updates strictly Super Admin)
    Route::get('/users', [AdminDashboardController::class, 'users'])->name('users');
    Route::post('/users/{user}/role', [AdminDashboardController::class, 'updateUserRole'])
        ->middleware('role:super_admin')
        ->name('users.role');

    // Courses & Default Course Communities / CR Seats
    Route::get('/courses', [AdminDashboardController::class, 'courses'])->name('courses');
    Route::post('/courses', [AdminDashboardController::class, 'storeCourse'])->name('courses.store');
    Route::post('/courses/{course}/assign-cr', [AdminDashboardController::class, 'assignCourseCr'])->name('courses.assign-cr');

    // Editable Motto, Vision, Mission & Site Settings
    Route::get('/settings', [AdminDashboardController::class, 'settings'])->name('settings');
    Route::post('/settings', [AdminDashboardController::class, 'updateSettings'])->name('settings.update');

    // Countdown Timer Announcements
    Route::get('/timers', [AdminDashboardController::class, 'timers'])->name('timers');
    Route::post('/timers', [AdminDashboardController::class, 'storeTimer'])->name('timers.store');
    Route::post('/timers/{timer}/toggle', [AdminDashboardController::class, 'toggleTimer'])->name('timers.toggle');
    Route::delete('/timers/{timer}', [AdminDashboardController::class, 'deleteTimer'])->name('timers.delete');

    // Announcements & Urgent Alerts
    Route::get('/announcements', [AdminModulesController::class, 'announcements'])->name('announcements');
    Route::post('/announcements', [AdminModulesController::class, 'storeAnnouncement'])->name('announcements.store');
    Route::delete('/announcements/{announcement}', [AdminModulesController::class, 'deleteAnnouncement'])->name('announcements.delete');

    // Elections & Contestants
    Route::get('/elections', [AdminModulesController::class, 'elections'])->name('elections');
    Route::post('/elections', [AdminModulesController::class, 'storeElection'])->name('elections.store');
    Route::post('/elections/{election}/flags', [AdminModulesController::class, 'updateElectionFlags'])->name('elections.flags');
    Route::post('/elections/{election}/end', [AdminModulesController::class, 'endElection'])->name('elections.end');
    Route::post('/elections/{election}/reopen', [AdminModulesController::class, 'reopenElection'])->name('elections.reopen');
    Route::post('/candidates/{candidate}/status', [AdminModulesController::class, 'updateCandidateStatus'])->name('candidates.status');

    // Campus Services (Venues, Accommodation, Help Desk, Lost & Found)
    Route::get('/services', [AdminModulesController::class, 'services'])->name('services');
    Route::post('/venues', [AdminModulesController::class, 'storeVenue'])->name('venues.store');
    Route::post('/venue-bookings/{booking}', [AdminModulesController::class, 'updateBookingStatus'])->name('bookings.status');
    Route::post('/accommodations', [AdminModulesController::class, 'storeAccommodation'])->name('accommodations.store');
    Route::post('/accommodation-requests/{accommRequest}', [AdminModulesController::class, 'updateAccommodationRequest'])->name('accommodation-requests.status');
    Route::post('/lost-found/{item}/status', [AdminModulesController::class, 'updateLostFoundStatus'])->name('lost-found.status');

    // Campus Life (Events, Sports, Charity, Leaders)
    Route::get('/campus-life', [AdminModulesController::class, 'campusLife'])->name('campus-life');
    Route::post('/events', [AdminModulesController::class, 'storeEvent'])->name('events.store');
    Route::post('/sports', [AdminModulesController::class, 'storeSport'])->name('sports.store');
    Route::post('/charity', [AdminModulesController::class, 'storeCharity'])->name('charity.store');
    Route::post('/charity/contributions/{contribution}/verify', [AdminModulesController::class, 'verifyCharityContribution'])->name('charity.verify');
    Route::post('/leaders', [AdminModulesController::class, 'storeLeader'])->name('leaders.store');
});
