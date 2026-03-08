<?php

namespace App\Support;

use Illuminate\Support\Str;

class MarkdownRenderer
{
    public static function render(?string $markdown): string
    {
        $markdown = trim((string) $markdown);

        if ($markdown === '') {
            return '<p class="empty-preview">Start writing to see the rendered preview.</p>';
        }

        return (string) Str::markdown($markdown, [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);
    }

    public static function excerpt(?string $markdown, int $limit = 180): string
    {
        $text = Str::of(strip_tags(self::render($markdown)))
            ->squish()
            ->trim();

        return $text->isEmpty() ? 'Untitled draft' : Str::limit($text->toString(), $limit);
    }

    public static function normalizeTags($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $tags = collect(explode(',', (string) $value))
            ->map(fn (string $tag) => Str::of($tag)->lower()->squish()->replace(' ', '-')->value())
            ->filter()
            ->unique()
            ->values();

        return $tags->isEmpty() ? null : $tags->implode(',');
    }
}
