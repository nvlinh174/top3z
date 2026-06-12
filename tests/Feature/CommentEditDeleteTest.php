<?php

use App\Enums\CommentStatus;
use App\Models\Comment;
use App\Models\User;

test('member can edit own community comment', function () {
    $user = User::factory()->create(['name' => 'Comment Editor']);
    $post = createCommunityPost(['slug' => 'community-comment-edit']);

    $comment = Comment::query()->create([
        'article_id' => $post->getKey(),
        'user_id' => $user->getKey(),
        'body' => 'Nội dung cũ',
        'status' => CommentStatus::Active,
    ]);

    $this->actingAs($user)
        ->patch(route('community.comments.update', [$post, $comment]), [
            'body' => 'Nội dung đã sửa',
        ])
        ->assertRedirect(route('community.show', $post).'#thao-luan')
        ->assertSessionHas('success');

    $comment->refresh();

    expect($comment->body)->toBe('Nội dung đã sửa')
        ->and($comment->edited_at)->not->toBeNull();

    $this->get(route('community.show', $post))
        ->assertSuccessful()
        ->assertSee('Nội dung đã sửa')
        ->assertSee('đã chỉnh sửa');
});

test('member can delete own community comment with placeholder', function () {
    $user = User::factory()->create();
    $post = createCommunityPost(['slug' => 'community-comment-delete']);

    $comment = Comment::query()->create([
        'article_id' => $post->getKey(),
        'user_id' => $user->getKey(),
        'body' => 'Sẽ bị xóa mềm',
        'status' => CommentStatus::Active,
    ]);

    $this->actingAs($user)
        ->delete(route('community.comments.destroy', [$post, $comment]))
        ->assertRedirect(route('community.show', $post).'#thao-luan')
        ->assertSessionHas('success');

    $comment->refresh();

    expect($comment->status)->toBe(CommentStatus::Hidden);

    $this->get(route('community.show', $post))
        ->assertSuccessful()
        ->assertSee('Bình luận đã bị xóa')
        ->assertDontSee('Sẽ bị xóa mềm');
});

test('member cannot edit another users community comment', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $post = createCommunityPost(['slug' => 'community-comment-edit-forbidden']);

    $comment = Comment::query()->create([
        'article_id' => $post->getKey(),
        'user_id' => $owner->getKey(),
        'body' => 'Không được sửa',
        'status' => CommentStatus::Active,
    ]);

    $this->actingAs($other)
        ->patch(route('community.comments.update', [$post, $comment]), [
            'body' => 'Cố sửa',
        ])
        ->assertForbidden();

    expect($comment->fresh()->body)->toBe('Không được sửa');
});

test('member cannot delete another users community comment', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $post = createCommunityPost(['slug' => 'community-comment-delete-forbidden']);

    $comment = Comment::query()->create([
        'article_id' => $post->getKey(),
        'user_id' => $owner->getKey(),
        'body' => 'Không được xóa',
        'status' => CommentStatus::Active,
    ]);

    $this->actingAs($other)
        ->delete(route('community.comments.destroy', [$post, $comment]))
        ->assertForbidden();

    expect($comment->fresh()->status)->toBe(CommentStatus::Active);
});

test('guest cannot edit or delete community comment', function () {
    $post = createCommunityPost(['slug' => 'community-comment-guest-manage']);

    $comment = Comment::query()->create([
        'article_id' => $post->getKey(),
        'guest_name' => 'Khách',
        'body' => 'Bình luận guest',
        'status' => CommentStatus::Active,
    ]);

    $this->patch(route('community.comments.update', [$post, $comment]), [
        'body' => 'Hack',
    ])->assertRedirect(route('login'));

    $this->delete(route('community.comments.destroy', [$post, $comment]))
        ->assertRedirect(route('login'));
});

test('member can edit own workshop comment', function () {
    $user = User::factory()->create();
    $workshop = createWorkshopArticle(['slug' => 'workshop-comment-edit']);

    $comment = Comment::query()->create([
        'article_id' => $workshop->getKey(),
        'user_id' => $user->getKey(),
        'body' => 'Góp ý cũ',
        'status' => CommentStatus::Active,
    ]);

    $this->actingAs($user)
        ->patch(route('workshops.comments.update', [$workshop, $comment]), [
            'body' => 'Góp ý mới',
        ])
        ->assertRedirect(route('workshops.show', $workshop).'#thao-luan')
        ->assertSessionHas('success');

    expect($comment->fresh()->body)->toBe('Góp ý mới');
});

test('member can delete own workshop comment', function () {
    $user = User::factory()->create();
    $workshop = createWorkshopArticle(['slug' => 'workshop-comment-delete']);

    $comment = Comment::query()->create([
        'article_id' => $workshop->getKey(),
        'user_id' => $user->getKey(),
        'body' => 'Góp ý sẽ ẩn',
        'status' => CommentStatus::Active,
    ]);

    $this->actingAs($user)
        ->delete(route('workshops.comments.destroy', [$workshop, $comment]))
        ->assertRedirect(route('workshops.show', $workshop).'#thao-luan');

    expect($comment->fresh()->status)->toBe(CommentStatus::Hidden);
});

test('owner sees manage menu on own comment', function () {
    $user = User::factory()->create(['name' => 'Owner Menu']);
    $post = createCommunityPost(['slug' => 'community-comment-menu']);

    Comment::query()->create([
        'article_id' => $post->getKey(),
        'user_id' => $user->getKey(),
        'body' => 'Bình luận có menu',
        'status' => CommentStatus::Active,
    ]);

    $this->actingAs($user)
        ->get(route('community.show', $post))
        ->assertSuccessful()
        ->assertSee('Bình luận có menu')
        ->assertSee('⋯');
});

test('other member does not see manage menu on comment', function () {
    $owner = User::factory()->create();
    $viewer = User::factory()->create();
    $post = createCommunityPost(['slug' => 'community-comment-no-menu']);

    Comment::query()->create([
        'article_id' => $post->getKey(),
        'user_id' => $owner->getKey(),
        'body' => 'Bình luận của người khác',
        'status' => CommentStatus::Active,
    ]);

    $response = $this->actingAs($viewer)
        ->get(route('community.show', $post))
        ->assertSuccessful()
        ->assertSee('Bình luận của người khác');

    expect($response->content())->not->toContain('Tùy chọn bình luận');
});
