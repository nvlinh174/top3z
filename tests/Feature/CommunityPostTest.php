<?php

use App\Models\Article;
use App\Models\Category;
use App\Models\User;

test('guest cannot access community post create form', function () {
    $this->get(route('community.create'))
        ->assertRedirect(route('login'));
});

test('authenticated member can create a community post', function () {
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
        ->and($post->body)->toContain('Đoạn đầu tiên')
        ->and($post->body)->toContain('Đoạn thứ hai');

    $response->assertRedirect(route('community.show', $post));

    $this->get(route('community.index'))
        ->assertSuccessful()
        ->assertSee('Bài viết từ thành viên');
});

test('author can edit their own community post', function () {
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
        ->assertRedirect();

    $post->refresh();

    expect($post->slug)->toBe('bai-da-cap-nhat');

    expect($post->title)->toBe('Bài đã cập nhật')
        ->and($post->body)->toContain('Nội dung mới sau khi sửa.');

    $this->actingAs($user)
        ->get(route('community.show', $post))
        ->assertSuccessful()
        ->assertSee('Sửa bài viết', false);
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
        ->assertSee('Đăng bài', false);
});
