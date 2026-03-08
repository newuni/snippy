@extends('pastes.layout', ['title' => ($paste->title ?: 'Untitled Draft') . ' - Manage - Snippy'])

@section('content')
<section class="grid gap-6 xl:grid-cols-[1.15fr_0.85fr]" data-editor-root>
    <div class="panel">
        <div class="flex flex-wrap items-start justify-between gap-4 border-b border-[var(--line)] pb-6">
            <div>
                <p class="eyebrow">Private manage URL</p>
                <h1 class="mt-2 text-3xl font-semibold">
                    {{ $paste->isPublished() ? 'Edit published post' : 'Edit private draft' }}
                </h1>
                <p class="mt-3 max-w-2xl text-sm text-[var(--muted)]" data-save-status>
                    @if ($paste->last_autosaved_at)
                        Last autosaved {{ $paste->last_autosaved_at->diffForHumans() }}.
                    @else
                        Autosave is ready.
                    @endif
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                <span class="pill" data-visibility-pill>{{ $paste->isPublished() ? 'Published' : 'Draft' }}</span>
                @if ($paste->hasUnpublishedChanges())
                    <span class="pill pill-warn">Unpublished changes</span>
                @endif
            </div>
        </div>

        <div class="mt-6 grid gap-4 md:grid-cols-2">
            <div>
                <label class="label" for="manage-link">Manage link</label>
                <div class="flex gap-2">
                    <input id="manage-link" class="link-field" type="text" readonly value="{{ route('pastes.manage', ['paste' => $paste->manage_token]) }}">
                    <button type="button" class="btn btn-secondary shrink-0" data-copy-target="manage-link">Copy</button>
                </div>
            </div>
            <div>
                <label class="label" for="public-link">Public link</label>
                <div class="flex gap-2">
                    <input id="public-link" class="link-field" type="text" readonly value="{{ $paste->isPublished() ? route('pastes.show', ['paste' => $paste->slug]) : 'Not published yet' }}" data-public-link>
                    <button type="button" class="btn btn-secondary shrink-0" data-copy-target="public-link">Copy</button>
                </div>
            </div>
        </div>

        <form id="clear-password-form" action="{{ route('pastes.password.clear', ['paste' => $paste->manage_token]) }}" method="POST" class="hidden">
            @csrf
            @method('DELETE')
        </form>

        <form id="unpublish-form" action="{{ route('pastes.unpublish', ['paste' => $paste->manage_token]) }}" method="POST" class="hidden">
            @csrf
        </form>

        <form
            action="{{ route('pastes.publish', ['paste' => $paste->manage_token]) }}"
            method="POST"
            class="mt-8 space-y-5"
            data-editor-form
            data-autosave-url="{{ route('pastes.autosave', ['paste' => $paste->manage_token]) }}"
        >
            @csrf

            <div>
                <label class="label" for="title">Title</label>
                <input id="title" class="input-field" type="text" name="title" value="{{ old('title', $paste->title) }}" maxlength="255" placeholder="Untitled until you need a title">
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="label" for="tags">Tags</label>
                    <input id="tags" class="input-field" type="text" name="tags" value="{{ old('tags', $paste->tags) }}" placeholder="markdown, docs, release-notes">
                </div>

                <div>
                    <label class="label" for="expiration_option">Expiration</label>
                    <select id="expiration_option" class="input-field" name="expiration_option">
                        @foreach ($expirationOptions as $value => $label)
                            <option value="{{ $value }}" @selected(old('expiration_option', $paste->expiration_option) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-[1fr_auto]">
                <div>
                    <label class="label" for="password">Public password</label>
                    <input id="password" class="input-field" type="password" name="password" autocomplete="new-password" minlength="4" maxlength="100" placeholder="{{ $paste->isProtected() ? 'Leave blank to keep current password' : 'Optional password for public readers' }}">
                    <p class="mt-2 text-xs text-[var(--muted)]" data-password-status>
                        {{ $paste->isProtected() ? 'A public password is active. Leave this blank to keep it unchanged.' : 'Optional. Drafts are already private by default.' }}
                    </p>
                </div>

                @if ($paste->isProtected())
                    <div class="flex items-end">
                        <button type="submit" form="clear-password-form" class="btn btn-secondary">Clear password</button>
                    </div>
                @endif
            </div>

            <div>
                <label class="label" for="content">Markdown</label>
                <textarea id="content" class="editor-field" name="content" rows="22" placeholder="# Start writing">{{ old('content', $paste->content) }}</textarea>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <button type="submit" class="btn btn-primary" data-publish-button>
                    {{ $paste->isPublished() ? 'Publish updates' : 'Publish post' }}
                </button>
                @if ($paste->status === 'published')
                    <button type="submit" form="unpublish-form" class="btn btn-secondary">Move back to draft</button>
                @endif
                <a href="{{ route('pastes.draft.raw', ['paste' => $paste->manage_token]) }}" class="btn btn-secondary" target="_blank" rel="noreferrer">Draft raw</a>
                @if ($paste->isPublished())
                    <a href="{{ route('pastes.raw', ['paste' => $paste->slug]) }}" class="btn btn-secondary" target="_blank" rel="noreferrer">Public raw</a>
                @endif
            </div>
        </form>
    </div>

    <aside class="panel preview-panel">
        <div class="border-b border-[var(--line)] pb-5">
            <p class="eyebrow">Live preview</p>
            <h2 class="mt-2 text-2xl font-semibold">Server-rendered markdown</h2>
            <p class="mt-3 text-sm text-[var(--muted)]">Preview updates after each autosave, using the same renderer as the public page.</p>
        </div>

        <div class="markdown-body mt-6" data-preview>
            {!! $paste->rendered_content !!}
        </div>
    </aside>
</section>
@endsection
