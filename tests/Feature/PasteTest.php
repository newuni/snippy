<?php

namespace Tests\Feature;

use App\Models\Paste;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PasteTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_loads(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Snippy');
    }

    public function test_create_page_loads(): void
    {
        $response = $this->get('/new');
        $response->assertStatus(200);
        $response->assertSee('New Paste');
    }

    public function test_can_create_paste(): void
    {
        $response = $this->post('/new', [
            'content' => 'Hello World',
            'syntax' => 'plaintext',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('pastes', [
            'content' => 'Hello World',
            'syntax' => 'plaintext',
        ]);
    }

    public function test_can_create_paste_with_title(): void
    {
        $response = $this->post('/new', [
            'title' => 'My Test Paste',
            'content' => 'Test content',
            'syntax' => 'php',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('pastes', [
            'title' => 'My Test Paste',
            'content' => 'Test content',
        ]);
    }

    public function test_can_view_paste(): void
    {
        $paste = Paste::create([
            'content' => 'Test content here',
            'syntax' => 'javascript',
        ]);

        $response = $this->get("/{$paste->slug}");
        $response->assertStatus(200);
        $response->assertSee('Test content here');
    }

    public function test_can_view_raw_paste(): void
    {
        $paste = Paste::create([
            'content' => 'Raw content',
            'syntax' => 'plaintext',
        ]);

        $response = $this->get("/{$paste->slug}/raw");
        $response->assertStatus(200);
        $response->assertSee('Raw content');
        $response->assertHeader('Content-Type', 'text/plain; charset=UTF-8');
    }

    public function test_paste_requires_content(): void
    {
        $response = $this->post('/new', [
            'syntax' => 'plaintext',
        ]);

        $response->assertSessionHasErrors('content');
    }

    public function test_paste_requires_valid_syntax(): void
    {
        $response = $this->post('/new', [
            'content' => 'Test',
            'syntax' => 'invalid-syntax',
        ]);

        $response->assertSessionHasErrors('syntax');
    }

    public function test_paste_with_expiration(): void
    {
        $response = $this->post('/new', [
            'content' => 'Expiring content',
            'syntax' => 'plaintext',
            'expiration' => '1h',
        ]);

        $paste = Paste::first();
        $this->assertNotNull($paste->expires_at);
        $this->assertTrue($paste->expires_at->isFuture());
    }

    public function test_expired_paste_returns_404(): void
    {
        $paste = Paste::create([
            'content' => 'Expired content',
            'syntax' => 'plaintext',
            'expires_at' => now()->subHour(),
        ]);

        $response = $this->get("/{$paste->slug}");
        $response->assertStatus(404);
    }

    public function test_protected_paste_shows_password_form(): void
    {
        $paste = Paste::create([
            'content' => 'Secret content',
            'syntax' => 'plaintext',
            'password' => password_hash('secret123', PASSWORD_DEFAULT),
        ]);

        $response = $this->get("/{$paste->slug}");
        $response->assertStatus(200);
        $response->assertSee('Protected Snippet');
        $response->assertDontSee('Secret content');
    }

    public function test_can_unlock_protected_paste(): void
    {
        $paste = Paste::create([
            'content' => 'Secret content',
            'syntax' => 'plaintext',
            'password' => password_hash('secret123', PASSWORD_DEFAULT),
        ]);

        $response = $this->post("/{$paste->slug}/unlock", [
            'password' => 'secret123',
        ]);

        $response->assertRedirect("/{$paste->slug}");
        
        $response = $this->get("/{$paste->slug}");
        $response->assertSee('Secret content');
    }

    public function test_wrong_password_shows_error(): void
    {
        $paste = Paste::create([
            'content' => 'Secret content',
            'syntax' => 'plaintext',
            'password' => password_hash('secret123', PASSWORD_DEFAULT),
        ]);

        $response = $this->post("/{$paste->slug}/unlock", [
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_protected_raw_paste_returns_403(): void
    {
        $paste = Paste::create([
            'content' => 'Secret content',
            'syntax' => 'plaintext',
            'password' => password_hash('secret123', PASSWORD_DEFAULT),
        ]);

        $response = $this->get("/{$paste->slug}/raw");
        $response->assertStatus(403);
    }

    public function test_recent_pastes_shown_on_homepage(): void
    {
        Paste::create(['content' => 'First paste', 'syntax' => 'plaintext']);
        Paste::create(['content' => 'Second paste', 'syntax' => 'plaintext', 'title' => 'My Title']);

        $response = $this->get('/');
        $response->assertSee('My Title');
    }

    public function test_expired_pastes_not_shown_on_homepage(): void
    {
        Paste::create([
            'content' => 'Expired',
            'syntax' => 'plaintext',
            'title' => 'Expired Paste',
            'expires_at' => now()->subHour(),
        ]);

        $response = $this->get('/');
        $response->assertDontSee('Expired Paste');
    }
}
