<?php

use App\Enums\ArticleModerationStatus;
use App\Models\User;
use Illuminate\Http\UploadedFile;

test('member profile page shows author and public posts', function () {
    $author = User::factory()->create(['name' => 'Tác giả Cộng đồng']);

    createCommunityPost([
        'author_id' => $author->getKey(),
        'title' => 'Bài public trên profile',
        'slug' => 'bai-public-tren-profile',
    ]);

    createCommunityPost([
        'author_id' => $author->getKey(),
        'title' => 'Bài chờ duyệt ẩn',
        'slug' => 'bai-cho-duyet-an',
        'moderation_status' => ArticleModerationStatus::Pending,
        'published_at' => null,
    ]);

    $this->get(route('members.show', $author))
        ->assertSuccessful()
        ->assertSee('Tác giả Cộng đồng')
        ->assertSee('Bài public trên profile')
        ->assertDontSee('Bài chờ duyệt ẩn');
});

test('member profile shows empty state without public posts', function () {
    $author = User::factory()->create(['name' => 'Chưa có bài']);

    $this->get(route('members.show', $author))
        ->assertSuccessful()
        ->assertSee('Chưa có bài công khai');
});

test('member can upload avatar on profile', function () {
    $user = User::factory()->create(['name' => 'Avatar User']);

    $this->actingAs($user)
        ->patch(route('profile.update'), [
            'name' => 'Avatar User',
            'avatar' => UploadedFile::fake()->image('avatar.jpg', 400, 400),
        ])
        ->assertRedirect(route('profile.edit'));

    expect($user->fresh()->avatarUrl())->not->toBeNull();
});

test('community show links author to member profile', function () {
    $author = User::factory()->create(['name' => 'Link Author']);

    $post = createCommunityPost([
        'author_id' => $author->getKey(),
        'title' => 'Bài có link tác giả',
        'slug' => 'bai-co-link-tac-gia',
    ]);

    $this->get(route('community.show', $post))
        ->assertSuccessful()
        ->assertSee(route('members.show', $author), false);
});
