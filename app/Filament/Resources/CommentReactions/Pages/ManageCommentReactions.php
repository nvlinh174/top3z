<?php

namespace App\Filament\Resources\CommentReactions\Pages;

use App\Filament\Resources\CommentReactions\CommentReactionResource;
use Filament\Resources\Pages\ManageRecords;

class ManageCommentReactions extends ManageRecords
{
    protected static string $resource = CommentReactionResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
