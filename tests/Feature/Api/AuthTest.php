<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

// Data dummy untuk digunakan dalam beberapa test
$userData = [
    'name'     => 'Memoowi',
    'email'    => 'memoowi@arch.linux',
    'password' => 'password123',
    'role'     => 'attendee',
];

test('user can register', function () use ($userData) {
    $response = $this->postJson('/api/register', [
        'name'                  => $userData['name'],
        'email'                 => $userData['email'],
        'password'              => $userData['password'],
        'password_confirmation' => $userData['password'],
    ]);

    $response->assertStatus(201);

    // Pastikan user tersimpan di DB
    $this->assertDatabaseHas('users', ['email' => $userData['email']]);

    // Cek struktur response
    expect($response->json('data.user'))->toMatchArray([
        'name'  => $userData['name'],
        'email' => $userData['email'],
        'role'  => $userData['role'],
    ]);

    expect($response->json('data.token'))->not->toBeEmpty();
});

test('user can login', function () use ($userData) {
    // 1. Persiapkan user di database
    User::create([
        'name'     => $userData['name'],
        'email'    => $userData['email'],
        'password' => Hash::make($userData['password']),
        'role'     => $userData['role'],
    ]);

    // 2. Lakukan request login
    $response = $this->postJson('/api/login', [
        'email'    => $userData['email'],
        'password' => $userData['password'],
    ]);

    $response->assertStatus(200);
    
    expect($response->json('data.token'))->not->toBeEmpty();
});

test('user can logout', function () {
    // 1. Buat user dan login secara manual untuk dapat token
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    // 2. Request logout dengan Bearer Token
    $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                     ->postJson('/api/logout');

    $response->assertStatus(200);

    // 3. Pastikan token di database sudah terhapus
    expect($user->tokens()->count())->toBe(0);
});