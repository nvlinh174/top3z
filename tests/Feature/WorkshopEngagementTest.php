<?php

use App\Enums\CommentStatus;
use App\Models\ArticleInterest;
use App\Models\Comment;
use App\Models\User;

test('guest can register interest on upcoming workshop', function () {
    $workshop = createWorkshopArticle(['slug' => 'interest-upcoming']);

    $this->post(route('workshops.interest.store', $workshop))
        ->assertRedirect(route('workshops.show', $workshop))
        ->assertSessionHas('success');

    expect($workshop->fresh()->interests)->toHaveCount(1);
});

test('guest interest state persists after workshop page reload', function () {
    $workshop = createWorkshopArticle(['slug' => 'interest-ui-persist']);

    $this->post(route('workshops.interest.store', $workshop))
        ->assertRedirect(route('workshops.show', $workshop));

    $this->get(route('workshops.show', $workshop))
        ->assertSuccessful()
        ->assertSee('Bạn đã quan tâm', false);
});

test('guest cannot register duplicate interest in same session', function () {
    $workshop = createWorkshopArticle(['slug' => 'interest-duplicate']);

    $this->post(route('workshops.interest.store', $workshop));
    $this->post(route('workshops.interest.store', $workshop))
        ->assertRedirect(route('workshops.show', $workshop))
        ->assertSessionHas('info');

    expect($workshop->fresh()->interests)->toHaveCount(1);
});

test('guest cannot register interest on past workshop', function () {
    $workshop = createWorkshopArticle([
        'slug' => 'interest-past',
        'starts_at' => now()->subWeek(),
        'ends_at' => now()->subWeek()->addHours(2),
    ]);

    $this->post(route('workshops.interest.store', $workshop))
        ->assertForbidden();

    expect($workshop->fresh()->interests)->toHaveCount(0);
});

test('guest can submit comment on workshop', function () {
    $workshop = createWorkshopArticle(['slug' => 'comment-guest']);

    $this->post(route('workshops.comments.store', $workshop), [
        'guest_name' => 'Minh',
        'body' => 'Mong có thêm workshop về in 3D.',
    ])
        ->assertRedirect(route('workshops.show', $workshop).'#thao-luan')
        ->assertSessionHas('success');

    $this->get(route('workshops.show', $workshop))
        ->assertSuccessful()
        ->assertSee('Mong có thêm workshop về in 3D.');
});

test('hidden comments are not shown on public workshop page', function () {
    $workshop = createWorkshopArticle(['slug' => 'comment-hidden']);

    Comment::query()->create([
        'article_id' => $workshop->getKey(),
        'body' => 'Góp ý bị ẩn không hiển thị',
        'status' => CommentStatus::Hidden,
    ]);

    Comment::query()->create([
        'article_id' => $workshop->getKey(),
        'guest_name' => 'An',
        'body' => 'Góp ý hiển thị công khai',
        'status' => CommentStatus::Active,
    ]);

    $this->get(route('workshops.show', $workshop))
        ->assertSuccessful()
        ->assertSee('Góp ý hiển thị công khai')
        ->assertDontSee('Góp ý bị ẩn không hiển thị');
});

test('honeypot blocks interest submission', function () {
    $workshop = createWorkshopArticle(['slug' => 'interest-honeypot']);

    $this->post(route('workshops.interest.store', $workshop), [
        'website' => 'https://spam.test',
    ])->assertSessionHasErrors('website');

    expect($workshop->fresh()->interests)->toHaveCount(0);
});

test('workshop page does not show zero interest count', function () {
    $workshop = createWorkshopArticle(['slug' => 'interest-zero']);

    $this->get(route('workshops.show', $workshop))
        ->assertSuccessful()
        ->assertSee('Hãy là người đầu tiên quan tâm buổi này')
        ->assertDontSee('0 người quan tâm', false);
});

test('interest count is shown when multiple people are interested', function () {
    $workshop = createWorkshopArticle(['slug' => 'interest-count']);

    $this->post(route('workshops.interest.store', $workshop));
    ArticleInterest::query()->create([
        'article_id' => $workshop->getKey(),
        'session_token' => 'other-session',
        'ip_hash' => hash('sha256', 'other-ip'),
    ]);

    $this->get(route('workshops.show', $workshop))
        ->assertSuccessful()
        ->assertSee('2')
        ->assertSee('người quan tâm');
});

