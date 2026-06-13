<?php

namespace App\Filament\Widgets;

use App\Support\ActivityTracker;
use Filament\Widgets\ChartWidget;

class SiteEngagementChart extends ChartWidget
{
    protected static ?int $sort = 40;

    protected int|string|array $columnSpan = 'full';

    protected ?string $heading = 'Tương tác 30 ngày';

    protected ?string $description = 'Quan tâm workshop, reaction, bình luận, tìm kiếm';

    protected ?string $pollingInterval = null;

    protected function getData(): array
    {
        $series = ActivityTracker::dailyEngagementSeries();

        return [
            'datasets' => [
                [
                    'label' => 'Quan tâm workshop',
                    'data' => $series->pluck('workshop_interests')->all(),
                    'borderColor' => '#f59e0b',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.1)',
                ],
                [
                    'label' => 'Reaction',
                    'data' => $series->pluck('reactions')->all(),
                    'borderColor' => '#ef4444',
                    'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
                ],
                [
                    'label' => 'Bình luận',
                    'data' => $series->pluck('comments')->all(),
                    'borderColor' => '#8b5cf6',
                    'backgroundColor' => 'rgba(139, 92, 246, 0.1)',
                ],
                [
                    'label' => 'Tìm kiếm',
                    'data' => $series->pluck('searches')->all(),
                    'borderColor' => '#64748b',
                    'backgroundColor' => 'rgba(100, 116, 139, 0.1)',
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
