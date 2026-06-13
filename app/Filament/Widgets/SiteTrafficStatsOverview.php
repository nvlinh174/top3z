<?php

namespace App\Filament\Widgets;

use App\Support\ActivityTracker;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SiteTrafficStatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 10;

    protected ?string $pollingInterval = null;

    protected int|string|array $columnSpan = 'full';

    protected ?string $heading = 'Lưu lượng website';

    protected ?string $description = 'Khách truy cập và lượt xem trang public';

    protected function getStats(): array
    {
        $todayStart = now()->startOfDay();
        $todayEnd = now()->endOfDay();
        $weekStart = now()->subDays(6)->startOfDay();

        $today = ActivityTracker::summaryForPeriod($todayStart, $todayEnd);
        $week = ActivityTracker::summaryForPeriod($weekStart, $todayEnd);

        return [
            Stat::make('Khách hôm nay', (string) $today['unique_visitors'])
                ->description('Phiên duy nhất')
                ->descriptionIcon('heroicon-m-users')
                ->color('success'),
            Stat::make('Lượt xem hôm nay', (string) $today['page_views'])
                ->description('Trang public')
                ->descriptionIcon('heroicon-m-eye')
                ->color('info'),
            Stat::make('Khách 7 ngày', (string) $week['unique_visitors'])
                ->description('Phiên duy nhất')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('success'),
            Stat::make('Lượt xem 7 ngày', (string) $week['page_views'])
                ->description('Trang public')
                ->descriptionIcon('heroicon-m-eye')
                ->color('info'),
            Stat::make('APK hôm nay', (string) $today['android_page_views'])
                ->description('Lượt xem từ app')
                ->descriptionIcon('heroicon-m-device-phone-mobile')
                ->color('warning'),
            Stat::make('Tìm kiếm 7 ngày', (string) $week['searches'])
                ->description('Community feed')
                ->descriptionIcon('heroicon-m-magnifying-glass')
                ->color('gray'),
        ];
    }
}
