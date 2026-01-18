@extends('pastes.layout')

@section('content')
<div class="bg-gray-800 rounded-xl p-6">
    <h1 class="text-2xl font-bold mb-6">New Paste</h1>

    <form action="{{ route('pastes.store') }}" method="POST">
        @csrf

        <div class="mb-4">
            <label for="title" class="block text-sm font-medium text-gray-400 mb-2">Title (optional)</label>
            <input type="text" name="title" id="title" value="{{ old('title') }}"
                   class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                   placeholder="My awesome code">
            @error('title')
                <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label for="content" class="block text-sm font-medium text-gray-400 mb-2">Content</label>
            <textarea name="content" id="content" rows="15" required
                      class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 font-mono text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                      placeholder="Paste your code or text here...">{{ old('content') }}</textarea>
            @error('content')
                <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid grid-cols-3 gap-4 mb-6">
            <div>
                <label for="syntax" class="block text-sm font-medium text-gray-400 mb-2">Syntax</label>
                <select name="syntax" id="syntax"
                        class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500">
                    <option value="plaintext">Plain Text</option>
                    <option value="php">PHP</option>
                    <option value="javascript">JavaScript</option>
                    <option value="python">Python</option>
                    <option value="sql">SQL</option>
                    <option value="json">JSON</option>
                    <option value="html">HTML</option>
                    <option value="css">CSS</option>
                    <option value="bash">Bash</option>
                    <option value="yaml">YAML</option>
                    <option value="markdown">Markdown</option>
                </select>
            </div>

            <div>
                <label for="expiration" class="block text-sm font-medium text-gray-400 mb-2">Expiration</label>
                <select name="expiration" id="expiration"
                        class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500">
                    <option value="never">Never</option>
                    <option value="10m">10 Minutes</option>
                    <option value="1h">1 Hour</option>
                    <option value="1d">1 Day</option>
                    <option value="1w">1 Week</option>
                    <option value="1M">1 Month</option>
                </select>
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-400 mb-2">Password (optional)</label>
                <input type="password" name="password" id="password"
                       class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500"
                       placeholder="🔒 Private">
            </div>
        </div>

        <button type="submit" 
                class="w-full bg-indigo-600 hover:bg-indigo-700 py-3 rounded-lg font-medium transition">
            Create Paste
        </button>
    </form>
</div>
@endsection
