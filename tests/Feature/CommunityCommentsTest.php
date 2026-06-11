<?php

use App\Enums\ArticleModerationStatus;
use App\Enums\CommentStatus;
use App\Models\Comment;
use App\Models\User;

test('guest can submit comment on public community post', function () {
    $post = createCommunityPost(['slug' => 'community-comment-guest']);

    $this->post(route('community.comments.store', $post), [
        'guest_name' => 'Lan',
        'body' => 'Bài viết rất hữu ích, cảm ơn bạn!',
    ])
        ->assertRedirect(route('community.show', $post).'#thao-luan')
        ->assertSessionHas('success');

    $this->get(route('community.show', $post))
        ->assertSuccessful()
        ->assertSee('Bài viết rất hữu ích, cảm ơn bạn!');
});

test('authenticated user comment is stored with user id on community post', function () {
    $user = User::factory()->create(['name' => 'Member Top3z']);
    $post = createCommunityPost(['slug' => 'community-comment-member']);

    $this->actingAs($user)
        ->post(route('community.comments.store', $post), [
            'guest_name' => 'Ignored',
            'body' => 'Mình đồng ý với quan điểm này.',
        ])
        ->assertRedirect(route('community.show', $post).'#thao-luan');

    $comment = Comment::query()->where('article_id', $post->getKey())->latest('id')->first();

    expect($comment)->not->toBeNull()
        ->and($comment->user_id)->toBe($user->getKey())
        ->and($comment->guest_name)->toBeNull()
        ->and($comment->body)->toBe('Mình đồng ý với quan điểm này.');

    $this->get(route('community.show', $post))
        ->assertSuccessful()
        ->assertSee('Member Top3z')
        ->assertSee('Mình đồng ý với quan điểm này.');
});

test('guest can reply to community post comment', function () {
    $post = createCommunityPost(['slug' => 'community-comment-reply']);

    $root = Comment::query()->create([
        'article_id' => $post->getKey(),
        'guest_name' => 'Hùng',
        'body' => 'Có thêm ảnh chi tiết không?',
        'status' => CommentStatus::Active,
    ]);

    $this->post(route('community.comments.store', $post), [
        'reply_to_id' => $root->getKey(),
        'guest_name' => 'Vy',
        'body' => 'Mình cũng muốn xem thêm!',
    ])->assertRedirect(route('community.show', $post).'#thao-luan');

    $this->get(route('community.show', $post))
        ->assertSuccessful()
        ->assertSee('Mình cũng muốn xem thêm!');
});

test('cannot comment on pending community post', function () {
    $author = User::factory()->create();
    $post = createCommunityPost([
        'author_id' => $author->id,
        'slug' => 'community-comment-pending',
        'moderation_status' => ArticleModerationStatus::Pending,
        'published_at' => null,
    ]);

    $this->post(route('community.comments.store', $post), [
        'guest_name' => 'Lan',
        'body' => 'Không được gửi.',
    ])->assertNotFound();

    expect(Comment::query()->where('article_id', $post->getKey())->count())->toBe(0);
});

test('preview community post does not show comments section', function () {
    $author = User::factory()->create();
    $post = createCommunityPost([
        'author_id' => $author->id,
        'slug' => 'community-comment-preview',
        'moderation_status' => ArticleModerationStatus::Pending,
        'published_at' => null,
    ]);

    $this->actingAs($author)
        ->get(route('community.show', $post))
        ->assertSuccessful()
        ->assertDontSee('Thảo luận')
        ->assertDontSee('Gửi bình luận');
});

test('hidden comments are not shown on public community post', function () {
    $post = createCommunityPost(['slug' => 'community-comment-hidden']);

    Comment::query()->create([
        'article_id' => $post->getKey(),
        'body' => 'Bình luận bị ẩn',
        'status' => CommentStatus::Hidden,
    ]);

    Comment::query()->create([
        'article_id' => $post->getKey(),
        'guest_name' => 'An',
        'body' => 'Bình luận hiển thị',
        'status' => CommentStatus::Active,
    ]);

    $this->get(route('community.show', $post))
        ->assertSuccessful()
        ->assertSee('Bình luận hiển thị')
        ->assertDontSee('Bình luận bị ẩn');
});
