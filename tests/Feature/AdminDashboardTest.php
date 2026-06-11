<?php

use App\Enums\ArticleModerationStatus;
use App\Filament\Widgets\PendingCommunityPostsTable;
use App\Models\User;
use App\Support\CommunityPostModeration;
use Livewire\Livewire;

test('admin dashboard shows pending community post', function () {
    $admin = User::factory()->admin()->create();
    $author = User::factory()->create();

    createCommunityPost([
        'author_id' => $author->id,
        'title' => 'Bài chờ trên dashboard',
        'slug' => 'bai-cho-tren-dashboard',
        'moderation_status' => ArticleModerationStatus::Pending,
        'published_at' => null,
    ]);

    $this->actingAs($admin)
        ->get('/admin')
        ->assertSuccessful()
        ->assertSee('Bài chờ trên dashboard')
        ->assertSee('Chờ duyệt');
});

test('admin can approve pending post from dashboard widget', function () {
    $admin = User::factory()->admin()->create();
    $author = User::factory()->create();

    $post = createCommunityPost([
        'author_id' => $author->id,
        'title' => 'Bài duyệt từ widget',
        'slug' => 'bai-duyet-tu-widget',
        'moderation_status' => ArticleModerationStatus::Pending,
        'published_at' => null,
    ]);

    $this->actingAs($admin);

    Livewire::test(PendingCommunityPostsTable::class)
        ->callTableAction('approve', $post);

    $post->refresh();

    expect($post->moderation_status)->toBe(ArticleModerationStatus::Approved)
        ->and($post->published_at)->not->toBeNull();
});

test('community post moderation helper rejects with note', function () {
    $post = createCommunityPost([
        'moderation_status' => ArticleModerationStatus::Pending,
        'published_at' => null,
    ]);

    CommunityPostModeration::reject($post, 'Nội dung chưa phù hợp.');

    $post->refresh();

    expect($post->moderation_status)->toBe(ArticleModerationStatus::Rejected)
        ->and($post->moderation_note)->toBe('Nội dung chưa phù hợp.');
});
