<?php

namespace App\Filament\Widgets;

use App\Models\Article;
use App\Models\User;
use Filament\Widgets\ChartWidget;

class CommunityActivityChart extends ChartWidget
{
    protected static ?int $sort = 70;

    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 'full';

    protected ?string $heading = 'Tăng trưởng cộng đồng 30 ngày';

    protected ?string $description = 'Bài đăng mới và thành viên đăng ký theo ngày';

    protected ?string $pollingInterval = null;

    protected function getData(): array
    {
        $labels = [];
        $publishedPosts = [];
        $newMembers = [];

        for ($daysAgo = 29; $daysAgo >= 0; $daysAgo--) {
            $date = now()->subDays($daysAgo)->toDateString();
            $labels[] = now()->subDays($daysAgo)->format('d/m');

            $publishedPosts[] = Article::query()
                ->communityPosts()
                ->moderationApproved()
                ->whereDate('published_at', $date)
                ->count();

            $newMembers[] = User::query()
                ->where('is_admin', false)
                ->whereDate('created_at', $date)
                ->count();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Bài đã đăng',
                    'data' => $publishedPosts,
                    'borderColor' => '#f59e0b',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.1)',
                ],
                [
                    'label' => 'Thành viên mới',
                    'data' => $newMembers,
                    'borderColor' => '#22c55e',
                    'backgroundColor' => 'rgba(34, 197, 94, 0.1)',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
