<?php

use App\Enums\ArticleModerationStatus;
use App\Models\Article;
use App\Models\User;
use App\Support\CommunityPostDraft;

test('community create form includes database draft autosave support', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('community.create'))
        ->assertSuccessful()
        ->assertSee('Lưu nháp tự động vào tài khoản', false)
        ->assertSee('data-draft-enabled', false)
        ->assertSee(route('community.drafts.store'), false);
});

test('community edit form enables draft autosave for draft posts only', function () {
    $user = User::factory()->create();

    $draft = createCommunityPost([
        'author_id' => $user->id,
        'slug' => 'ban-nhap-thu',
        'title' => CommunityPostDraft::PLACEHOLDER_TITLE,
        'moderation_status' => ArticleModerationStatus::Draft,
        'published_at' => null,
        'submitted_at' => null,
        'body' => '<p>Nội dung nháp</p>',
    ]);

    $published = createCommunityPost([
        'author_id' => $user->id,
        'slug' => 'bai-da-dang-khong-nhap',
    ]);

    $this->actingAs($user)
        ->get(route('community.edit', $draft))
        ->assertSuccessful()
        ->assertSee('Lưu nháp tự động vào tài khoản', false)
        ->assertSee(route('community.drafts.autosave', $draft), false);

    $this->actingAs($user)
        ->get(route('community.edit', $published))
        ->assertSuccessful()
        ->assertDontSee('Lưu nháp tự động vào tài khoản', false);
});

test('autosave creates draft article in database', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->postJson(route('community.drafts.store'), [
            'title' => 'Bài nháp mới',
            'excerpt' => 'Tóm tắt nháp',
            'body' => '<p>Nội dung nháp</p>',
        ])
        ->assertSuccessful()
        ->assertJsonStructure(['id', 'slug', 'saved_at', 'autosave_url', 'edit_url', 'destroy_url']);

    $post = Article::query()->findOrFail($response->json('id'));

    expect($post->author_id)->toBe($user->id)
        ->and($post->moderation_status)->toBe(ArticleModerationStatus::Draft)
        ->and($post->submitted_at)->toBeNull()
        ->and($post->published_at)->toBeNull()
        ->and($post->title)->toBe('Bài nháp mới');
});

test('autosave updates existing draft article', function () {
    $user = User::factory()->create();

    $draft = createCommunityPost([
        'author_id' => $user->id,
        'slug' => 'ban-nhap-cap-nhat',
        'title' => CommunityPostDraft::PLACEHOLDER_TITLE,
        'moderation_status' => ArticleModerationStatus::Draft,
        'published_at' => null,
        'submitted_at' => null,
        'body' => '<p>Cũ</p>',
    ]);

    $this->actingAs($user)
        ->patchJson(route('community.drafts.autosave', $draft), [
            'title' => 'Tiêu đề đã cập nhật',
            'body' => '<p>Nội dung mới</p>',
        ])
        ->assertSuccessful();

    $draft->refresh();

    expect($draft->title)->toBe('Tiêu đề đã cập nhật')
        ->and($draft->body)->toContain('Nội dung mới')
        ->and($draft->moderation_status)->toBe(ArticleModerationStatus::Draft);
});

test('draft posts are hidden from public community index', function () {
    $user = User::factory()->create();

    createCommunityPost([
        'author_id' => $user->id,
        'title' => 'Bài nháp ẩn',
        'slug' => 'bai-nhap-an',
        'moderation_status' => ArticleModerationStatus::Draft,
        'published_at' => null,
        'submitted_at' => null,
    ]);

    $this->get(route('community.index'))
        ->assertSuccessful()
        ->assertDontSee('Bài nháp ẩn');
});

test('guest cannot view draft community post', function () {
    $user = User::factory()->create();
    $draft = createCommunityPost([
        'author_id' => $user->id,
        'slug' => 'ban-nhap-rieng-tu',
        'moderation_status' => ArticleModerationStatus::Draft,
        'published_at' => null,
        'submitted_at' => null,
    ]);

    $this->get(route('community.show', $draft))->assertNotFound();
});

test('author can view their draft community post', function () {
    $user = User::factory()->create();
    $draft = createCommunityPost([
        'author_id' => $user->id,
        'title' => 'Nháp của tôi',
        'slug' => 'nhap-cua-toi',
        'moderation_status' => ArticleModerationStatus::Draft,
        'published_at' => null,
        'submitted_at' => null,
    ]);

    $this->actingAs($user)
        ->get(route('community.show', $draft))
        ->assertSuccessful()
        ->assertSee('Nháp của tôi');
});

test('submitting draft publishes post for moderation', function () {
    $user = User::factory()->create();
    $draft = createCommunityPost([
        'author_id' => $user->id,
        'slug' => 'ban-nhap-gui-duyet',
        'title' => CommunityPostDraft::PLACEHOLDER_TITLE,
        'moderation_status' => ArticleModerationStatus::Draft,
        'published_at' => null,
        'submitted_at' => null,
        'body' => '<p>Sắp gửi</p>',
    ]);

    $this->actingAs($user)
        ->patch(route('community.update', $draft), [
            'title' => 'Bài gửi từ nháp',
            'excerpt' => 'Tóm tắt',
            'body' => '<p>Nội dung gửi duyệt.</p>',
        ])
        ->assertRedirect(route('community.my-posts', ['tab' => 'pending']));

    $draft->refresh();

    expect($draft->moderation_status)->toBe(ArticleModerationStatus::Pending)
        ->and($draft->submitted_at)->not->toBeNull()
        ->and($draft->title)->toBe('Bài gửi từ nháp');
});

test('author can delete draft via api', function () {
    $user = User::factory()->create();
    $draft = createCommunityPost([
        'author_id' => $user->id,
        'slug' => 'ban-nhap-xoa',
        'moderation_status' => ArticleModerationStatus::Draft,
        'published_at' => null,
        'submitted_at' => null,
    ]);

    $this->actingAs($user)
        ->deleteJson(route('community.drafts.destroy', $draft))
        ->assertSuccessful();

    expect(Article::withTrashed()->find($draft->getKey())?->trashed())->toBeTrue();
});

test('my posts page includes drafts tab', function () {
    $user = User::factory()->create();

    createCommunityPost([
        'author_id' => $user->id,
        'title' => 'Nháp trong danh sách',
        'slug' => 'nhap-trong-danh-sach',
        'moderation_status' => ArticleModerationStatus::Draft,
        'published_at' => null,
        'submitted_at' => null,
    ]);

    $this->actingAs($user)
        ->get(route('community.my-posts', ['tab' => 'drafts']))
        ->assertSuccessful()
        ->assertSee('Bản nháp', false)
        ->assertSee('Nháp trong danh sách')
        ->assertSee('Tiếp tục soạn', false);
});

test('autosave rejects empty draft payload', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson(route('community.drafts.store'), [
            'title' => '',
            'excerpt' => '',
            'body' => '<p><br></p>',
        ])
        ->assertUnprocessable();
});
