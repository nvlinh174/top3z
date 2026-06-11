<?php

namespace App\Models;

use App\Enums\ArticleReactionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArticleReaction extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'article_id',
        'user_id',
        'session_token',
        'ip_hash',
        'type',
    ];

    protected function casts(): array
    {
        return [
            'type' => ArticleReactionType::class,
        ];
    }

    /**
     * @return BelongsTo<Article, $this>
     */
    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
