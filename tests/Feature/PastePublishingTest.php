<?php

namespace Tests\Feature;

use App\Models\Paste;
use App\Support\MarkdownRenderer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PastePublishingTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_and_explore_page_load(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Markdown publishing');

        $this->get('/explore')
            ->assertOk()
            ->assertSee('Published markdown posts');
    }

    public function test_new_route_creates_private_draft_and_redirects_to_manage_url(): void
    {
        $response = $this->get('/new');

        $paste = Paste::first();

        $response->assertRedirect(route('pastes.manage', ['paste' => $paste->manage_token]));
        $this->assertSame('draft', $paste->status);
        $this->assertNull($paste->published_at);
    }

    public function test_autosave_updates_draft_fields_and_returns_preview_html(): void
    {
        $paste = Paste::factory()->create([
            'title' => null,
            'content' => '',
            'rendered_content' => MarkdownRenderer::render(''),
        ]);

        $response = $this->putJson(route('pastes.autosave', ['paste' => $paste->manage_token]), [
            'title' => 'Working title',
            'content' => "# Heading\n\nBody copy",
            'tags' => 'Laravel, Notes',
            'expiration_option' => '1w',
            'password' => '',
        ]);

        $response->assertOk()
            ->assertJsonPath('is_published', false)
            ->assertJsonPath('has_unpublished_changes', true);

        $paste->refresh();

        $this->assertSame('Working title', $paste->title);
        $this->assertSame('laravel,notes', $paste->tags);
        $this->assertSame('1w', $paste->expiration_option);
        $this->assertStringContainsString('<h1>Heading</h1>', $paste->rendered_content);
        $this->assertNotNull($paste->last_autosaved_at);
    }

    public function test_draft_is_not_public_until_published(): void
    {
        $paste = Paste::factory()->create([
            'title' => 'Draft only',
            'content' => 'Private text',
            'rendered_content' => MarkdownRenderer::render('Private text'),
        ]);

        $this->get(route('pastes.show', ['paste' => $paste->slug]))->assertNotFound();
        $this->get('/explore')->assertDontSee('Draft only');
    }

    public function test_publish_creates_public_snapshot_and_lists_post_on_explore(): void
    {
        $paste = Paste::factory()->create();

        $response = $this->post(route('pastes.publish', ['paste' => $paste->manage_token]), [
            'title' => 'Release Notes',
            'content' => "# Release Notes\n\nShipped today.",
            'tags' => 'release, updates',
            'expiration_option' => 'never',
            'password' => '',
        ]);

        $response->assertRedirect(route('pastes.manage', ['paste' => $paste->manage_token]));

        $paste->refresh();

        $this->assertSame('published', $paste->status);
        $this->assertSame('Release Notes', $paste->published_title);
        $this->assertSame("# Release Notes\n\nShipped today.", $paste->published_content);
        $this->assertSame('release,updates', $paste->published_tags);
        $this->assertNotNull($paste->published_at);

        $this->get(route('pastes.show', ['paste' => $paste->slug]))
            ->assertOk()
            ->assertSee('Release Notes')
            ->assertSee('Shipped today.');

        $this->get('/explore')
            ->assertOk()
            ->assertSee('Release Notes')
            ->assertSee('#release');
    }

    public function test_editing_published_draft_does_not_change_public_page_until_republished(): void
    {
        $paste = Paste::factory()->published()->create([
            'title' => 'Version One',
            'content' => "# Version One\n\nPublic body",
            'rendered_content' => MarkdownRenderer::render("# Version One\n\nPublic body"),
            'published_title' => 'Version One',
            'published_content' => "# Version One\n\nPublic body",
            'published_rendered_content' => MarkdownRenderer::render("# Version One\n\nPublic body"),
        ]);

        $this->putJson(route('pastes.autosave', ['paste' => $paste->manage_token]), [
            'title' => 'Version Two',
            'content' => "# Version Two\n\nDraft body",
            'tags' => 'draft',
            'expiration_option' => 'never',
            'password' => '',
        ])->assertOk();

        $this->get(route('pastes.show', ['paste' => $paste->slug]))
            ->assertOk()
            ->assertSee('Version One')
            ->assertSee('Public body')
            ->assertDontSee('Version Two')
            ->assertDontSee('Draft body');

        $this->post(route('pastes.publish', ['paste' => $paste->manage_token]), [
            'title' => 'Version Two',
            'content' => "# Version Two\n\nDraft body",
            'tags' => 'draft',
            'expiration_option' => 'never',
            'password' => '',
        ])->assertRedirect();

        $this->get(route('pastes.show', ['paste' => $paste->slug]))
            ->assertOk()
            ->assertSee('Version Two')
            ->assertSee('Draft body');
    }

    public function test_published_password_protection_still_works(): void
    {
        $paste = Paste::factory()->published()->create([
            'title' => 'Protected',
            'content' => '# Protected',
            'rendered_content' => MarkdownRenderer::render('# Protected'),
            'published_title' => 'Protected',
            'published_content' => '# Protected',
            'published_rendered_content' => MarkdownRenderer::render('# Protected'),
            'password' => password_hash('secret123', PASSWORD_DEFAULT),
        ]);

        $this->get(route('pastes.show', ['paste' => $paste->slug]))
            ->assertOk()
            ->assertSee('Unlock this post')
            ->assertSee('protected with a password');

        $this->get(route('pastes.raw', ['paste' => $paste->slug]))->assertForbidden();

        $this->post(route('pastes.unlock', ['paste' => $paste->slug]), [
            'password' => 'secret123',
        ])->assertRedirect(route('pastes.show', ['paste' => $paste->slug]));

        $this->get(route('pastes.show', ['paste' => $paste->slug]))
            ->assertOk()
            ->assertSee('Protected');
    }

    public function test_expiration_hides_public_page_but_manage_page_remains_available(): void
    {
        $paste = Paste::factory()->create();

        $this->post(route('pastes.publish', ['paste' => $paste->manage_token]), [
            'title' => 'Temporary note',
            'content' => 'Expires quickly',
            'tags' => 'temp',
            'expiration_option' => '10m',
            'password' => '',
        ])->assertRedirect();

        $paste->refresh();
        $this->assertNotNull($paste->expires_at);

        $this->travel(11)->minutes();

        $this->get(route('pastes.show', ['paste' => $paste->slug]))->assertNotFound();
        $this->get(route('pastes.manage', ['paste' => $paste->manage_token]))->assertOk();
        $this->get('/explore')->assertDontSee('Temporary note');
    }

    public function test_explore_supports_search_and_tag_filters(): void
    {
        Paste::factory()->published()->create([
            'title' => 'Laravel Launch',
            'content' => '# Laravel Launch',
            'rendered_content' => MarkdownRenderer::render('# Laravel Launch'),
            'published_title' => 'Laravel Launch',
            'published_content' => '# Laravel Launch',
            'published_rendered_content' => MarkdownRenderer::render('# Laravel Launch'),
            'tags' => 'laravel,release',
            'published_tags' => 'laravel,release',
        ]);

        Paste::factory()->published()->create([
            'title' => 'Travel Diary',
            'content' => '# Travel Diary',
            'rendered_content' => MarkdownRenderer::render('# Travel Diary'),
            'published_title' => 'Travel Diary',
            'published_content' => '# Travel Diary',
            'published_rendered_content' => MarkdownRenderer::render('# Travel Diary'),
            'tags' => 'travel',
            'published_tags' => 'travel',
        ]);

        $this->get('/explore?q=Laravel')
            ->assertOk()
            ->assertSee('Laravel Launch')
            ->assertDontSee('Travel Diary');

        $this->get('/explore?tag=travel')
            ->assertOk()
            ->assertSee('Travel Diary')
            ->assertDontSee('Laravel Launch');
    }
}
