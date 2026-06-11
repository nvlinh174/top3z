<?php

namespace App\Policies;

use App\Enums\ArticleType;
use App\Models\Article;
use App\Models\User;

class ArticlePolicy
{
    public function create(User $user): bool
    {
        return true;
    }

    public function view(?User $user, Article $article): bool
    {
        if ($article->type !== ArticleType::Article) {
            return false;
        }

        if ($article->isPublicCommunityPost()) {
            return true;
        }

        if ($user === null) {
            return false;
        }

        if ($user->is_admin) {
            return true;
        }

        return $article->author_id === $user->id;
    }

    public function update(User $user, Article $article): bool
    {
        if ($user->is_admin) {
            return true;
        }

        return $article->type === ArticleType::Article
            && $article->author_id === $user->id;
    }
}
