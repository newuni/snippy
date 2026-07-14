@extends('pastes.layout', [
    'title' => ($paste->published_title ?: 'Untitled') . ' - Snippy',
    'description' => $paste->excerpt(160),
    'canonical' => route('pastes.show', ['paste' => $paste->slug]),
    'robots' => $paste->isProtected() ? 'noindex, nofollow, noarchive' : 'index, follow',
    'alternateMarkdown' => route('pastes.raw', ['paste' => $paste->slug]),
    'openGraphType' => 'article',
    'structuredData' => [
        '@context' => 'https://schema.org',
        '@type' => 'Article',
        'headline' => $paste->published_title ?: 'Untitled',
        'datePublished' => optional($paste->published_at)->toIso8601String(),
        'dateModified' => optional($paste->updated_at)->toIso8601String(),
        'url' => route('pastes.show', ['paste' => $paste->slug]),
        'isAccessibleForFree' => true,
        'isPartOf' => [
            '@type' => 'WebSite',
            'name' => 'Snippy',
            'url' => route('pastes.index'),
        ],
    ],
])

@section('content')
<article class="panel mx-auto max-w-4xl">
    <div class="flex flex-wrap items-start justify-between gap-4 border-b border-[var(--line)] pb-6">
        <div>
            <p class="eyebrow">Published post</p>
            <h1 class="mt-2 text-4xl font-semibold tracking-tight">{{ $paste->published_title ?: 'Untitled' }}</h1>
            <p class="mt-3 text-sm text-[var(--muted)]">
                Published {{ optional($paste->published_at)->diffForHumans() }}
                @if ($paste->expires_at)
                    · Expires {{ $paste->expires_at->diffForHumans() }}
                @endif
                @if ($paste->isProtected())
                    · Password protected
                @endif
            </p>
        </div>

        <div class="flex flex-wrap gap-2">
            <textarea id="article-markdown" hidden readonly>{{ $paste->published_content }}</textarea>
            <button class="btn btn-secondary" type="button" data-copy-target="article-markdown">Copy article</button>
            <a href="{{ route('pastes.raw', ['paste' => $paste->slug]) }}" class="btn btn-secondary" target="_blank" rel="noreferrer">Raw markdown</a>
            <input id="public-link" class="link-field w-full min-w-72" type="text" readonly value="{{ route('pastes.show', ['paste' => $paste->slug]) }}">
            <button class="btn btn-primary" type="button" data-copy-target="public-link">Copy link</button>
        </div>
    </div>

    @if ($paste->published_tag_list)
        <div class="mt-5 flex flex-wrap gap-2">
            @foreach ($paste->published_tag_list as $tag)
                <a href="{{ route('pastes.explore', ['tag' => $tag]) }}" class="tag-chip">#{{ $tag }}</a>
            @endforeach
        </div>
    @endif

    <div class="markdown-body mt-8">
        {!! $paste->published_rendered_content !!}
    </div>
</article>
@endsection
