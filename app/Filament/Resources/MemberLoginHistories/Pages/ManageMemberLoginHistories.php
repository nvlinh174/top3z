<?php

namespace App\Filament\Resources\MemberLoginHistories\Pages;

use App\Filament\Resources\MemberLoginHistories\MemberLoginHistoryResource;
use Filament\Resources\Pages\ManageRecords;

class ManageMemberLoginHistories extends ManageRecords
{
    protected static string $resource = MemberLoginHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
