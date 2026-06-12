<?php

use App\Enums\ArticleReactionType;
use App\Enums\CommentStatus;
use App\Filament\Resources\ArticleInterests\ArticleInterestResource;
use App\Filament\Resources\ArticleReactions\ArticleReactionResource;
use App\Filament\Resources\ArticleReactions\Pages\ManageArticleReactions;
use App\Filament\Resources\CommentReactions\CommentReactionResource;
use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\UserResource;
use App\Models\ArticleInterest;
use App\Models\ArticleReaction;
use App\Models\Comment;
use App\Models\CommentReaction;
use App\Models\User;
use Livewire\Livewire;

test('admin can list members resource', function () {
    $admin = User::factory()->admin()->create();
    $member = User::factory()->create(['name' => 'Nguyễn Văn A']);

    $this->actingAs($admin)
        ->get(UserResource::getUrl('index'))
        ->assertSuccessful()
        ->assertSee('Nguyễn Văn A');
});

test('admin can create member account with temporary password', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin);

    Livewire::test(CreateUser::class)
        ->fillForm([
            'name' => 'Khách workshop',
            'email' => 'workshop-guest@example.com',
            'is_admin' => false,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $member = User::query()->where('email', 'workshop-guest@example.com')->first();

    expect($member)->not->toBeNull()
        ->and($member->name)->toBe('Khách workshop')
        ->and($member->is_admin)->toBeFalse()
        ->and($member->email_verified_at)->not->toBeNull();

    $this->assertCredentials([
        'email' => 'workshop-guest@example.com',
        'password' => config('auth.default_member_password'),
    ]);
});

test('admin can edit member name and admin flag', function () {
    $admin = User::factory()->admin()->create();
    $member = User::factory()->create(['name' => 'Tên cũ']);

    $this->actingAs($admin);

    Livewire::test(EditUser::class, ['record' => $member->getRouteKey()])
        ->fillForm([
            'name' => 'Tên mới',
            'is_admin' => true,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $member->refresh();

    expect($member->name)->toBe('Tên mới')
        ->and($member->is_admin)->toBeTrue();
});

test('admin cannot edit admin accounts', function () {
    $admin = User::factory()->admin()->create();
    $otherAdmin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(UserResource::getUrl('edit', ['record' => $otherAdmin]))
        ->assertForbidden();

    $this->actingAs($admin)
        ->get(UserResource::getUrl('edit', ['record' => $admin]))
        ->assertForbidden();
});

test('members cannot access filament user resource', function () {
    $member = User::factory()->create();

    $this->actingAs($member)
        ->get(UserResource::getUrl('index'))
        ->assertForbidden();
});

test('admin can list workshop interests', function () {
    $admin = User::factory()->admin()->create();
    $workshop = createWorkshopArticle(['title' => 'Workshop quan tâm test']);

    ArticleInterest::query()->create([
        'article_id' => $workshop->getKey(),
        'display_name' => 'Khách ABC',
        'session_token' => hash('sha256', 'guest-interest-test'),
    ]);

    $this->actingAs($admin)
        ->get(ArticleInterestResource::getUrl('index'))
        ->assertSuccessful()
        ->assertSee('Workshop quan tâm test')
        ->assertSee('Khách ABC')
        ->assertSee('Khách');
});

test('admin can list article reactions with guest badge', function () {
    $admin = User::factory()->admin()->create();
    $post = createCommunityPost(['title' => 'Bài reaction admin test']);

    ArticleReaction::query()->create([
        'article_id' => $post->getKey(),
        'user_id' => null,
        'session_token' => hash('sha256', 'guest-article-reaction-admin'),
        'type' => ArticleReactionType::Like,
    ]);

    $this->actingAs($admin)
        ->get(ArticleReactionResource::getUrl('index'))
        ->assertSuccessful()
        ->assertSee('Bài reaction admin test')
        ->assertSee('Khách');
});

test('admin can list comment reactions', function () {
    $admin = User::factory()->admin()->create();
    $member = User::factory()->create(['name' => 'Commenter']);
    $liker = User::factory()->create(['name' => 'Liker']);
    $post = createCommunityPost(['title' => 'Bài có comment reaction']);

    $comment = Comment::query()->create([
        'article_id' => $post->getKey(),
        'user_id' => $member->getKey(),
        'body' => 'Bình luận để thích',
        'status' => CommentStatus::Active,
    ]);

    CommentReaction::query()->create([
        'comment_id' => $comment->getKey(),
        'user_id' => $liker->getKey(),
    ]);

    $this->actingAs($admin)
        ->get(CommentReactionResource::getUrl('index'))
        ->assertSuccessful()
        ->assertSee('Bài có comment reaction')
        ->assertSee('Commenter')
        ->assertSee('Liker');
});

test('admin can delete article reaction from resource', function () {
    $admin = User::factory()->admin()->create();
    $post = createCommunityPost();
    $reaction = ArticleReaction::query()->create([
        'article_id' => $post->getKey(),
        'user_id' => $admin->getKey(),
        'session_token' => hash('sha256', 'delete-reaction-test'),
        'type' => ArticleReactionType::Favorite,
    ]);

    $this->actingAs($admin);

    Livewire::test(ManageArticleReactions::class)
        ->callTableAction('delete', $reaction);

    expect(ArticleReaction::query()->find($reaction->getKey()))->toBeNull();
});
