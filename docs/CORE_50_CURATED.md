# WPShadow Core 50: Trusted Diagnostic Set

**Last Updated:** April 3, 2026  
**Audit Response:** Curation Framework  
**Philosophy:** Ruthlessly curated for signal-to-noise, actionability, and confidence
**Usage:** Default set for new users; full 230 available via Settings for advanced admins

---

## Overview

**Core 50** is the essential set of diagnostics that every WordPress site owner should care about. These 50 checks represent:

- ✅ **High confidence** (heuristics validated, false-positive rare)
- ✅ **High actionability** (clear fixes, measurable impact)
- ✅ **Broad applicability** (works for most WordPress installations)
- ✅ **Clear value** (security, performance, health obviously matter)

The remaining 180 diagnostics in the full 230 are:
- Optional (advanced, specialized, architecture-specific)
- For users who want comprehensive auditing
- Available via "Advanced Insights" toggle in Settings

---

## Core 50 List by Category

### 🔐 SECURITY (12 checks)

Essential hardening for every WordPress site.

| # | Diagnostic | Risk | Fix Type | Priority |
|---|-----------|------|----------|----------|
| 1 | **Auth Keys and Salts Set** | Critical | Auto | P0 |
| 2 | **No Default Admin User** | Critical | Manual | P0 |
| 3 | **Weak Database Prefix** | High | Manual | P1 |
| 4 | **No Admin Email Verification** | High | Manual | P1 |
| 5 | **Insecure Password Hashing** | High | Auto | P1 |
| 6 | **XML-RPC Enabled** | Medium | Auto | P2 |
| 7 | **Comment Moderation Not Set** | Medium | Manual | P2 |
| 8 | **No Comment Link Limits** | Medium | Auto | P2 |
| 9 | **File Uploads Unrestricted** | Medium | Manual | P2 |
| 10 | **REST API Auth Not Required** | Low-Medium | Manual | P2 |
| 11 | **No Site-Wide SSL** | High | Manual | P1 |
| 12 | **Outdated WordPress Core** | Critical | Manual | P0 |

### ⚡ PERFORMANCE (12 checks)

High-impact optimizations for speed.

| # | Diagnostic | Impact | Fix Type | Priority |
|---|-----------|--------|----------|----------|
| 13 | **Browser Caching Headers Missing** | High | Auto | P0 |
| 14 | **Compression Not Enabled (GZIP)** | High | Auto | P1 |
| 15 | **Database Query Count Too High** | High | Manual | P1 |
| 16 | **Database Optimization** | Medium | Auto | P1 |
| 17 | **Autosave Interval Not Optimized** | Medium | Auto | P2 |
| 18 | **Block Library CSS Unoptimized** | Medium | Auto | P2 |
| 19 | **Dashicons Loaded on Frontend** | Low-Medium | Auto | P2 |
| 20 | **WooCommerce Sessions Not Cleaned** | Medium | Auto | P2 |
| 21 | **Admin Scripts in Head (Blocking)** | Medium | Manual | P2 |
| 22 | **Image Sizes Not Optimized** | Low | Manual | P2 |
| 23 | **Adjacent Posts Links Queried** | Low | Auto | P2 |
| 24 | **Database Not Indexed** | High | Manual | P1 |

### 🗄️ DATABASE (11 checks)

Health and integrity of your data layer.

| # | Diagnostic | Risk | Fix Type | Priority |
|---|-----------|------|----------|----------|
| 25 | **Database Charset/Collation Correct** | High | Manual | P1 |
| 26 | **Database Version Supported** | Medium | Manual | P1 |
| 27 | **No MyISAM Tables** | High | Manual | P1 |
| 28 | **Database Prefix Intentional** | Low | Manual | P2 |
| 29 | **No Tables Missing Primary Key** | High | Manual | P1 |
| 30 | **Auto-Draft Accumulation** | Low | Auto | P2 |
| 31 | **Stale Sessions Cleared** | Low | Auto | P2 |
| 32 | **WP Options Autoload Size Healthy** | Medium | Manual | P2 |
| 33 | **No Orphaned User Meta** | Low | Auto | P2 |
| 34 | **Post Meta Bloat Detected** | Low | Auto | P2 |
| 35 | **No Duplicate Post Meta Keys** | Low | Auto | P2 |

### ⚙️ WORDPRESS HEALTH (13 checks)

Core WordPress system integrity.

