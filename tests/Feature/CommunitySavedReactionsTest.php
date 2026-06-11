<?php

use App\Enums\ArticleModerationStatus;
use App\Enums\ArticleReactionType;
use App\Models\ArticleReaction;
use App\Models\User;

test('guest cannot access saved community posts page', function () {
    $this->get(route('community.saved'))
        ->assertRedirect(route('login'));
});

test('authenticated user sees liked posts on default tab', function () {
    $user = User::factory()->create();
    $liked = createCommunityPost([
        'title' => 'Bài user đã thích',
        'slug' => 'bai-user-da-thich',
    ]);
    $other = createCommunityPost([
        'title' => 'Bài không thích',
        'slug' => 'bai-khong-thich',
    ]);

    ArticleReaction::query()->create([
        'article_id' => $liked->getKey(),
        'user_id' => $user->getKey(),
        'type' => ArticleReactionType::Like,
    ]);

    $this->actingAs($user)
        ->get(route('community.saved'))
        ->assertSuccessful()
        ->assertSee('Bài đã lưu')
        ->assertSee('Bài user đã thích')
        ->assertDontSee('Bài không thích');
});

test('favorited tab shows only favorited public posts', function () {
    $user = User::factory()->create();

    $favorited = createCommunityPost([
        'title' => 'Bài yêu thích',
        'slug' => 'bai-yeu-thich-saved',
    ]);

    $likedOnly = createCommunityPost([
        'title' => 'Chỉ thích thôi',
        'slug' => 'chi-thich-thoi',
    ]);

    ArticleReaction::query()->create([
        'article_id' => $favorited->getKey(),
        'user_id' => $user->getKey(),
        'type' => ArticleReactionType::Favorite,
    ]);

    ArticleReaction::query()->create([
        'article_id' => $likedOnly->getKey(),
        'user_id' => $user->getKey(),
        'type' => ArticleReactionType::Like,
    ]);

    $this->actingAs($user)
        ->get(route('community.saved', ['tab' => 'favorited']))
        ->assertSuccessful()
        ->assertSee('Bài yêu thích')
        ->assertDontSee('Chỉ thích thôi');
});

test('saved list hides posts that are no longer public', function () {
    $user = User::factory()->create();

    $hidden = createCommunityPost([
        'title' => 'Bài không còn public',
        'slug' => 'bai-khong-con-public',
        'moderation_status' => ArticleModerationStatus::Pending,
        'published_at' => null,
    ]);

    ArticleReaction::query()->create([
        'article_id' => $hidden->getKey(),
        'user_id' => $user->getKey(),
        'type' => ArticleReactionType::Like,
    ]);

    $this->actingAs($user)
        ->get(route('community.saved'))
        ->assertSuccessful()
        ->assertDontSee('Bài không còn public');
});

test('header shows saved link for authenticated users', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('community.index'))
        ->assertSuccessful()
        ->assertSee('Bài đã lưu');
});
