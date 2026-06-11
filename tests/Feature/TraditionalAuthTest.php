<?php

use App\Models\ArticleInterest;
use App\Models\Comment;
use App\Models\User;

test('registered users are not admins', function () {
    $this->post('/register', [
        'name' => 'Member User',
        'email' => 'member@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertRedirect(route('home'));

    expect(User::query()->where('email', 'member@example.com')->first())
        ->is_admin->toBeFalse();
});

test('members cannot access filament admin panel', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/admin')
        ->assertForbidden();
});

test('admins can access filament admin panel', function () {
    $user = User::factory()->admin()->create();

    $this->actingAs($user)
        ->get('/admin')
        ->assertSuccessful();
});

test('guest workshop interest is not merged when user registers', function () {
    $workshop = createWorkshopArticle(['slug' => 'no-merge-on-register']);
    $token = hash('sha256', 'guest-token-for-register');

    ArticleInterest::query()->create([
        'article_id' => $workshop->getKey(),
        'session_token' => $token,
        'ip_hash' => hash('sha256', 'guest-ip'),
    ]);

    $this->withCookie('top3z_guest_token', $token)
        ->post('/register', [
            'name' => 'New Member',
            'email' => 'newmember@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])
        ->assertRedirect(route('home'));

    $user = User::query()->where('email', 'newmember@example.com')->first();

    expect($user)->not->toBeNull();

    expect(ArticleInterest::query()
        ->where('article_id', $workshop->getKey())
        ->where('user_id', $user->id)
        ->exists())->toBeFalse();

    $this->actingAs($user)
        ->get(route('workshops.show', $workshop))
        ->assertSuccessful()
        ->assertSee('Tôi sẽ tham gia', false)
        ->assertDontSee('Bạn đã quan tâm', false);
});

test('logged in user does not inherit guest interest from browser cookie', function () {
    $workshop = createWorkshopArticle(['slug' => 'guest-cookie-ignored']);
    $user = User::factory()->create();
    $token = hash('sha256', 'guest-token-before-login');

    ArticleInterest::query()->create([
        'article_id' => $workshop->getKey(),
        'session_token' => $token,
        'ip_hash' => hash('sha256', 'guest-ip'),
    ]);

    $this->withCookie('top3z_guest_token', $token)
        ->actingAs($user)
        ->get(route('workshops.show', $workshop))
        ->assertSuccessful()
        ->assertSee('Tôi sẽ tham gia', false)
        ->assertDontSee('Bạn đã quan tâm', false);
});

test('guest comments with matching email are merged on login', function () {
    $workshop = createWorkshopArticle(['slug' => 'comment-merge']);
    $user = User::factory()->create(['email' => 'commenter@example.com']);

    $comment = Comment::query()->create([
        'article_id' => $workshop->getKey(),
        'guest_name' => 'Guest Name',
        'guest_email' => 'commenter@example.com',
        'body' => 'Bình luận trước khi đăng nhập',
    ]);

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $comment->refresh();

    expect($comment->user_id)->toBe($user->id)
        ->and($comment->guest_name)->toBeNull()
        ->and($comment->guest_email)->toBeNull();
});

test('login page shows vietnamese copy in makerspace layout', function () {
    $this->get('/login')
        ->assertSuccessful()
        ->assertSee('Đăng nhập')
        ->assertSee('Top3z', false);
});
