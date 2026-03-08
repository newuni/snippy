<?php

namespace App\Http\Controllers;

use App\Models\Paste;
use App\Support\MarkdownRenderer;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PasteController extends Controller
{
    public function index()
    {
        $recent = Paste::published()
            ->orderByDesc('published_at')
            ->take(6)
            ->get();

        return view('pastes.index', compact('recent'));
    }

    public function create()
    {
        $paste = Paste::create([
            'title' => null,
            'content' => '',
            'rendered_content' => MarkdownRenderer::render(''),
            'syntax' => 'markdown',
            'status' => 'draft',
            'expiration_option' => 'never',
            'last_autosaved_at' => now(),
        ]);

        return redirect()->route('pastes.manage', ['paste' => $paste->manage_token]);
    }

    public function manage(Paste $paste)
    {
        return view('pastes.manage', [
            'paste' => $paste,
            'expirationOptions' => $this->expirationOptions($paste),
        ]);
    }

    public function autosave(Request $request, Paste $paste)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'content' => 'nullable|string|max:500000',
            'tags' => 'nullable|string|max:255',
            'expiration_option' => 'required|string|in:10m,1h,1d,1w,1M,never,custom',
            'password' => 'nullable|string|min:4|max:100',
        ]);

        $this->fillDraft($paste, $validated);
        $paste->last_autosaved_at = now();
        $paste->save();

        return response()->json([
            'saved_at' => $paste->last_autosaved_at?->toIso8601String(),
            'preview_html' => $paste->rendered_content,
            'has_unpublished_changes' => $paste->hasUnpublishedChanges(),
            'is_published' => $paste->isPublished(),
            'manage_url' => route('pastes.manage', ['paste' => $paste->manage_token]),
            'public_url' => $paste->isPublished() ? route('pastes.show', ['paste' => $paste->slug]) : null,
            'public_raw_url' => $paste->isPublished() ? route('pastes.raw', ['paste' => $paste->slug]) : null,
        ]);
    }

    public function publish(Request $request, Paste $paste)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'content' => 'required|string|max:500000',
            'tags' => 'nullable|string|max:255',
            'expiration_option' => 'required|string|in:10m,1h,1d,1w,1M,never,custom',
            'password' => 'nullable|string|min:4|max:100',
        ]);

        $this->fillDraft($paste, $validated);
        $paste->last_autosaved_at = now();
        $paste->publish();

        return redirect()
            ->route('pastes.manage', ['paste' => $paste->manage_token])
            ->with('success', 'Post published.');
    }

    public function unpublish(Paste $paste)
    {
        $paste->update(['status' => 'draft']);

        return redirect()
            ->route('pastes.manage', ['paste' => $paste->manage_token])
            ->with('success', 'Post moved back to draft.');
    }

    public function clearPassword(Paste $paste)
    {
        $paste->update(['password' => null]);

        return redirect()
            ->route('pastes.manage', ['paste' => $paste->manage_token])
            ->with('success', 'Public password removed.');
    }

    public function explore(Request $request)
    {
        $search = $request->string('q')->toString();
        $tag = $request->string('tag')->toString();

        $posts = Paste::query()
            ->published()
            ->searchPublished($search)
            ->withPublishedTag($tag)
            ->orderByDesc('published_at')
            ->paginate(12)
            ->withQueryString();

        $topTags = Paste::query()
            ->published()
            ->get()
            ->flatMap(fn (Paste $paste) => $paste->published_tag_list)
            ->filter()
            ->countBy()
            ->sortDesc()
            ->keys()
            ->take(12);

        return view('pastes.explore', compact('posts', 'search', 'tag', 'topTags'));
    }

    public function show(Request $request, Paste $paste)
    {
        if (!$paste->isPublished()) {
            abort(404, 'This post is not public.');
        }

        if ($paste->isProtected() && !$request->session()->get("paste_unlocked_{$paste->id}")) {
            return view('pastes.password', compact('paste'));
        }

        return view('pastes.show', compact('paste'));
    }

    public function unlock(Request $request, Paste $paste)
    {
        $request->validate(['password' => 'required|string']);

        if ($paste->checkPassword($request->password)) {
            $request->session()->put("paste_unlocked_{$paste->id}", true);
            return redirect()->route('pastes.show', $paste);
        }

        return back()->withErrors(['password' => 'Incorrect password']);
    }

    public function raw(Request $request, Paste $paste)
    {
        if (!$paste->isPublished()) {
            abort(404, 'This post is not public.');
        }

        if ($paste->isProtected() && !$request->session()->get("paste_unlocked_{$paste->id}")) {
            abort(403, 'This paste is password protected.');
        }

        return response($paste->published_content)
            ->header('Content-Type', 'text/plain');
    }

    public function draftRaw(Paste $paste): Response
    {
        return response($paste->content)
            ->header('Content-Type', 'text/plain');
    }

    private function fillDraft(Paste $paste, array $validated): void
    {
        $paste->title = $validated['title'] ?? null;
        $paste->content = $validated['content'] ?? '';
        $paste->rendered_content = MarkdownRenderer::render($paste->content);
        $paste->tags = $validated['tags'] ?? null;
        $paste->syntax = 'markdown';
        $paste->expiration_option = $validated['expiration_option'];

        if (!empty($validated['password'])) {
            $paste->password = password_hash($validated['password'], PASSWORD_DEFAULT);
        }
    }

    private function expirationOptions(Paste $paste): array
    {
        $options = [
            'never' => 'Never expires',
            '10m' => '10 minutes',
            '1h' => '1 hour',
            '1d' => '1 day',
            '1w' => '1 week',
            '1M' => '1 month',
        ];

        if ($paste->expiration_option === 'custom') {
            $options['custom'] = 'Keep legacy custom expiry';
        }

        return $options;
    }
}
