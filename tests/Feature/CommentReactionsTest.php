<?php

use App\Enums\ArticleModerationStatus;
use App\Enums\CommentStatus;
use App\Models\Comment;
use App\Models\CommentReaction;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;

test('authenticated user can toggle like on workshop comment', function () {
    $user = User::factory()->create();
    $workshop = createWorkshopArticle(['slug' => 'workshop-comment-like']);

    $comment = Comment::query()->create([
        'article_id' => $workshop->getKey(),
        'guest_name' => 'An',
        'body' => 'Workshop hay quá!',
        'status' => CommentStatus::Active,
    ]);

    $this->actingAs($user)
        ->postJson(route('workshops.comment-reactions.toggle', [$workshop, $comment]))
        ->assertSuccessful()
        ->assertJson(['active' => true, 'count' => 1]);

    $this->actingAs($user)
        ->postJson(route('workshops.comment-reactions.toggle', [$workshop, $comment]))
        ->assertSuccessful()
        ->assertJson(['active' => false, 'count' => 0]);

    expect(CommentReaction::query()->count())->toBe(0);
});

test('authenticated user can toggle like on community comment', function () {
    $user = User::factory()->create();
    $post = createCommunityPost(['slug' => 'community-comment-like']);

    $comment = Comment::query()->create([
        'article_id' => $post->getKey(),
        'guest_name' => 'Bình',
        'body' => 'Bài viết hay!',
        'status' => CommentStatus::Active,
    ]);

    $this->actingAs($user)
        ->postJson(route('community.comment-reactions.toggle', [$post, $comment]))
        ->assertSuccessful()
        ->assertJson(['active' => true, 'count' => 1]);
});

test('guest cannot like comment', function () {
    $workshop = createWorkshopArticle(['slug' => 'workshop-comment-like-guest']);

    $comment = Comment::query()->create([
        'article_id' => $workshop->getKey(),
        'guest_name' => 'An',
        'body' => 'Góp ý workshop',
        'status' => CommentStatus::Active,
    ]);

    $this->postJson(route('workshops.comment-reactions.toggle', [$workshop, $comment]))
        ->assertUnauthorized();
});

test('cannot like hidden comment', function () {
    $user = User::factory()->create();
    $post = createCommunityPost(['slug' => 'community-comment-like-hidden']);

    $comment = Comment::query()->create([
        'article_id' => $post->getKey(),
        'body' => 'Bình luận ẩn',
        'status' => CommentStatus::Hidden,
    ]);

    $this->actingAs($user)
        ->postJson(route('community.comment-reactions.toggle', [$post, $comment]))
        ->assertNotFound();
});

test('cannot like comment on pending community post', function () {
    $user = User::factory()->create();
    $author = User::factory()->create();

    $post = createCommunityPost([
        'author_id' => $author->id,
        'slug' => 'community-comment-like-pending',
        'moderation_status' => ArticleModerationStatus::Pending,
        'published_at' => null,
    ]);

    $comment = Comment::query()->create([
        'article_id' => $post->getKey(),
        'body' => 'Bình luận trên bài chờ',
        'status' => CommentStatus::Active,
    ]);

    $this->actingAs($user)
        ->postJson(route('community.comment-reactions.toggle', [$post, $comment]))
        ->assertNotFound();
});

test('comment reaction unique constraint prevents duplicate like', function () {
    $user = User::factory()->create();
    $workshop = createWorkshopArticle(['slug' => 'workshop-comment-like-unique']);

    $comment = Comment::query()->create([
        'article_id' => $workshop->getKey(),
        'body' => 'Góp ý',
        'status' => CommentStatus::Active,
    ]);

    CommentReaction::query()->create([
        'comment_id' => $comment->getKey(),
        'user_id' => $user->getKey(),
    ]);

    expect(fn () => CommentReaction::query()->create([
        'comment_id' => $comment->getKey(),
        'user_id' => $user->getKey(),
    ]))->toThrow(UniqueConstraintViolationException::class);
});

test('comment from another article returns not found', function () {
    $user = User::factory()->create();
    $workshop = createWorkshopArticle(['slug' => 'workshop-comment-like-mismatch']);
    $otherWorkshop = createWorkshopArticle(['slug' => 'workshop-comment-like-other']);

    $comment = Comment::query()->create([
        'article_id' => $otherWorkshop->getKey(),
        'body' => 'Góp ý workshop khác',
        'status' => CommentStatus::Active,
    ]);

    $this->actingAs($user)
        ->postJson(route('workshops.comment-reactions.toggle', [$workshop, $comment]))
        ->assertNotFound();
});
