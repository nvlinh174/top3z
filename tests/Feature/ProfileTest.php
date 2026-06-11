<?php

use App\Models\User;

test('profile page is displayed', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get('/profile');

    $response->assertOk();
});

test('profile name can be updated', function () {
    $user = User::factory()->create([
        'name' => 'Old Name',
        'email' => 'member@example.com',
    ]);

    $response = $this
        ->actingAs($user)
        ->patch('/profile', [
            'name' => 'New Name',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $user->refresh();

    expect($user->name)->toBe('New Name')
        ->and($user->email)->toBe('member@example.com');
});

test('profile email cannot be changed via update', function () {
    $user = User::factory()->create([
        'email' => 'original@example.com',
    ]);

    $this->actingAs($user)
        ->patch('/profile', [
            'name' => $user->name,
            'email' => 'hacked@example.com',
        ])
        ->assertRedirect('/profile');

    expect($user->fresh()->email)->toBe('original@example.com');
});

test('user can delete their account', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->delete('/profile', [
            'password' => 'password',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/');

    $this->assertGuest();
    $this->assertNull($user->fresh());
});

test('correct password must be provided to delete account', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->from('/profile')
        ->delete('/profile', [
            'password' => 'wrong-password',
        ]);

    $response
        ->assertSessionHasErrorsIn('userDeletion', 'password')
        ->assertRedirect('/profile');

    $this->assertNotNull($user->fresh());
});
