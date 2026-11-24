<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\Election;
use App\Models\Vote;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ElectionController extends Controller
{
    public function index(): View
    {
        $election = Election::latest()->first();
        $user = Auth::user();

        $presidentialCandidates = collect();
        $prCandidates = collect();
        $otherCandidates = collect();
        $votedPositions = [];

        if ($election) {
            $presidentialCandidates = $election->approvedCandidates()
                ->with('course')
                ->where('position', 'President')
                ->get();

            $prCandidates = $election->approvedCandidates()
                ->with('course')
                ->where('position', 'Parliament Representative')
                ->when($user && $user->course_id, fn ($q) => $q->where('course_id', $user->course_id))
                ->get();

            $otherCandidates = $election->approvedCandidates()
                ->with('course')
                ->whereNotIn('position', ['President', 'Parliament Representative'])
                ->get();

            if ($user) {
                $votedPositions = $election->votes()
                    ->where('voter_id', $user->id)
                    ->pluck('position')
                    ->toArray();
            }
        }

        return view('pages.elections.index', compact(
            'election',
            'presidentialCandidates',
            'prCandidates',
            'otherCandidates',
            'votedPositions'
        ));
    }

    public function ballot(Request $request): View|RedirectResponse
    {
        $election = Election::latest()->first();
        if (! $election || ! $election->isVotingActive()) {
            return redirect()->route('elections.index')
                ->with('error', 'Online voting is currently closed for this election cycle.');
        }

        $position = $request->query('position', 'President');
        if (! in_array($position, Candidate::POSITIONS, true)) {
            $position = 'President';
        }

        $user = $request->user()->load('course');

        if ($election->hasUserVotedForPosition($user, $position)) {
            return redirect()->route('elections.index')
                ->with('info', "You have already cast your verified ballot for {$position}.");
        }

        $candidatesQuery = $election->approvedCandidates()
            ->with('course')
            ->where('position', $position);

        if ($position === 'Parliament Representative') {
            if (! $user->course_id) {
                return redirect()->route('elections.index')
                    ->with('error', 'Your account is not linked to an NIT course, so you cannot vote for a Course PR.');
            }
            $candidatesQuery->where('course_id', $user->course_id);
        }

        $candidates = $candidatesQuery->get();

        return view('pages.elections.ballot', compact('election', 'position', 'candidates', 'user'));
    }

    public function vote(Request $request): RedirectResponse
    {
        $election = Election::latest()->first();

        if (! $election || ! $election->isVotingActive()) {
            return redirect()->route('elections.index')
                ->with('error', 'Voting is currently disabled or outside the voting window.');
        }

        $data = $request->validate([
            'position' => ['required', 'string', 'in:'.implode(',', Candidate::POSITIONS)],
            'candidate_id' => ['required', 'integer', 'exists:candidates,id'],
        ]);

        $user = $request->user();

        $candidate = Candidate::where('id', $data['candidate_id'])
            ->where('election_id', $election->id)
            ->where('position', $data['position'])
            ->where('status', 'approved')
            ->first();

        if (! $candidate) {
            return back()->with('error', 'Invalid or unapproved candidate selected.');
        }

        if ($data['position'] === 'Parliament Representative' && (int) $candidate->course_id !== (int) $user->course_id) {
            abort(403, 'You may only vote for Parliament Representative candidates from your own course.');
        }

        if ($election->hasUserVotedForPosition($user, $data['position'])) {
            return redirect()->route('elections.index')
                ->with('error', "You have already voted for {$data['position']}.");
        }

        try {
            DB::transaction(function () use ($election, $candidate, $user, $data) {
                Vote::create([
                    'election_id' => $election->id,
                    'candidate_id' => $candidate->id,
                    'voter_id' => $user->id,
                    'position' => $data['position'],
                    'course_id' => $data['position'] === 'Parliament Representative' ? $user->course_id : null,
                    'voted_at' => now(),
                ]);
            });
        } catch (QueryException $e) {
            return redirect()->route('elections.index')
                ->with('error', 'Duplicate ballot prevented: you have already cast your vote for this position.');
        }

        return redirect()->route('elections.index')
            ->with('success', "Your secure ballot for {$data['position']} has been recorded.");
    }

    public function showApply(): View|RedirectResponse
    {
        $election = Election::latest()->first();
        if (! $election || ! $election->is_enabled || ! $election->applications_open) {
            return redirect()->route('elections.index')
                ->with('error', 'Candidate nominations are currently closed.');
        }

        $user = Auth::user()->load('course');

        return view('pages.elections.apply', compact('election', 'user'));
    }

    public function apply(Request $request): RedirectResponse
    {
        $election = Election::latest()->first();
        if (! $election || ! $election->is_enabled || ! $election->applications_open) {
            return redirect()->route('elections.index')
                ->with('error', 'Candidate nominations are currently closed.');
        }

        $user = $request->user();

        $data = $request->validate([
            'position' => ['required', 'string', 'in:'.implode(',', Candidate::POSITIONS)],
            'academic_level' => ['required', 'string', 'max:40'],
            'motto' => ['required', 'string', 'max:180'],
            'manifesto' => ['required', 'string', 'min:20', 'max:3000'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $alreadyApplied = Candidate::where('election_id', $election->id)
            ->where('user_id', $user->id)
            ->exists();

        if ($alreadyApplied) {
            return redirect()->route('elections.index')
                ->with('info', 'You have already submitted a candidate nomination for this election cycle.');
        }

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $filename = 'cand_'.$user->id.'_'.time().'_'.Str::random(4).'.'.$file->getClientOriginalExtension();
            $file->move(public_path('uploads/candidates'), $filename);
            $photoPath = 'uploads/candidates/'.$filename;
        }

        Candidate::create([
            'election_id' => $election->id,
            'user_id' => $user->id,
            'fullname' => $user->name,
            'reg_no' => $user->reg_no ?? $user->email,
            'course_id' => $user->course_id,
            'academic_level' => $data['academic_level'],
            'position' => $data['position'],
            'motto' => $data['motto'],
            'manifesto' => $data['manifesto'],
            'photo' => $photoPath,
            'status' => 'pending',
        ]);

        return redirect()->route('elections.index')
            ->with('success', 'Your candidate nomination has been submitted to the Electoral Commission for vetting.');
    }

    public function results(): View|RedirectResponse
    {
        $election = Election::latest()->first();
        $user = Auth::user();

        if (! $election) {
            return redirect()->route('elections.index')->with('info', 'No election cycle available yet.');
        }

        if (! $election->results_published && (! $user || ! $user->isAdmin())) {
            return redirect()->route('elections.index')
                ->with('info', 'Official results will be published once the Electoral Commission closes the polls.');
        }

        $presidentialResults = $election->approvedCandidates()
            ->with('course')
            ->withCount('votes')
            ->where('position', 'President')
            ->orderByDesc('votes_count')
            ->get();

        $totalPresidentialVotes = max(1, $presidentialResults->sum('votes_count'));

        $prCandidates = $election->approvedCandidates()
            ->with('course')
            ->withCount('votes')
            ->where('position', 'Parliament Representative')
            ->orderByDesc('votes_count')
            ->get()
            ->groupBy(fn ($c) => $c->course?->code ?? 'General');

        $totalUniqueVoters = $election->votes()->distinct('voter_id')->count('voter_id');
        $totalBallotsCast = $election->votes()->count();

        return view('pages.elections.results', compact(
            'election',
            'presidentialResults',
            'totalPresidentialVotes',
            'prCandidates',
            'totalUniqueVoters',
            'totalBallotsCast'
        ));
    }
}
