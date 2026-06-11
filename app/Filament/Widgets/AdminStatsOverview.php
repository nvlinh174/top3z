<?php

namespace App\Filament\Widgets;

use App\Enums\ArticleModerationStatus;
use App\Filament\Resources\Articles\ArticleResource;
use App\Models\Article;
use App\Models\ArticleInterest;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AdminStatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected ?string $pollingInterval = null;

    protected static bool $isLazy = false;

    protected ?string $heading = 'Tổng quan';

    protected function getStats(): array
    {
        $since = now()->subDays(7);

        $pendingCount = Article::query()
            ->communityPosts()
            ->moderationPending()
            ->count();

        return [
            Stat::make('Chờ duyệt', (string) $pendingCount)
                ->description('Bài cộng đồng')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning')
                ->url(ArticleResource::getUrl('index', [
                    'tableFilters' => [
                        'moderation_status' => [
                            'value' => (string) ArticleModerationStatus::Pending->value,
                        ],
                    ],
                ])),
            Stat::make('Thành viên mới', (string) User::query()
                ->where('is_admin', false)
                ->where('created_at', '>=', $since)
                ->count())
                ->description('7 ngày qua')
                ->descriptionIcon('heroicon-m-user-plus')
                ->color('success'),
            Stat::make('Bài đã đăng', (string) Article::query()
                ->communityPosts()
                ->moderationApproved()
                ->where('published_at', '>=', $since)
                ->count())
                ->description('7 ngày qua')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('info'),
            Stat::make('Quan tâm workshop', (string) ArticleInterest::query()
                ->where('created_at', '>=', $since)
                ->count())
                ->description('7 ngày qua')
                ->descriptionIcon('heroicon-m-hand-thumb-up')
                ->color('gray'),
            Stat::make('Tổng lượt xem', (string) number_format((int) Article::query()
                ->communityPosts()
                ->moderationApproved()
                ->sum('views_count')))
                ->description('Bài cộng đồng')
                ->descriptionIcon('heroicon-m-eye')
                ->color('info'),
        ];
    }
}
