@extends('pastes.layout', ['title' => 'Protected Snippet - Snippy'])

@section('content')
<div class="max-w-md mx-auto">
    <div class="bg-gray-800 rounded-xl p-8 text-center">
        <div class="text-5xl mb-4">🔒</div>
        <h1 class="text-2xl font-bold mb-2">Protected Snippet</h1>
        <p class="text-gray-400 mb-6">This snippet is password protected.</p>

        <form action="{{ route('pastes.unlock', $paste) }}" method="POST">
            @csrf

            <div class="mb-4">
                <input type="password" name="password" required autofocus
                       class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 text-center focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                       placeholder="Enter password">
                @error('password')
                    <p class="text-red-400 text-sm mt-2">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" 
                    class="w-full bg-indigo-600 hover:bg-indigo-700 py-3 rounded-lg font-medium transition">
                Unlock
            </button>
        </form>
    </div>
</div>
@endsection
