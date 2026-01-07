<?php

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
