# Changelog

## [Unreleased]

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
