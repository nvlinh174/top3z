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

function createCommunityPost(array $overrides = []): Article
{
    return Article::query()->create(array_merge([
        'type' => ArticleType::Article,
        'category_id' => createWorkshopCategory()->getKey(),
        'title' => 'Bài cộng đồng thử nghiệm',
        'slug' => 'community-'.uniqid(),
        'excerpt' => 'Tóm tắt bài viết cộng đồng.',
        'body' => '<p>Nội dung bài viết cộng đồng</p>',
        'status' => GeneralStatus::ACTIVE,
        'published_at' => now()->subDay(),
    ], $overrides));
}
