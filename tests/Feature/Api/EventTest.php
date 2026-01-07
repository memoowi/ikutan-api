<?php

use App\Models\Event;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

// Helper untuk membuat Admin
function asAdmin()
{
    return test()->actingAs(User::factory()->create(['role' => 'admin']), 'sanctum');
}

// Helper untuk membuat Staff
function asStaff()
{
    return test()->actingAs(User::factory()->create(['role' => 'staff']), 'sanctum');
}

// Helper untuk membuat Attendee
function asAttendee()
{
    return test()->actingAs(User::factory()->create(['role' => 'attendee']), 'sanctum');
}

$eventData = [
    'name' => 'Workshop Flutter IDN',
    'desc' => 'Belajar integrasi API Laravel dan Flutter',
    'date' => '2026-05-20 09:00:00',
    'max_reservation' => 50,
    'is_active' => true
];

test('admin can create event', function () use ($eventData) {
    Storage::fake('public'); // Memalsukan storage agar tidak mengotori disk asli

    $image = UploadedFile::fake()->image('poster.jpg');

    $response = asAdmin()->postJson('/api/events', [
        'name' => $eventData['name'],
        'desc' => $eventData['desc'],
        'images' => [$image],
        'date' => $eventData['date'],
        'max_reservation' => $eventData['max_reservation'],
    ]);

    $response->assertStatus(201);
    $this->assertDatabaseHas('events', [
        'name' => $eventData['name'],
        'desc' => $eventData['desc'],
        'date' => $eventData['date'],
        'images' => json_encode([Storage::url('events/' . $image->hashName())]),
        'max_reservation' => $eventData['max_reservation'],
        'is_active' => $eventData['is_active'],
    ]);

    // Memastikan file tersimpan di storage
    Storage::disk('public')->exists('events/' . $image->hashName());
});

test('user can get all events', function () {
    // Buat 1 event aktif dan 1 event tidak aktif
    Event::factory(10)->create(['is_active' => true, 'name' => 'Event Aktif']);
    Event::factory(10)->create(['is_active' => false, 'name' => 'Event Tutup']);

    // Attendee
    $response = asAttendee()->getJson('/api/events');
    $response->assertStatus(200);
    // Attendee biasa tidak boleh melihat event yang non-aktif
    expect($response->json('data'))->toHaveCount(10);
    expect($response->json('data.0.name'))->toBe('Event Aktif');

    // Admin
    $response = asAdmin()->getJson('/api/events');
    $response->assertStatus(200);
    expect($response->json('data'))->toHaveCount(20);
});