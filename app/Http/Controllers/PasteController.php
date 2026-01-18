<?php

namespace App\Http\Controllers;

use App\Models\Paste;
use Illuminate\Http\Request;

class PasteController extends Controller
{
    public function index()
    {
        $pastes = Paste::whereNull('expires_at')
            ->orWhere('expires_at', '>', now())
            ->latest()
            ->take(10)
            ->get();
            
        return view('pastes.index', compact('pastes'));
    }

    public function create()
    {
        return view('pastes.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'content' => 'required|string|max:500000',
            'syntax' => 'required|string|in:plaintext,php,javascript,python,sql,json,html,css,bash,yaml,markdown',
            'expiration' => 'nullable|string|in:10m,1h,1d,1w,1M,never',
            'password' => 'nullable|string|min:4|max:100',
        ]);

        $expiresAt = match($validated['expiration'] ?? 'never') {
            '10m' => now()->addMinutes(10),
            '1h' => now()->addHour(),
            '1d' => now()->addDay(),
            '1w' => now()->addWeek(),
            '1M' => now()->addMonth(),
            default => null,
        };

        $paste = Paste::create([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'syntax' => $validated['syntax'],
            'password' => !empty($validated['password']) ? password_hash($validated['password'], PASSWORD_DEFAULT) : null,
            'expires_at' => $expiresAt,
        ]);

        return redirect()->route('pastes.show', $paste)
            ->with('success', 'Paste created successfully!');
    }

    public function show(Request $request, Paste $paste)
    {
        if ($paste->isExpired()) {
            abort(404, 'This paste has expired.');
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
        if ($paste->isExpired()) {
            abort(404, 'This paste has expired.');
        }

        if ($paste->isProtected() && !$request->session()->get("paste_unlocked_{$paste->id}")) {
            abort(403, 'This paste is password protected.');
        }

        return response($paste->content)
            ->header('Content-Type', 'text/plain');
    }
}
