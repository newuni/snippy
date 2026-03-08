<?php

use App\Models\Paste;
use App\Support\MarkdownRenderer;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pastes', function (Blueprint $table) {
            $table->string('manage_token', 32)->nullable()->unique()->after('slug');
            $table->string('status')->default('draft')->after('password');
            $table->longText('rendered_content')->nullable()->after('content');
            $table->string('tags')->nullable()->after('title');
            $table->string('expiration_option')->default('never')->after('expires_at');
            $table->string('published_title')->nullable()->after('expiration_option');
            $table->longText('published_content')->nullable()->after('published_title');
            $table->longText('published_rendered_content')->nullable()->after('published_content');
            $table->string('published_tags')->nullable()->after('published_rendered_content');
            $table->timestamp('published_at')->nullable()->after('published_tags');
            $table->timestamp('last_autosaved_at')->nullable()->after('published_at');

            $table->index(['status', 'published_at']);
        });

        DB::table('pastes')
            ->orderBy('id')
            ->chunkById(100, function (Collection $pastes): void {
                foreach ($pastes as $paste) {
                    $createdAt = $paste->created_at ? Carbon::parse($paste->created_at) : now();
                    $expiresAt = $paste->expires_at ? Carbon::parse($paste->expires_at) : null;

                    DB::table('pastes')
                        ->where('id', $paste->id)
                        ->update([
                            'manage_token' => $this->generateManageToken(),
                            'status' => 'published',
                            'rendered_content' => MarkdownRenderer::render($paste->content),
                            'published_title' => $paste->title,
                            'published_content' => $paste->content,
                            'published_rendered_content' => MarkdownRenderer::render($paste->content),
                            'published_tags' => null,
                            'published_at' => $createdAt,
                            'last_autosaved_at' => $createdAt,
                            'expiration_option' => $this->inferExpirationOption($createdAt, $expiresAt),
                        ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('pastes', function (Blueprint $table) {
            $table->dropIndex(['status', 'published_at']);

            $table->dropColumn([
                'manage_token',
                'status',
                'rendered_content',
                'tags',
                'expiration_option',
                'published_title',
                'published_content',
                'published_rendered_content',
                'published_tags',
                'published_at',
                'last_autosaved_at',
            ]);
        });
    }

    private function generateManageToken(): string
    {
        do {
            $token = Str::random(32);
        } while (Paste::where('manage_token', $token)->exists());

        return $token;
    }

    private function inferExpirationOption($createdAt, $expiresAt): string
    {
        if ($expiresAt === null) {
            return 'never';
        }

        $minutes = $createdAt->diffInMinutes($expiresAt);

        return match (true) {
            $minutes === 10 => '10m',
            $minutes === 60 => '1h',
            $minutes === 60 * 24 => '1d',
            $minutes === 60 * 24 * 7 => '1w',
            $minutes >= 60 * 24 * 28 && $minutes <= 60 * 24 * 31 => '1M',
            default => 'custom',
        };
    }
};