| # | Diagnostic | Risk | Fix Type | Priority |
|---|-----------|------|----------|----------|
| 36 | **No Debug Mode Enabled** | High | Manual | P1 |
| 37 | **No Debug Log Public** | Critical | Manual | P0 |
| 38 | **No Default Posts/Categories** | Low | Auto | P2 |
| 39 | **Cron System Healthy** | Medium | Auto | P2 |
| 40 | **Multisite Configuration OK** | Medium-High | Manual | P1 |
| 41 | **Transients Used (Not Options)** | Low | Manual | P2 |
| 42 | **Revisions Limited** | Low | Auto | P2 |
| 43 | **Comments Auto-Close on Old Posts** | Low | Auto | P2 |
| 44 | **Trackbacks/Pingbacks Disabled** | Low | Manual | P2 |
| 45 | **Heartbeat Optimization** | Low | Auto | P2 |
| 46 | **Theme Compatibility** | Medium | Manual | P1 |
| 47 | **Plugin Compatibility** | Medium | Manual | P1 |
| 48 | **WordPress File Permissions** | High | Manual | P1 |

### ♿ ACCESSIBILITY (2 checks)

Basic WCAG compliance for users.

| # | Diagnostic | Standard | Fix Type | Priority |
|---|-----------|----------|----------|----------|
| 49 | **Headings Hierarchy Valid** | WCAG 2.1 | Manual | P2 |
| 50 | **Alt Text Coverage** | WCAG 2.1 | Manual | P2 |

---

## Why These 50?

### Inclusion Criteria Met:
1. **Affects most sites:** Not specialized (e.g., WooCommerce-only, BuddyPress-specific)
2. **High signal:** True problems, not edge cases
3. **Clear fix:** Admin knows what "fixed" looks like
4. **Safe auto-fix:** Most auto-fixes won't break anything (auto applied / manual reviewed)
5. **Actionable:** Site owner can directly fix or know whom to call

### What's NOT in Core 50?

- **Specialized:** WooCommerce optimization, SEO checks, performance metrics beyond basics
- **Heuristic-heavy:** Checks requiring multiple interpretations (e.g., "Is my site fast enough?" varies by business)
- **Low-frequency issues:** Rare edge cases or advanced architecture checks
- **Experimental:** Beta diagnostics, roadmap items, internal tests
- **Speculative:** Checks that "might be a problem" without strong evidence

→ These 180+ checks are in "Advanced Insights" (full 230 diagnostics accessible via Settings)

---

## Implementation

### For New Users (Default)

```
Settings → Diagnostics → Default View:
[Core 50] [Advanced (Show all 230)]

Only Core 50 run in:
- Initial dashboard scan
- Weekly automatic scans (by default)
- "Run Quick Scan" button
```

### For Advanced Users

```
Settings → Diagnostics → Advanced Insights:
[Show all 230 diagnostics]

Choose which to include in:
- Automatic scans
- On-demand runs
- Dashboard reporting
```

### Dashboard Indicators

```
🟢 CORE 50:    Green badge (trusted, tested)
🟡 ADVANCED:   Yellow badge (requires review)
🔴 EXPERIMENTAL: Red badge (hidden by default)
```

---

## Maintenance & Updates

**Quarterly Review (Starting Q2 2026):**
- Verify each Core 50 check still high-confidence
- Audit false-positive rate
- Promote/demote based on feedback
- Update with new high-confidence discoveries

**Feedback Loop:**
- Community can suggest promotions to Core 50
- Audit committee reviews quarterly
- Changes documented and rolled out in minor releases

---

## What This Adds

| Aspect | Before | After |
|--------|--------|-------|
| **UX Clarity** | 230 diagnostics overwhelming | Core 50 focus + optional expansion |
| **Signal** | Unclear which matter most | 🟢 Core = trusted; 🟡 Advanced = specialized |
| **Onboarding** | New users lost in choices | New users see Core 50; upgrade when ready |
| **Trust** | "Why so many??" | "These 50 really matter; 180 more available" |
| **Defaults** | Scans all 230 always | Smart defaults: Core 50 by default; all 230 optional |

---

## Conclusion

**Core 50** transforms WPShadow's positioning from:
> "Massive plugin with 230 diagnostics" (overwhelming)

To:
> "Essential 50 checks you need + 180 advanced options" (focused)

This is ruthless curation: we say "NO" to 180 nice-to-haves so we can say "DEFINITELY YES" to 50 must-haves.

That's how you build trust in a large product.
