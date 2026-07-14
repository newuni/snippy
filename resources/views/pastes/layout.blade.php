<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="{{ $description ?? 'Draft-first Markdown publishing with private management and public reading URLs.' }}">
    <meta name="robots" content="{{ $robots ?? 'index, follow' }}">
    <title>{{ $title ?? 'Snippy' }}</title>
    @isset($canonical)
        <link rel="canonical" href="{{ $canonical }}">
        <meta property="og:url" content="{{ $canonical }}">
    @endisset
    <meta property="og:type" content="{{ $openGraphType ?? 'website' }}">
    <meta property="og:site_name" content="Snippy">
    <meta property="og:title" content="{{ $title ?? 'Snippy' }}">
    <meta property="og:description" content="{{ $description ?? 'Draft-first Markdown publishing with private management and public reading URLs.' }}">
    <link rel="alternate" type="text/plain" title="Snippy agent guide" href="{{ route('agent.llms') }}">
    @isset($alternateMarkdown)
        <link rel="alternate" type="text/markdown" title="Raw Markdown" href="{{ $alternateMarkdown }}">
    @endisset
    @isset($structuredData)
        <script type="application/ld+json">{{ Illuminate\Support\Js::from($structuredData) }}</script>
    @endisset
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[var(--paper)] text-[var(--ink)]">
    <div class="page-shell">
        <header class="border-b border-[var(--line)] bg-white/70 backdrop-blur">
            <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-5 py-4 sm:px-8">
                <div class="flex items-center gap-8">
                    <a href="{{ route('pastes.index') }}" class="brand-mark">Snippy</a>
                    <nav class="hidden items-center gap-5 text-sm font-medium text-[var(--muted)] sm:flex">
                        <a href="{{ route('pastes.index') }}" class="hover:text-[var(--ink)]">Home</a>
                        <a href="{{ route('pastes.explore') }}" class="hover:text-[var(--ink)]">Explore</a>
                    </nav>
                </div>
                <form action="{{ route('pastes.create') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-primary">New draft</button>
                </form>
            </div>
        </header>

        <main class="mx-auto w-full max-w-7xl flex-1 px-5 py-8 sm:px-8">
            @if (session('success'))
                <div class="notice notice-success mb-6">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div class="notice notice-error mb-6">
                    {{ $errors->first() }}
                </div>
            @endif

            @yield('content')
        </main>

        <footer class="border-t border-[var(--line)] bg-white/45">
            <div class="mx-auto flex w-full max-w-7xl flex-col gap-2 px-5 py-5 text-sm text-[var(--muted)] sm:flex-row sm:items-center sm:justify-between sm:px-8">
                <p>Snippy · public Markdown with private draft management.</p>
                <a href="https://newuni.org/" class="font-semibold text-[var(--accent)] hover:text-[var(--accent-strong)]">Visit newuni.org</a>
            </div>
        </footer>
    </div>
</body>
</html>
