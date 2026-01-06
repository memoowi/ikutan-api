<?php

namespace App\Http\Controllers\Api;

use App\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TicketController extends Controller
{
    use ApiResponse;
    public function store(Request $request)
    {
        $request->validate([
            'event_id' => 'required|exists:events,id',
        ]);

        $user = $request->user();

        DB::beginTransaction();

        try {
            $event = Event::where('id', $request->event_id)->lockForUpdate()->firstOrFail();

            if (!$event->is_active) {
                DB::rollBack(); 
                return $this->errorResponse('This event is no longer active.', 400);
            }

            $existingTicket = Ticket::where('user_id', $user->id)->where('event_id', $event->id)->where('is_canceled_by_user', false)->exists();

            if ($existingTicket) {
                DB::rollBack();
                return $this->errorResponse('You already have an active ticket for this event.', 400);
            }

            $currentBookings = $event->tickets()->where('is_canceled_by_user', false)->count();
            if ($currentBookings >= $event->max_reservation) {
                DB::rollBack();
                return $this->errorResponse('Event is fully booked.', 400);
            }

            $code = 'ticket-' . uniqid() . '-' . Str::random(8) . '-' . time();

            $ticket = Ticket::create([
                'user_id' => $user->id,
                'event_id' => $event->id,
                'code' => $code,
            ]);

            DB::commit();

            return $this->successResponse($ticket, 'Ticket reserved successfully!', 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to reserve ticket: ' . $e->getMessage(), 500);
        }
    }
}
