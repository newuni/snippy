@extends('pastes.layout')

@section('content')
<section class="grid gap-6 lg:grid-cols-[1.2fr_0.8fr]">
    <div class="panel hero-panel">
        <p class="eyebrow">Markdown publishing, not throwaway pastes</p>
        <h1 class="mt-4 max-w-3xl text-4xl font-semibold tracking-tight sm:text-5xl">
            Draft privately, autosave continuously, and publish only when the page is ready.
        </h1>
        <p class="mt-5 max-w-2xl text-lg text-[var(--muted)]">
            Snippy v1 keeps a private manage link for editing and a separate public URL for readers. Markdown preview, tags, raw output, expiration, and legacy public passwords still work.
        </p>
        <div class="mt-8 flex flex-wrap gap-3">
            <a href="{{ route('pastes.create') }}" class="btn btn-primary">Start a draft</a>
            <a href="{{ route('pastes.explore') }}" class="btn btn-secondary">Browse published posts</a>
        </div>
    </div>

    <div class="panel">
        <p class="eyebrow">How it works</p>
        <ol class="mt-5 space-y-4 text-sm text-[var(--muted)]">
            <li><span class="step-index">1</span> `GET /new` creates a private draft and redirects to its manage URL.</li>
            <li><span class="step-index">2</span> Typing triggers debounced autosave and server-rendered markdown preview updates.</li>
            <li><span class="step-index">3</span> `POST /manage/{token}/publish` snapshots the draft to the public slug URL.</li>
        </ol>
    </div>
</section>

<section class="mt-8 panel">
    <div class="flex items-center justify-between gap-4">
        <div>
            <p class="eyebrow">Recent publications</p>
            <h2 class="mt-2 text-2xl font-semibold">Fresh from Explore</h2>
        </div>
        <a href="{{ route('pastes.explore') }}" class="text-sm font-medium text-[var(--accent)]">See all</a>
    </div>

    <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @forelse ($recent as $paste)
            <article class="card">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <a href="{{ route('pastes.show', ['paste' => $paste->slug]) }}" class="text-lg font-semibold hover:text-[var(--accent)]">
                            {{ $paste->published_title ?: 'Untitled' }}
                        </a>
                        <p class="mt-1 text-sm text-[var(--muted)]">
                            Published {{ optional($paste->published_at)->diffForHumans() }}
                            @if ($paste->isProtected())
                                · Password protected
                            @endif
                        </p>
                    </div>
                    <span class="pill">Markdown</span>
                </div>
                <p class="mt-4 text-sm leading-6 text-[var(--muted)]">{{ $paste->excerpt(140) }}</p>
                @if ($paste->published_tag_list)
                    <div class="mt-4 flex flex-wrap gap-2">
                        @foreach ($paste->published_tag_list as $tag)
                            <a href="{{ route('pastes.explore', ['tag' => $tag]) }}" class="tag-chip">#{{ $tag }}</a>
                        @endforeach
                    </div>
                @endif
            </article>
        @empty
            <p class="text-[var(--muted)]">No public posts yet. Publish the first one from a private draft.</p>
        @endforelse
    </div>
</section>
@endsection
