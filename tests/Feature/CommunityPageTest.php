<?php

use App\Enums\GeneralStatus;

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
