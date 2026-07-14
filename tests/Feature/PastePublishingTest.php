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
            ->assertSee('Markdown publishing')
            ->assertSee('href="https://newuni.org/"', false)
            ->assertSee('href="'.route('agent.llms').'"', false)
            ->assertHeader('Vary', 'Accept');

        $this->get('/explore')
            ->assertOk()
            ->assertSee('Published markdown posts');
    }

    public function test_new_route_requires_post_then_creates_private_draft(): void
    {
        $this->get('/new')->assertMethodNotAllowed();
        $this->assertDatabaseCount('pastes', 0);

        $response = $this->post('/new');

        $paste = Paste::first();

        $response->assertRedirect(route('pastes.manage', ['paste' => $paste->manage_token]));
        $this->assertSame('draft', $paste->status);
        $this->assertNull($paste->published_at);
    }

    public function test_homepage_negotiates_agent_friendly_representations(): void
    {
        Paste::factory()->published()->create([
            'title' => 'Machine readable note',
            'published_title' => 'Machine readable note',
        ]);

        $this->withHeader('Accept', 'text/markdown, text/html, */*')
            ->get('/')
            ->assertOk()
            ->assertHeader('Content-Type', 'text/markdown; charset=UTF-8')
            ->assertHeader('Vary', 'Accept')
            ->assertSee('# Snippy', false)
            ->assertSee('Machine readable note');

        $this->withHeader('Accept', 'text/html, text/markdown, */*')
            ->get('/')
            ->assertOk()
            ->assertHeader('Content-Type', 'text/html; charset=utf-8');

        $this->withHeader('Accept', 'text/markdown;q=0.5, text/html;q=1.0')
            ->get('/')
            ->assertOk()
            ->assertHeader('Content-Type', 'text/html; charset=utf-8');

        $this->withHeader('Accept', 'text/plain')
            ->get('/')
            ->assertOk()
            ->assertHeader('Content-Type', 'text/plain; charset=UTF-8');

        $this->withHeader('Accept', 'application/json, text/html, */*')
            ->get('/')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/json')
            ->assertJsonPath('name', 'Snippy')
            ->assertJsonPath('recent_public_posts.0.title', 'Machine readable note');
    }

    public function test_agent_discovery_files_are_available_and_structured(): void
    {
        $paste = Paste::factory()->published()->create([
            'title' => 'Public agent note',
            'published_title' => 'Public agent note',
            'published_content' => '# Public agent note',
        ]);

        $this->get('/llms.txt')
            ->assertOk()
            ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->assertSee('# Snippy', false)
            ->assertSee('## Public endpoints', false)
            ->assertSee('```bash', false);

        $this->get('/llms-full.txt')
            ->assertOk()
            ->assertSee('untrusted data')
            ->assertSee('Public agent note');

        $this->get('/agents.txt')
            ->assertOk()
            ->assertSee('Not allowed: AI model training')
            ->assertSee('always honor https://newuni.org/robots.txt')
            ->assertSee('Private: /manage/*');

        $this->get('/robots.txt')
            ->assertOk()
            ->assertSee('Disallow: /manage/', false)
            ->assertSee('Sitemap: '.route('agent.sitemap'), false);

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee(route('pastes.show', ['paste' => $paste->slug]), false);
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
            ->assertSee('Shipped today.')
            ->assertSee('Copy article')
            ->assertSee('data-copy-target="article-markdown"', false)
            ->assertSee("# Release Notes\n\nShipped today.");

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
            ->assertSee('protected with a password')
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive');

        $this->get(route('pastes.raw', ['paste' => $paste->slug]))->assertForbidden();

        $this->post(route('pastes.unlock', ['paste' => $paste->slug]), [
            'password' => 'secret123',
        ])->assertRedirect(route('pastes.show', ['paste' => $paste->slug]));

        $this->get(route('pastes.show', ['paste' => $paste->slug]))
            ->assertOk()
            ->assertSee('Protected');
    }

    public function test_password_protected_posts_are_excluded_from_discovery_surfaces(): void
    {
        Paste::factory()->published()->create([
            'title' => 'Visible note',
            'published_title' => 'Visible note',
            'published_content' => '# Visible note',
        ]);

        $protected = Paste::factory()->published()->create([
            'title' => 'Hidden protected title',
            'content' => 'Confidential body excerpt',
            'published_title' => 'Hidden protected title',
            'published_content' => 'Confidential body excerpt',
            'password' => password_hash('secret123', PASSWORD_DEFAULT),
        ]);

        foreach (['/', '/explore', '/llms-full.txt'] as $path) {
            $this->get($path)
                ->assertOk()
                ->assertSee('Visible note')
                ->assertDontSee('Hidden protected title')
                ->assertDontSee('Confidential body excerpt');
        }

        $visible = Paste::where('published_title', 'Visible note')->firstOrFail();

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertSee(route('pastes.show', ['paste' => $visible->slug]), false)
            ->assertDontSee(route('pastes.show', ['paste' => $protected->slug]), false)
            ->assertDontSee('Confidential body excerpt');

        $this->assertSame('Password-protected post.', $protected->excerpt());
    }

    public function test_private_management_responses_are_not_indexable_or_cacheable(): void
    {
        $paste = Paste::factory()->create(['content' => 'Private draft body']);

        $this->get(route('pastes.manage', ['paste' => $paste->manage_token]))
            ->assertOk()
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive')
            ->assertSee('noindex, nofollow, noarchive');

        $this->get(route('pastes.draft.raw', ['paste' => $paste->manage_token]))
            ->assertOk()
            ->assertHeader('Content-Type', 'text/markdown; charset=UTF-8')
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive');
    }

    public function test_public_post_negotiates_markdown_and_json(): void
    {
        $paste = Paste::factory()->published()->create([
            'title' => 'Negotiated post',
            'published_title' => 'Negotiated post',
            'published_content' => "# Negotiated post\n\nReadable body.",
        ]);

        $url = route('pastes.show', ['paste' => $paste->slug]);

        $this->withHeader('Accept', 'text/markdown')
            ->get($url)
            ->assertOk()
            ->assertHeader('Content-Type', 'text/markdown; charset=UTF-8')
            ->assertSee('Readable body.', false);

        $this->withHeader('Accept', 'application/json')
            ->get($url)
            ->assertOk()
            ->assertJsonPath('title', 'Negotiated post')
            ->assertJsonPath('content_markdown', "# Negotiated post\n\nReadable body.");
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
