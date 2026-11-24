<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AccommodationRequest;
use App\Models\Announcement;
use App\Models\Candidate;
use App\Models\CharityCampaign;
use App\Models\Community;
use App\Models\Course;
use App\Models\Election;
use App\Models\HelpTicket;
use App\Models\LostFoundItem;
use App\Models\SiteSetting;
use App\Models\TimerAnnouncement;
use App\Models\User;
use App\Models\VenueBooking;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(): View
    {
        $metrics = [
            'users' => User::count(),
            'courses' => Course::count(),
            'communities' => Community::count(),
            'vacant_crs' => Community::where('type', 'course_default')->whereNull('cr_user_id')->count(),
            'open_tickets' => HelpTicket::whereIn('status', ['open', 'in_progress'])->count(),
            'pending_bookings' => VenueBooking::where('status', 'pending')->count(),
            'pending_accommodations' => AccommodationRequest::where('status', 'pending')->count(),
            'pending_candidates' => Candidate::where('status', 'pending')->count(),
            'active_timers' => TimerAnnouncement::live()->count(),
            'urgent_announcements' => Announcement::where('is_urgent', true)->where('is_published', true)->count(),
            'charity_raised' => CharityCampaign::sum('raised_amount'),
            'open_lost_found' => LostFoundItem::where('status', 'open')->count(),
            'total_visits' => \App\Models\VisitorLog::count(),
            'today_visits' => \App\Models\VisitorLog::where('visited_at', '>=', now()->startOfDay())->count(),
            'unique_visitors' => \App\Models\VisitorLog::distinct('ip_address')->count('ip_address'),
            'total_activities' => \App\Models\ActivityLog::count(),
        ];

        $recentTickets = HelpTicket::with('user')->latest()->take(5)->get();
        $recentBookings = VenueBooking::with(['venue', 'user'])->latest()->take(5)->get();
        $activeElection = Election::withCount(['candidates', 'votes'])->latest()->first();

        return view('admin.dashboard', compact('metrics', 'recentTickets', 'recentBookings', 'activeElection'));
    }

    // --- SUPER ADMIN ROLE & USER GOVERNANCE ---
    public function users(Request $request): View
    {
        $search = trim((string) $request->query('q'));
        $roleFilter = $request->query('role');

        $users = User::with('course')
            ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('reg_no', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"))
            ->when($roleFilter, fn ($q) => $q->where('role', $roleFilter))
            ->orderByRaw("CASE role WHEN 'super_admin' THEN 1 WHEN 'admin' THEN 2 WHEN 'class_rep' THEN 3 ELSE 4 END")
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $courses = Course::orderBy('code')->get();

        return view('admin.users', compact('users', 'courses', 'search', 'roleFilter'));
    }

    public function updateUserRole(Request $request, User $user): RedirectResponse
    {
        abort_unless($request->user()->isSuperAdmin(), 403, 'Only Super Administrators can assign or revoke user roles.');

        $data = $request->validate([
            'role' => ['required', 'in:super_admin,admin,class_rep,student'],
            'course_id' => ['nullable', 'exists:courses,id'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        // Prevent super admin from accidentally demoting or locking themselves out
        if ((int) $user->id === (int) $request->user()->id && $data['role'] !== User::ROLE_SUPER_ADMIN) {
            return back()->with('error', 'You cannot demote your own Super Admin account.');
        }

        $user->update([
            'role' => $data['role'],
            'course_id' => $data['course_id'] ?? $user->course_id,
            'is_active' => $request->has('is_active') ? $request->boolean('is_active') : $user->is_active,
        ]);

        $user->syncCourseCommunities();

        // If promoted to class_rep and has a course, assign their default course community CR seat if currently vacant
        if ($user->role === User::ROLE_CLASS_REP && $user->course_id) {
            $defaultCommunity = Community::where('type', 'course_default')
                ->where('course_id', $user->course_id)
                ->first();

            if ($defaultCommunity && $defaultCommunity->cr_user_id === null) {
                $defaultCommunity->update(['cr_user_id' => $user->id]);
                $defaultCommunity->members()->syncWithoutDetaching([
                    $user->id => ['member_role' => 'cr', 'joined_at' => now()],
                ]);
            }
        }

        return back()->with('success', "Updated {$user->name}'s role to {$user->roleLabel()}.");
    }

    public function updateUser(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'reg_no' => ['nullable', 'string', 'max:50', 'unique:users,reg_no,'.$user->id],
            'email' => ['required', 'email', 'max:120', 'unique:users,email,'.$user->id],
            'phone' => ['nullable', 'string', 'max:30'],
            'course_id' => ['nullable', 'exists:courses,id'],
            'academic_level' => ['nullable', 'string', 'max:40'],
            'sonit_title' => ['nullable', 'string', 'max:120'],
            'is_active' => ['nullable', 'boolean'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_avatar' => ['nullable', 'boolean'],
        ]);

        if ($request->boolean('remove_avatar')) {
            if ($user->avatar && str_starts_with($user->avatar, 'uploads/') && file_exists(public_path($user->avatar))) {
                @unlink(public_path($user->avatar));
            }
            $data['avatar'] = null;
        } elseif ($request->hasFile('avatar')) {
            if ($user->avatar && str_starts_with($user->avatar, 'uploads/') && file_exists(public_path($user->avatar))) {
                @unlink(public_path($user->avatar));
            }
            $file = $request->file('avatar');
            $filename = 'avatar_'.$user->id.'_'.time().'.'.$file->getClientOriginalExtension();
            $file->move(public_path('uploads/avatars'), $filename);
            $data['avatar'] = 'uploads/avatars/'.$filename;
        } else {
            unset($data['avatar']);
        }

        unset($data['remove_avatar']);
        $data['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : $user->is_active;

        $user->update($data);
        $user->syncCourseCommunities();

        \App\Models\ActivityLog::record('users', 'user_updated', "Admin updated account details for {$user->name} ({$user->reg_no}).");

        return back()->with('success', "Updated user {$user->name}.");
    }

    public function removeUserAvatar(User $user): RedirectResponse
    {
        if ($user->avatar && str_starts_with($user->avatar, 'uploads/') && file_exists(public_path($user->avatar))) {
            @unlink(public_path($user->avatar));
        }

        $user->update(['avatar' => null]);

        \App\Models\ActivityLog::record('users', 'avatar_removed', "Admin removed profile photo of {$user->name}.");

        return back()->with('success', "Removed profile photo for {$user->name}.");
    }

    public function deleteUser(Request $request, User $user): RedirectResponse
    {
        if ((int) $user->id === (int) $request->user()->id) {
            return back()->with('error', 'You cannot delete your own active admin account.');
        }

        if ($user->isSuperAdmin() && ! $request->user()->isSuperAdmin()) {
            abort(403, 'Only a Super Admin can delete another Super Admin account.');
        }

        $name = $user->name;
        if ($user->avatar && str_starts_with($user->avatar, 'uploads/') && file_exists(public_path($user->avatar))) {
            @unlink(public_path($user->avatar));
        }
        $user->delete();

        \App\Models\ActivityLog::record('users', 'user_deleted', "Admin deleted user account {$name}.");

        return back()->with('success', "User {$name} has been deleted.");
    }

    // --- COURSES & DEFAULT COMMUNITIES + CR SEAT MANAGEMENT ---
    public function courses(): View
    {
        $courses = Course::with(['defaultCommunity.cr', 'defaultCommunity' => fn ($q) => $q->withCount('members')])
            ->withCount(['students', 'communities'])
            ->orderBy('faculty')
            ->orderBy('code')
            ->get();

        $eligibleCrs = User::with('course')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.courses', compact('courses', 'eligibleCrs'));
    }

    public function storeCourse(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:20', 'unique:courses,code'],
            'name' => ['required', 'string', 'max:180'],
            'level' => ['required', 'in:bachelor,diploma,certificate,masters'],
            'faculty' => ['required', 'string', 'max:140'],
            'duration_years' => ['required', 'integer', 'min:1', 'max:6'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $course = Course::create([
            ...$data,
            'code' => strtoupper($data['code']),
            'is_active' => true,
        ]);

        // Automatically create default course community with reserved CR seat controlled by Admins
        Community::create([
            'name' => "{$course->code} Official Course Community",
            'slug' => Str::slug($course->code.'-official'),
            'description' => "Default official academic and welfare channel for {$course->name} ({$course->code}). Managed by SONIT Administration until a CR is assigned.",
            'type' => 'course_default',
            'course_id' => $course->id,
            'cr_user_id' => null,
            'created_by' => $request->user()->id,
            'is_official' => true,
            'is_active' => true,
        ]);

        \App\Models\ActivityLog::record('courses', 'course_created', "Created course {$course->code} - {$course->name}.");

        return back()->with('success', "Course {$course->code} created along with its default Course Community.");
    }

    public function updateCourse(Request $request, Course $course): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:20', 'unique:courses,code,'.$course->id],
            'name' => ['required', 'string', 'max:180'],
            'level' => ['required', 'in:bachelor,diploma,certificate,masters'],
            'faculty' => ['required', 'string', 'max:140'],
            'duration_years' => ['required', 'integer', 'min:1', 'max:6'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $course->update([...$data, 'code' => strtoupper($data['code'])]);

        \App\Models\ActivityLog::record('courses', 'course_updated', "Updated course {$course->code} - {$course->name}.");

        return back()->with('success', "Updated course {$course->code}.");
    }

    public function deleteCourse(Course $course): RedirectResponse
    {
        $code = $course->code;
        $course->communities()->delete();
        $course->delete();

        \App\Models\ActivityLog::record('courses', 'course_deleted', "Deleted course {$code}.");

        return back()->with('success', "Course {$code} deleted.");
    }

    public function assignCourseCr(Request $request, Course $course): RedirectResponse
    {
        $data = $request->validate([
            'cr_user_id' => ['nullable', 'exists:users,id'],
        ]);

        $community = Community::firstOrCreate(
            ['type' => 'course_default', 'course_id' => $course->id],
            [
                'name' => "{$course->code} Official Course Community",
                'slug' => Str::slug($course->code.'-official'),
                'description' => "Default official community for {$course->name}.",
                'created_by' => $request->user()->id,
                'is_official' => true,
                'is_active' => true,
            ]
        );

        $crId = $data['cr_user_id'] ?: null;

        if ($crId) {
            $crUser = User::findOrFail($crId);
            if ($crUser->role === User::ROLE_STUDENT) {
                $crUser->update(['role' => User::ROLE_CLASS_REP, 'course_id' => $course->id]);
            }
            $community->update(['cr_user_id' => $crUser->id]);
            $community->members()->syncWithoutDetaching([
                $crUser->id => ['member_role' => 'cr', 'joined_at' => now()],
            ]);

            \App\Models\ActivityLog::record('courses', 'cr_assigned', "Assigned {$crUser->name} as CR for {$course->code}.");

            return back()->with('success', "Assigned {$crUser->name} as Class Representative for {$course->code}.");
        }

        $community->update(['cr_user_id' => null]);

        \App\Models\ActivityLog::record('courses', 'cr_vacated', "Vacated CR seat for {$course->code} (Admin managed).");

        return back()->with('success', "CR seat for {$course->code} vacated. SONIT Admins retain full governance of the channel.");
    }

    // --- EDITABLE MOTTO, VISION, MISSION & SITE SETTINGS ---
    public function settings(): View
    {
        $settings = SiteSetting::orderBy('group')->orderBy('id')->get();

        return view('admin.settings', compact('settings'));
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'settings' => ['required', 'array'],
            'settings.*' => ['nullable', 'string', 'max:3000'],
        ]);

        foreach ($data['settings'] as $key => $value) {
            SiteSetting::where('key', $key)->update(['value' => $value]);
        }

        SiteSetting::clearCache();
        \App\Models\ActivityLog::record('settings', 'settings_updated', 'Updated SONIT Motto, Vision, Mission, and portal settings.');

        return back()->with('success', 'Organization Motto, Vision, Mission, and Portal Settings updated live.');
    }

    // --- TOP-OF-HOMEPAGE COUNTDOWN TIMER ANNOUNCEMENTS ---
    public function timers(): View
    {
        $timers = TimerAnnouncement::with('creator')->latest()->get();

        return view('admin.timers', compact('timers'));
    }

    public function storeTimer(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'subtitle' => ['nullable', 'string', 'max:280'],
            'event_when' => ['nullable', 'string', 'max:140'],
            'event_where' => ['nullable', 'string', 'max:180'],
            'planned_activities' => ['nullable', 'string', 'max:1200'],
            'account_number' => ['nullable', 'string', 'max:180'],
            'contact_info' => ['nullable', 'string', 'max:180'],
            'category' => ['required', 'in:charity,election,event,deadline,urgent'],
            'ends_at' => ['required', 'date', 'after:now'],
            'cta_label' => ['nullable', 'string', 'max:50'],
            'cta_url' => ['nullable', 'string', 'max:200'],
            'target_amount' => ['nullable', 'integer', 'min:0'],
            'raised_amount' => ['nullable', 'integer', 'min:0'],
            'priority' => ['nullable', 'integer', 'min:0', 'max:100'],
        ]);

        TimerAnnouncement::create([
            ...$data,
            'starts_at' => now(),
            'raised_amount' => $data['raised_amount'] ?? 0,
            'priority' => $data['priority'] ?? 10,
            'is_active' => true,
            'created_by' => $request->user()->id,
        ]);

        \App\Models\ActivityLog::record('timers', 'timer_created', "Published countdown notice: {$data['title']}.");

        return back()->with('success', 'Countdown Timer Announcement published to the top of the homepage.');
    }

    public function updateTimer(Request $request, TimerAnnouncement $timer): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'category' => ['required', 'in:charity,election,event,deadline,urgent'],
            'ends_at' => ['required', 'date'],
            'event_when' => ['nullable', 'string', 'max:140'],
            'event_where' => ['nullable', 'string', 'max:180'],
            'planned_activities' => ['nullable', 'string', 'max:1200'],
            'account_number' => ['nullable', 'string', 'max:180'],
            'contact_info' => ['nullable', 'string', 'max:180'],
            'cta_label' => ['nullable', 'string', 'max:50'],
            'cta_url' => ['nullable', 'string', 'max:200'],
            'priority' => ['nullable', 'integer', 'min:0', 'max:100'],
        ]);

        $timer->update($data);
        \App\Models\ActivityLog::record('timers', 'timer_updated', "Updated countdown notice: {$timer->title}.");

        return back()->with('success', 'Countdown notice updated.');
    }

    public function toggleTimer(TimerAnnouncement $timer): RedirectResponse
    {
        $timer->update(['is_active' => ! $timer->is_active]);

        return back()->with('success', 'Timer announcement status updated.');
    }

    public function deleteTimer(TimerAnnouncement $timer): RedirectResponse
    {
        $title = $timer->title;
        $timer->delete();

        \App\Models\ActivityLog::record('timers', 'timer_deleted', "Deleted countdown timer: {$title}.");

        return back()->with('success', 'Timer announcement deleted.');
    }

    // --- ACTIVITY AUDIT LOGS & VISITOR ANALYTICS ---
    public function logs(Request $request): View
    {
        $module = $request->query('module');
        $search = trim((string) $request->query('q'));

        $activities = \App\Models\ActivityLog::query()
            ->when($module, fn ($q) => $q->where('module', $module))
            ->when($search, fn ($q) => $q->where('description', 'like', "%{$search}%")->orWhere('user_name', 'like', "%{$search}%")->orWhere('action', 'like', "%{$search}%"))
            ->latest('created_at')
            ->paginate(25, ['*'], 'activity_page')
            ->withQueryString();

        $visitors = \App\Models\VisitorLog::with('user')
            ->latest('visited_at')
            ->paginate(25, ['*'], 'visitor_page')
            ->withQueryString();

        $visitorStats = [
            'total_visits' => \App\Models\VisitorLog::count(),
            'unique_ips' => \App\Models\VisitorLog::distinct('ip_address')->count('ip_address'),
            'today_visits' => \App\Models\VisitorLog::where('visited_at', '>=', now()->startOfDay())->count(),
            'guest_visits' => \App\Models\VisitorLog::whereNull('user_id')->count(),
            'member_visits' => \App\Models\VisitorLog::whereNotNull('user_id')->count(),
            'total_activities' => \App\Models\ActivityLog::count(),
        ];

        $modules = \App\Models\ActivityLog::distinct()->orderBy('module')->pluck('module');

        return view('admin.logs', compact('activities', 'visitors', 'visitorStats', 'modules', 'module', 'search'));
    }
}
