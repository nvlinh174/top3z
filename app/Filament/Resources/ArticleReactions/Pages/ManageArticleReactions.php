<?php

namespace App\Filament\Resources\ArticleReactions\Pages;

use App\Filament\Resources\ArticleReactions\ArticleReactionResource;
use Filament\Resources\Pages\ManageRecords;

class ManageArticleReactions extends ManageRecords
{
    protected static string $resource = ArticleReactionResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
