<?php

namespace Tests\Unit;

use App\Models\Paste;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PasteModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_slug_is_generated_on_create(): void
    {
        $paste = Paste::create([
            'content' => 'Test',
            'syntax' => 'plaintext',
        ]);

        $this->assertNotNull($paste->slug);
        $this->assertEquals(8, strlen($paste->slug));
    }

    public function test_slug_is_unique(): void
    {
        $paste1 = Paste::create(['content' => 'Test 1', 'syntax' => 'plaintext']);
        $paste2 = Paste::create(['content' => 'Test 2', 'syntax' => 'plaintext']);

        $this->assertNotEquals($paste1->slug, $paste2->slug);
    }

    public function test_is_expired_returns_true_for_expired(): void
    {
        $paste = new Paste([
            'content' => 'Test',
            'syntax' => 'plaintext',
            'expires_at' => now()->subHour(),
        ]);

        $this->assertTrue($paste->isExpired());
    }

    public function test_is_expired_returns_false_for_future(): void
    {
        $paste = new Paste([
            'content' => 'Test',
            'syntax' => 'plaintext',
            'expires_at' => now()->addHour(),
        ]);

        $this->assertFalse($paste->isExpired());
    }

    public function test_is_expired_returns_false_for_null(): void
    {
        $paste = new Paste([
            'content' => 'Test',
            'syntax' => 'plaintext',
            'expires_at' => null,
        ]);

        $this->assertFalse($paste->isExpired());
    }

    public function test_is_protected_returns_true_with_password(): void
    {
        $paste = new Paste([
            'content' => 'Test',
            'syntax' => 'plaintext',
            'password' => 'hashed_password',
        ]);

        $this->assertTrue($paste->isProtected());
    }

    public function test_is_protected_returns_false_without_password(): void
    {
        $paste = new Paste([
            'content' => 'Test',
            'syntax' => 'plaintext',
        ]);

        $this->assertFalse($paste->isProtected());
    }

    public function test_check_password_validates_correctly(): void
    {
        $paste = new Paste([
            'content' => 'Test',
            'syntax' => 'plaintext',
            'password' => password_hash('correct', PASSWORD_DEFAULT),
        ]);

        $this->assertTrue($paste->checkPassword('correct'));
        $this->assertFalse($paste->checkPassword('wrong'));
    }

    public function test_route_key_is_slug(): void
    {
        $paste = new Paste();
        $this->assertEquals('slug', $paste->getRouteKeyName());
    }

    public function test_password_is_hidden(): void
    {
        $paste = Paste::create([
            'content' => 'Test',
            'syntax' => 'plaintext',
            'password' => password_hash('secret', PASSWORD_DEFAULT),
        ]);

        $array = $paste->toArray();
        $this->assertArrayNotHasKey('password', $array);
    }
}
