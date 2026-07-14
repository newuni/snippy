{!! '<'.'?xml version="1.0" encoding="UTF-8"?'.'>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc>{{ route('pastes.index') }}</loc>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>
    <url>
        <loc>{{ route('pastes.explore') }}</loc>
        <changefreq>daily</changefreq>
        <priority>0.8</priority>
    </url>
    @foreach ($posts as $paste)
        <url>
            <loc>{{ route('pastes.show', ['paste' => $paste->slug]) }}</loc>
            <lastmod>{{ optional($paste->published_at)->toAtomString() }}</lastmod>
            <changefreq>weekly</changefreq>
            <priority>0.7</priority>
        </url>
    @endforeach
</urlset>
