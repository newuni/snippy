const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

document.querySelectorAll('[data-copy-target]').forEach((button) => {
    button.addEventListener('click', async () => {
        const target = document.getElementById(button.dataset.copyTarget);

        if (!target) {
            return;
        }

        const original = button.textContent;

        try {
            await copyText(target.value);
            button.textContent = 'Copied';
        } catch (error) {
            button.textContent = 'Copy failed';
        }

        window.setTimeout(() => {
            button.textContent = original;
        }, 1200);
    });
});

async function copyText(text) {
    if (navigator.clipboard?.writeText) {
        try {
            await navigator.clipboard.writeText(text);

            return;
        } catch (error) {
            // Fall back for browsers that expose the API but deny permission.
        }
    }

    const textarea = document.createElement('textarea');
    textarea.value = text;
    textarea.setAttribute('readonly', '');
    textarea.style.position = 'fixed';
    textarea.style.opacity = '0';
    document.body.appendChild(textarea);
    textarea.select();

    const copied = document.execCommand('copy');
    textarea.remove();

    if (!copied) {
        throw new Error('Unable to copy text');
    }
}

const editorRoot = document.querySelector('[data-editor-root]');

if (editorRoot) {
    setupEditor(editorRoot);
}

function setupEditor(root) {
    const form = root.querySelector('[data-editor-form]');
    const preview = root.querySelector('[data-preview]');
    const status = root.querySelector('[data-save-status]');
    const publicLink = root.querySelector('[data-public-link]');
    const visibility = root.querySelector('[data-visibility-pill]');
    const passwordStatus = root.querySelector('[data-password-status]');
    const publishButton = root.querySelector('[data-publish-button]');
    const fields = Array.from(form.querySelectorAll('input[name], textarea[name], select[name]'));

    let saveTimer = null;
    let dirty = false;
    let requestNumber = 0;

    const queueSave = () => {
        dirty = true;
        window.clearTimeout(saveTimer);
        status.textContent = 'Unsaved changes...';
        saveTimer = window.setTimeout(() => void autosave(), 700);
    };

    fields.forEach((field) => {
        const eventName = field.tagName === 'SELECT' ? 'change' : 'input';
        field.addEventListener(eventName, queueSave);
    });

    window.addEventListener('beforeunload', (event) => {
        if (!dirty) {
            return;
        }

        void autosave({ keepalive: true });
        event.preventDefault();
        event.returnValue = '';
    });

    async function autosave({ keepalive = false } = {}) {
        const currentRequest = ++requestNumber;
        const payload = Object.fromEntries(new FormData(form).entries());

        status.textContent = 'Saving...';

        try {
            const response = await fetch(form.dataset.autosaveUrl, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify(payload),
                keepalive,
            });

            if (!response.ok) {
                throw new Error(`Autosave failed with ${response.status}`);
            }

            const data = await response.json();

            if (currentRequest !== requestNumber) {
                return;
            }

            dirty = false;
            preview.innerHTML = data.preview_html;
            status.textContent = `Saved ${formatTimestamp(data.saved_at)}`;
            visibility.textContent = data.is_published ? 'Published' : 'Draft';
            publishButton.textContent = data.is_published ? 'Publish updates' : 'Publish post';

            if (publicLink) {
                publicLink.value = data.public_url ?? 'Not published yet';
            }

            const passwordInput = form.querySelector('input[name="password"]');

            if (passwordInput?.value) {
                passwordStatus.textContent = 'Password saved. Leave the field blank unless you want to replace it.';
                passwordInput.value = '';
            }
        } catch (error) {
            dirty = true;
            status.textContent = 'Autosave failed. Changes are still local.';
        }
    }
}

function formatTimestamp(timestamp) {
    const date = new Date(timestamp);

    if (Number.isNaN(date.getTime())) {
        return 'just now';
    }

    return date.toLocaleTimeString([], {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
    });
}
