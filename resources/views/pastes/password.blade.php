@extends('pastes.layout', ['title' => 'Protected Post - Snippy'])

@section('content')
<div class="mx-auto max-w-md">
    <div class="panel text-center">
        <p class="eyebrow">Public password required</p>
        <h1 class="mt-3 text-3xl font-semibold">Unlock this post</h1>
        <p class="mt-3 text-sm text-[var(--muted)]">This published page is protected with a password.</p>

        <form action="{{ route('pastes.unlock', ['paste' => $paste->slug]) }}" method="POST" class="mt-8 space-y-4">
            @csrf

            <input type="password" name="password" required autofocus class="input-field text-center" placeholder="Enter password">

            <button type="submit" class="btn btn-primary w-full">
                Unlock
            </button>
        </form>
    </div>
</div>
@endsection
