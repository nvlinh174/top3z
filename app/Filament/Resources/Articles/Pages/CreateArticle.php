<?php

namespace App\Filament\Resources\Articles\Pages;

use App\Enums\ArticleType;
use App\Enums\GeneralStatus;
use App\Filament\Resources\Articles\ArticleResource;
use Filament\Resources\Pages\CreateRecord;

class CreateArticle extends CreateRecord
{
    protected static string $resource = ArticleResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['author_id'] ??= auth()->id();
        $data['type'] ??= ArticleType::Announcement->value;
        $data['status'] ??= GeneralStatus::ACTIVE->value;

        return $data;
    }
}
