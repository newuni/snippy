@extends('pastes.layout', ['title' => 'Explore - Snippy'])

@section('content')
<section class="panel">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="eyebrow">Explore</p>
            <h1 class="mt-2 text-4xl font-semibold tracking-tight">Published markdown posts</h1>
            <p class="mt-3 max-w-2xl text-sm text-[var(--muted)]">Search title, markdown body, or tags. Password-protected posts are listed here only if they were explicitly published.</p>
        </div>

        <form action="{{ route('pastes.explore') }}" method="GET" class="flex w-full max-w-2xl flex-col gap-3 md:flex-row">
            <input class="input-field flex-1" type="search" name="q" value="{{ $search }}" placeholder="Search posts">
            <input class="input-field md:max-w-48" type="text" name="tag" value="{{ $tag }}" placeholder="Filter tag">
            <button type="submit" class="btn btn-primary">Search</button>
        </form>
    </div>

    @if ($topTags->isNotEmpty())
        <div class="mt-6 flex flex-wrap gap-2">
            @foreach ($topTags as $topTag)
                <a href="{{ route('pastes.explore', ['tag' => $topTag]) }}" class="tag-chip">#{{ $topTag }}</a>
            @endforeach
        </div>
    @endif
</section>

<section class="mt-8 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
    @forelse ($posts as $post)
        <article class="card">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <a href="{{ route('pastes.show', ['paste' => $post->slug]) }}" class="text-xl font-semibold hover:text-[var(--accent)]">
                        {{ $post->published_title ?: 'Untitled' }}
                    </a>
                    <p class="mt-2 text-sm text-[var(--muted)]">
                        Published {{ optional($post->published_at)->diffForHumans() }}
                        @if ($post->expires_at)
                            · Expires {{ $post->expires_at->diffForHumans() }}
                        @endif
                    </p>
                </div>
                @if ($post->isProtected())
                    <span class="pill">Protected</span>
                @endif
            </div>

            <p class="mt-4 text-sm leading-6 text-[var(--muted)]">{{ $post->excerpt(170) }}</p>

            @if ($post->published_tag_list)
                <div class="mt-4 flex flex-wrap gap-2">
                    @foreach ($post->published_tag_list as $publishedTag)
                        <a href="{{ route('pastes.explore', ['tag' => $publishedTag]) }}" class="tag-chip">#{{ $publishedTag }}</a>
                    @endforeach
                </div>
            @endif
        </article>
    @empty
        <div class="panel md:col-span-2 xl:col-span-3">
            <p class="text-[var(--muted)]">No published posts matched this filter.</p>
        </div>
    @endforelse
</section>

<div class="mt-8">
    {{ $posts->links() }}
</div>
@endsection
