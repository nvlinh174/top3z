<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Tên hiển thị')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->label('Email')
                    ->disabled()
                    ->dehydrated(false),
                Toggle::make('is_admin')
                    ->label('Quản trị viên')
                    ->helperText('Quản trị viên có thể truy cập Filament /admin.'),
            ]);
    }

    public static function configureForCreate(Schema $schema): Schema
    {
        $defaultPassword = config('auth.default_member_password');

        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Tên hiển thị')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->label('Email đăng nhập')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique()
                    ->helperText('Gửi email và mật khẩu mặc định cho thành viên qua Zalo hoặc tin nhắn.'),
                Placeholder::make('default_password')
                    ->label('Mật khẩu đăng nhập')
                    ->content("Mặc định: {$defaultPassword} — thành viên đổi tại Tài khoản sau khi đăng nhập."),
                Toggle::make('is_admin')
                    ->label('Quản trị viên')
                    ->default(false)
                    ->helperText('Thường để tắt — chỉ bật khi cần cấp quyền admin.'),
            ]);
    }
}
