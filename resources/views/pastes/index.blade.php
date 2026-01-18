@extends('pastes.layout')

@section('content')
<div class="text-center mb-10">
    <h1 class="text-3xl font-bold mb-2">Snippy</h1>
    <p class="text-gray-400">Share code snippets and text quickly</p>
</div>

<div class="bg-gray-800 rounded-xl p-6 mb-8">
    <h2 class="text-lg font-semibold mb-4 text-gray-300">Recent Pastes</h2>
    
    @forelse($pastes as $paste)
        <a href="{{ route('pastes.show', $paste) }}" 
           class="block bg-gray-700/50 hover:bg-gray-700 rounded-lg p-4 mb-3 transition">
            <div class="flex justify-between items-start">
                <div>
                    <span class="font-medium text-indigo-400">
                        {{ $paste->title ?: 'Untitled' }}
                    </span>
                    <span class="text-gray-500 text-sm ml-2">{{ $paste->slug }}</span>
                </div>
                <span class="text-xs px-2 py-1 bg-gray-600 rounded">{{ $paste->syntax }}</span>
            </div>
            <p class="text-gray-400 text-sm mt-2 truncate">
                {{ Str::limit($paste->content, 100) }}
            </p>
            <span class="text-gray-500 text-xs mt-2 block">
                {{ $paste->created_at->diffForHumans() }}
                @if($paste->expires_at)
                    · Expires {{ $paste->expires_at->diffForHumans() }}
                @endif
            </span>
        </a>
    @empty
        <p class="text-gray-500 text-center py-8">No pastes yet. Create the first one!</p>
    @endforelse
</div>
@endsection
