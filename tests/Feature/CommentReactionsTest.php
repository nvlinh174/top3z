<?php

use App\Enums\AppNotificationType;
use App\Enums\ArticleModerationStatus;
use App\Enums\CommentStatus;
use App\Models\Comment;
use App\Models\CommentReaction;
use App\Models\User;
use App\Support\GuestEngagement;
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

test('guest can toggle like on community comment', function () {
    $post = createCommunityPost(['slug' => 'community-comment-like-guest']);
    $sessionToken = GuestEngagement::sessionToken();

    $comment = Comment::query()->create([
        'article_id' => $post->getKey(),
        'guest_name' => 'Khách',
        'body' => 'Hay quá!',
        'status' => CommentStatus::Active,
    ]);

    $this->postJson(route('community.comment-reactions.toggle', [$post, $comment]))
        ->assertSuccessful()
        ->assertJson(['active' => true, 'count' => 1]);

    $reaction = CommentReaction::query()->first();

    expect($reaction)->not->toBeNull()
        ->and($reaction->user_id)->toBeNull()
        ->and($reaction->session_token)->toBe($sessionToken);
});

test('guest cannot like workshop comment', function () {
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

test('liking community comment notifies member comment author', function () {
    $author = User::factory()->create();
    $post = createCommunityPost(['slug' => 'community-comment-like-notify']);

    $comment = Comment::query()->create([
        'article_id' => $post->getKey(),
        'user_id' => $author->getKey(),
        'body' => 'Bình luận của member',
        'status' => CommentStatus::Active,
    ]);

    $this->postJson(route('community.comment-reactions.toggle', [$post, $comment]))
        ->assertSuccessful()
        ->assertJson(['active' => true]);

    $notification = $author->fresh()->notifications->first();

    expect($notification)->not->toBeNull()
        ->and($notification->data['type'])->toBe(AppNotificationType::CommentLiked->value);
});

test('member does not get notified when liking own comment', function () {
    $user = User::factory()->create();
    $post = createCommunityPost(['slug' => 'community-comment-like-self']);

    $comment = Comment::query()->create([
        'article_id' => $post->getKey(),
        'user_id' => $user->getKey(),
        'body' => 'Tự thích bình luận',
        'status' => CommentStatus::Active,
    ]);

    $this->actingAs($user)
        ->postJson(route('community.comment-reactions.toggle', [$post, $comment]))
        ->assertSuccessful()
        ->assertJson(['active' => true]);

    expect($user->fresh()->notifications)->toHaveCount(0);
});

test('comment liked notification is debounced for five minutes', function () {
    $author = User::factory()->create();
    $liker = User::factory()->create();
    $post = createCommunityPost(['slug' => 'community-comment-like-debounce']);

    $comment = Comment::query()->create([
        'article_id' => $post->getKey(),
        'user_id' => $author->getKey(),
        'body' => 'Bình luận debounce',
        'status' => CommentStatus::Active,
    ]);

    $this->actingAs($liker)
        ->postJson(route('community.comment-reactions.toggle', [$post, $comment]))
        ->assertJson(['active' => true]);

    $this->actingAs($liker)
        ->postJson(route('community.comment-reactions.toggle', [$post, $comment]))
        ->assertJson(['active' => false]);

    $this->actingAs($liker)
        ->postJson(route('community.comment-reactions.toggle', [$post, $comment]))
        ->assertJson(['active' => true]);

    expect($author->fresh()->notifications)->toHaveCount(1);
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

test('comment reaction unique constraint prevents duplicate member like', function () {
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
        'session_token' => hash('sha256', 'member-session'),
    ]);

    expect(fn () => CommentReaction::query()->create([
        'comment_id' => $comment->getKey(),
        'user_id' => $user->getKey(),
        'session_token' => hash('sha256', 'other-session'),
    ]))->toThrow(UniqueConstraintViolationException::class);
});

test('comment reaction unique constraint prevents duplicate guest session like', function () {
    $post = createCommunityPost(['slug' => 'community-comment-like-guest-unique']);
    $sessionToken = hash('sha256', 'guest-comment-session');

    $comment = Comment::query()->create([
        'article_id' => $post->getKey(),
        'guest_name' => 'Khách',
        'body' => 'Hay',
        'status' => CommentStatus::Active,
    ]);

    CommentReaction::query()->create([
        'comment_id' => $comment->getKey(),
        'user_id' => null,
        'session_token' => $sessionToken,
    ]);

    expect(fn () => CommentReaction::query()->create([
        'comment_id' => $comment->getKey(),
        'user_id' => null,
        'session_token' => $sessionToken,
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
