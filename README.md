# WPShadow - Development Guide

> **The Helpful Neighbor: A WordPress Plugin That Genuinely Helps**

**Version:** 1.2601.2112  
**Status:** Production Ready - Phase 3.5 (Code Quality)  
**License:** GPL v2 or later

---

## 🎯 Quick Start

### For Users
👉 **Install from WordPress.org:** [WPShadow Plugin](https://wordpress.org/plugins/wpshadow/)

### For Developers

**1. Clone & Setup**
```bash
git clone https://github.com/thisismyurl/wpshadow.git
cd wpshadow
cp wp-config-extra.php /path/to/wordpress/
docker-compose up -d
```

**Access URLs (Codespaces)**
- Test site (port 9000): https://fictional-space-bassoon-qr65q7qqx4p2xvgr-9000.app.github.dev/

**2. Read Philosophy First**
```
Start with: docs/PRODUCT_PHILOSOPHY.md (11 Commandments)
Then read: docs/ROADMAP.md (current phases)
```

**3. Understand Architecture**
```
docs/ARCHITECTURE.md          - System design & base classes
docs/CODING_STANDARDS.md      - Code style & security patterns
docs/FILE_STRUCTURE_GUIDE.md  - Codebase organization
```

**4. Start Contributing**
```
docs/GITHUB_WORKFLOW.md       - Issue labels & workflow
docs/DEPLOYMENT.md             - Release process
docs/CODE_REVIEW_SENIOR_DEVELOPER.md  - DRY patterns
```

---

## 📊 Current State

**Production Features:**
- ✅ **57 Diagnostics** across 10 categories (security, performance, code quality, config, monitoring, system)
- ✅ **44 Treatments** (safe, reversible automatic fixes)
- ✅ **Kanban Board** (6-column workflow tracking)
- ✅ **KPI Tracking** (time saved, issues fixed, value delivered)
- ✅ **Workflow Automation** (11-file engine with triggers, actions, executor)
- ✅ **Multisite Support** (network-aware with proper capabilities)

**Code Quality:** ⭐⭐⭐⭐ (4/5)
- 43/43 treatments use base classes (100% DRY)
- 17/25 AJAX handlers use base classes (68% coverage)
- 31% duplicate code reduction in progress
- All base classes verified error-free

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
- [wpshadow.php](wpshadow.php) (~2000 lines) - Plugin initialization, menu registration, AJAX router

**Foundation**
- [includes/core/class-treatment-base.php](includes/core/class-treatment-base.php) - Base for all treatments
- [includes/core/class-diagnostic-base.php](includes/core/class-diagnostic-base.php) - Base for all diagnostics
- [includes/core/class-ajax-handler-base.php](includes/core/class-ajax-handler-base.php) - Base for AJAX handlers
- [includes/core/class-kpi-tracker.php](includes/core/class-kpi-tracker.php) - KPI measurement & tracking
- [includes/core/class-abstract-registry.php](includes/core/class-abstract-registry.php) - Auto-discovery pattern

**Features**
- [includes/diagnostics/](includes/diagnostics/) - 57 diagnostic classes + registry
- [includes/treatments/](includes/treatments/) - 44 treatment classes + registry
- [includes/workflow/](includes/workflow/) - 11-file automation engine
- [includes/admin/](includes/admin/) - Dashboard UI & AJAX handlers

**Views**
- [includes/views/](includes/views/) - PHP templates (dashboard, Kanban, help, settings)
- [includes/data/](includes/data/) - Tooltip JSON (1200+ definitions)

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

# 4. Load plugin locally
docker-compose up -d
# Access: http://localhost:8080/wp-admin/
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
├── PRODUCT_PHILOSOPHY.md              # 11 Commandments (READ FIRST)
├── ROADMAP.md                         # Phases 1-8 timeline
├── TECHNICAL_STATUS.md                # Current production state
├── ARCHITECTURE.md                    # System design & patterns
├── CODING_STANDARDS.md                # Code style & security
├── FEATURE_MATRIX_DIAGNOSTICS.md      # All 57 diagnostics
├── FEATURE_MATRIX_TREATMENTS.md       # All 44 treatments
├── GITHUB_WORKFLOW.md                 # Labels, milestones, workflow
├── DEPLOYMENT.md                      # Release & deployment process
├── FILE_STRUCTURE_GUIDE.md            # Codebase organization
└── archive/                           # Old session/build reports
    └── (58 archived docs)
```

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

## 🎯 Philosophy Reminders

**The 11 Commandments (See PRODUCT_PHILOSOPHY.md):**

1. **Helpful Neighbor** - Guide users, don't push sales
2. **Free as Possible** - Local features free forever
3. **Register Not Pay** - Cloud features require registration, not payment
4. **Advice Not Sales** - Educational, not manipulative
5. **Drive to KB** - Link to free knowledge base
6. **Drive to Training** - Link to free training videos
7. **Ridiculously Good** - Better than premium plugins
8. **Inspire Confidence** - UX so intuitive users trust all WordPress is this easy
9. **Show Value (KPIs)** - Track time saved, issues fixed
10. **Beyond Pure (Privacy)** - Consent-first, transparent
11. **Talk-Worthy** - So good users want to recommend it

---

## 🔗 Resources

**Getting Started**
- [PRODUCT_PHILOSOPHY.md](docs/PRODUCT_PHILOSOPHY.md) - Philosophy & values
- [COMPLETE_SETUP_GUIDE.md](docs/COMPLETE_SETUP_GUIDE.md) - Environment setup
- [GITHUB_WORKFLOW.md](docs/GITHUB_WORKFLOW.md) - Contribution workflow

**Development**
- [ARCHITECTURE.md](docs/ARCHITECTURE.md) - System design
- [CODING_STANDARDS.md](docs/CODING_STANDARDS.md) - Code style
- [CODE_REVIEW_SENIOR_DEVELOPER.md](docs/CODE_REVIEW_SENIOR_DEVELOPER.md) - Refactoring guide

**Features**
- [FEATURE_MATRIX_DIAGNOSTICS.md](docs/FEATURE_MATRIX_DIAGNOSTICS.md) - Diagnostic catalog
- [FEATURE_MATRIX_TREATMENTS.md](docs/FEATURE_MATRIX_TREATMENTS.md) - Treatment catalog
- [ROADMAP.md](docs/ROADMAP.md) - Future features

**Operations**
- [DEPLOYMENT.md](docs/DEPLOYMENT.md) - Release process
- [GITHUB_WORKFLOW.md](docs/GITHUB_WORKFLOW.md) - Issue/PR workflow
- [FILE_STRUCTURE_GUIDE.md](docs/FILE_STRUCTURE_GUIDE.md) - Codebase map

---

## 📞 Contributing

1. Fork the repository
2. Read [PRODUCT_PHILOSOPHY.md](docs/PRODUCT_PHILOSOPHY.md) - **This is required**
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

**Last Updated:** January 22, 2026  
**Status:** ✅ Ready for Development
