<?php

namespace App\Http\Controllers;

use App\Models\Accommodation;
use App\Models\AccommodationRequest;
use App\Models\HelpTicket;
use App\Models\HelpTicketReply;
use App\Models\LostFoundItem;
use App\Models\Venue;
use App\Models\VenueBooking;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CampusServiceController extends Controller
{
    // --- VENUES ---
    public function venues(Request $request): View
    {
        $category = $request->query('category');
        $venues = Venue::when($category, fn ($q) => $q->where('category', $category))
            ->orderBy('name')
            ->get();

        $myBookings = Auth::check()
            ? Auth::user()->venueBookings()->with('venue')->latest()->take(10)->get()
            : collect();

        return view('pages.services.venues', compact('venues', 'category', 'myBookings'));
    }

    public function bookVenue(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'venue_id' => ['required', 'exists:venues,id'],
            'event_title' => ['required', 'string', 'max:140'],
            'organization_or_course' => ['required', 'string', 'max:120'],
            'booking_date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required', 'string', 'max:10'],
            'end_time' => ['required', 'string', 'max:10'],
            'expected_attendees' => ['required', 'integer', 'min:5', 'max:5000'],
            'purpose' => ['required', 'string', 'max:1000'],
        ]);

        VenueBooking::create([
            ...$data,
            'user_id' => $request->user()->id,
            'status' => 'pending',
        ]);

        return back()->with('success', 'Your venue reservation request has been submitted for SONIT approval.');
    }

    // --- ACCOMMODATION ---
    public function accommodations(Request $request): View
    {
        $type = $request->query('type');
        $gender = $request->query('gender');

        $accommodations = Accommodation::where('is_active', true)
            ->when($type, fn ($q) => $q->where('type', $type))
            ->when($gender, fn ($q) => $q->where('gender_policy', $gender))
            ->orderByDesc('is_verified')
            ->orderBy('price_per_semester')
            ->get();

        $myRequests = Auth::check()
            ? Auth::user()->accommodationRequests()->with('accommodation')->latest()->get()
            : collect();

        return view('pages.services.accommodations', compact('accommodations', 'type', 'gender', 'myRequests'));
    }

    public function requestAccommodation(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'accommodation_id' => ['required', 'exists:accommodations,id'],
            'preferred_room_type' => ['required', 'string', 'max:60'],
            'move_in_semester' => ['required', 'string', 'max:60'],
            'notes' => ['nullable', 'string', 'max:800'],
        ]);

        AccommodationRequest::create([
            ...$data,
            'user_id' => $request->user()->id,
            'status' => 'pending',
        ]);

        return back()->with('success', 'Your accommodation placement inquiry has been forwarded to the SONIT Accommodation Desk.');
    }

    // --- HELP DESK ---
    public function helpDesk(): View
    {
        $user = Auth::user();
        $tickets = $user
            ? ($user->isAdmin()
                ? HelpTicket::with(['user.course', 'replies'])->latest()->paginate(15)
                : $user->helpTickets()->with('replies')->latest()->paginate(15))
            : collect();

        return view('pages.services.help_desk', compact('tickets'));
    }

    public function storeTicket(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'category' => ['required', 'string', 'in:'.implode(',', array_keys(HelpTicket::CATEGORIES))],
            'subject' => ['required', 'string', 'min:5', 'max:160'],
            'description' => ['required', 'string', 'min:10', 'max:3000'],
            'priority' => ['required', 'in:low,normal,high,urgent'],
            'is_anonymous' => ['nullable', 'boolean'],
        ]);

        $ticketNumber = 'SONIT-'.date('Y').'-'.str_pad((string) (HelpTicket::count() + 1), 4, '0', STR_PAD_LEFT).'-'.strtoupper(Str::random(2));

        $ticket = HelpTicket::create([
            'ticket_number' => $ticketNumber,
            'user_id' => $request->user()->id,
            'category' => $data['category'],
            'subject' => $data['subject'],
            'description' => $data['description'],
            'priority' => $data['priority'],
            'status' => 'open',
            'is_anonymous' => $request->boolean('is_anonymous'),
        ]);

        return redirect()->route('help-desk.show', $ticket)
            ->with('success', "Support ticket {$ticket->ticket_number} created. SONIT Welfare Desk will respond shortly.");
    }

    public function showTicket(HelpTicket $ticket): View
    {
        $user = Auth::user();
        abort_unless($user && ($user->isAdmin() || (int) $ticket->user_id === (int) $user->id), 403);

        $ticket->load(['user.course', 'assignee', 'replies.user']);

        return view('pages.services.help_ticket_show', compact('ticket'));
    }

    public function replyTicket(Request $request, HelpTicket $ticket): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user->isAdmin() || (int) $ticket->user_id === (int) $user->id, 403);

        $data = $request->validate([
            'message' => ['required', 'string', 'min:2', 'max:2500'],
            'status' => ['nullable', 'in:open,in_progress,resolved,closed'],
        ]);

        HelpTicketReply::create([
            'help_ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'message' => $data['message'],
            'is_staff_reply' => $user->isAdmin(),
        ]);

        if ($user->isAdmin()) {
            $ticket->update([
                'status' => $data['status'] ?? ($ticket->status === 'open' ? 'in_progress' : $ticket->status),
                'assigned_to' => $ticket->assigned_to ?? $user->id,
            ]);
        }

        return back()->with('success', 'Reply added to ticket.');
    }

    // --- LOST & FOUND ---
    public function lostFound(Request $request): View
    {
        $type = $request->query('type');
        $status = $request->query('status');

        $items = LostFoundItem::with('reporter')
            ->when($type, fn ($q) => $q->where('type', $type))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('pages.services.lost_found', compact('items', 'type', 'status'));
    }

    public function storeLostFound(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'type' => ['required', 'in:lost,found'],
            'item_name' => ['required', 'string', 'max:120'],
            'category' => ['required', 'in:id_card,electronics,documents,keys,bags,accessories,other'],
            'description' => ['required', 'string', 'max:1200'],
            'location' => ['required', 'string', 'max:140'],
            'date_occurred' => ['nullable', 'date'],
            'contact_info' => ['required', 'string', 'max:140'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = 'lf_'.time().'_'.Str::random(5).'.'.$file->getClientOriginalExtension();
            $file->move(public_path('uploads/lost_found'), $filename);
            $imagePath = 'uploads/lost_found/'.$filename;
        }

        LostFoundItem::create([
            'user_id' => $request->user()->id,
            'type' => $data['type'],
            'item_name' => $data['item_name'],
            'category' => $data['category'],
            'description' => $data['description'],
            'location' => $data['location'],
            'date_occurred' => $data['date_occurred'] ?? now()->toDateString(),
            'contact_info' => $data['contact_info'],
            'image' => $imagePath,
            'status' => 'open',
        ]);

        return back()->with('success', 'Your Lost & Found listing has been published.');
    }

    public function claimLostFound(Request $request, LostFoundItem $item): RedirectResponse
    {
        $data = $request->validate([
            'claimed_by_info' => ['required', 'string', 'max:200'],
        ]);

        $item->update([
            'status' => 'claimed',
            'claimed_by_info' => $data['claimed_by_info'].' (Logged by '.$request->user()->name.')',
        ]);

        return back()->with('success', 'Claim request recorded. Please present your NIT Student ID at the SONIT Secretariat Desk for handover.');
    }
}
