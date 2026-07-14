# Changelog

## [Unreleased]

### Changed
- Upgraded the application to Laravel 13, Tinker 3, PHPUnit 12, Vite 8, Tailwind CSS 4.3, and the latest compatible Composer/npm dependencies.
- Raised the supported PHP baseline to 8.3 and the Node.js baseline to 22.12.
- Made Docker builds reproducible with `npm ci` and removed unused Sail and Axios dependencies.

### Fixed
- Retry title-based slug allocation when concurrent publishes race for the same unique slug.
- Avoid passing a null application URL to `rtrim` during configuration loading.

### Security
- Disabled cache object unserialization and refreshed lock files until both Composer and npm audits report no known vulnerabilities.

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
