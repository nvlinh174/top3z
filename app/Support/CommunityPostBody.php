<?php

namespace App\Support;

class CommunityPostBody
{
    /**
     * @var list<string>
     */
    private const ALLOWED_TAGS = [
        '<p>', '<br>', '<strong>', '<b>', '<em>', '<i>', '<u>', '<s>',
        '<h2>', '<h3>', '<ul>', '<ol>', '<li>', '<blockquote>', '<a>',
    ];

    public static function sanitize(string $html): string
    {
        $clean = strip_tags($html, implode('', self::ALLOWED_TAGS));

        return trim($clean);
    }

    public static function isEmpty(string $html): bool
    {
        $text = trim(strip_tags($html));

        return $text === '';
    }
}
