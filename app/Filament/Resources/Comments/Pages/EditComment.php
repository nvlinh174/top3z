<?php

namespace App\Filament\Resources\Comments\Pages;

use App\Enums\CommentStatus;
use App\Filament\Resources\Comments\CommentResource;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Schema;

class EditComment extends EditRecord
{
    protected static string $resource = CommentResource::class;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('article_title')
                    ->label('Workshop')
                    ->disabled()
                    ->dehydrated(false)
                    ->formatStateUsing(fn ($record): ?string => $record?->article?->title),
                TextInput::make('guest_name')
                    ->label('Tên khách')
                    ->maxLength(100),
                TextInput::make('guest_email')
                    ->label('Email khách')
                    ->email()
                    ->maxLength(255),
                Textarea::make('body')
                    ->label('Nội dung')
                    ->required()
                    ->rows(6)
                    ->columnSpanFull(),
                Select::make('status')
                    ->label('Trạng thái')
                    ->options(collect(CommentStatus::cases())->mapWithKeys(fn (CommentStatus $case): array => [$case->value => $case->label()]))
                    ->required()
                    ->native(false),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
