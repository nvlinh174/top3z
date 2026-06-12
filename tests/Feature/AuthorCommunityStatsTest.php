<?php

use App\Enums\ArticleModerationStatus;
use App\Enums\ArticleReactionType;
use App\Models\ArticleReaction;
use App\Models\User;
use App\Support\AuthorCommunityStats;

test('author stats only count approved published posts', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    $published = createCommunityPost([
        'author_id' => $user->id,
        'title' => 'Bài đã đăng',
        'slug' => 'bai-da-dang-stats',
        'views_count' => 120,
    ]);

    createCommunityPost([
        'author_id' => $user->id,
        'title' => 'Bài chờ duyệt',
        'slug' => 'bai-cho-duyet-stats',
        'moderation_status' => ArticleModerationStatus::Pending,
        'published_at' => null,
        'views_count' => 500,
    ]);

    createCommunityPost([
        'author_id' => $user->id,
        'title' => 'Bài bị từ chối',
        'slug' => 'bai-bi-tu-choi-stats',
        'moderation_status' => ArticleModerationStatus::Rejected,
        'published_at' => null,
        'views_count' => 300,
    ]);

    ArticleReaction::query()->create([
        'article_id' => $published->getKey(),
        'user_id' => $other->getKey(),
        'session_token' => hash('sha256', 'author-stats-like'),
        'type' => ArticleReactionType::Like,
    ]);

    ArticleReaction::query()->create([
        'article_id' => $published->getKey(),
        'user_id' => $other->getKey(),
        'session_token' => hash('sha256', 'author-stats-favorite'),
        'type' => ArticleReactionType::Favorite,
    ]);

    $stats = AuthorCommunityStats::summaryFor($user);

    expect($stats)->toBe([
        'posts_count' => 1,
        'total_views' => 120,
        'total_likes' => 1,
        'total_favorites' => 1,
    ]);
});

test('author stats top viewed posts only includes approved published posts', function () {
    $user = User::factory()->create();

    createCommunityPost([
        'author_id' => $user->id,
        'title' => 'Bài ít xem',
        'slug' => 'bai-it-xem',
        'views_count' => 10,
    ]);

    createCommunityPost([
        'author_id' => $user->id,
        'title' => 'Bài nhiều xem',
        'slug' => 'bai-nhieu-xem',
        'views_count' => 250,
    ]);

    createCommunityPost([
        'author_id' => $user->id,
        'title' => 'Bài pending nhiều xem',
        'slug' => 'bai-pending-nhieu-xem',
        'moderation_status' => ArticleModerationStatus::Pending,
        'published_at' => null,
        'views_count' => 999,
    ]);

    $topPosts = AuthorCommunityStats::topViewedPostsFor($user);

    expect($topPosts)->toHaveCount(2)
        ->and($topPosts->first()->title)->toBe('Bài nhiều xem')
        ->and($topPosts->last()->title)->toBe('Bài ít xem');
});

test('my posts page shows author stats cards above tabs', function () {
    $user = User::factory()->create();

    createCommunityPost([
        'author_id' => $user->id,
        'title' => 'Bài thống kê',
        'slug' => 'bai-thong-ke',
        'views_count' => 42,
    ]);

    $this->actingAs($user)
        ->get(route('community.my-posts'))
        ->assertSuccessful()
        ->assertSee('Tổng lượt xem', false)
        ->assertSee('Lượt thích', false)
        ->assertSee('Yêu thích', false)
        ->assertSee('Bài đã đăng', false)
        ->assertSee('42', false)
        ->assertSee('Bài xem nhiều nhất', false)
        ->assertSee('Bài thống kê');
});
