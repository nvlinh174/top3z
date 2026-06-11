<?php

use App\Enums\ArticleReactionType;
use App\Filament\Widgets\TopReactedCommunityPostsTable;
use App\Filament\Widgets\TopViewedCommunityPostsTable;
use App\Models\ArticleReaction;
use App\Models\User;
use Livewire\Livewire;

test('admin dashboard shows top viewed community posts widget', function () {
    $admin = User::factory()->admin()->create();

    createCommunityPost([
        'title' => 'Bài ít xem',
        'slug' => 'bai-it-xem',
        'views_count' => 3,
    ]);

    createCommunityPost([
        'title' => 'Bài nhiều xem nhất',
        'slug' => 'bai-nhieu-xem',
        'views_count' => 99,
    ]);

    $this->actingAs($admin)
        ->get('/admin')
        ->assertSuccessful()
        ->assertSee('Top 10 lượt xem')
        ->assertSee('Bài nhiều xem nhất')
        ->assertSee('99');
});

test('top viewed community posts widget orders by views count', function () {
    $admin = User::factory()->admin()->create();

    $low = createCommunityPost([
        'title' => 'Thấp',
        'slug' => 'top-view-thap',
        'views_count' => 1,
    ]);

    $high = createCommunityPost([
        'title' => 'Cao',
        'slug' => 'top-view-cao',
        'views_count' => 50,
    ]);

    $this->actingAs($admin);

    Livewire::test(TopViewedCommunityPostsTable::class)
        ->assertCanSeeTableRecords([$high, $low], inOrder: true);
});

test('admin dashboard shows top reacted community posts widget', function () {
    $admin = User::factory()->admin()->create();
    $member = User::factory()->create();

    $popular = createCommunityPost([
        'title' => 'Bài nhiều reaction',
        'slug' => 'bai-nhieu-reaction',
    ]);

    createCommunityPost([
        'title' => 'Bài ít reaction',
        'slug' => 'bai-it-reaction',
    ]);

    ArticleReaction::query()->create([
        'article_id' => $popular->getKey(),
        'user_id' => $member->getKey(),
        'type' => ArticleReactionType::Like,
    ]);

    ArticleReaction::query()->create([
        'article_id' => $popular->getKey(),
        'user_id' => $admin->getKey(),
        'type' => ArticleReactionType::Favorite,
    ]);

    $this->actingAs($admin)
        ->get('/admin')
        ->assertSuccessful()
        ->assertSee('Top 10 reaction')
        ->assertSee('Bài nhiều reaction');
});

test('top reacted community posts widget orders by total reactions', function () {
    $admin = User::factory()->admin()->create();
    $member = User::factory()->create();

    $less = createCommunityPost([
        'title' => 'Ít reaction',
        'slug' => 'top-react-it',
    ]);

    $more = createCommunityPost([
        'title' => 'Nhiều reaction',
        'slug' => 'top-react-nhieu',
    ]);

    ArticleReaction::query()->create([
        'article_id' => $more->getKey(),
        'user_id' => $member->getKey(),
        'type' => ArticleReactionType::Like,
    ]);

    ArticleReaction::query()->create([
        'article_id' => $more->getKey(),
        'user_id' => $admin->getKey(),
        'type' => ArticleReactionType::Favorite,
    ]);

    ArticleReaction::query()->create([
        'article_id' => $less->getKey(),
        'user_id' => $member->getKey(),
        'type' => ArticleReactionType::Like,
    ]);

    $this->actingAs($admin);

    Livewire::test(TopReactedCommunityPostsTable::class)
        ->assertCanSeeTableRecords([$more, $less], inOrder: true);
});
