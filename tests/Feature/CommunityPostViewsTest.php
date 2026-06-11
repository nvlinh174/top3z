<?php

use App\Actions\RecordCommunityPostView;
use App\Enums\ArticleModerationStatus;
use App\Models\User;

test('public community post increments views count on first visit', function () {
    $post = createCommunityPost([
        'slug' => 'bai-dem-luot-xem',
        'views_count' => 0,
    ]);

    $this->get(route('community.show', $post))
        ->assertSuccessful()
        ->assertSee('1 lượt xem');

    expect($post->fresh()->views_count)->toBe(1);
});

test('refreshing community post within 24 hours does not increment views again', function () {
    $post = createCommunityPost([
        'slug' => 'bai-khong-dem-lai',
        'views_count' => 0,
    ]);

    $this->get(route('community.show', $post))->assertSuccessful();
    $this->get(route('community.show', $post))->assertSuccessful();

    expect($post->fresh()->views_count)->toBe(1);
});

test('pending community post preview does not increment views', function () {
    $author = User::factory()->create();
    $post = createCommunityPost([
        'author_id' => $author->id,
        'slug' => 'bai-cho-khong-dem-view',
        'moderation_status' => ArticleModerationStatus::Pending,
        'published_at' => null,
        'views_count' => 0,
    ]);

    $this->actingAs($author)
        ->get(route('community.show', $post))
        ->assertSuccessful()
        ->assertDontSee('lượt xem');

    expect($post->fresh()->views_count)->toBe(0);
});

test('guest can increment views on public community post', function () {
    $post = createCommunityPost([
        'slug' => 'bai-guest-view',
        'views_count' => 5,
    ]);

    $this->get(route('community.show', $post))->assertSuccessful();

    expect($post->fresh()->views_count)->toBe(6);
});

test('record community post view action respects deduplication', function () {
    $post = createCommunityPost([
        'views_count' => 0,
    ]);

    $action = app(RecordCommunityPostView::class);

    expect($action($post))->toBeTrue()
        ->and($post->fresh()->views_count)->toBe(1)
        ->and($action($post->fresh()))->toBeFalse()
        ->and($post->fresh()->views_count)->toBe(1);
});
