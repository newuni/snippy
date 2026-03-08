<?php

namespace Database\Factories;

use App\Models\Paste;
use App\Support\MarkdownRenderer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Paste>
 */
class PasteFactory extends Factory
{
    protected $model = Paste::class;

    public function definition(): array
    {
        $title = fake()->sentence(3);
        $content = "# {$title}\n\n".fake()->paragraphs(2, true);

        return [
            'title' => $title,
            'content' => $content,
            'rendered_content' => MarkdownRenderer::render($content),
            'syntax' => 'markdown',
            'status' => 'draft',
            'expiration_option' => 'never',
            'last_autosaved_at' => now(),
        ];
    }

    public function published(): static
    {
        return $this->state(function (array $attributes): array {
            return [
                'published_title' => $attributes['title'],
                'published_content' => $attributes['content'],
                'published_rendered_content' => $attributes['rendered_content'],
                'published_tags' => $attributes['tags'] ?? null,
                'published_at' => now(),
                'status' => 'published',
            ];
        });
    }
}
