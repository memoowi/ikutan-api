<?php

namespace App\Http\Controllers\Api;

use App\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    use ApiResponse; // use the trait
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'desc' => 'required|string',
            'images' => 'required|array',
            'images.*' => 'string|url',
            'date' => 'required|date',
            'maxReservation' => 'required|integer|min:1',
            'isActive' => 'sometimes|boolean',
        ]);

        $event = Event::create(
            $request->all()
        );

        return $this->successResponse(
            $event, 
            'Event created successfully', 
            201
        );
    }
}
