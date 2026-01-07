<?php

use App\Models\Event;
use App\Models\Ticket;
use App\Models\User;

test('attendee can create ticket', function () {
    $event = Event::factory()->create(['is_active' => true]);

    $response = asAttendee()->postJson('/api/tickets', [
        'event_id' => $event->id,
    ]);

    $response->assertStatus(201);
    expect($response->json('data.event_id'))->toBe($event->id);
    expect($response->json('data.code'))->not->toBeEmpty();
});

test('attendee cant create ticket on full event', function () {
    $event = Event::factory()->create(['is_active' => true, 'max_reservation' => 1]);
    Ticket::factory()->create(['event_id' => $event->id, 'is_canceled_by_user' => false]);

    $response = asAttendee()->postJson('/api/tickets', [
        'event_id' => $event->id,
    ]);

    $response->assertStatus(400);
    expect($response->json('message'))->toContain('fully booked');
});

test('attendee can cancel their ticket', function () {
    $event = Event::factory()->create(['is_active' => true]);
    $user = User::factory()->create(['role' => 'attendee']);
    $ticket = Ticket::factory()->create(['user_id' => $user->id, 'event_id' => $event->id, 'is_canceled_by_user' => false]);

    $response = $this->actingAs($user)->patchJson("/api/tickets/{$ticket->id}");

    $response->assertStatus(200);

    // Verifikasi status di database berubah
    expect($ticket->refresh()->is_canceled_by_user)->toBeTrue();
});