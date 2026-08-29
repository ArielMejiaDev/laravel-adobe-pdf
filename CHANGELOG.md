# Changelog

All notable changes to `laravel-adobe-pdf` will be documented in this file.

## 1.1.0 - 2026-08-29

- Lower the minimum PHP requirement to **8.3** (`php: ^8.3`). No code changes were required — the package only uses PHP 8.1+ features.
- Add PHP 8.3 back to the test matrix.

## 1.0.0 - 2026-08-28

Initial release.

- Fluent, async-first wrapper around the Adobe PDF Services API.
- Operations: Create PDF, Extract PDF, Compress PDF, Combine PDF, Document Generation, HTML to PDF and Watermark.
- Queued `ProcessAdobePdfOperation` job with non-blocking polling (release-based), plus a synchronous `dispatchSync()` escape hatch.
- Per-operation tracking via the `adobe_pdf_processes` table (status, Adobe error payload, output path).
- Cached OAuth Server-to-Server token management and a typed exception hierarchy (`AdobePdfException`, `AuthenticationException`, `RateLimitException`, `OperationFailedException`, `TimeoutException`, `AssetUploadException`).
- Full Pest test suite built on mocked HTTP, plus a VitePress documentation site published to GitHub Pages.
