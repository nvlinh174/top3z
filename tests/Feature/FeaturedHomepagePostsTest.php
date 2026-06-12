<?php

use App\Enums\ArticleModerationStatus;

test('home page shows featured community posts with badge', function () {
    createCommunityPost([
        'title' => 'Bài thường mới nhất',
        'slug' => 'bai-thuong-moi-nhat',
        'published_at' => now()->subHour(),
    ]);

    createCommunityPost([
        'title' => 'Bài nổi bật trang chủ',
        'slug' => 'bai-noi-bat-trang-chu',
        'is_featured' => true,
        'published_at' => now()->subDay(),
    ]);

    $this->get(route('home'))
        ->assertSuccessful()
        ->assertSee('Bài nổi bật trang chủ')
        ->assertSee('Nổi bật')
        ->assertSee('Bài thường mới nhất');
});

test('pending featured post does not appear on home page', function () {
    createCommunityPost([
        'title' => 'Bài featured chờ duyệt',
        'slug' => 'bai-featured-cho-duyet',
        'is_featured' => true,
        'moderation_status' => ArticleModerationStatus::Pending,
        'published_at' => null,
    ]);

    $this->get(route('home'))
        ->assertSuccessful()
        ->assertDontSee('Bài featured chờ duyệt');
});

test('home page does not duplicate featured post in recent list', function () {
    $featured = createCommunityPost([
        'title' => 'Bài featured không trùng',
        'slug' => 'bai-featured-khong-trung',
        'is_featured' => true,
        'published_at' => now(),
    ]);

    createCommunityPost([
        'title' => 'Bài thường thứ hai',
        'slug' => 'bai-thuong-thu-hai',
        'published_at' => now()->subHour(),
    ]);

    $response = $this->get(route('home'))->assertSuccessful();

    expect(substr_count($response->content(), 'Bài featured không trùng'))->toBe(1)
        ->and(substr_count($response->content(), route('community.show', $featured)))->toBe(1);
});

test('home page shows at most three featured community posts', function () {
    foreach (range(1, 4) as $index) {
        createCommunityPost([
            'title' => "Bài featured số {$index}",
            'slug' => "bai-featured-so-{$index}",
            'is_featured' => true,
            'published_at' => now()->subDays($index),
        ]);
    }

    $response = $this->get(route('home'))->assertSuccessful();

    expect(substr_count($response->content(), 'Nổi bật'))->toBe(3);

    $response->assertDontSee('Bài featured số 4');
});

test('home page fills remaining slots with latest non featured posts up to six total', function () {
    foreach (range(1, 2) as $index) {
        createCommunityPost([
            'title' => "Featured fill {$index}",
            'slug' => "featured-fill-{$index}",
            'is_featured' => true,
            'published_at' => now()->subDays($index),
        ]);
    }

    foreach (range(1, 5) as $index) {
        createCommunityPost([
            'title' => "Recent fill {$index}",
            'slug' => "recent-fill-{$index}",
            'published_at' => now()->subHours($index),
        ]);
    }

    $response = $this->get(route('home'))->assertSuccessful();

    expect(substr_count($response->content(), 'Featured fill'))->toBe(2)
        ->and(substr_count($response->content(), 'Recent fill'))->toBe(4);

    $response->assertDontSee('Recent fill 5');
});
