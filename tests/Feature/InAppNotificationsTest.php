<?php

use App\Enums\AppNotificationType;
use App\Enums\ArticleModerationStatus;
use App\Enums\CommentStatus;
use App\Models\Comment;
use App\Models\User;
use App\Support\CommunityPostModeration;
use Illuminate\Notifications\DatabaseNotification;

test('approving community post notifies author', function () {
    $author = User::factory()->create();
    $post = createCommunityPost([
        'author_id' => $author->getKey(),
        'title' => 'Bài chờ duyệt thông báo',
        'slug' => 'bai-cho-duyet-thong-bao',
        'moderation_status' => ArticleModerationStatus::Pending,
        'published_at' => null,
    ]);

    CommunityPostModeration::approve($post);

    $notification = $author->fresh()->notifications->first();

    expect($notification)->toBeInstanceOf(DatabaseNotification::class)
        ->and($notification->data['type'])->toBe(AppNotificationType::PostApproved->value)
        ->and($notification->data['message'])->toContain('Bài chờ duyệt thông báo')
        ->and($notification->data['url'])->toBe(route('community.show', $post));
});

test('rejecting community post notifies author with note', function () {
    $author = User::factory()->create();
    $post = createCommunityPost([
        'author_id' => $author->getKey(),
        'title' => 'Bài bị từ chối thông báo',
        'slug' => 'bai-bi-tu-choi-thong-bao',
        'moderation_status' => ArticleModerationStatus::Pending,
        'published_at' => null,
    ]);

    CommunityPostModeration::reject($post, 'Nội dung chưa phù hợp.');

    $notification = $author->fresh()->notifications->first();

    expect($notification->data['type'])->toBe(AppNotificationType::PostRejected->value)
        ->and($notification->data['message'])->toContain('Nội dung chưa phù hợp.')
        ->and($notification->data['url'])->toBe(route('community.my-posts', ['tab' => 'rejected']));
});

test('new comment on post notifies post author', function () {
    $author = User::factory()->create(['name' => 'Tác giả bài']);
    $commenter = User::factory()->create(['name' => 'Người bình luận']);
    $post = createCommunityPost([
        'author_id' => $author->getKey(),
        'slug' => 'community-notify-author',
    ]);

    $this->actingAs($commenter)
        ->post(route('community.comments.store', $post), [
            'body' => 'Bình luận mới trên bài của bạn.',
        ])
        ->assertRedirect();

    $notification = $author->fresh()->notifications->first();

    expect($notification->data['type'])->toBe(AppNotificationType::CommentOnPost->value)
        ->and($notification->data['message'])->toContain('Người bình luận')
        ->and($notification->data['url'])->toBe(route('community.show', $post).'#thao-luan');
});

test('reply to member comment notifies comment author', function () {
    $parentAuthor = User::factory()->create(['name' => 'Chủ comment']);
    $replier = User::factory()->create(['name' => 'Người trả lời']);
    $post = createCommunityPost(['slug' => 'community-notify-reply']);

    $parent = Comment::query()->create([
        'article_id' => $post->getKey(),
        'user_id' => $parentAuthor->getKey(),
        'body' => 'Comment gốc cần trả lời',
        'status' => CommentStatus::Active,
    ]);

    $this->actingAs($replier)
        ->post(route('community.comments.store', $post), [
            'reply_to_id' => $parent->getKey(),
            'body' => 'Đây là câu trả lời.',
        ])
        ->assertRedirect();

    $notification = $parentAuthor->fresh()->notifications->first();

    expect($notification->data['type'])->toBe(AppNotificationType::CommentReply->value)
        ->and($notification->data['message'])->toContain('Người trả lời');

    $reply = Comment::query()
        ->where('article_id', $post->getKey())
        ->where('parent_id', $parent->getKey())
        ->latest('id')
        ->first();

    expect($notification->data['url'])->toBe(route('community.show', $post).'#comment-'.$reply->getKey());
});

test('author does not get notified when commenting on own post', function () {
    $author = User::factory()->create();
    $post = createCommunityPost([
        'author_id' => $author->getKey(),
        'slug' => 'community-notify-self-comment',
    ]);

    $this->actingAs($author)
        ->post(route('community.comments.store', $post), [
            'body' => 'Tự bình luận bài của mình.',
        ])
        ->assertRedirect();

    expect($author->fresh()->notifications)->toHaveCount(0);
});

test('guest cannot access notification routes', function () {
    $this->get(route('notifications.index'))->assertRedirect(route('login'));
    $this->get(route('notifications.unread-count'))->assertRedirect(route('login'));
    $this->get(route('notifications.recent'))->assertRedirect(route('login'));
});

test('member can read unread count and mark notifications read', function () {
    $user = User::factory()->create();
    $post = createCommunityPost([
        'author_id' => $user->getKey(),
        'slug' => 'community-notify-mark-read',
        'moderation_status' => ArticleModerationStatus::Pending,
        'published_at' => null,
    ]);

    CommunityPostModeration::approve($post);

    $this->actingAs($user)
        ->getJson(route('notifications.unread-count'))
        ->assertSuccessful()
        ->assertJson(['count' => 1]);

    $notification = $user->fresh()->unreadNotifications->first();

    $this->actingAs($user)
        ->postJson(route('notifications.read', $notification->id))
        ->assertSuccessful()
        ->assertJson(['url' => route('community.show', $post)]);

    expect($user->fresh()->unreadNotifications)->toHaveCount(0);
});

test('member can mark all notifications as read', function () {
    $user = User::factory()->create();
    $post = createCommunityPost([
        'author_id' => $user->getKey(),
        'slug' => 'community-notify-read-all-1',
        'moderation_status' => ArticleModerationStatus::Pending,
        'published_at' => null,
    ]);

    CommunityPostModeration::approve($post);
    CommunityPostModeration::reject(
        createCommunityPost([
            'author_id' => $user->getKey(),
            'slug' => 'community-notify-read-all-2',
            'moderation_status' => ArticleModerationStatus::Pending,
            'published_at' => null,
        ]),
        'Lý do từ chối.',
    );

    $this->actingAs($user)
        ->postJson(route('notifications.read-all'))
        ->assertSuccessful()
        ->assertJson(['ok' => true]);

    expect($user->fresh()->unreadNotifications)->toHaveCount(0);
});

test('notifications index page lists user notifications', function () {
    $user = User::factory()->create();
    $post = createCommunityPost([
        'author_id' => $user->getKey(),
        'title' => 'Bài hiển thị trong danh sách',
        'slug' => 'community-notify-index',
        'moderation_status' => ArticleModerationStatus::Pending,
        'published_at' => null,
    ]);

    CommunityPostModeration::approve($post);

    $this->actingAs($user)
        ->get(route('notifications.index'))
        ->assertSuccessful()
        ->assertSee('Thông báo')
        ->assertSee('Bài hiển thị trong danh sách');
});
