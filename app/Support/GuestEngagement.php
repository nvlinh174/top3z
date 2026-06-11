<?php

namespace App\Support;

class GuestEngagement
{
    private const SESSION_KEY = 'guest_engagement_token';

    public static function sessionToken(): string
    {
        if (! session()->has(self::SESSION_KEY)) {
            session([
                self::SESSION_KEY => hash('sha256', session()->getId().'|'.config('app.key')),
            ]);
        }

        return (string) session(self::SESSION_KEY);
    }

    public static function ipHash(): ?string
    {
        $ip = request()->ip();

        if ($ip === null) {
            return null;
        }

        return hash('sha256', $ip.'|'.config('app.key'));
    }
}
