<?php

namespace App\Filament\Resources\ArticleInterests\Pages;

use App\Filament\Resources\ArticleInterests\ArticleInterestResource;
use App\Models\ArticleInterest;
use Filament\Actions\Action;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ManageArticleInterests extends ManageRecords
{
    protected static string $resource = ArticleInterestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportCsv')
                ->label('Xuất CSV')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(fn (): StreamedResponse => $this->exportCsv()),
        ];
    }

    protected function exportCsv(): StreamedResponse
    {
        $filename = 'workshop-interests-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function (): void {
            $handle = fopen('php://output', 'w');

            if ($handle === false) {
                return;
            }

            fputcsv($handle, ['Workshop', 'Người', 'Loại', 'Email', 'Ngày']);

            $this->getExportQuery()
                ->orderBy('created_at')
                ->lazy()
                ->each(function (ArticleInterest $interest) use ($handle): void {
                    fputcsv($handle, [
                        $interest->article?->title ?? '',
                        $interest->user?->name ?? $interest->display_name ?? 'Khách',
                        $interest->user_id !== null ? 'Thành viên' : 'Khách',
                        $interest->user?->email ?? '',
                        $interest->created_at?->toDateTimeString() ?? '',
                    ]);
                });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * @return Builder<ArticleInterest>
     */
    protected function getExportQuery(): Builder
    {
        return ArticleInterestResource::getEloquentQuery()
            ->with(['article', 'user']);
    }
}