test('guest can reply to a root comment', function () {
    $workshop = createWorkshopArticle(['slug' => 'comment-reply-root']);

    $root = Comment::query()->create([
        'article_id' => $workshop->getKey(),
        'guest_name' => 'An',
        'body' => 'Workshop laser rất hay!',
        'status' => CommentStatus::Active,
    ]);

    $this->post(route('workshops.comments.store', $workshop), [
        'reply_to_id' => $root->getKey(),
        'guest_name' => 'Bình',
        'body' => 'Mình cũng thích chủ đề này.',
    ])->assertRedirect(route('workshops.show', $workshop).'#thao-luan');

    $reply = Comment::query()->where('parent_id', $root->getKey())->first();

    expect($reply)->not->toBeNull()
        ->and($reply->reply_to_id)->toBeNull()
        ->and($reply->body)->toBe('Mình cũng thích chủ đề này.');

    $this->get(route('workshops.show', $workshop))
        ->assertSuccessful()
        ->assertSee('Mình cũng thích chủ đề này.');
});

test('guest replying to a child comment uses mention and stays at two levels', function () {
    $workshop = createWorkshopArticle(['slug' => 'comment-reply-child']);

    $root = Comment::query()->create([
        'article_id' => $workshop->getKey(),
        'guest_name' => 'An',
        'body' => 'Có ai đi cùng không?',
        'status' => CommentStatus::Active,
    ]);

    $child = Comment::query()->create([
        'article_id' => $workshop->getKey(),
        'parent_id' => $root->getKey(),
        'guest_name' => 'Bình',
        'body' => 'Tôi có thể đi!',
        'status' => CommentStatus::Active,
    ]);

    $this->post(route('workshops.comments.store', $workshop), [
        'reply_to_id' => $child->getKey(),
        'guest_name' => 'Chi',
        'body' => 'Mình đi chung nhé!',
    ])->assertRedirect();

    $reply = Comment::query()->latest('id')->first();

    expect($reply->parent_id)->toBe($root->getKey())
        ->and($reply->reply_to_id)->toBe($child->getKey());

    $this->get(route('workshops.show', $workshop))
        ->assertSuccessful()
        ->assertSee('@Bình')
        ->assertSee('Mình đi chung nhé!');
});

test('authenticated user comment is stored with user id not guest name', function () {
    $user = User::factory()->create(['name' => 'Test01']);
    $workshop = createWorkshopArticle(['slug' => 'comment-auth-user']);

    $this->actingAs($user)
        ->post(route('workshops.comments.store', $workshop), [
            'guest_name' => 'Should Be Ignored',
            'body' => 'Góp ý từ tài khoản đã đăng nhập.',
        ])
        ->assertRedirect(route('workshops.show', $workshop).'#thao-luan');

    $comment = Comment::query()->where('article_id', $workshop->getKey())->latest('id')->first();

    expect($comment->user_id)->toBe($user->id)
        ->and($comment->guest_name)->toBeNull()
        ->and($comment->body)->toBe('Góp ý từ tài khoản đã đăng nhập.');

    $this->actingAs($user)
        ->get(route('workshops.show', $workshop))
        ->assertSuccessful()
        ->assertSee('Test01')
        ->assertSee('Góp ý từ tài khoản đã đăng nhập.')
        ->assertDontSee('Góp ý với tên', false)
        ->assertDontSee('Đổi tên', false);
});

test('authenticated user interest is stored with user id', function () {
    $user = User::factory()->create();
    $workshop = createWorkshopArticle(['slug' => 'interest-auth-user']);

    $this->actingAs($user)
        ->post(route('workshops.interest.store', $workshop))
        ->assertRedirect(route('workshops.show', $workshop));

    $interest = ArticleInterest::query()
        ->where('article_id', $workshop->getKey())
        ->where('user_id', $user->id)
        ->first();

    expect($interest)->not->toBeNull();

    $this->actingAs($user)
        ->get(route('workshops.show', $workshop))
        ->assertSuccessful()
        ->assertSee('Bạn đã quan tâm', false);
});
