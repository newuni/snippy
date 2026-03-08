<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Snippy' }}</title>
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
                <a href="{{ route('pastes.create') }}" class="btn btn-primary">New draft</a>
            </div>
        </header>

        <main class="mx-auto w-full max-w-7xl px-5 py-8 sm:px-8">
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
    </div>
</body>
</html>
