<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\Schemas\UserForm;
use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Schema;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    public function form(Schema $schema): Schema
    {
        return UserForm::configureForCreate($schema);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['email_verified_at'] = now();
        $data['is_admin'] = (bool) ($data['is_admin'] ?? false);
        $data['password'] = config('auth.default_member_password');

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return UserResource::getUrl('index');
    }
}
