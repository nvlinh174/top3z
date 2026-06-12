<?php

use App\Enums\ArticleModerationStatus;
use App\Enums\ArticleReactionType;
use App\Enums\GeneralStatus;
use App\Models\ArticleReaction;
use App\Models\User;

test('community index lists published community posts', function () {
    $post = createCommunityPost([
        'title' => 'Kệ mini đầu tay',
        'slug' => 'ke-mini-dau-tay',
    ]);

    $this->get(route('community.index'))
        ->assertSuccessful()
        ->assertSee('Cộng đồng')
        ->assertSee('Kệ mini đầu tay');
});

test('community show displays post detail', function () {
    $post = createCommunityPost([
        'title' => 'Chi tiết bài cộng đồng',
        'slug' => 'chi-tiet-bai-cong-dong',
        'excerpt' => 'Đoạn tóm tắt ngắn.',
    ]);

    $this->get(route('community.show', $post))
        ->assertSuccessful()
        ->assertSee('Chi tiết bài cộng đồng')
        ->assertSee('Đoạn tóm tắt ngắn.');
});

test('community show returns 404 for workshop announcement', function () {
    $workshop = createWorkshopArticle(['slug' => 'workshop-khong-phai-bai']);

    $this->get(route('community.show', $workshop))->assertNotFound();
});

test('pending community post is hidden from index', function () {
    createCommunityPost([
        'title' => 'Bài chờ duyệt',
        'slug' => 'bai-cho-duyet-index',
        'moderation_status' => ArticleModerationStatus::Pending,
        'published_at' => null,
    ]);

    $this->get(route('community.index'))
        ->assertSuccessful()
        ->assertDontSee('Bài chờ duyệt');
});

test('unpublished community post is hidden from index', function () {
    createCommunityPost([
        'title' => 'Bài ẩn',
        'status' => GeneralStatus::INACTIVE,
    ]);

    $this->get(route('community.index'))
        ->assertSuccessful()
        ->assertDontSee('Bài ẩn');
});

test('home page shows recent community posts', function () {
    createCommunityPost([
        'title' => 'Bài mới trên trang chủ',
        'published_at' => now()->subHour(),
    ]);

    $this->get(route('home'))
        ->assertSuccessful()
        ->assertSee('Bài mới trên trang chủ')
        ->assertSee('Từ cộng đồng');
});

test('community index filters by category slug', function () {
    $categoryA = createWorkshopCategory();
    $categoryB = createWorkshopCategory();

    createCommunityPost([
        'title' => 'Bài danh mục A',
        'category_id' => $categoryA->getKey(),
        'slug' => 'bai-danh-muc-a',
    ]);

    createCommunityPost([
        'title' => 'Bài danh mục B',
        'category_id' => $categoryB->getKey(),
        'slug' => 'bai-danh-muc-b',
    ]);

    $this->get(route('community.index', ['category' => $categoryA->slug]))
        ->assertSuccessful()
        ->assertSee('Bài danh mục A')
        ->assertDontSee('Bài danh mục B');
});

test('workshop show still returns 404 for community article type', function () {
    $post = createCommunityPost(['slug' => 'bai-khong-phai-workshop']);

    $this->get(route('workshops.show', $post))->assertNotFound();
});

test('community index searches posts by title', function () {
    createCommunityPost([
        'title' => 'Kệ gỗ tự làm',
        'slug' => 'ke-go-tu-lam',
    ]);

    createCommunityPost([
        'title' => 'Đèn LED mini',
        'slug' => 'den-led-mini',
    ]);

    $this->get(route('community.index', ['q' => 'Kệ gỗ']))
        ->assertSuccessful()
        ->assertSee('Kệ gỗ tự làm')
        ->assertDontSee('Đèn LED mini')
        ->assertSee('Kết quả cho');
});

test('community index searches posts by excerpt', function () {
    createCommunityPost([
        'title' => 'Bài A',
        'slug' => 'bai-a-search-excerpt',
        'excerpt' => 'Hướng dẫn lắp ráp chi tiết',
    ]);

    createCommunityPost([
        'title' => 'Bài B',
        'slug' => 'bai-b-search-excerpt',
        'excerpt' => 'Không liên quan',
    ]);

    $this->get(route('community.index', ['q' => 'lắp ráp']))
        ->assertSuccessful()
        ->assertSee('Bài A')
        ->assertDontSee('Bài B');
});

test('community index sorts posts by views count', function () {
    createCommunityPost([
        'title' => 'Ít lượt xem',
        'slug' => 'it-luot-xem',
        'views_count' => 5,
        'published_at' => now()->subDay(),
    ]);

    createCommunityPost([
        'title' => 'Nhiều lượt xem',
        'slug' => 'nhieu-luot-xem',
        'views_count' => 120,
        'published_at' => now()->subDays(2),
    ]);

    $response = $this->get(route('community.index', ['sort' => 'views']))
        ->assertSuccessful();

    expect($response->content())->toContain('Nhiều lượt xem')
        ->and(strpos($response->content(), 'Nhiều lượt xem'))->toBeLessThan(strpos($response->content(), 'Ít lượt xem'));
});

test('community index sorts posts by likes count', function () {
    $user = User::factory()->create();

    $popular = createCommunityPost([
        'title' => 'Nhiều lượt thích',
        'slug' => 'nhieu-luot-thich',
    ]);

    $quiet = createCommunityPost([
        'title' => 'Ít lượt thích',
        'slug' => 'it-luot-thich',
    ]);

    ArticleReaction::query()->create([
        'article_id' => $popular->getKey(),
        'user_id' => $user->getKey(),
        'session_token' => hash('sha256', 'sort-likes-test'),
        'type' => ArticleReactionType::Like,
    ]);

    $response = $this->get(route('community.index', ['sort' => 'likes']))
        ->assertSuccessful();

    expect(strpos($response->content(), 'Nhiều lượt thích'))->toBeLessThan(strpos($response->content(), 'Ít lượt thích'));
});

test('community index combines category filter with search query', function () {
    $categoryA = createWorkshopCategory();
    $categoryB = createWorkshopCategory();

    createCommunityPost([
        'title' => 'Tủ gỗ trong danh mục A',
        'category_id' => $categoryA->getKey(),
        'slug' => 'tu-go-danh-muc-a',
    ]);

    createCommunityPost([
        'title' => 'Tủ gỗ trong danh mục B',
        'category_id' => $categoryB->getKey(),
        'slug' => 'tu-go-danh-muc-b',
    ]);

    $this->get(route('community.index', [
        'category' => $categoryA->slug,
        'q' => 'Tủ gỗ',
    ]))
        ->assertSuccessful()
        ->assertSee('Tủ gỗ trong danh mục A')
        ->assertDontSee('Tủ gỗ trong danh mục B');
});

test('community index shows empty state when search has no results', function () {
    createCommunityPost([
        'title' => 'Bài có sẵn',
        'slug' => 'bai-co-san',
    ]);

    $this->get(route('community.index', ['q' => 'không-tồn-tại-xyz']))
        ->assertSuccessful()
        ->assertSee('Không tìm thấy bài phù hợp')
        ->assertDontSee('Bài có sẵn');
});
