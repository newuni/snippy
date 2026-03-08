# ✂️ Snippy v1

Draft-first markdown publishing for internal notes and public posts.

## What changed in v1

- ✍️ **Markdown editor + live preview** (server-rendered markdown)
- 💾 **Autosave while typing** (debounced, private draft updates)
- 🔒 **Private-by-default drafts** via unguessable manage link
- 🚀 **Explicit publish action** to push a snapshot public
- 🔗 **Two links per post**
  - **Manage link**: private editing URL (`/manage/{manage_token}`)
  - **Public link**: reader URL (`/p/{slug}`)
- 🌍 **Explore page** for published posts (`/explore`) with search + tag filter
- 🧷 Optional public password + expiration rules preserved

## Routes (v1)

- `GET /new` → creates draft + redirects to manage URL
- `GET /manage/{manage_token}` → editor
- `PUT /manage/{manage_token}/autosave` → autosave endpoint
- `POST /manage/{manage_token}/publish` → publish snapshot
- `POST /manage/{manage_token}/unpublish` → set back to draft
- `GET /p/{slug}` → public page
- `GET /explore` → published index/search/tags

## Quick start (Docker Compose)

```bash
git clone https://github.com/newuni/snippy.git
cd snippy
cp .env.docker.example .env
# set APP_KEY, DB_PASSWORD, APP_URL

docker compose up -d --build
```

By default app listens on `127.0.0.1:${APP_PORT:-8081}`.

## Internal deployment with Caddy (newuni.org)

Recommended host: **`snippy.newuni.org`**

1) Keep Snippy private on loopback:
- Docker maps app to `127.0.0.1:8081`

2) Add Caddy site block:

```caddyfile
snippy.newuni.org {
    encode zstd gzip
    reverse_proxy 127.0.0.1:8081
    header {
        Referrer-Policy strict-origin-when-cross-origin
        X-Content-Type-Options nosniff
        X-Frame-Options SAMEORIGIN
    }
}
```

3) Reload Caddy:

```bash
sudo caddy validate --config /etc/caddy/Caddyfile
sudo systemctl reload caddy
```

## Testing

### PHP tests

```bash
php artisan test
```

If local PHP misses sqlite driver, run tests inside app container configured with PostgreSQL.

### Playwright e2e

```bash
npm install
npx playwright install chromium
npm run test:e2e
```

E2E covers: draft creation → autosave preview → publish → explore visibility.

## Release notes

See [CHANGELOG.md](./CHANGELOG.md).
