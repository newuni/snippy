@extends('pastes.layout', ['title' => ($paste->title ?: 'Untitled') . ' - Pastebin'])

@section('content')
<div class="bg-gray-800 rounded-xl overflow-hidden">
    <div class="p-4 border-b border-gray-700 flex justify-between items-center">
        <div>
            <h1 class="text-xl font-bold">{{ $paste->title ?: 'Untitled' }}</h1>
            <p class="text-gray-400 text-sm mt-1">
                Created {{ $paste->created_at->diffForHumans() }}
                @if($paste->expires_at)
                    · Expires {{ $paste->expires_at->diffForHumans() }}
                @endif
            </p>
        </div>
        <div class="flex gap-2">
            <span class="text-xs px-3 py-1 bg-gray-600 rounded-full">{{ $paste->syntax }}</span>
            <a href="{{ route('pastes.raw', $paste) }}" 
               class="text-xs px-3 py-1 bg-gray-600 hover:bg-gray-500 rounded-full transition"
               target="_blank">
                Raw
            </a>
            <button onclick="copyToClipboard()" 
                    class="text-xs px-3 py-1 bg-indigo-600 hover:bg-indigo-500 rounded-full transition">
                Copy
            </button>
        </div>
    </div>

    <div class="relative">
        <pre class="!m-0 !rounded-none"><code id="paste-content" class="language-{{ $paste->syntax }}">{{ $paste->content }}</code></pre>
    </div>
</div>

<div class="mt-4 text-center">
    <span class="text-gray-500 text-sm">Share: </span>
    <input type="text" readonly value="{{ route('pastes.show', $paste) }}" 
           class="bg-gray-700 border border-gray-600 rounded px-3 py-1 text-sm w-96 text-center"
           onclick="this.select()">
</div>

<script>
function copyToClipboard() {
    const content = document.getElementById('paste-content').textContent;
    navigator.clipboard.writeText(content).then(() => {
        alert('Copied to clipboard!');
    });
}
</script>
@endsection
