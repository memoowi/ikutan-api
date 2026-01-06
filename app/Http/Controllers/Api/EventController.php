<?php

namespace App\Http\Controllers\Api;

use App\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EventController extends Controller
{
    use ApiResponse; // use the trait
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'desc' => 'required|string',
            'images' => 'required|array|min:1',
            'images.*' => 'image|mimes:jpeg,png,jpg|max:2048',
            'date' => 'required|date',
            'max_reservation' => 'required|integer|min:1',
            'is_active' => 'sometimes|boolean',
        ]);

        $paths = [];

        // Loop through each uploaded file
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                // Store in 'storage/app/public/events' and get the relative path
                $path = $image->store('events', 'public');
                $paths[] = Storage::url($path); // Convert to a public URL
            }
        }

        $validated['images'] = $paths;

        $event = Event::create($validated);

        return $this->successResponse($event, 'Event created successfully', 201);
    }
    public function index(Request $request)
    {
        $userRole = $request->user()->role;

        // 1. Check for Unauthorized roles early (Guard Clause)
        if (!in_array($userRole, ['admin', 'staff', 'attendee'])) {
            return $this->errorResponse('Unauthorized access', 403);
        }

        $events = Event::withCount([
            'tickets' => function ($query) {
                // Only count tickets where is_canceled_by_user is false
                $query->where('is_canceled_by_user', false);
            },
        ])
            ->when($userRole === 'attendee', fn($query) => $query->where('is_active', true))
            ->latest()
            ->get();

        return $this->successResponse($events, 'Events fetched successfully', 200);
    }
    public function show(Request $request, Event $event)
    {
        $userRole = $request->user()->role;
        // 1. Check for Unauthorized roles early (Guard Clause)
        if (!in_array($userRole, ['admin', 'staff', 'attendee'])) {
            return $this->errorResponse('Unauthorized access', 403);
        }


        // 2. Apply logic based on role
        if ($userRole === 'attendee') {
            // Attendees only need the count of active tickets
            $event->loadCount(['tickets' => fn($q) => $q->where('is_canceled_by_user', false)]);
        } else {
            // Admin/Staff need the actual ticket data (list of people)
            $event->load(['tickets' => fn($q) => $q->where('is_canceled_by_user', false)]);
        }

        return $this->successResponse($event, 'Event fetched successfully');
    }
    public function toggle(Event $event)
    {
        $event->update(['is_active' => !$event->is_active]);
        return $this->successResponse($event, 'Event status updated successfully');
    }
}
