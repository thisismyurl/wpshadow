# WP Shadow

WP Shadow is a local-first WordPress diagnostics and remediation plugin built to help site owners understand what matters, act safely, and recover with confidence.

This repository is the source for the first public beta.

- Version: 0.Yddd
- Status: Public beta
- Requires WordPress: 6.4+
- Requires PHP: 8.1+
- License: GPL v2 or later
- Last updated: April 5, 2026

## What Ships Today

WP Shadow currently exposes:

- 230 display-ready diagnostics across 11 categories via `Diagnostic_Registry::get_diagnostic_definitions()`.
- 101 executable treatment classes via `Treatment_Registry::get_all()`.
- 93 automated treatments and 8 guidance-only treatment entries via `Treatment_Metadata::get_counts()`.
- dashboard, findings, and WordPress Site Health integration.
- file-write review, local backup, and recovery workflows.
- activity logging, KPI tracking, and multisite-aware admin behavior.
- top-level runtime wrappers and WP-CLI commands for diagnostics, scans, treatments, and readiness export.

The plugin is built around a few non-negotiable ideas:

- advice instead of pressure
- accessibility as a product requirement
- safe-by-default workflows
- plain-English explanations
- no required cloud dependency for core functionality

## Beta Scope

This beta is focused on the core plugin experience.

Included in the current beta:

- local diagnostics and findings management
- remediation workflows with apply, undo, review, and rollback guidance
- backup and restore safeguards for riskier operations
- WordPress Site Health and dashboard reporting
- WP-CLI coverage for common diagnostic and treatment workflows
- accessibility-first admin copy and lower-stress recovery paths

Not part of the current beta:

- required registration
- paid tiers
- cloud-only features
- telemetry by default

## Quick Start

### Site Owners

1. Install and activate the plugin.
2. Open the WP Shadow dashboard.
3. Review findings by category.
4. Apply safe fixes where appropriate.
5. Use file review or backup workflows before higher-risk changes.

### Contributors

1. Clone the repository.
2. Install Composer dependencies.
3. Read the philosophy and feature inventory before changing behavior or copy.
4. Run the available tests before opening a pull request.

```bash
git clone https://github.com/thisismyurl/wpshadow.git
cd wpshadow
composer install
composer test:smoke
composer test:phpunit
```

If your environment needs an explicit PHP binary for PHPUnit:

```bash
php8.3 ./vendor/bin/phpunit --configuration phpunit.xml.dist
```

### WP-CLI

When WP-CLI is available, WP Shadow registers commands for:

- `wp wpshadow diagnostics list`
- `wp wpshadow diagnostics run <diagnostic>`
- `wp wpshadow scan run`
- `wp wpshadow treatments list`
- `wp wpshadow treatments apply <finding>`
- `wp wpshadow readiness export`

## Documentation Map

Start with these documents when evaluating or contributing:

- [docs/CORE_PHILOSOPHY.md](docs/CORE_PHILOSOPHY.md)
- [docs/FEATURES.md](docs/FEATURES.md)
- [docs/INDEX.md](docs/INDEX.md)
- [docs/CONTRIBUTING.md](docs/CONTRIBUTING.md)
- [docs/SUPPORT.md](docs/SUPPORT.md)
- [docs/DONATE.md](docs/DONATE.md)
- [docs/SECURITY.md](docs/SECURITY.md)

## Source Of Truth

Public documentation should treat these as the authoritative count sources:

- the live inventory returned by `Diagnostic_Registry::get_diagnostic_definitions()`
- the treatment counts returned by `Treatment_Metadata::get_counts()`
- [docs/FEATURES.md](docs/FEATURES.md)

Planning notes, archived reports, and placeholder code should not be used for headline totals.

## Accessibility And Privacy

WP Shadow is built for people who use keyboards, screen readers, zoom, reduced motion, simpler language, and lower-stress workflows. The docs should help a busy site owner understand what a finding means, what happens next, and how to recover if something goes wrong.

WP Shadow runs locally. The current beta does not require an account, does not require cloud infrastructure, and should not make unexpected third-party requests.

See [docs/ACCESSIBILITY.md](docs/ACCESSIBILITY.md), [docs/PRIVACY.md](docs/PRIVACY.md), and [docs/BUSINESS_MODEL.md](docs/BUSINESS_MODEL.md).

## License

GPL v2 or later.

---

## About This Is My URL

This plugin is built and maintained by [This Is My URL](https://thisismyurl.com/), a WordPress development and technical SEO practice with more than 25 years of experience helping organizations build practical, maintainable web systems.

Christopher Ross ([@thisismyurl](https://profiles.wordpress.org/thisismyurl/)) is a WordCamp speaker, plugin developer, and WordPress practitioner based in Fort Erie, Ontario, Canada. Member of the WordPress community since 2007.

### More Resources

- **Plugin page:** [https://wpshadow.com](https://wpshadow.com)
- **WordPress.org profile:** [profiles.wordpress.org/thisismyurl](https://profiles.wordpress.org/thisismyurl/)
- **Other plugins:** [github.com/thisismyurl](https://github.com/thisismyurl)
- **Website:** [thisismyurl.com](https://thisismyurl.com/)

## License

GPL-2.0-or-later — see [LICENSE](LICENSE) or [gnu.org/licenses/gpl-2.0.html](https://www.gnu.org/licenses/gpl-2.0.html).
