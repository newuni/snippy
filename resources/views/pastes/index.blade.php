@extends('pastes.layout')

@section('content')
<section class="grid gap-6 lg:grid-cols-[1.2fr_0.8fr]">
    <div class="panel hero-panel">
        <p class="eyebrow">Write first. Share when you’re ready.</p>
        <h1 class="mt-4 max-w-3xl text-4xl font-semibold tracking-tight sm:text-5xl">
            A calm place for notes worth sharing.
        </h1>
        <p class="mt-5 max-w-2xl text-lg text-[var(--muted)]">
            Start with a private draft and take your time. Snippy saves as you write, shows you how your Markdown will look, and creates a public link only when you choose to publish. Add tags, an expiry date, or a password whenever you need them.
        </p>
        <div class="mt-8 flex flex-wrap gap-3">
            <form action="{{ route('pastes.create') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-primary">Write a note</button>
            </form>
            <a href="{{ route('pastes.explore') }}" class="btn btn-secondary">Explore public notes</a>
        </div>
    </div>

    <div class="panel">
        <p class="eyebrow">From draft to shared</p>
        <h2 class="mt-2 text-2xl font-semibold">Simple by design</h2>
        <ol class="mt-5 space-y-4 text-sm text-[var(--muted)]">
            <li><span class="step-index">1</span> Open a private draft and write at your own pace.</li>
            <li><span class="step-index">2</span> See your Markdown take shape while Snippy saves every change.</li>
            <li><span class="step-index">3</span> Publish when you’re happy with it, then share the public link and keep the editing link to yourself.</li>
        </ol>
    </div>
</section>

<section class="mt-8 panel">
    <div class="flex items-center justify-between gap-4">
        <div>
            <p class="eyebrow">Recently shared</p>
            <h2 class="mt-2 text-2xl font-semibold">Latest public notes</h2>
        </div>
        <a href="{{ route('pastes.explore') }}" class="text-sm font-medium text-[var(--accent)]">Explore all</a>
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
            <p class="text-[var(--muted)]">Nothing has been shared yet. Your note could be the first.</p>
        @endforelse
    </div>
</section>
@endsection
