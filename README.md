# Snippy

Snippy is a small, self-hosted Markdown publisher built around a simple idea: write privately, then publish a deliberate snapshot when it is ready.

Each draft has an unguessable management URL for editing and a separate public URL for readers. Published posts support search, tags, optional passwords, expiration, raw Markdown, content negotiation, and machine-readable discovery.

## Features

- Private-by-default drafts with debounced autosave and server-rendered preview
- Explicit publishing and unpublishing, with public snapshots that change only when republished
- Separate management (`/manage/{token}`) and public (`/p/{slug}`) URLs
- Full Markdown and rendered-body clipboard actions on published posts
- Explore page with search and tag filters
- Optional public passwords and expiration rules
- HTML, Markdown, plain-text, and JSON representations
- `llms.txt`, `llms-full.txt`, `agents.txt`, `robots.txt`, and sitemap discovery

Password-protected posts are excluded from Home, Explore, sitemap, and agent feeds. Draft creation is POST-only, rate-limited, and intended for people using the browser interface.

## Quick start with Docker Compose

Requirements: Docker with Compose v2.

```bash
git clone https://github.com/newuni/snippy.git
cd snippy
cp .env.docker.example .env
```

Generate an application key, choose a database password, and set them in `.env`:

```bash
docker run --rm php:8.4-cli php -r "echo 'base64:'.base64_encode(random_bytes(32)).PHP_EOL;"
```

Then start Snippy:

```bash
docker compose up -d --build
```

The application listens on `127.0.0.1:${APP_PORT:-8081}` by default. PostgreSQL is available only on the private Compose network and uses the mounted SCRAM authentication policy in `docker/postgres/pg_hba.conf`, including for existing database volumes.

## Reverse proxy

Keep the application bound to loopback and terminate HTTPS in a reverse proxy. A generic Caddy configuration is available at [`deploy/caddy/Caddyfile.example`](deploy/caddy/Caddyfile.example):

```caddyfile
snippy.example.com {
    encode zstd gzip
    reverse_proxy 127.0.0.1:8081

    header {
        Referrer-Policy strict-origin-when-cross-origin
        Strict-Transport-Security "max-age=31536000"
        X-Content-Type-Options nosniff
        X-Frame-Options SAMEORIGIN
    }
}
```

Set `APP_URL=https://snippy.example.com` in production. Snippy derives secure session cookies from an HTTPS `APP_URL`; `SESSION_SECURE_COOKIE=true` can enforce the setting explicitly.

## Optional instance identity

The public repository is deployment-neutral. An operator can still connect an instance to a parent website and its domain-level crawler policy through environment variables:

```dotenv
SNIPPY_PARENT_SITE_NAME=Example
SNIPPY_PARENT_SITE_URL=https://example.com/
SNIPPY_ROOT_ROBOTS_URL=https://example.com/robots.txt
SNIPPY_ALLOW_AI_TRAINING=false
```

When the parent name and URL are omitted, the footer and structured data contain no parent-site branding. When the root robots URL is omitted, agent guidance points to Snippy's own `robots.txt`.

## Public routes

- `POST /new` creates a private draft after an explicit browser action
- `GET /manage/{manage_token}` opens the private editor
- `PUT /manage/{manage_token}/autosave` stores draft changes
- `POST /manage/{manage_token}/publish` publishes a snapshot
- `POST /manage/{manage_token}/unpublish` returns a post to draft state
- `GET /p/{slug}` serves a published post
- `GET /p/{slug}/raw` serves its raw Markdown when publicly accessible
- `GET /explore` searches and filters published posts
- `GET /llms.txt` describes safe machine-readable access
- `GET /llms-full.txt` streams the public, unprotected Markdown corpus
- `GET /agents.txt` declares crawler and agent boundaries
- `GET /robots.txt` and `GET /sitemap.xml` expose discovery policy and URLs

## Security model

Management URLs are bearer capabilities: anyone who has one can edit and publish that draft. Do not put management URLs in public pages, logs, tickets, analytics, or crawler indexes.

Runtime secrets belong in an untracked `.env` or an external secret store. Snippy's Docker context excludes environment variants, Composer/npm credentials, private keys, and certificate bundles. The checked-in example files contain placeholders only.

See [`SECURITY.md`](SECURITY.md) for supported versions and private vulnerability reporting.

## Development and testing

```bash
composer install
npm ci
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan test
npm run build
```

For the browser flow:

```bash
npx playwright install chromium
npm run test:e2e
```

The end-to-end scenario covers draft creation, autosave preview, publication, and Explore visibility. Release history is recorded in [`CHANGELOG.md`](CHANGELOG.md).
