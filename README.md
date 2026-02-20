# ✂️ Snippy

A simple, self-hosted code snippet sharing app. Share code with syntax highlighting, optional password protection, and auto-expiration.

## Features

- 📝 **Share code snippets** — Paste code or text and get a shareable link
- 🎨 **Syntax highlighting** — Support for 11 languages (PHP, JavaScript, Python, SQL, etc.)
- 🔒 **Password protection** — Make snippets private with optional password
- ⏰ **Expiration** — Auto-delete snippets after 10min, 1h, 1d, 1w, or 1 month
- 📋 **Raw view** — Get plain text version for easy copying
- 🌙 **Dark theme** — Easy on the eyes

## Quick Start

```bash
# Clone
git clone https://github.com/newuni/snippy.git
cd snippy

# Configure
cp .env.docker.example .env.docker
# Edit .env.docker with your secrets

# Run
docker compose --env-file .env.docker up -d
```

Access at http://localhost:8081

## Configuration

Edit `.env.docker`:

```env
APP_KEY=base64:your-generated-key-here
APP_URL=http://your-domain.com
APP_PORT=8081

DB_DATABASE=snippy
DB_USERNAME=snippy
DB_PASSWORD=your-secure-password
```

Generate an APP_KEY:
```bash
php artisan key:generate --show
```

## Tech Stack

- **Backend:** Laravel 12 (PHP 8.3)
- **Database:** PostgreSQL 16
- **Frontend:** Tailwind CSS + Highlight.js
- **Deployment:** Docker Compose

## License

MIT (see [`LICENSE`](./LICENSE))


## Third-Party Services & Trademarks

This project uses third-party open-source components (Laravel, PostgreSQL, Docker, Highlight.js).
All trademarks are property of their respective owners; no affiliation/endorsement is implied.


## Responsible Use

Use this software only for lawful purposes.
Do not use it for unauthorized access, abuse, or illegal content distribution.

See [`SECURITY.md`](./SECURITY.md).
