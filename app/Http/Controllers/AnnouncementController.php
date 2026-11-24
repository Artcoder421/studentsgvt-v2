<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\AnnouncementComment;
use App\Models\AnnouncementRead;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    public function index(Request $request): View
    {
        $category = $request->query('category');
        $urgentOnly = $request->boolean('urgent');

        $announcements = Announcement::with('author')
            ->withCount('comments')
            ->where('is_published', true)
            ->when($category, fn ($q) => $q->where('category', $category))
            ->when($urgentOnly, fn ($q) => $q->where('is_urgent', true))
            ->orderByDesc('is_urgent')
            ->orderByDesc('is_pinned')
            ->latest('published_at')
            ->paginate(9)
            ->withQueryString();

        $readIds = Auth::check()
            ? Auth::user()->announcementReads()->pluck('announcement_id')->toArray()
            : [];

        return view('pages.announcements.index', compact('announcements', 'category', 'urgentOnly', 'readIds'));
    }

    public function show(Announcement $announcement): View
    {
        abort_unless($announcement->is_published || (Auth::check() && Auth::user()->isAdmin()), 404);

        if (Auth::check()) {
            AnnouncementRead::firstOrCreate(
                ['announcement_id' => $announcement->id, 'user_id' => Auth::id()],
                ['read_at' => now()]
            );
        }

        $announcement->load(['author', 'comments.user.course']);

        return view('pages.announcements.show', compact('announcement'));
    }

    public function comment(Request $request, Announcement $announcement): RedirectResponse
    {
        $data = $request->validate([
            'body' => ['required', 'string', 'min:2', 'max:1500'],
        ]);

        AnnouncementComment::create([
            'announcement_id' => $announcement->id,
            'user_id' => $request->user()->id,
            'body' => $data['body'],
        ]);

        return back()->with('success', 'Your comment has been posted.');
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        $user = $request->user();
        $ids = Announcement::where('is_published', true)->pluck('id');

        foreach ($ids as $id) {
            AnnouncementRead::firstOrCreate(
                ['announcement_id' => $id, 'user_id' => $user->id],
                ['read_at' => now()]
            );
        }

        return back()->with('success', 'All announcements marked as read.');
    }
}
