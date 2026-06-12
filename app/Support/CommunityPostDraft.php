<?php

namespace App\Support;

use App\Enums\ArticleModerationStatus;
use App\Enums\ArticleType;
use App\Enums\GeneralStatus;
use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Str;

class CommunityPostDraft
{
    public const PLACEHOLDER_TITLE = 'Bản nháp';

    public static function isDraft(Article $article): bool
    {
        return $article->type === ArticleType::Article
            && $article->moderation_status === ArticleModerationStatus::Draft;
    }

    public static function displayTitle(?string $title): string
    {
        $trimmed = trim($title ?? '');

        if ($trimmed === '' || $trimmed === self::PLACEHOLDER_TITLE) {
            return 'Bản nháp không tiêu đề';
        }

        return $trimmed;
    }

    /**
     * @param  array{title?: string|null, excerpt?: string|null, body?: string|null}  $data
     */
    public static function hasSavableContent(array $data): bool
    {
        $title = trim($data['title'] ?? '');
        $excerpt = trim($data['excerpt'] ?? '');
        $body = $data['body'] ?? '';

        if ($title !== '' && $title !== self::PLACEHOLDER_TITLE) {
            return true;
        }

        if ($excerpt !== '') {
            return true;
        }

        return ! CommunityPostBody::isEmpty($body);
    }

    /**
     * @param  array{title?: string|null, excerpt?: string|null, body?: string|null}  $data
     */
    public static function createForUser(User $user, array $data): Article
    {
        $title = self::resolveTitle($data['title'] ?? null);

        return Article::query()->create([
            'type' => ArticleType::Article,
            'category_id' => Category::communityPostsCategory()->getKey(),
            'author_id' => $user->id,
            'title' => $title,
            'slug' => self::generateUniqueSlug($title),
            'excerpt' => $data['excerpt'] ?? null,
            'body' => CommunityPostBody::sanitize($data['body'] ?? ''),
            'status' => GeneralStatus::ACTIVE,
            'moderation_status' => ArticleModerationStatus::Draft,
            'moderation_note' => null,
            'submitted_at' => null,
            'published_at' => null,
        ]);
    }

    /**
     * @param  array{title?: string|null, excerpt?: string|null, body?: string|null}  $data
     */
    public static function updateDraft(Article $article, array $data): Article
    {
        abort_unless(self::isDraft($article), 404);

        $title = self::resolveTitle($data['title'] ?? $article->title);

        $article->fill([
            'title' => $title,
            'excerpt' => $data['excerpt'] ?? null,
            'body' => CommunityPostBody::sanitize($data['body'] ?? ''),
        ]);

        if ($article->isDirty('title')) {
            $article->slug = self::generateUniqueSlug($title, $article->getKey());
        }

        $article->save();

        return $article->refresh();
    }

    /**
     * @param  array{title: string, excerpt?: string|null, body: string}  $data
     */
    public static function publish(Article $article, array $data): Article
    {
        abort_unless(self::isDraft($article), 404);

        $article->update([
            'title' => $data['title'],
            'excerpt' => $data['excerpt'] ?? null,
            'body' => CommunityPostBody::sanitize($data['body']),
            'moderation_status' => ArticleModerationStatus::Pending,
            'moderation_note' => null,
            'submitted_at' => now(),
            'published_at' => null,
        ]);

        return $article->refresh();
    }

    private static function resolveTitle(?string $title): string
    {
        $trimmed = trim($title ?? '');

        return $trimmed === '' ? self::PLACEHOLDER_TITLE : $trimmed;
    }

    private static function generateUniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title);
        $base = $base !== '' ? $base : 'ban-nhap';
        $slug = $base;
        $suffix = 1;

        while (
            Article::query()
                ->where('slug', $slug)
                ->when($ignoreId !== null, fn ($query) => $query->whereKeyNot($ignoreId))
                ->exists()
        ) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }
}
