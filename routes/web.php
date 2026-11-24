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

    // Communities (Strictly authenticated; students/CRs only see their own course & general channels)
    Route::get('/communities', [CommunityController::class, 'index'])->name('communities.index');
    Route::post('/communities', [CommunityController::class, 'store'])->name('communities.store');
    Route::get('/communities/{community:slug}', [CommunityController::class, 'show'])->name('communities.show');
    Route::post('/communities/{community:slug}/join', [CommunityController::class, 'join'])->name('communities.join');
    Route::post('/communities/{community:slug}/leave', [CommunityController::class, 'leave'])->name('communities.leave');
    Route::post('/communities/{community:slug}/messages', [CommunityController::class, 'postMessage'])->name('communities.messages.store');
    Route::post('/communities/{community:slug}/messages/{message}/pin', [CommunityController::class, 'togglePinMessage'])->name('communities.messages.pin');
    Route::post('/communities/{community:slug}/toggle-lock', [CommunityController::class, 'toggleLock'])->name('communities.toggle-lock');

    // Elections voting, nominations & fee payment reference
    Route::get('/elections/ballot', [ElectionController::class, 'ballot'])->name('elections.ballot');
    Route::post('/elections/vote', [ElectionController::class, 'vote'])->name('elections.vote');
    Route::get('/elections/apply', [ElectionController::class, 'showApply'])->name('elections.apply');
    Route::post('/elections/apply', [ElectionController::class, 'apply'])->name('elections.apply.submit');
    Route::post('/elections/application/{candidate}/payment', [ElectionController::class, 'submitPayment'])->name('elections.application.payment');

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
    Route::get('/logs', [AdminDashboardController::class, 'logs'])->name('logs');

    // User & Role Governance (Admin can edit/delete users & remove avatars; role changes strictly Super Admin)
    Route::get('/users', [AdminDashboardController::class, 'users'])->name('users');
    Route::put('/users/{user}', [AdminDashboardController::class, 'updateUser'])->name('users.update');
    Route::post('/users/{user}/remove-avatar', [AdminDashboardController::class, 'removeUserAvatar'])->name('users.remove-avatar');
    Route::delete('/users/{user}', [AdminDashboardController::class, 'deleteUser'])->name('users.delete');
    Route::post('/users/{user}/role', [AdminDashboardController::class, 'updateUserRole'])
        ->middleware('role:super_admin')
        ->name('users.role');

    // Courses & Default Course Communities / CR Seats
    Route::get('/courses', [AdminDashboardController::class, 'courses'])->name('courses');
    Route::post('/courses', [AdminDashboardController::class, 'storeCourse'])->name('courses.store');
    Route::put('/courses/{course}', [AdminDashboardController::class, 'updateCourse'])->name('courses.update');
    Route::delete('/courses/{course}', [AdminDashboardController::class, 'deleteCourse'])->name('courses.delete');
    Route::post('/courses/{course}/assign-cr', [AdminDashboardController::class, 'assignCourseCr'])->name('courses.assign-cr');

    // Editable Motto, Vision, Mission & Site Settings
    Route::get('/settings', [AdminDashboardController::class, 'settings'])->name('settings');
    Route::post('/settings', [AdminDashboardController::class, 'updateSettings'])->name('settings.update');

    // Countdown Timer Announcements
    Route::get('/timers', [AdminDashboardController::class, 'timers'])->name('timers');
    Route::post('/timers', [AdminDashboardController::class, 'storeTimer'])->name('timers.store');
    Route::put('/timers/{timer}', [AdminDashboardController::class, 'updateTimer'])->name('timers.update');
    Route::post('/timers/{timer}/toggle', [AdminDashboardController::class, 'toggleTimer'])->name('timers.toggle');
    Route::delete('/timers/{timer}', [AdminDashboardController::class, 'deleteTimer'])->name('timers.delete');

    // Announcements & Urgent Alerts
    Route::get('/announcements', [AdminModulesController::class, 'announcements'])->name('announcements');
    Route::post('/announcements', [AdminModulesController::class, 'storeAnnouncement'])->name('announcements.store');
    Route::put('/announcements/{announcement}', [AdminModulesController::class, 'updateAnnouncement'])->name('announcements.update');
    Route::delete('/announcements/{announcement}', [AdminModulesController::class, 'deleteAnnouncement'])->name('announcements.delete');

    // Elections & Contestants (Open, End/Auto-Promote, Edit, Delete)
    Route::get('/elections', [AdminModulesController::class, 'elections'])->name('elections');
    Route::post('/elections', [AdminModulesController::class, 'storeElection'])->name('elections.store');
    Route::put('/elections/{election}', [AdminModulesController::class, 'updateElection'])->name('elections.update');
    Route::delete('/elections/{election}', [AdminModulesController::class, 'deleteElection'])->name('elections.delete');
    Route::post('/elections/{election}/flags', [AdminModulesController::class, 'updateElectionFlags'])->name('elections.flags');
    Route::post('/elections/{election}/open', [AdminModulesController::class, 'openElection'])->name('elections.open');
    Route::post('/elections/{election}/end', [AdminModulesController::class, 'endElection'])->name('elections.end');
    Route::post('/elections/{election}/reopen', [AdminModulesController::class, 'reopenElection'])->name('elections.reopen');
    Route::post('/candidates/{candidate}/status', [AdminModulesController::class, 'updateCandidateStatus'])->name('candidates.status');
    Route::put('/candidates/{candidate}', [AdminModulesController::class, 'updateCandidate'])->name('candidates.update');
    Route::delete('/candidates/{candidate}', [AdminModulesController::class, 'deleteCandidate'])->name('candidates.delete');

    // Campus Services (Venues, Accommodation, Help Desk, Lost & Found)
    Route::get('/services', [AdminModulesController::class, 'services'])->name('services');
    Route::post('/venues', [AdminModulesController::class, 'storeVenue'])->name('venues.store');
    Route::put('/venues/{venue}', [AdminModulesController::class, 'updateVenue'])->name('venues.update');
    Route::delete('/venues/{venue}', [AdminModulesController::class, 'deleteVenue'])->name('venues.delete');
    Route::post('/venue-bookings/{booking}', [AdminModulesController::class, 'updateBookingStatus'])->name('bookings.status');
    Route::post('/accommodations', [AdminModulesController::class, 'storeAccommodation'])->name('accommodations.store');
    Route::put('/accommodations/{accommodation}', [AdminModulesController::class, 'updateAccommodation'])->name('accommodations.update');
    Route::delete('/accommodations/{accommodation}', [AdminModulesController::class, 'deleteAccommodation'])->name('accommodations.delete');
    Route::post('/accommodation-requests/{accommRequest}', [AdminModulesController::class, 'updateAccommodationRequest'])->name('accommodation-requests.status');
    Route::post('/lost-found/{item}/status', [AdminModulesController::class, 'updateLostFoundStatus'])->name('lost-found.status');
    Route::delete('/lost-found/{item}', [AdminModulesController::class, 'deleteLostFound'])->name('lost-found.delete');
    Route::delete('/help-tickets/{ticket}', [AdminModulesController::class, 'deleteTicket'])->name('tickets.delete');

    // Campus Life (Events, Sports, Charity, Leaders)
    Route::get('/campus-life', [AdminModulesController::class, 'campusLife'])->name('campus-life');
    Route::post('/events', [AdminModulesController::class, 'storeEvent'])->name('events.store');
    Route::put('/events/{event}', [AdminModulesController::class, 'updateEvent'])->name('events.update');
    Route::delete('/events/{event}', [AdminModulesController::class, 'deleteEvent'])->name('events.delete');
    Route::post('/sports', [AdminModulesController::class, 'storeSport'])->name('sports.store');
    Route::put('/sports/{sport}', [AdminModulesController::class, 'updateSport'])->name('sports.update');
    Route::delete('/sports/{sport}', [AdminModulesController::class, 'deleteSport'])->name('sports.delete');
    Route::post('/charity', [AdminModulesController::class, 'storeCharity'])->name('charity.store');
    Route::put('/charity/{campaign}', [AdminModulesController::class, 'updateCharity'])->name('charity.update');
    Route::delete('/charity/{campaign}', [AdminModulesController::class, 'deleteCharity'])->name('charity.delete');
    Route::post('/charity/contributions/{contribution}/verify', [AdminModulesController::class, 'verifyCharityContribution'])->name('charity.verify');
    Route::post('/leaders', [AdminModulesController::class, 'storeLeader'])->name('leaders.store');
    Route::put('/leaders/{leader}', [AdminModulesController::class, 'updateLeader'])->name('leaders.update');
    Route::delete('/leaders/{leader}', [AdminModulesController::class, 'deleteLeader'])->name('leaders.delete');
});
