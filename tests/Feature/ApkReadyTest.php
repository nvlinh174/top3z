<?php

use App\Models\User;

test('web manifest is served for apk and pwa shells', function () {
    $this->get(route('manifest'))
        ->assertSuccessful()
        ->assertHeader('content-type', 'application/manifest+json')
        ->assertJsonPath('display', 'standalone')
        ->assertJsonPath('start_url', '/')
        ->assertJsonPath('short_name', config('pwa.short_name'));
});

test('public layout includes pwa meta tags and manifest link', function () {
    $this->get(route('home'))
        ->assertSuccessful()
        ->assertSee('viewport-fit=cover', false)
        ->assertSee('name="theme-color"', false)
        ->assertSee('rel="manifest"', false)
        ->assertSee(asset('icon.svg'), false)
        ->assertSee(route('manifest'), false);
});

test('community create form includes mobile sticky submit bar', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('community.create'))
        ->assertSuccessful()
        ->assertSee('id="community-post-form"', false)
        ->assertSee('form="community-post-form"', false)
        ->assertSee('mobile-form-actions-spacer', false);
});

test('community show breadcrumb truncates long titles', function () {
    $post = createCommunityPost([
        'title' => 'Tiêu đề bài viết rất dài để kiểm tra truncate trên mobile breadcrumb',
        'slug' => 'bai-title-dai-breadcrumb',
    ]);

    $this->get(route('community.show', $post))
        ->assertSuccessful()
        ->assertSee('class="block truncate text-content-primary"', false);
});
