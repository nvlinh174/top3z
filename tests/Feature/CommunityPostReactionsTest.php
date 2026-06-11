<?php

use App\Enums\ArticleModerationStatus;
use App\Enums\ArticleReactionType;
use App\Models\ArticleReaction;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;

test('authenticated user can toggle like on and off', function () {
    $user = User::factory()->create();
    $post = createCommunityPost(['slug' => 'bai-reaction-like']);

    $this->actingAs($user)
        ->postJson(route('community.reactions.toggle', $post), ['type' => 'like'])
        ->assertSuccessful()
        ->assertJson([
            'type' => 'like',
            'active' => true,
            'counts' => [
                'like' => 1,
                'favorite' => 0,
            ],
        ]);

    expect(ArticleReaction::query()->count())->toBe(1);

    $this->actingAs($user)
        ->postJson(route('community.reactions.toggle', $post), ['type' => 'like'])
        ->assertSuccessful()
        ->assertJson([
            'type' => 'like',
            'active' => false,
            'counts' => [
                'like' => 0,
                'favorite' => 0,
            ],
        ]);

    expect(ArticleReaction::query()->count())->toBe(0);
});

test('authenticated user can like and favorite the same post independently', function () {
    $user = User::factory()->create();
    $post = createCommunityPost(['slug' => 'bai-reaction-both']);

    $this->actingAs($user)
        ->postJson(route('community.reactions.toggle', $post), ['type' => 'like'])
        ->assertSuccessful()
        ->assertJsonPath('counts.like', 1);

    $this->actingAs($user)
        ->postJson(route('community.reactions.toggle', $post), ['type' => 'favorite'])
        ->assertSuccessful()
        ->assertJson([
            'type' => 'favorite',
            'active' => true,
            'counts' => [
                'like' => 1,
                'favorite' => 1,
            ],
        ]);

    expect(ArticleReaction::query()->count())->toBe(2);
});

test('guest cannot toggle reactions', function () {
    $post = createCommunityPost(['slug' => 'bai-reaction-guest']);

    $this->postJson(route('community.reactions.toggle', $post), ['type' => 'like'])
        ->assertUnauthorized();
});

test('pending community post returns not found for reaction toggle', function () {
    $user = User::factory()->create();
    $author = User::factory()->create();

    $post = createCommunityPost([
        'author_id' => $author->id,
        'slug' => 'bai-reaction-pending',
        'moderation_status' => ArticleModerationStatus::Pending,
        'published_at' => null,
    ]);

    $this->actingAs($user)
        ->postJson(route('community.reactions.toggle', $post), ['type' => 'like'])
        ->assertNotFound();
});

test('article reaction unique constraint prevents duplicate like', function () {
    $user = User::factory()->create();
    $post = createCommunityPost(['slug' => 'bai-reaction-unique']);

    ArticleReaction::query()->create([
        'article_id' => $post->getKey(),
        'user_id' => $user->getKey(),
        'type' => ArticleReactionType::Like,
    ]);

    expect(fn () => ArticleReaction::query()->create([
        'article_id' => $post->getKey(),
        'user_id' => $user->getKey(),
        'type' => ArticleReactionType::Like,
    ]))->toThrow(UniqueConstraintViolationException::class);
});

test('public community post shows reaction buttons', function () {
    $post = createCommunityPost(['slug' => 'bai-reaction-ui']);

    $this->get(route('community.show', $post))
        ->assertSuccessful()
        ->assertSee('Thích')
        ->assertSee('Yêu thích');
});

test('login page stores intended url from query parameter', function () {
    $post = createCommunityPost(['slug' => 'bai-reaction-intended']);

    $this->get(route('login', ['intended' => route('community.show', $post)]))
        ->assertSuccessful();

    expect(session('url.intended'))->toBe(route('community.show', $post));
});
