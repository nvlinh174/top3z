<?php

namespace App\Filament\Widgets;

use App\Support\ActivityTracker;
use Filament\Widgets\ChartWidget;

class SiteTrafficChart extends ChartWidget
{
    protected static ?int $sort = 20;

    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 'full';

    protected ?string $heading = 'Lưu lượng 30 ngày';

    protected ?string $description = 'Lượt xem trang và phiên khách theo ngày';

    protected ?string $pollingInterval = null;

    protected function getData(): array
    {
        $series = ActivityTracker::dailyTrafficSeries();

        return [
            'datasets' => [
                [
                    'label' => 'Lượt xem trang',
                    'data' => $series->pluck('page_views')->all(),
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                ],
                [
                    'label' => 'Khách (phiên)',
                    'data' => $series->pluck('unique_visitors')->all(),
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
