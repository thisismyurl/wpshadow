# WPShadow - The Helpful Neighbor

> **A WordPress plugin that genuinely helps, built on principles of accessibility, inclusivity, and education**

**Version:** 0.6093.1200 (Format: 0.{last year digit}{julian day}.{hour}{minute} in Toronto time)
**Status:** ✅ Core Plugin Production Ready | Release Documentation Aligned
**License:** GPL v2 or later
**Last Updated:** April 3, 2026

---

## 🎯 Quick Start

### For Users
👉 **Install from WordPress.org:** [WPShadow Plugin](https://wordpress.org/plugins/wpshadow/)
👉 **Cloud Services:** [wpshadow.com](https://wpshadow.com) (Cloud Guardian diagnostics, KB articles, training, cloud backups)

**Note:** The core plugin includes the local Guardian monitoring system (100% free). WPShadow Cloud extends it with Cloud Guardian diagnostics that require external services.

### For Developers

**1. Clone & Setup**
```bash
git clone https://github.com/thisismyurl/wpshadow.git
cd wpshadow
```

**Development Environment**

For development, open this repository in GitHub Codespaces or VS Code with the Dev Containers extension. The environment will be automatically configured.

**2. Read Core Philosophy**
```
✅ MUST READ: docs/CORE_PHILOSOPHY.md (12 Commandments + 5 CANON Pillars)
✅ MUST READ: docs/PRODUCT_FAMILY.md (ecosystem map)
✅ MUST READ: docs/BUSINESS_MODEL.md (free-first business model)
Then read: docs/MILESTONES.md (current phases)
```

**3. Understand Architecture**
```
docs/ARCHITECTURE.md                      - System design & base classes
docs/CODING_STANDARDS.md                  - Code style & security patterns
docs/FILE_STRUCTURE_GUIDE.md              - Codebase organization
docs/FEATURES.md                          - Canonical shipped inventory + count policy
docs/ACCESSIBILITY.md                     - Accessibility and disability-inclusion commitment
docs/MILESTONES.md                        - Current release phases and roadmap
```

**4. Start Contributing**
```
docs/GITHUB_WORKFLOW.md                   - Issue labels & workflow
docs/DEPLOYMENT.md                        - Release process
docs/CODE_REVIEW_SENIOR_DEVELOPER.md      - DRY patterns
docs/INDEX.md                             - Complete documentation index
```

---

## ♿ Accessibility & Inclusion Commitment

WPShadow is built for real people, including people who use screen readers, keyboard navigation, zoom, reduced motion, voice control, captions, simpler language, or extra time to process information.

Our standard is simple:

- accessibility is a **product requirement**, not polish
- plain-English explanations are part of usability
- safe, reversible actions should be understandable under stress
- disability-related barriers should be treated as real bugs worth fixing quickly
- inclusive design should shape both the plugin and the documentation around it

See [`docs/ACCESSIBILITY.md`](docs/ACCESSIBILITY.md) for the full commitment and development approach.

---

## 📊 Current State

### April 2026 Release Update
- Release metadata is aligned at `0.6093.1200` across plugin headers, stable tags, and distributable readmes.
- Future-dated `@since` tags were normalized to the current release version to keep shipped code annotations consistent.
- Dashboard gauge reports now open consistent detailed report pages, with the WordPress gauge linking to Site Health.
- Recent bootstrap and admin menu regressions have been addressed so release builds load cleanly.
- Release packaging and validation remain in place to keep shipped metadata and docs synchronized.

**Production Features:**
- ✅ **229 shipped diagnostics** across 11 live categories, verified from `Settings → Diagnostics`
- ✅ **Treatment framework + safe fixes** for reversible remediation, with public counts now tied to shipped UI inventory instead of stale headline totals
- ✅ **KPI Tracking** (time saved, issues fixed, value delivered)
- ✅ **Workflow Automation** (39-file engine with triggers, actions, commands, executor)
- ✅ **Multisite Support** (network-aware with proper capabilities)
- ✅ **Accessibility-First Design** (WCAG compliant, inclusive patterns)
- ✅ **Guardian System** (local diagnostic monitoring with real-time health checks)
- ✅ **16 Built-in Tools** (accessibility audit, color contrast, deep scan, cache, etc.)
- ✅ **54 Curated Docs** (cleaned, organized, publication-ready)

**Code Quality:** ⭐⭐⭐⭐⭐ (5/5)
- Shared base classes and registries are used throughout the diagnostics, treatments, and AJAX layers
- All code passes PHP syntax check
- All code passes WordPress coding standards (phpcs)
- All code passes static analysis (phpstan)
- Accessibility-first patterns enforced throughout

**Release Snapshot:**
- 229 diagnostics are currently shipped in the live release build and visible in `Settings → Diagnostics`.
- Public documentation now treats `docs/FEATURES.md` plus the live diagnostics screen as the count source of truth.
- Accessibility-first guidance and CANON principles are integrated throughout the product and docs.
- Release metadata, report routing, and admin boot paths were validated for the distributable build.

---

## 🗓️ Planned Pro Release Cadence (2026)

We ship Pro in a dependency‑first order so every module lands after its foundation.

- **March 31:** WPShadow Pro platform (licensing + module manager)
- **April 30:** WPAdmin Core (primary admin foundation)
- **May onward:** Secondary WPAdmin modules begin monthly releases
- **July 31:** Media Hub (primary media foundation)
- **August onward:** Media Image, Video, and Document modules begin monthly releases

Full schedule: [docs/MILESTONES.md](docs/MILESTONES.md)
Product map: [docs/PRODUCT_FAMILY.md](docs/PRODUCT_FAMILY.md)

---

## 🏗️ Architecture Highlights

### Base Classes (Inheritance Pattern)
```php
// All treatments extend this:
class Treatment_Base {
    public static function apply() { /* ... */ }
    public static function undo() { /* ... */ }
    public static function can_apply() { /* ... */ }
}

// All AJAX handlers extend this:
class AJAX_Handler_Base {
    public static function verify_request() { /* nonce + capability */ }
    public static function get_post_param() { /* sanitization */ }
    public static function send_success() { /* JSON response */ }
}

// All diagnostics extend this:
class Diagnostic_Base {
    public static function check() { /* return finding */ }
    public static function get_name() { /* plain English */ }
}
```

### Registry Pattern (Auto-Discovery)
```php
// Automatic detection of all treatments
$treatments = Treatment_Registry::get_all();
// Returns: ['ssl' => Treatment_SSL, 'debug_mode' => Treatment_Debug_Mode, ...]

// Automatic detection of all diagnostics
$diagnostics = Diagnostic_Registry::get_all();
```

### Philosophy-First Development
```php
// Every treatment must:
✅ Be 100% reversible (undo implemented)
✅ Link to KB article (education)
✅ Link to training video (learning)
✅ Track KPIs (show value)
✅ Pass security audit (nonce, capability, sanitize, escape)
✅ Use plain English (no jargon)
✅ Have no artificial limits (free forever locally)
```

---

## 📁 Key Files

**Core Bootstrap**
- [wpshadow.php](wpshadow.php) (~85 lines) - Plugin initialization and autoloader bootstrap

**Foundation**
- [includes/systems/core/class-treatment-base.php](includes/systems/core/class-treatment-base.php) - Base for all treatments
- [includes/systems/core/class-diagnostic-base.php](includes/systems/core/class-diagnostic-base.php) - Base for all diagnostics
- [includes/systems/core/class-ajax-handler-base.php](includes/systems/core/class-ajax-handler-base.php) - Base for AJAX handlers
- [includes/systems/core/class-kpi-tracker.php](includes/systems/core/class-kpi-tracker.php) - KPI measurement & tracking
- [includes/systems/core/class-abstract-registry.php](includes/systems/core/class-abstract-registry.php) - Auto-discovery pattern

**Features**
- [includes/diagnostics/](includes/diagnostics/) - diagnostic inventory + registry (229 shipped items in the current build)
- [includes/treatments/](includes/treatments/) - treatment framework, registry, and safe remediation logic
- [includes/systems/workflow/](includes/systems/workflow/) - automation engine (triggers, actions, commands, executor)
- [includes/admin/](includes/admin/) - Dashboard UI & AJAX handlers

**Views**
- [includes/ui/views/](includes/ui/views/) - PHP views (dashboard, findings, settings, activity history)
- [includes/assets/data/](includes/assets/data/) - Tooltip and impact metadata

---

## 🔄 Development Workflow

### 1. Create a New Diagnostic
```bash
# Copy template
cp includes/diagnostics/class-diagnostic-template.php \
   includes/diagnostics/class-diagnostic-my-check.php

# Edit the file (extends Diagnostic_Base)
# Auto-registers via registry pattern
# Test: Appears in dashboard immediately
```

### 2. Create a New Treatment
```bash
# Copy template
cp includes/treatments/class-treatment-template.php \
   includes/treatments/class-treatment-my-fix.php

# Implement:
# - apply() - fix logic (100% reversible)
# - undo() - restore previous state
# - can_apply() - check permissions
# Auto-registers via registry pattern
```

### 3. Create a New AJAX Handler
```bash
# Copy base class pattern
# Create: includes/admin/ajax/class-my-action-handler.php
# Extend: AJAX_Handler_Base
# Implements: handle() method
# Auto-registers via wp_ajax_wpshadow_* hook
```

### 4. Make a Pull Request
```bash
# Branch: feature/my-feature
git checkout -b feature/my-feature

# Code with philosophy check:
# - Is it free locally? ✅
# - Is it educational? ✅
# - Does it track KPIs? ✅
# - Is UX intuitive? ✅
# - Security audit passed? ✅

# Commit with philosophy-aligned message:
git commit -m "Feature: [Helpful description]

Philosophy:
- Commandment #1: Helpful neighbor - [how]
- Commandment #7: Ridiculously good - [how]

Closes: #<issue-number>"

# Push & create PR
git push origin feature/my-feature
```

---

## ✅ Quality Gates

**Before committing:**
```bash
# 1. Syntax check
find includes -name "*.php" -print0 | xargs -0 php -l

# 2. WordPress Coding Standards
composer phpcs

# 3. Static analysis
composer phpstan

# 4. Load plugin locally (using Codespaces)
# Plugin is already loaded in development environment
# Access: Your Codespaces URL (shown in terminal on start)
```

**Before pushing:**
```bash
# 1. Philosophy checklist (see GITHUB_WORKFLOW.md)
# 2. Test all affected features
# 3. Check for new security issues
# 4. Update relevant docs
# 5. Add KB link if needed
```

---

## 📚 Documentation Structure

```
docs/
├── INDEX.md                           # START HERE - Main navigation
├── CORE_PHILOSOPHY.md                 # 12 Commandments + 5 CANON Pillars (READ FIRST)
├── ROADMAP.md                         # Phases 1-8 timeline
├── TECHNICAL_STATUS.md                # Current production state
├── ARCHITECTURE.md                    # System design & patterns
├── CODING_STANDARDS.md                # Code style & security
├── FEATURES.md                        # Canonical shipped inventory + count policy
├── MILESTONES.md                      # Current release scope and roadmap
├── GITHUB_WORKFLOW.md                 # Labels, milestones, workflow
├── DEPLOYMENT.md                      # Release & deployment process
├── FILE_STRUCTURE_GUIDE.md            # Codebase organization
├── RELEASE_NOTES.md                   # Release information
├── TESTING_GUIDE.md                   # Testing procedures
├── INSTALL.md                         # Installation guide
└── archive/                           # Old session/build reports
    └── (58 archived docs)

dev-tools/                             # Development scripts (not in releases)
├── README.md                          # Developer tools documentation
├── kb-articles/                       # Knowledge base article sources
└── wp-content/                        # Test WordPress content

MURPHYS_LAW_AUDIT.md                   # ⚙️ Murphy's Law compliance audit
MURPHYS_LAW_INTEGRATION_GUIDE.md       # Step-by-step integration guide
MURPHYS_LAW_COMPLETE_SUMMARY.md        # Implementation summary
includes/systems/core/
├── class-murphy-safe-request.php      # Network resilience wrapper
├── class-murphy-safe-database.php     # Database verification wrapper
└── class-murphy-safe-file.php         # File operation safety wrapper
assets/js/murphy-form-autosave.js      # Form auto-save module
```

### Murphy's Law Implementation (Feb 2026) ⚙️

**Status:** ✅ Infrastructure Complete, Integration In Progress

The plugin now implements **Murphy's Law (CANON Pillar #5)**: defensive programming that assumes everything will fail and handles it gracefully.

**What This Means:**
- ✅ Network requests have 4-tier fallback (cache → API → stale → default)
- ✅ Database operations are verified and rolled back on corruption
- ✅ File writes are atomic with disk space checks
- ✅ Forms auto-save every 5 seconds to prevent data loss
- ✅ All failures retry automatically via cron queues
- ✅ Users see friendly errors, not stack traces

**Key Files:**
- `MURPHYS_LAW_AUDIT.md` - Complete audit findings (600+ lines)
- `MURPHYS_LAW_INTEGRATION_GUIDE.md` - How to integrate wrappers (800+ lines)
- `MURPHYS_LAW_COMPLETE_SUMMARY.md` - Implementation summary
- `includes/systems/core/class-murphy-safe-*.php` - Three defensive wrappers (1,080 lines)
- `assets/js/murphy-form-autosave.js` - Form protection (450 lines)

**Integration Priority:**
1. 🔴 **Critical:** Guardian API, Vault Manager, Settings Registry, Event Logger
2. 🟡 **High:** Report integrations, CSV export, Vault registration
3. 🟢 **Medium:** Diagnostic HTML fetchers, form auto-save rollout

See `MURPHYS_LAW_INTEGRATION_GUIDE.md` for complete integration instructions.

---

## 🚀 Common Tasks

### Add a new diagnostic
1. Create file: `includes/diagnostics/class-diagnostic-SLUG.php`
2. Extend `Diagnostic_Base`
3. Implement `check()` method
4. Test locally
5. Create KB article
6. Link in treatment (if auto-fix available)

### Add a new treatment
1. Create file: `includes/treatments/class-treatment-SLUG.php`
2. Extend `Treatment_Base`
3. Implement `apply()`, `undo()`, `can_apply()`
4. Test undo functionality thoroughly
5. Create training video
6. Track KPI improvements

### Update documentation
1. Only update docs/ files
2. Keep docs in sync with code
3. Archive build/session reports
4. Update version header
5. Commit with message: "Docs: [description]"

### Fix a bug
1. Create issue (or reference existing)
2. Branch from main
3. Fix & test locally
4. Verify no regressions
5. PR with issue reference
6. Merge after approval

---

## 🎯 Core Philosophy

**The 12 Commandments (See CORE_PHILOSOPHY.md):**

1. **Helpful Neighbor** - Guide users, don't push sales
2. **Free as Possible** - Local features free forever
3. **Register Not Pay** - Cloud features require registration, not payment
4. **Advice Not Sales** - Educational, not manipulative
5. **Drive to KB** - Link to free knowledge base
6. **Drive to Training** - Link to free training videos
7. **Ridiculously Good** - Better than premium plugins
8. **Inspire Confidence** - UX so intuitive users trust all WordPress is this easy
9. **Everything Has a KPI** - Track time saved, issues fixed
10. **Beyond Pure (Privacy)** - Consent-first, transparent
11. **Talk-Worthy** - So good users want to recommend it
12. **Expandable** - Open to extensions by other developers

**5 CANON Pillars (Non-Negotiable):**

See [docs/CORE_PHILOSOPHY.md](docs/CORE_PHILOSOPHY.md)

1. **Accessibility First** - All features must be accessible to users with diverse abilities
   - WCAG 2.1 AA compliance baseline
   - Keyboard navigation everywhere
   - Screen reader friendly
   - Color contrast standards

2. **Learning Inclusive** - Documentation and code must be understandable to learners at all levels
   - Jargon-free explanations
   - Visual aids and examples
   - Progressive complexity
   - Welcoming tone throughout

3. **Culturally Respectful** - Implementation must honor diverse cultural contexts and perspectives
   - No cultural assumptions
   - Inclusive language standards
   - Global accessibility focus
   - Community-informed design

4. **Safe by Default** - Protect users from mistakes and malicious attacks
   - Confirm risky actions
   - Backups before changes
   - Secure defaults and audit logs

5. **Murphy's Law** - Defensive engineering that assumes failure and recovers gracefully
   - Fallbacks for network, database, and file operations
   - Automatic retries and safe recovery paths

---

## 🔗 Resources

**Getting Started**
- [CORE_PHILOSOPHY.md](docs/CORE_PHILOSOPHY.md) - Philosophy & values
- [COMPLETE_SETUP_GUIDE.md](docs/COMPLETE_SETUP_GUIDE.md) - Environment setup
- [GITHUB_WORKFLOW.md](docs/GITHUB_WORKFLOW.md) - Contribution workflow

**Development**
- [ARCHITECTURE.md](docs/ARCHITECTURE.md) - System design
- [CODING_STANDARDS.md](docs/CODING_STANDARDS.md) - Code style
- [CODE_REVIEW_SENIOR_DEVELOPER.md](docs/CODE_REVIEW_SENIOR_DEVELOPER.md) - Refactoring guide

**Features**
- [FEATURES.md](docs/FEATURES.md) - Canonical shipped inventory + count policy
- [MILESTONES.md](docs/MILESTONES.md) - Release scope and roadmap
- [ROADMAP.md](docs/ROADMAP.md) - Future features

**Operations**
- [DEPLOYMENT.md](docs/DEPLOYMENT.md) - Release process
- [GITHUB_WORKFLOW.md](docs/GITHUB_WORKFLOW.md) - Issue/PR workflow
- [FILE_STRUCTURE_GUIDE.md](docs/FILE_STRUCTURE_GUIDE.md) - Codebase map

---

## 📞 Contributing

1. Fork the repository
2. Read [CORE_PHILOSOPHY.md](docs/CORE_PHILOSOPHY.md) - **This is required**
3. Check [GITHUB_WORKFLOW.md](docs/GITHUB_WORKFLOW.md) for label/milestone info
4. Follow [CODING_STANDARDS.md](docs/CODING_STANDARDS.md)
5. Submit PR with philosophy verification checklist

---

## 📄 License

WPShadow is licensed under the GNU General Public License v2.0 or later.
See [LICENSE](LICENSE) for details.

---

## 🎉 Thanks

Built with philosophy-first development:
> **"The bar: People should question why this is free."**

**Current Contributors:** [thisismyurl](https://github.com/thisismyurl)

---

**Last Updated:** April 1, 2026
**Status:** ✅ Production Ready - Release Documentation Synced

**Current Release Highlights:**
- ✅ Version `0.6093.1200` is aligned across the plugin header, runtime constant, WordPress readme, and distributable package.
- ✅ Release-facing documentation matches the current dashboard, reporting, and monitoring feature set.
- ✅ Distributable docs and code paths were refreshed for a cleaner release handoff.
