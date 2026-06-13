<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\AdminStatsOverview;
use App\Filament\Widgets\CommunityActivityChart;
use App\Filament\Widgets\PendingCommunityPostsTable;
use App\Filament\Widgets\RecentMembersTable;
use App\Filament\Widgets\SiteEngagementChart;
use App\Filament\Widgets\SiteTrafficChart;
use App\Filament\Widgets\SiteTrafficStatsOverview;
use App\Filament\Widgets\TopPagesTable;
use App\Filament\Widgets\TopReactedCommunityPostsTable;
use App\Filament\Widgets\TopRecentPostViewsTable;
use App\Filament\Widgets\TopViewedCommunityPostsTable;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $title = 'Tổng quan';

    /**
     * @return int | array<string, ?int>
     */
    public function getColumns(): int|array
    {
        return [
            'default' => 1,
            'lg' => 2,
        ];
    }

    /**
     * @return array<class-string>
     */
    public function getWidgets(): array
    {
        return [
            SiteTrafficStatsOverview::class,
            SiteTrafficChart::class,
            TopPagesTable::class,
            TopRecentPostViewsTable::class,
            SiteEngagementChart::class,
            AdminStatsOverview::class,
            PendingCommunityPostsTable::class,
            RecentMembersTable::class,
            CommunityActivityChart::class,
            TopViewedCommunityPostsTable::class,
            TopReactedCommunityPostsTable::class,
        ];
    }
}
