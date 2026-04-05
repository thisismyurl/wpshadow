# WPShadow Product Family

**Date:** April 5, 2026
**Version:** Current
**Status:** ✅ WPShadow Core — only product currently available

---

## Current State

Only one product exists: **WPShadow Core**. It is a free WordPress plugin.

---

## WPShadow Core

**Type:** WordPress Plugin (free)
**Repository:** `thisismyurl/wpshadow`
**Status:** Active, fully open source
**License:** Free — no artificial limitations

**What it is:** A focused WordPress diagnostic and dashboard tool.

**What it does:**
- 230 display-ready diagnostics spanning performance, security, SEO, accessibility, design, settings, database, monitoring, and more
- 101 executable treatment classes, including 93 automated and 8 guidance-only treatment entries
- Real-time admin dashboard displaying findings and site health
- File review plus local backup and recovery workflows for riskier changes
- Activity logging, KPI tracking, and WordPress Site Health integration
- Top-level runtime wrappers and WP-CLI support

**Public inventory rule:** `docs/FEATURES.md` is the documentation source of truth for counts, and only shipped / production-ready items are included in headline totals.

**What it is not:**
- Not a cloud service
- Not a SaaS platform
- Not a paid product
- Not a hosted backup service
- Not an LMS or learning platform

**Runs entirely on your WordPress server.** No external API, cloud tokens, or registration required.

---

## Naming Standards

To maintain consistency in documentation, code, and communications:

### Official Names

| Product | Type | Always Called | Never Called |
|---------|------|---------------|--------------|
| **WPShadow Core** | Free WordPress Plugin | "WPShadow Core", "the core plugin", or "WPShadow" | "WPShadow Guardian" (Guardian is a planned feature name, not the plugin) |

### What Exists vs. What Is Planned

| Name | Status |
|------|--------|
| WPShadow Core | ✅ Exists — free plugin |
| WPShadow Cloud | ❌ Not yet built |
| WPShadow Guardian (local monitor feature) | ❌ Not yet a distinct feature |
| WPShadow Academy | ❌ Not yet built |
| WPShadow Vault | ❌ Not yet built |
| WPShadow Pro (any module) | ❌ Not yet built |
| WPShadow Theme | ❌ Not yet built |

### Code Namespace Standard
```php
// Core plugin
namespace WPShadow\Core;

// Future cloud services (if/when built)
namespace WPShadow\Cloud;
```

---

## Philosophy

WPShadow's model is **free as possible**. Anything that runs on the user's own server and requires no ongoing infrastructure cost will be free. Future paid products, if introduced, would only be charged to the extent of actual server and service costs — never to gate functionality from users who need it.

See [BUSINESS_MODEL.md](BUSINESS_MODEL.md) for the full model.
See [CORE_PHILOSOPHY.md](CORE_PHILOSOPHY.md) for guiding principles.

---

**Version:** Current
**Maintained By:** WPShadow Team
**Repository:** [github.com/thisismyurl/wpshadow](https://github.com/thisismyurl/wpshadow)
