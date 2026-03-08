<?php

namespace App\Models;

use App\Support\MarkdownRenderer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Paste extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'rendered_content',
        'tags',
        'syntax',
        'password',
        'expires_at',
        'expiration_option',
        'published_title',
        'published_content',
        'published_rendered_content',
        'published_tags',
        'published_at',
        'last_autosaved_at',
        'slug',
        'manage_token',
        'status',
    ];

    protected $hidden = ['password', 'manage_token'];

    protected $casts = [
        'expires_at' => 'datetime',
        'published_at' => 'datetime',
        'last_autosaved_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Paste $paste): void {
            $paste->manage_token ??= self::generateManageToken();
            $paste->slug ??= self::generateSlug();
            $paste->status ??= 'draft';
            $paste->syntax ??= 'markdown';
            $paste->expiration_option ??= 'never';
            $paste->rendered_content ??= MarkdownRenderer::render($paste->content);
        });

        static::saving(function (Paste $paste): void {
            $paste->tags = MarkdownRenderer::normalizeTags($paste->tags);
        });
    }

    public static function generateSlug(): string
    {
        do {
            $slug = 'note-'.Str::lower(Str::random(6));
        } while (self::where('slug', $slug)->exists());

        return $slug;
    }

    public static function generateManageToken(): string
    {
        do {
            $token = Str::random(32);
        } while (self::where('manage_token', $token)->exists());

        return $token;
    }

    public function isProtected(): bool
    {
        return !empty($this->password);
    }

    public function checkPassword(string $password): bool
    {
        return $this->password && password_verify($password, $this->password);
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function isPublished(): bool
    {
        return $this->status === 'published' && !empty($this->published_content) && !$this->isExpired();
    }

    public function hasUnpublishedChanges(): bool
    {
        if ($this->status !== 'published') {
            return filled($this->content) || filled($this->title) || filled($this->tags);
        }

        return $this->title !== $this->published_title
            || $this->content !== $this->published_content
            || MarkdownRenderer::normalizeTags($this->tags) !== MarkdownRenderer::normalizeTags($this->published_tags)
            || $this->rendered_content !== $this->published_rendered_content;
    }

    public function getTagListAttribute(): array
    {
        if (blank($this->tags)) {
            return [];
        }

        return explode(',', $this->tags);
    }

    public function getPublishedTagListAttribute(): array
    {
        if (blank($this->published_tags)) {
            return [];
        }

        return explode(',', $this->published_tags);
    }

    public function excerpt(int $limit = 180): string
    {
        return MarkdownRenderer::excerpt($this->published_content ?? $this->content, $limit);
    }

    public static function generateSlugFromTitle(?string $title): string
    {
        $base = Str::slug((string) $title);
        $base = $base !== '' ? Str::limit($base, 48, '') : 'note';
        $slug = $base;
        $suffix = 2;

        while (self::where('slug', $slug)->exists()) {
            $slug = Str::limit($base, 42, '').'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }

    public function publish(): void
    {
        if (!$this->published_at && filled($this->title) && Str::startsWith($this->slug, 'note-')) {
            $this->slug = self::generateSlugFromTitle($this->title);
        }

        $this->published_title = $this->title;
        $this->published_content = $this->content;
        $this->published_rendered_content = $this->rendered_content ?: MarkdownRenderer::render($this->content);
        $this->published_tags = MarkdownRenderer::normalizeTags($this->tags);
        $this->published_at = now();
        $this->status = 'published';
        $this->expires_at = $this->resolveExpirationTimestamp();
        $this->save();
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status', 'published')
            ->whereNotNull('published_content')
            ->where(function (Builder $builder): void {
                $builder->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    public function scopeSearchPublished(Builder $query, ?string $search): Builder
    {
        if (blank($search)) {
            return $query;
        }

        $like = '%'.$search.'%';

        return $query->where(function (Builder $builder) use ($like): void {
            $builder->where('published_title', 'like', $like)
                ->orWhere('published_content', 'like', $like)
                ->orWhere('published_tags', 'like', $like);
        });
    }

    public function scopeWithPublishedTag(Builder $query, ?string $tag): Builder
    {
        $tag = MarkdownRenderer::normalizeTags($tag);

        if (blank($tag)) {
            return $query;
        }

        return $query->where('published_tags', 'like', '%'.$tag.'%');
    }

    private function resolveExpirationTimestamp()
    {
        return match ($this->expiration_option) {
            '10m' => now()->addMinutes(10),
            '1h' => now()->addHour(),
            '1d' => now()->addDay(),
            '1w' => now()->addWeek(),
            '1M' => now()->addMonth(),
            'custom' => $this->expires_at,
            default => null,
        };
    }
}
