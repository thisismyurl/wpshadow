# Changelog

All notable changes to WPShadow are documented here.

This project aims to follow the spirit of **Keep a Changelog**, while keeping entries readable for both technical and non-technical users.

---

## [Unreleased]

### Added
- New public-facing documentation for contributing, support, security, privacy, sponsorship, and next-step planning
- A formal documentation index at `docs/INDEX.md`
- GitHub issue and pull request templates to improve community collaboration
- Migrated site content models into WP Shadow with 9 custom post types and source-parity taxonomy registrations for case studies, portfolio, testimonials, training, downloads, tools, and FAQs
- New WP Shadow Post Types admin screen with per-CPT cards, scoped feature toggles, and generated implementation snippets

### Changed
- Public documentation now uses a clearer source of truth for shipped feature counts and project status
- Community-facing project guidance now more explicitly reflects the Helpful Neighbor philosophy
- Hardened migrated content model bootstrapping with rewrite-version safety and added PHPUnit coverage for post type and taxonomy registration integrity
- Added runtime scoped CPT/taxonomy override filters driven by the new Post Types feature settings

---

## [0.6095] - 2026-04-05

### Changed
- Aligned release metadata across plugin headers, stable tags, and distributable documentation
- Normalized future-dated `@since` annotations to the current release version
- Improved dashboard detail routing and linked the WordPress gauge to native Site Health
- Hardened bootstrap and admin menu loading to reduce recent startup regressions
- Kept release packaging and validation safeguards in place so shipped metadata stays synchronized

---

## [0.6035] - 2026-02-04

### Added
- Accessibility and inclusivity improvements aligned with the CANON pillars
- Documentation cleanup and reorganization into a more curated structure
- Expanded diagnostic coverage and production-release readiness work

### Changed
- Continued release hardening and community-readiness improvements

---

## [0.6030] - 2026-01-30

### Added
- Initial development release
- Early diagnostic and dashboard foundation

---

## Notes

For WordPress.org-specific release notes, see:
- [`readme.txt`](readme.txt)

For current project direction, see:
- [`docs/MILESTONES.md`](docs/MILESTONES.md)
- [`docs/NEXT_STEPS.md`](docs/NEXT_STEPS.md)
