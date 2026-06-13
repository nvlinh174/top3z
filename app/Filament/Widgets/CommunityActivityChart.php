<?php

namespace App\Filament\Widgets;

use App\Support\ActivityTracker;
use Filament\Widgets\ChartWidget;

class CommunityActivityChart extends ChartWidget
{
    protected static ?int $sort = 70;

    protected int|string|array $columnSpan = 'full';

    protected ?string $heading = 'Tăng trưởng cộng đồng 30 ngày';

    protected ?string $description = 'Bài đăng mới và thành viên đăng ký theo ngày';

    protected ?string $pollingInterval = null;

    protected function getData(): array
    {
        $series = ActivityTracker::communityGrowthSeries();

        return [
            'datasets' => [
                [
                    'label' => 'Bài đã đăng',
                    'data' => $series->pluck('published_posts')->all(),
                    'borderColor' => '#f59e0b',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.1)',
                ],
                [
                    'label' => 'Thành viên mới',
                    'data' => $series->pluck('new_members')->all(),
                    'borderColor' => '#22c55e',
                    'backgroundColor' => 'rgba(34, 197, 94, 0.1)',
                ],
            ],
            'labels' => $series->pluck('label')->all(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
