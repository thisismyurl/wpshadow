# WPShadow Documentation

**Your Helpful Neighbor for WordPress Management**

**Version:** 1.26.75000  
**Status:** Active Development (live + stubs)  
**License:** GPL v2+  
**Philosophy:** [Read why we're different →](PRODUCT_PHILOSOPHY.md)

---

## What is WPShadow?

WPShadow is a **free WordPress plugin** that acts like a trusted neighbor who actually knows WordPress. We help you detect issues, fix them automatically, and learn why it matters—all while tracking the value we're delivering.

**Not freemium. Not a trial. Actually free.** Everything that runs on your server is free forever. Only cloud features (that cost us money) have optional paid tiers.

### The Helpful Neighbor Approach

Think of us as the neighbor who:
- Spots the problem ("Your gutters are clogged")
- Fixes it for free ("I brought my ladder")
- Explains why it matters ("Prevents foundation damage")
- Teaches you the skill ("Here's how to check them yourself")
- Mentions good resources ("This is where I got my tools")

We're not selling you something. We're genuinely helping you succeed, then naturally guiding you toward free education that deepens that success.

---

## Quick Start

1. **Install WPShadow** → Run first diagnostic scan
2. **Review findings** → See what issues exist (plain English)
3. **Apply auto-fixes** → Click to fix safely (with backups)
4. **Track your value** → See time saved and improvements
5. **Learn more** → Click KB links for deeper understanding

---

## Why WPShadow is Different

### ✅ Free Forever (Really)
- 57 live diagnostics (security, performance, config checks)
- 44 auto-fix treatments (with backup + undo)
- 95 persona-focused diagnostic stubs staged for upcoming releases
- Kanban board for managing findings
- KPI tracking and value demonstration
- WordPress Site Health integration
- Educational tooltips everywhere

**Only paid:** Cloud scans, email notifications (generous free tiers included)

### 📚 Education-First
Every feature teaches you:
- Plain English explanations (no jargon)
- "Why this matters" context
- Step-by-step fix-it-yourself guides
- Free training courses
- Knowledge base articles

**Goal:** Empower you with knowledge, not just fixes.

### 📊 Proves Its Value
See exactly what we're doing:
- Issues detected and fixed
- Time saved (hours per month)
- Site health improvements (percentage)
- Value delivered ($ equivalent)

**No vague promises.** Real metrics.

### 🎨 Ridiculously Polished
- Better UX than premium plugins
- Modern design that feels expensive
- So intuitive you don't need a manual
- Confidence-inspiring interface

**The bar:** People should question why this is free.

### 🔒 Privacy-First
- No data collection without consent
- Anonymous by default
- Transparent about what we collect
- Easy opt-out anytime
- Complete audit trail

**Standard:** Podcast-worthy privacy practices.

---

## Core Systems (All Free Forever)

### 🔍 Diagnostics (57 Checks)
**What:** Comprehensive site health scans across security, performance, database, updates, code quality, and more.

**Philosophy:** Each diagnostic explains why it matters, shows impact in plain English, and links to free knowledge base articles for learning.

**Examples:**
- Plugin conflicts detection
- Database optimization opportunities
- Security vulnerabilities
- Performance bottlenecks
- WordPress core update status

[View all diagnostics](includes/diagnostics/)

### 🔧 Treatments (44 Auto-Fixes)
**What:** Safe, reversible fixes with automatic backups. One-click or schedule for later.

**Philosophy:** Every treatment explains what it will do, why it's safe, and offers an undo button. You learn while we fix.

**Examples:**
- Optimize database tables
- Fix file permissions
- Clear transient cache
- Update WordPress core
- Enable two-factor authentication

[View all treatments](includes/treatments/)

### ⚙️ Workflows (Automation with Consent)
**What:** Trigger → Action automation. "When X happens, do Y."

**Philosophy:** You control the automation. We suggest smart workflows, explain the reasoning, and always provide manual override.

**Examples:**
- Auto-backup before updates
- Email on failed backups
- Weekly performance reports
- Automatic cache clearing
- Scheduled maintenance tasks

[Learn about workflows](includes/workflows/)

### 💬 Intelligent Tooltips
**What:** Context-sensitive help across all wp-admin pages. Hover for explanations.

**Philosophy:** Teach in context. No hunting for help docs—answers appear where you need them.

**Coverage:**
- 1200+ WordPress admin elements
- Plugin/theme settings explained
- Best practices embedded
- Plain English, no jargon

[Tooltip reference](docs/TOOLTIP_QUICK_REFERENCE.md)

### 📊 Dashboard & Kanban
**What:** Visual health monitoring + Kanban board for managing findings.

**Philosophy:** At-a-glance understanding. Move cards between columns to categorize fixes, trigger workflows, or mark "I'll handle this."

**Features:**
- 11 health gauges (overall + 10 categories)
- Drill-down category dashboards
- Kanban with smart column actions
- KPI tracking (time saved, issues fixed)
- Complete activity history log

[Dashboard layout guide](docs/DASHBOARD_LAYOUT_GUIDE.md)

1. Install WPShadow (via WordPress admin → Plugins → Add New)
2. Activate the plugin
3. Visit **Dashboard → WPShadow** to see your site health
4. Browse findings in Kanban board, move cards to categorize
5. Click any gauge to drill into that category's details

**First-Time Users:** Optional guided tour explains each section. Skip anytime.

**Philosophy:** No forced onboarding, no registration prompts, no upsells. Jump straight to value.

---

## For Developers

### 📁 Documentation

- [**Product Philosophy**](PRODUCT_PHILOSOPHY.md) - Our 11 commandments (start here!)
- [Architecture Overview](ARCHITECTURE.md) - Plugin structure and patterns
- [File Structure Guide](FILE_STRUCTURE_GUIDE.md) - Where to find things
- [Coding Standards](CODING_STANDARDS.md) - Our code style and rules
- [Roadmap](ROADMAP.md) - What's next (Phases 1-8)
- [Testing Setup](TESTING_SETUP.md) - Development environment

### 🏗️ Architecture Highlights

**Hub-and-Spoke Design:**
- Core plugin (hub): wpshadow.php + includes/
- Pro addon (spoke): Separate repository, optional

**Registry Pattern:**
- Diagnostics, treatments, workflows auto-register
- Extensible via filters
- No hardcoded lists

**Multisite-Aware:**
- Network admin contexts respected
- `manage_network_options` vs `manage_options`
- Per-site vs network-wide settings

[Full architecture docs](ARCHITECTURE.md)

---

## Core Features (Technical Detail)

### 🔍 Site Health Diagnostics (57 Checks)

**What it does:** Quick and deep scans detect issues across security, performance, SEO, content, and configuration.

**Philosophy in code:**
- Each diagnostic links to KB article (education-first)
- Plain English descriptions (no jargon)
- Shows impact/severity (helps prioritization)
- Suggests specific treatment when available

**Categories:**
- Security: SSL, file editors, debug mode, admin username, updates
- Performance: Memory limits, caching, database, PHP version
- SEO: Permalinks, tagline, search indexing, sitemaps
- Content: Comments, accessibility, mobile friendliness
- Configuration: Timezone, email, privacy policy, site health

**Key files:**
- `includes/diagnostics/class-diagnostic-*.php` (57 diagnostic classes)
- `includes/diagnostics/class-diagnostic-registry.php` (auto-registration)
- `includes/diagnostics/class-diagnostic-runner.php` (execution)

**Extending:** Filter `wpshadow_diagnostics` to add custom checks.

[System Overview](SYSTEM_OVERVIEW.md) | [Diagnostic Patterns](ARCHITECTURE.md#diagnostics)

### 🛠️ Auto-Fix Treatments (44 Safe Fixes)

**What it does:** One-click fixes with automatic backups, undo capability, and KPI tracking.

**Philosophy in code:**
- Every treatment has `apply()` and `undo()` methods (reversibility)
- Automatic backups before dangerous operations (safety)
- Explains what it will do before applying (transparency)
- Tracks time saved and issues fixed (show value)
- Requires user permission per fix type (consent)

**Examples:**
- Database optimization (optimize tables, remove transients)
- Security hardening (disable file editors, force SSL admin)
- Performance (enable object cache, increase memory limits)
- Maintenance (update WordPress core, clear caches)

**Key files:**
- `includes/treatments/class-treatment-*.php` (44 treatment classes)
- `includes/treatments/class-treatment-registry.php` (auto-registration)
- `includes/treatments/class-treatment-executor.php` (execution with backup)

**Extending:** Filter `wpshadow_treatments` to add custom fixes.

[Treatment Patterns](ARCHITECTURE.md#treatments)

### ⚡ Workflow System (Automation Engine)

**What it does:** Trigger → Action automation with user consent and manual override.

**Philosophy in code:**
- User controls everything (manual override always available)
- Explains reasoning before automating (transparency)
- Suggests smart workflows, doesn't force (helpful, not presumptive)
- Tracks automation KPIs (time saved, tasks completed)

**Built-in triggers:**
- Schedule (hourly, daily, weekly, custom cron)
- Post published (new content)
- Comment submitted (moderation)
- User registration (onboarding)
- Plugin/theme activated/deactivated
- WordPress core updated

**Built-in actions:**
- Run diagnostic scans
- Apply specific treatments
- Clear caches (object, transient, page)
- Optimize database (tables, revisions)
- Send email notifications
- Update plugin settings
- Create site backups

**Key files:**
- `includes/workflow/class-workflow-manager.php` (registration & orchestration)
- `includes/workflow/class-workflow-executor.php` (safe execution with rollback)
- `includes/workflow/class-workflow-wizard.php` (UI for creating workflows)

**Extending:** Filters `wpshadow_workflow_triggers` and `wpshadow_workflow_actions` for custom automation.

[Workflow Builder](WORKFLOW_BUILDER.md) | [Triggers Reference](WORKFLOW_TRIGGERS_REFERENCE.md) | [Execution Engine](WORKFLOW_EXECUTION_ENGINE.md)

### 💡 Contextual Tooltips (1200+ Elements)

**What it does:** Context-sensitive help appears where you need it—no hunting for docs.

**Philosophy in code:**
- Teach in context (help appears at decision point)
- Plain English explanations (no jargon or acronyms)
- Links to KB articles for deeper learning (education funnel)
- User-dismissible per tooltip (respects user knowledge)
- Never interrupts workflow (hover/click to reveal)

**Coverage:**
- WordPress admin pages (dashboard, posts, pages, media, comments, appearance, plugins, users, tools, settings)
- Plugin and theme settings screens
- WP Site Health recommendations
- Category-based filtering (user sees relevant tips)
- Skips pages where tooltips would be distracting (plugins.php, edit.php)

**Key files:**
- `assets/js/tooltips.js` (smart positioning, dismissal state)
- `includes/data/tooltips-*.json` (1200+ tooltip definitions)
- `includes/core/class-tooltip-manager.php` (loading & filtering)

**Extending:** Filter `wpshadow_tooltips` to add custom contextual help.

[Tooltip Quick Reference](TOOLTIP_QUICK_REFERENCE.md) | [Tooltip System Architecture](ARCHITECTURE.md#tooltips)

### 📊 Dashboard & Kanban Board

**What it does:** Visual site health monitoring + Kanban board for managing findings.

**Philosophy in code:**
- At-a-glance understanding (11 health gauges with visual hierarchy)
- Progressive disclosure (click gauge → filtered category dashboard)
- User categorization (move Kanban cards to indicate intent)
- Smart column actions (moving cards triggers workflows automatically)
- Complete transparency (activity history logs everything)
- Show value constantly (KPI widget tracks time saved, issues fixed)

**Dashboard features:**
- Overall Site Health gauge (large, left side)
- 10 category gauges (security, performance, database, plugins, themes, SEO, content, accessibility, privacy, updates)
- Drill-down category dashboards (click any gauge)
- Real-time health percentage
- Quick action buttons

**Kanban columns:**
- **Detected:** New findings from scans
- **Ignored:** User decided not to fix (excluded from future scans)
- **User to Fix:** User will handle manually (we stop reminding)
- **Fix Now:** Create disposable workflow, auto-schedule
- **Workflows:** Create visible workflow with defaults
- **Fixed:** Completed treatments (archived after 30 days)

**Key files:**
- `includes/admin/` (dashboard assets, registry, widgets, layout)
- `includes/views/kanban-board.php` (Kanban UI & AJAX handlers)
- `includes/views/dashboard.php` (main dashboard view)
- `includes/core/class-kpi-tracker.php` (value metrics)
- `includes/core/class-finding-status-manager.php` (Kanban state)

**Extending:** Actions `wpshadow_dashboard_widget` and `wpshadow_kanban_column_moved` for custom integrations.

[Dashboard Layout Guide](DASHBOARD_LAYOUT_GUIDE.md) | [Kanban UI Guide](KANBAN_UI_GUIDE.md) | [Default Widget Order](DEFAULT_WIDGET_ORDER.md)

---

## Development Setup

### Requirements

- PHP 7.4+ (8.0+ recommended)
- WordPress 5.8+ (6.0+ recommended)
- Composer (for dev dependencies)
- Node.js 16+ (for asset building, if needed)

### Local Development

### File Structure

```
wpshadow/
├── wpshadow.php              # Main plugin file
├── includes/
│   ├── admin/                # Dashboard, AJAX handlers, layout
│   ├── core/                 # Registry, settings, base classes
│   ├── diagnostics/          # Health checks
│   ├── treatments/           # Auto-fixes
│   ├── workflow/             # Workflow engine
│   ├── views/                # Templates
│   └── data/                 # JSON data
├── assets/                   # CSS, JS, images
└── wpshadow-pro/             # Pro addon (https://github.com/thisismyurl/wpshadow-pro)
```

### Coding Standards

- **Namespace:** `WPShadow\{Module}`
- **Strict types:** `declare(strict_types=1);`
- **Capabilities:** `manage_options` (site), `manage_network_options` (network)
- **Security:** Always verify nonces, sanitize inputs, escape outputs
- **Text domain:** `wpshadow`

See [CODING_STANDARDS.md](CODING_STANDARDS.md).

```bash
# Clone repository
git clone https://github.com/thisismyurl/wpshadow.git
cd wpshadow

# Install dependencies (dev only - plugin has zero runtime dependencies)
composer install

# Run code quality checks
composer phpcs      # PHP_CodeSniffer (WordPress standards)
composer phpstan    # Static analysis (level 6)
```

See [TESTING_GUIDE.md](TESTING_GUIDE.md) for detailed testing instructions.

### File Structure

```
wpshadow/
├── wpshadow.php              # Main plugin bootstrap
├── includes/
│   ├── admin/                # Dashboard, widgets, AJAX handlers
│   ├── core/                 # Registry, settings, base classes, KPI tracking
│   ├── diagnostics/          # 57 health check classes
│   ├── treatments/           # 44 auto-fix classes
│   ├── workflow/             # Automation engine (39 files)
│   ├── views/                # PHP templates (dashboard, Kanban, help, rules)
│   └── data/                 # Tooltip JSON files (1200+ definitions)
├── assets/                   # CSS, JS, images (admin UI)
├── detectors/                # Detection utilities
├── helpers/                  # Helper functions
├── docs/                     # 60+ documentation files (you are here!)
├── vendor/                   # Composer dev dependencies (not included in releases)
└── composer.json             # Dev dependencies only

# Pro addon (separate repository)
wpshadow-pro/                 # https://github.com/thisismyurl/wpshadow-pro
```

See [FILE_STRUCTURE_GUIDE.md](FILE_STRUCTURE_GUIDE.md) for detailed reference.

### Coding Standards

**Philosophy:** Code is communication. Write for humans first, machines second.

- **Namespace:** `WPShadow\{Module}` (e.g., `WPShadow\Diagnostics`, `WPShadow\Treatments`)
- **Strict types:** `declare(strict_types=1);` in all PHP files
- **Capabilities:** 
  - Single site: `manage_options` for actions, `read` for menu visibility
  - Multisite: `manage_network_options` for network-wide actions
- **Security:** 
  - Always verify nonces on AJAX/form submissions
  - Sanitize all inputs (`sanitize_text_field`, `sanitize_key`, etc.)
  - Escape all outputs (`esc_html`, `esc_attr`, `wp_kses_post`)
- **Text domain:** `wpshadow` (internationalization)
- **Asset versioning:** Use `WPSHADOW_VERSION` constant (format: `1.YDDD.HHMM` in Toronto time - e.g., 1.6028.1430)
- **No inline CSS/JS:** Always enqueue via handles
- **WordPress Coding Standards:** Follow WordPress PHP Coding Standards (enforced via `composer phpcs`)

See [CODING_STANDARDS.md](CODING_STANDARDS.md) for complete guidelines.

---

## Documentation Index

### 📖 Start Here
- [**PRODUCT_PHILOSOPHY.md**](PRODUCT_PHILOSOPHY.md) - **Read this first!** Our 11 commandments
- [**TECHNICAL_STATUS.md**](TECHNICAL_STATUS.md) - **Current state summary** (features, metrics, readiness)
- [README.md](README.md) - This file (overview)
- [ROADMAP.md](ROADMAP.md) - Development phases & priorities

### 🏗️ Architecture
- [ARCHITECTURE.md](ARCHITECTURE.md) - System design & patterns
- [SYSTEM_OVERVIEW.md](SYSTEM_OVERVIEW.md) - High-level overview
- [FILE_STRUCTURE_GUIDE.md](FILE_STRUCTURE_GUIDE.md) - Directory reference
- [CODING_STANDARDS.md](CODING_STANDARDS.md) - Code style & security
- [VISUAL_SUMMARY_ONE_PAGE.md](VISUAL_SUMMARY_ONE_PAGE.md) - Architecture diagrams

### 🔬 Code Quality & Review
- [CODE_REVIEW_SENIOR_DEVELOPER.md](CODE_REVIEW_SENIOR_DEVELOPER.md) - Senior dev analysis (900 lines)
- [WORDCAMP_READINESS_GUIDE.md](WORDCAMP_READINESS_GUIDE.md) - WordCamp presentation strategy
- [PHASE_4_QUICK_WINS_IMPLEMENTATION.md](PHASE_4_QUICK_WINS_IMPLEMENTATION.md) - DRY refactoring tasks
- [FEATURE_MATRIX_DIAGNOSTICS.md](FEATURE_MATRIX_DIAGNOSTICS.md) - All 57 diagnostics matrix
- [FEATURE_MATRIX_TREATMENTS.md](FEATURE_MATRIX_TREATMENTS.md) - All 44 treatments matrix

### ✨ Features
- [WORKFLOW_BUILDER.md](WORKFLOW_BUILDER.md) - Automation engine
- [TOOLTIP_QUICK_REFERENCE.md](TOOLTIP_QUICK_REFERENCE.md) - Contextual help system
- [KANBAN_UI_GUIDE.md](KANBAN_UI_GUIDE.md) - Kanban board design
- [DASHBOARD_LAYOUT_GUIDE.md](DASHBOARD_LAYOUT_GUIDE.md) - Dashboard architecture
- [SITE_HEALTH_QUICK_REFERENCE.md](SITE_HEALTH_QUICK_REFERENCE.md) - WP integration
- [FEATURE_KPI_TRACKING.md](FEATURE_KPI_TRACKING.md) - Value metrics

### 🔧 Technical References
- [WORKFLOW_TRIGGERS_REFERENCE.md](WORKFLOW_TRIGGERS_REFERENCE.md) - Available triggers
- [WORKFLOW_EXECUTION_ENGINE.md](WORKFLOW_EXECUTION_ENGINE.md) - How automation works
- [EXTERNAL_CRON_INTEGRATION_GUIDE.md](EXTERNAL_CRON_INTEGRATION_GUIDE.md) - wp-cron alternatives
- [TIMEZONE_SYSTEM_README.php](TIMEZONE_SYSTEM_README.php) - Timezone handling

### 🧪 Testing & QA
- [TESTING_SETUP.md](TESTING_SETUP.md) - Development environment
- [README-TESTING.md](README-TESTING.md) - Test procedures
- [PERFORMANCE_AUDIT.txt](PERFORMANCE_AUDIT.txt) - Performance benchmarks

### 📋 Project Management
- [GITHUB_ISSUES_ALIGNMENT.md](GITHUB_ISSUES_ALIGNMENT.md) - Issues vs roadmap alignment
- [GITHUB_ISSUES_TEMPLATE.md](GITHUB_ISSUES_TEMPLATE.md) - Issue reporting format
- [ISSUE_REPOSITORY_DEVELOPER_GUIDE.md](ISSUE_REPOSITORY_DEVELOPER_GUIDE.md) - Issue workflow

---

## Roadmap (Philosophy-Driven)

> See [ROADMAP.md](ROADMAP.md) for detailed phases and [PRODUCT_PHILOSOPHY.md](PRODUCT_PHILOSOPHY.md) for guiding principles.

### ✅ Phase 1: Foundation (COMPLETE)
- 57 diagnostics across 10 categories
- 44 safe, reversible treatments with backups
- Kanban board with finding management
- KPI tracking (time saved, issues fixed)
- 1200+ contextual tooltips
- Workflow automation engine

### 🚧 Phase 2-3: Expansion (IN PROGRESS)
- Core diagnostics quality improvements
- Treatment expansion (more auto-fixes)
- Each diagnostic linked to KB article
- Each treatment linked to training video

### 🎯 Phase 4: Dashboard & UX Excellence (NEXT - Q1 2026)
- 11 health gauges (1 large overall + 10 categories)
- Breakout category dashboards (progressive disclosure)
- Comprehensive activity logging (transparency)
- Kanban smart actions (column moves trigger workflows)
- WordPress Site Health integration enhancement

**GitHub Issues:** #563, #564, #565, #567, #558

### 📚 Phase 5: KB & Training Integration (Q1-Q2 2026)
- Knowledge base system (free, searchable, comprehensive)
- Training video library (free, embedded, 2-5 min per topic)
- Contextual KB links in every diagnostic/treatment
- "Learn more" never means "pay us"

### 🔒 Phase 6: Privacy & Consent Excellence (Q2 2026)
- Anonymous data consent (first-run opt-in)
- Transparent disclosure (what/why we collect)
- Easy opt-out (settings UI, no pressure)
- Privacy-first by design

**GitHub Issue:** #566

### ☁️ Phase 7: Cloud Features & SaaS (Q3 2026+)
- Registration (not payment) for cloud features
- Generous free tiers (3 sites, 10 workflows/month, 90-day history)
- Usage-based paid tiers (no artificial limits)
- Transparent, simple pricing

### 🤖 Phase 8: Guardian & Automation (Q4 2026+)
- AI-driven predictive maintenance
- Proactive issue detection
- Self-healing capabilities (with consent)
- "So good they talk about it"

---

## Contributing

We welcome contributions that embody our [Product Philosophy](PRODUCT_PHILOSOPHY.md):

### ✅ Great Contributions
- Features that educate users while helping them
- Improvements to clarity, transparency, or intuitiveness
- Documentation that teaches, not just documents
- Performance optimizations
- Bug fixes with explanations
- Accessibility improvements

### ⚠️ Review Carefully
- Features requiring infrastructure (must have generous free tier)
- Data collection (must be consent-first, transparent benefit)
- Complex UI (must be exceptionally intuitive)

### 🛑 Won't Merge
- Features that gate functionality behind payment
- Sales-driven copy or dark patterns
- Dependency-creating features (users should learn, not rely)
- Non-consensual data collection
- "Enterprise only" features

### How to Contribute

1. Read [PRODUCT_PHILOSOPHY.md](PRODUCT_PHILOSOPHY.md) (seriously, read it!)
2. Check [ROADMAP.md](ROADMAP.md) for planned work
3. Open an issue to discuss your idea first
4. Fork and create a feature branch
5. Follow [CODING_STANDARDS.md](CODING_STANDARDS.md)
6. Write tests if applicable
7. Update documentation
8. Submit PR with clear description of philosophy alignment

---

## Pro Addon (Separate Repository)

**WPShadow Pro** is a separate, optional addon that extends the free plugin with cloud-based features.

- **Repository:** https://github.com/thisismyurl/wpshadow-pro
- **Philosophy:** Generous free tiers, registration (not payment) required
- **Free Plugin:** Works fully without Pro—never degraded or feature-limited
- **When to Upgrade:** When you need cloud features (multi-site dashboard, cross-site workflows, cloud backups)
- **Pricing:** Usage-based, transparent, predictable (no "contact sales")

**Pro Features (with free tiers):**
- Multi-site management dashboard (free: 3 sites, paid: unlimited)
- Cloud-based workflow execution (free: 10/month, paid: unlimited)
- Cross-site reporting (free: weekly, paid: real-time)
- External cron integration (free: essential jobs, paid: unlimited)
- Extended historical data (free: 90 days, paid: unlimited)

See [ROADMAP.md Phase 7](ROADMAP.md) for cloud features roadmap.

---

## License

**GPL v2 or later** - Same as WordPress.

Free to use, modify, and distribute. Contributions welcome.

---

## Support & Community

- **GitHub Issues:** [Report bugs or request features](https://github.com/thisismyurl/wpshadow/issues)
- **Knowledge Base:** Coming in Phase 5 (free, comprehensive)
- **Training:** Coming in Phase 5 (free video courses)
- **Philosophy:** Read [PRODUCT_PHILOSOPHY.md](PRODUCT_PHILOSOPHY.md) to understand our "helpful neighbor" approach

---

## Credits

Built with ❤️ by developers who believe WordPress management should be:
- **Free** (locally, forever)
- **Educational** (learn, don't just rely)
- **Transparent** (see everything we do)
- **Intuitive** (no manual required)
- **Ridiculously good** (better than premium alternatives)

See our 11 commandments in [PRODUCT_PHILOSOPHY.md](PRODUCT_PHILOSOPHY.md).

---

*Last Updated: January 21, 2026*  
*Version: 1.2601.2112*  
*"Your helpful neighbor for WordPress management"*

