<?php

namespace Tests\Unit;

use App\Models\Paste;
use App\Support\MarkdownRenderer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PasteModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_manage_token_and_slug_are_generated_on_create(): void
    {
        $paste = Paste::factory()->create();

        $this->assertNotNull($paste->manage_token);
        $this->assertNotNull($paste->slug);
        $this->assertSame(32, strlen($paste->manage_token));
    }

    public function test_publish_copies_draft_into_public_snapshot(): void
    {
        $paste = Paste::factory()->create([
            'title' => 'Draft title',
            'content' => "# Draft title\n\nBody",
            'rendered_content' => MarkdownRenderer::render("# Draft title\n\nBody"),
            'tags' => 'laravel,notes',
            'expiration_option' => '1d',
        ]);

        $paste->publish();
        $paste->refresh();

        $this->assertSame('published', $paste->status);
        $this->assertSame($paste->title, $paste->published_title);
        $this->assertSame($paste->content, $paste->published_content);
        $this->assertSame($paste->tags, $paste->published_tags);
        $this->assertNotNull($paste->published_at);
        $this->assertTrue($paste->isPublished());
    }

    public function test_has_unpublished_changes_detects_draft_edits_after_publish(): void
    {
        $paste = Paste::factory()->published()->create([
            'title' => 'Original',
            'content' => '# Original',
            'rendered_content' => MarkdownRenderer::render('# Original'),
            'published_title' => 'Original',
            'published_content' => '# Original',
            'published_rendered_content' => MarkdownRenderer::render('# Original'),
        ]);

        $this->assertFalse($paste->hasUnpublishedChanges());

        $paste->title = 'Changed';

        $this->assertTrue($paste->hasUnpublishedChanges());
    }

    public function test_tags_are_normalized(): void
    {
        $paste = Paste::factory()->create([
            'tags' => 'Laravel, release notes, Laravel ',
        ]);

        $paste->refresh();

        $this->assertSame('laravel,release-notes', $paste->tags);
        $this->assertSame(['laravel', 'release-notes'], $paste->tag_list);
    }

    public function test_password_is_hidden_from_serialized_model(): void
    {
        $paste = Paste::factory()->create([
            'password' => password_hash('secret', PASSWORD_DEFAULT),
        ]);

        $this->assertArrayNotHasKey('password', $paste->toArray());
        $this->assertArrayNotHasKey('manage_token', $paste->toArray());
    }
}
