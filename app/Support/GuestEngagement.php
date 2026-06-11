<?php

namespace App\Support;

use App\Models\Comment;
use App\Models\User;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;

class GuestEngagement
{
    private const SESSION_KEY = 'guest_engagement_token';

    private const COOKIE_NAME = 'top3z_guest_token';

    private const COOKIE_MINUTES = 60 * 24 * 365;

    public static function sessionToken(): string
    {
        $cookieToken = request()->cookie(self::COOKIE_NAME);

        if (self::isValidToken($cookieToken)) {
            self::rememberToken($cookieToken);

            return $cookieToken;
        }

        $sessionToken = session(self::SESSION_KEY);

        if (self::isValidToken($sessionToken)) {
            self::queueGuestTokenCookie($sessionToken);

            return $sessionToken;
        }

        $token = hash('sha256', Str::random(40).'|'.config('app.key'));

        self::rememberToken($token);
        self::queueGuestTokenCookie($token);

        return $token;
    }

    public static function ipHash(): ?string
    {
        $ip = request()->ip();

        if ($ip === null) {
            return null;
        }

        return hash('sha256', $ip.'|'.config('app.key'));
    }

    public static function mergeActivityForUser(User $user): void
    {
        Comment::query()
            ->whereNull('user_id')
            ->where('guest_email', $user->email)
            ->update([
                'user_id' => $user->id,
                'guest_name' => null,
                'guest_email' => null,
            ]);
    }

    public static function rotateToken(): string
    {
        $token = hash('sha256', Str::random(40).'|'.config('app.key'));

        self::rememberToken($token);
        self::queueGuestTokenCookie($token);

        return $token;
    }

    private static function rememberToken(string $token): void
    {
        if (session(self::SESSION_KEY) !== $token) {
            session([self::SESSION_KEY => $token]);
        }
    }

    private static function isValidToken(mixed $token): bool
    {
        return is_string($token) && strlen($token) === 64;
    }

    private static function queueGuestTokenCookie(string $token): void
    {
        if (request()->cookie(self::COOKIE_NAME) === $token) {
            return;
        }

        Cookie::queue(self::guestTokenCookie($token));
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Cookie
     */
    private static function guestTokenCookie(string $token)
    {
        return cookie(
            name: self::COOKIE_NAME,
            value: $token,
            minutes: self::COOKIE_MINUTES,
            path: config('session.path', '/'),
            domain: config('session.domain'),
            secure: config('session.secure'),
            httpOnly: true,
            raw: false,
            sameSite: config('session.same_site', 'lax'),
        );
    }
}
