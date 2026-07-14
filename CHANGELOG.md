# Changelog

## [Unreleased]

## [1.1.0] - 2026-07-14

### Added
- Added clipboard actions for public URLs, full Markdown source, and the rendered article body.
- Added machine-readable discovery through `llms.txt`, `llms-full.txt`, `agents.txt`, `robots.txt`, content negotiation, JSON-LD, and sitemap metadata.
- Added optional parent-site branding and domain-level crawler policy through environment configuration.

### Changed
- Upgraded the application to Laravel 13, Tinker 3, PHPUnit 12, Vite 8, Tailwind CSS 4.3, and the latest compatible Composer/npm dependencies.
- Raised the supported PHP baseline to 8.3 and the Node.js baseline to 22.12.
- Made Docker builds reproducible with `npm ci` and removed unused Sail and Axios dependencies.
- Reworked the landing-page copy, editor link layout, and Markdown list styling.
- Made the README and reverse-proxy example deployment-neutral.
- Streamed aggregate agent and sitemap responses and bounded Explore tag aggregation to the columns it needs.

### Fixed
- Retry title-based slug allocation when concurrent publishes race for the same unique slug.
- Avoid passing a null application URL to `rtrim` during configuration loading.
- Preserve generated URL visibility in the editor at narrow widths.
- Restore ordered, unordered, and nested list markers in rendered Markdown.

### Security
- Disabled cache object unserialization and refreshed lock files until both Composer and npm audits report no known vulnerabilities.
- Excluded secret-bearing environment, credential, key, and certificate files from Docker build contexts.
- Required PostgreSQL SCRAM password authentication on the Compose network, including existing database volumes.
- Enabled secure session cookies automatically for HTTPS application URLs.
- Added application rate limits for anonymous draft creation, management writes, protected-post password attempts, and expensive public aggregates.
- Raised the minimum password length for newly protected posts from four to eight characters.

## [1.0.0] - 2026-03-08

### Added
- Draft-first markdown workflow with private manage URLs (`/manage/{token}`)
- Public publishing URLs (`/p/{slug}`) with explicit publish action
- Debounced autosave API + live server-rendered markdown preview
- Explore page with search and tag filtering (`/explore`)
- Markdown helper utility (`App\Support\MarkdownRenderer`)
- Schema upgrade for publication snapshots, tags, status, and autosave metadata
- Playwright config + e2e scenario for draft→publish flow

### Changed
- Home page redesigned around JotSpot-like draft/publish model
- Existing pastes migrated to published snapshot structure for compatibility
- Tailwind/Vite driven UI (removed CDN-only style setup)

### Kept
- Optional password protection for published pages
- Optional expiration policies for published content

## [2026-02-20] - baseline
- Added LICENSE, SECURITY.md, and repository maturity baseline docs/workflows.
