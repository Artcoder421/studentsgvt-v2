<?php

namespace App\Http\Controllers;

use App\Models\Community;
use App\Models\CommunityMessage;
use App\Models\Course;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CommunityController extends Controller
{
    public function index(Request $request): View
    {
        $faculty = $request->query('faculty');
        $search = trim((string) $request->query('q'));
        $user = Auth::user();

        $generalChannels = Community::withCount(['members', 'messages'])
            ->where('is_active', true)
            ->where('type', 'general')
            ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('description', 'like', "%{$search}%"))
            ->orderBy('name')
            ->get();

        $defaultCourseCommunities = Community::with(['course', 'cr'])
            ->withCount(['members', 'messages'])
            ->where('is_active', true)
            ->where('type', 'course_default')
            ->when($faculty, fn ($q) => $q->whereHas('course', fn ($cq) => $cq->where('faculty', $faculty)))
            ->when($search, function ($q) use ($search) {
                $q->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhereHas('course', fn ($cq) => $cq->where('code', 'like', "%{$search}%")->orWhere('name', 'like', "%{$search}%"));
                });
            })
            ->orderBy('name')
            ->get();

        $courseSubGroups = Community::with(['course', 'cr', 'creator'])
            ->withCount(['members', 'messages'])
            ->where('is_active', true)
            ->where('type', 'course_group')
            ->when($faculty, fn ($q) => $q->whereHas('course', fn ($cq) => $cq->where('faculty', $faculty)))
            ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->latest()
            ->get();

        $faculties = Course::where('is_active', true)->distinct()->orderBy('faculty')->pluck('faculty');

        // Courses available to create a sub-community for:
        // Super Admin / Admin -> all courses; Class Rep -> ONLY their own course!
        $creatableCourses = collect();
        if ($user) {
            if ($user->isAdmin()) {
                $creatableCourses = Course::where('is_active', true)->orderBy('code')->get();
            } elseif ($user->isClassRep() && $user->course_id) {
                $creatableCourses = Course::where('id', $user->course_id)->get();
            }
        }

        return view('pages.communities.index', compact(
            'generalChannels',
            'defaultCourseCommunities',
            'courseSubGroups',
            'faculties',
            'faculty',
            'search',
            'creatableCourses'
        ));
    }

    public function show(Community $community): View
    {
        abort_unless($community->is_active || (Auth::check() && Auth::user()->isAdmin()), 404);

        $community->load(['course', 'cr', 'creator']);
        $community->loadCount('members');

        $pinnedMessages = $community->messages()
            ->with(['user.course'])
            ->where('is_pinned', true)
            ->latest()
            ->get();

        $messages = $community->messages()
            ->with(['user.course', 'replyTo.user'])
            ->oldest()
            ->get();

        $members = $community->members()
            ->with('course')
            ->orderBy('name')
            ->take(40)
            ->get();

        $user = Auth::user();
        $isMember = $community->hasMember($user);
        $canModerate = $user ? $user->canModerateCommunity($community) : false;

        $relatedCourseGroups = $community->course_id
            ? Community::where('course_id', $community->course_id)
                ->where('id', '!=', $community->id)
                ->where('is_active', true)
                ->get()
            : collect();

        return view('pages.communities.show', compact(
            'community',
            'pinnedMessages',
            'messages',
            'members',
            'isMember',
            'canModerate',
            'relatedCourseGroups'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user->isAdmin() && ! $user->isClassRep()) {
            abort(403, 'Only Class Representatives (for their own course) and SONIT Administrators can create communities.');
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'min:3', 'max:100'],
            'description' => ['required', 'string', 'max:600'],
            'type' => ['required', 'in:course_group,general'],
            'course_id' => ['nullable', 'exists:courses,id'],
        ]);

        if ($user->isClassRep()) {
            // Strict enforcement: Class Representatives can ONLY create sub-groups for THEIR OWN course!
            if ($data['type'] !== 'course_group') {
                abort(403, 'Class Representatives can only create course sub-communities for their assigned course.');
            }

            if ((int) ($data['course_id'] ?? 0) !== (int) $user->course_id) {
                abort(403, 'You are only authorized to create communities for your own course.');
            }
        } elseif ($data['type'] === 'course_group' && empty($data['course_id'])) {
            return back()->withErrors(['course_id' => 'Please choose a course for a course sub-community.'])->withInput();
        }

        $slug = Str::slug($data['name']).'-'.Str::lower(Str::random(4));

        $community = Community::create([
            'name' => $data['name'],
            'slug' => $slug,
            'description' => $data['description'],
            'type' => $data['type'],
            'course_id' => $data['type'] === 'course_group' ? $data['course_id'] : null,
            'cr_user_id' => $user->isClassRep() ? $user->id : null,
            'created_by' => $user->id,
            'is_official' => $user->isAdmin(),
            'is_active' => true,
        ]);

        $community->members()->attach($user->id, [
            'member_role' => $user->isAdmin() ? 'admin' : 'cr',
            'joined_at' => now(),
        ]);

        return redirect()->route('communities.show', $community)
            ->with('success', "Community \"{$community->name}\" has been created.");
    }

    public function join(Request $request, Community $community): RedirectResponse
    {
        $user = $request->user();

        $community->members()->syncWithoutDetaching([
            $user->id => [
                'member_role' => $user->canModerateCommunity($community)
                    ? ($user->isAdmin() ? 'admin' : 'cr')
                    : 'member',
                'joined_at' => now(),
            ],
        ]);

        return back()->with('success', "You joined {$community->name}.");
    }

    public function leave(Request $request, Community $community): RedirectResponse
    {
        $community->members()->detach($request->user()->id);

        return back()->with('success', "You left {$community->name}.");
    }

    public function postMessage(Request $request, Community $community): RedirectResponse
    {
        $user = $request->user();

        if ($community->is_locked && ! $user->canModerateCommunity($community)) {
            return back()->with('error', 'This channel is currently in announcement-only mode (CR & Admins only).');
        }

        $data = $request->validate([
            'body' => ['required', 'string', 'min:1', 'max:2000'],
            'reply_to_id' => ['nullable', 'exists:community_messages,id'],
            'attachment' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
        ]);

        // Auto-enroll member when posting
        if (! $community->hasMember($user)) {
            $community->members()->attach($user->id, [
                'member_role' => $user->canModerateCommunity($community) ? ($user->isAdmin() ? 'admin' : 'cr') : 'member',
                'joined_at' => now(),
            ]);
        }

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $filename = 'msg_'.$community->id.'_'.time().'_'.Str::random(5).'.'.$file->getClientOriginalExtension();
            $file->move(public_path('uploads/community'), $filename);
            $attachmentPath = 'uploads/community/'.$filename;
        }

        CommunityMessage::create([
            'community_id' => $community->id,
            'user_id' => $user->id,
            'reply_to_id' => $data['reply_to_id'] ?? null,
            'body' => $data['body'],
            'attachment' => $attachmentPath,
        ]);

        return redirect()->route('communities.show', $community)->with('success', 'Message posted.');
    }

    public function togglePinMessage(Request $request, Community $community, CommunityMessage $message): RedirectResponse
    {
        abort_unless($request->user()->canModerateCommunity($community), 403);
        abort_unless((int) $message->community_id === (int) $community->id, 404);

        $message->update(['is_pinned' => ! $message->is_pinned]);

        return back()->with('success', $message->is_pinned ? 'Message pinned to channel header.' : 'Message unpinned.');
    }

    public function toggleLock(Request $request, Community $community): RedirectResponse
    {
        abort_unless($request->user()->canModerateCommunity($community), 403);

        $community->update(['is_locked' => ! $community->is_locked]);

        return back()->with('success', $community->is_locked ? 'Channel set to Broadcast Mode (CR/Admin only).' : 'Channel opened for all student messages.');
    }
}
