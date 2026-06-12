<?php

use App\Enums\ArticleModerationStatus;
use App\Models\Article;
use App\Models\Category;
use App\Models\User;

test('guest cannot access community post create form', function () {
    $this->get(route('community.create'))
        ->assertRedirect(route('login'));
});

test('authenticated member can create a community post pending moderation', function () {
    $user = User::factory()->create();
    $communityCategory = Category::communityPostsCategory();

    $response = $this->actingAs($user)
        ->post(route('community.store'), [
            'title' => 'Bài viết từ thành viên',
            'excerpt' => 'Tóm tắt ngắn.',
            'body' => '<p>Đoạn đầu tiên.</p><p>Đoạn thứ hai.</p>',
        ]);

    $post = Article::query()->where('title', 'Bài viết từ thành viên')->first();

    expect($post)->not->toBeNull()
        ->and($post->author_id)->toBe($user->id)
        ->and($post->category_id)->toBe($communityCategory->getKey())
        ->and($post->moderation_status)->toBe(ArticleModerationStatus::Pending)
        ->and($post->published_at)->toBeNull()
        ->and($post->submitted_at)->not->toBeNull()
        ->and($post->body)->toContain('Đoạn đầu tiên')
        ->and($post->body)->toContain('Đoạn thứ hai');

    $response->assertRedirect(route('community.my-posts', ['tab' => 'pending']));

    $this->get(route('community.index'))
        ->assertSuccessful()
        ->assertDontSee('Bài viết từ thành viên');

    $this->actingAs($user)
        ->get(route('community.show', $post))
        ->assertSuccessful()
        ->assertSee('Bài đang chờ duyệt', false);
});

test('guest cannot view pending community post', function () {
    $user = User::factory()->create();
    $post = createCommunityPost([
        'author_id' => $user->id,
        'slug' => 'bai-cho-duyet',
        'moderation_status' => ArticleModerationStatus::Pending,
        'published_at' => null,
    ]);

    $this->get(route('community.show', $post))->assertNotFound();
});

test('approved community post appears on index after moderation', function () {
    $user = User::factory()->create();
    $post = createCommunityPost([
        'author_id' => $user->id,
        'title' => 'Bài đã duyệt',
        'slug' => 'bai-da-duyet',
        'moderation_status' => ArticleModerationStatus::Pending,
        'published_at' => null,
    ]);

    $this->get(route('community.index'))
        ->assertSuccessful()
        ->assertDontSee('Bài đã duyệt');

    $post->update([
        'moderation_status' => ArticleModerationStatus::Approved,
        'published_at' => now(),
    ]);

    $this->get(route('community.index'))
        ->assertSuccessful()
        ->assertSee('Bài đã duyệt');
});

test('author can edit their own community post and resubmit for moderation', function () {
    $user = User::factory()->create();
    $post = createCommunityPost([
        'author_id' => $user->id,
        'title' => 'Bài cần sửa',
        'slug' => 'bai-can-sua',
        'body' => '<p>Nội dung cũ</p>',
    ]);

    $this->actingAs($user)
        ->patch(route('community.update', $post), [
            'title' => 'Bài đã cập nhật',
            'excerpt' => 'Tóm tắt mới',
            'body' => '<p>Nội dung mới sau khi sửa.</p>',
        ])
        ->assertRedirect(route('community.my-posts', ['tab' => 'pending']));

    $post->refresh();

    expect($post->slug)->toBe('bai-da-cap-nhat');

    expect($post->title)->toBe('Bài đã cập nhật')
        ->and($post->body)->toContain('Nội dung mới sau khi sửa.')
        ->and($post->moderation_status)->toBe(ArticleModerationStatus::Pending)
        ->and($post->published_at)->toBeNull();

    $this->get(route('community.index'))
        ->assertSuccessful()
        ->assertDontSee('Bài đã cập nhật');
});

test('member cannot edit another users community post', function () {
    $author = User::factory()->create();
    $other = User::factory()->create();
    $post = createCommunityPost([
        'author_id' => $author->id,
        'slug' => 'bai-cua-nguoi-khac',
    ]);

    $this->actingAs($other)
        ->get(route('community.edit', $post))
        ->assertForbidden();

    $this->actingAs($other)
        ->patch(route('community.update', $post), [
            'title' => 'Cố đổi tiêu đề',
            'body' => '<p>Nội dung hack.</p>',
        ])
        ->assertForbidden();
});

test('community create form is available to authenticated users', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('community.create'))
        ->assertSuccessful()
        ->assertSee('Viết bài mới')
        ->assertSee('Gửi duyệt', false);
});

test('member can view my posts page with tabs', function () {
    $user = User::factory()->create();

    createCommunityPost([
        'author_id' => $user->id,
        'title' => 'Bài chờ duyệt của tôi',
        'slug' => 'bai-cho-duyet-cua-toi',
        'moderation_status' => ArticleModerationStatus::Pending,
        'published_at' => null,
    ]);

    createCommunityPost([
        'author_id' => $user->id,
        'title' => 'Bài đã đăng của tôi',
        'slug' => 'bai-da-dang-cua-toi',
    ]);

    $this->actingAs($user)
        ->get(route('community.my-posts', ['tab' => 'pending']))
        ->assertSuccessful()
        ->assertSee('Bài chờ duyệt của tôi');

    $this->actingAs($user)
        ->get(route('community.my-posts', ['tab' => 'published']))
        ->assertSuccessful()
        ->assertSee('Bài đã đăng của tôi')
        ->assertDontSee('Bài chờ duyệt của tôi');
});

test('rejected post shows moderation note to author', function () {
    $user = User::factory()->create();
    $post = createCommunityPost([
        'author_id' => $user->id,
        'slug' => 'bai-bi-tu-choi',
        'moderation_status' => ArticleModerationStatus::Rejected,
        'moderation_note' => 'Nội dung chưa phù hợp chủ đề makerspace.',
        'published_at' => null,
    ]);

    $this->actingAs($user)
        ->get(route('community.show', $post))
        ->assertSuccessful()
        ->assertSee('Bài bị từ chối', false)
        ->assertSee('Nội dung chưa phù hợp chủ đề makerspace.');
});
