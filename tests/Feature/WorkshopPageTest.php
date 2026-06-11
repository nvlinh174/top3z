<?php

use App\Enums\ArticleType;
use App\Enums\GeneralStatus;
use App\Models\Article;
use App\Models\Category;

function createWorkshopCategory(): Category
{
    $root = Category::query()->find(Category::SYSTEM_ROOT_ID);

    if ($root === null) {
        $root = new Category([
            'name' => 'System',
            'slug' => 'system',
            'status' => GeneralStatus::ACTIVE,
        ]);
        $root->saveAsRoot();
    }

    return Category::query()->create([
        'name' => 'Workshop',
        'slug' => 'workshop-'.uniqid(),
        'parent_id' => $root->getKey(),
        'status' => GeneralStatus::ACTIVE,
    ]);
}

function createWorkshopArticle(array $overrides = []): Article
{
    return Article::query()->create(array_merge([
        'type' => ArticleType::Announcement,
        'category_id' => createWorkshopCategory()->getKey(),
        'title' => 'Workshop thử nghiệm',
        'slug' => 'workshop-'.uniqid(),
        'body' => '<p>Nội dung workshop</p>',
        'status' => GeneralStatus::ACTIVE,
        'published_at' => now()->subDay(),
        'starts_at' => now()->addWeek(),
    ], $overrides));
}

test('workshops index lists upcoming workshops', function () {
    $workshop = createWorkshopArticle(['title' => 'Build kệ mini']);

    $response = $this->get(route('workshops.index'));

    $response
        ->assertSuccessful()
        ->assertSee('Lịch workshop')
        ->assertSee('Build kệ mini');
});

test('workshop show displays article detail and schedule', function () {
    $startsAt = now()->addWeek()->setTime(19, 0);
    $endsAt = $startsAt->copy()->addHours(2);

    $workshop = createWorkshopArticle([
        'title' => 'Workshop chi tiết',
        'slug' => 'workshop-chi-tiet',
        'starts_at' => $startsAt,
        'ends_at' => $endsAt,
    ]);

    $this->get(route('workshops.show', $workshop))
        ->assertSuccessful()
        ->assertSee('Workshop chi tiết')
        ->assertSee($startsAt->format('d/m/Y H:i'))
        ->assertSee($endsAt->format('H:i'));
});

test('workshop show returns 404 for article type', function () {
    $category = createWorkshopCategory();

    $article = Article::query()->create([
        'type' => ArticleType::Article,
        'category_id' => $category->getKey(),
        'title' => 'Bài viết thường',
        'slug' => 'bai-viet-thuong',
        'body' => '<p>Nội dung</p>',
        'status' => GeneralStatus::ACTIVE,
        'published_at' => now(),
    ]);

    $this->get(route('workshops.show', $article))->assertNotFound();
});

test('unpublished workshop is hidden from public list', function () {
    createWorkshopArticle([
        'title' => 'Workshop ẩn',
        'status' => GeneralStatus::INACTIVE,
    ]);

    $this->get(route('workshops.index'))
        ->assertSuccessful()
        ->assertDontSee('Workshop ẩn');
});

test('home page shows featured upcoming workshop', function () {
    createWorkshopArticle(['title' => 'Workshop trên trang chủ']);

    $this->get(route('home'))
        ->assertSuccessful()
        ->assertSee('Workshop trên trang chủ');
});
