# WPShadow Feature Capability Quick Reference

## One-Page Summary

### Admin/Super Admin Only (`manage_options`)

**System Administration & Security**
| Feature ID | Name | Default | AJAX Checks |
|---|---|---|---|
| core-integrity | File Security Scanner | ✓ | 3 handlers - implicit |
| core-diagnostics | Health Check-Up | ✓ | None detected |
| consent-checks | Cookie Privacy Manager | ✓ | Needs investigation |
| external-fonts-disabler | Block External Fonts | ✗ | No explicit checks |
| hotlink-protection | Hotlink Protection | ✗ | None |
| iframe-busting | Clickjacking Protection | ✗ | None |

**Content & Quality Control**
| Feature ID | Name | Default | AJAX Checks |
|---|---|---|---|
| a11y-audit | Accessibility Checker | ? | 1 handler - ✓ checks |
| color-contrast-checker | Text Readability Checker | ✓ | 1 handler - ✓ checks |
| setup-checks | Initial Setup Checklist | ? | 2 handlers - implicit |

**Performance & Optimization**
| Feature ID | Name | Default | AJAX Checks |
|---|---|---|---|
| block-cleanup | Remove Block Editor Code | ✗ | None |
| css-class-cleanup | Remove Extra Code Labels | ✗ | None |
| embed-disable | Stop Extra Embed Code | ? | None |
| head-cleanup | Remove Unnecessary Page Code | ✓ | None |
| html-cleanup | Shrink Page Code | ✗ | None |
| jquery-cleanup | Remove Old jQuery Code | ✓ | None |
| plugin-cleanup | Stop Unused Plugin Code | ? | None |
| resource-hints | Prepare External Connections | ? | None |
| simple-cache | Save Pages for Faster Loading | ✗ | 1 handler - ✓ checks |

**Accessibility & Navigation**
| Feature ID | Name | Default | AJAX Checks |
|---|---|---|---|
| skiplinks | Add Skip Navigation Links | ✓ | None |
| nav-accessibility | Better Navigation for Everyone | ✓ | None |

**Support & Monitoring**
| Feature ID | Name | Default | AJAX Checks |
|---|---|---|---|
| magic-link-support | Temporary Support Login | ? | Token-based auth |
| tips-coach | Smart Tips Helper | ? | 2 handlers - ✓ checks |

---

### Content Contributors/Editors (`edit_posts`)

**Pre-Publishing & Content Quality**
| Feature ID | Name | Default | AJAX Checks |
|---|---|---|---|
| pre-publish-review | Check Content Before Publishing | ✗ | 2 handlers - ✓ checks |
| content-optimizer | Complete Content Quality Optimizer | ✓ | Needs investigation |
| paste-cleanup | Clean Up Pasted Content | ✓ | Needs investigation |

---

### Special Permissions

**Update Managers (`update_core`)**
| Feature ID | Name | Default | Permission |
|---|---|---|---|
| maintenance-cleanup | Fix Stuck Updates | ? | update_core (not manage_options) |

**All Logged-In Users / Frontend**
| Feature ID | Name | Default | Permission |
|---|---|---|---|
| dark-mode | Dark Mode | ✓ | Per-user preference (no cap check) |
| image-lazy-loading | Load Images As Needed | ? | Frontend only |
| broken-link-checker | Find Broken Links | ? | No explicit AJAX checks |
| mobile-friendliness | Mobile Phone Checker | ? | No explicit checks |
| http-ssl-audit | Security Lock Checker | ? | Site health checks |
| emergency-support | Crash Alert System | ✓ | System-level monitoring |
| interactivity-cleanup | Remove Modern Block Code | ? | Frontend only |

---

## Critical Implementation Notes

### Features with Explicit `current_user_can()` Checks
✓ = Properly implemented with capability verification

- ✓ **a11y-audit** - Line 92, 114: `if ( ! current_user_can( 'manage_options' ) )`
- ✓ **color-contrast-checker** - Line 84: `if ( ! current_user_can( 'manage_options' ) )`
- ✓ **maintenance-cleanup** - Line 53: `if ( ! current_user_can( 'update_core' ) )` ⚠️ Note: Uses `update_core`, not `manage_options`
- ✓ **magic-link-support** - Lines 48, 206: `if ( ! current_user_can( 'manage_options' ) )`
- ✓ **pre-publish-review** - Lines 504, 810, 978: `if ( ! current_user_can( 'edit_posts' ) )` ⚠️ Special case: Correctly uses `edit_posts`
- ✓ **simple-cache** - Lines 430, 525: `if ( ! current_user_can( 'manage_options' ) )`
- ✓ **tips-coach** - Lines 308, 336: `if ( ! current_user_can( 'manage_options' ) )`
- ✓ **external-fonts-disabler** - Line 504: `if ( ! current_user_can( 'manage_options' ) )`

### Features Needing Verification
? = Needs code review for AJAX security

- content-optimizer - May need to support `edit_posts`
- paste-cleanup - Likely supports editors, needs verification
- broken-link-checker - Read-only, may need admin-only verification
- block-cleanup - No AJAX handlers detected
- embed-disable - No AJAX handlers detected
- interactivity-cleanup - No AJAX handlers detected
- image-lazy-loading - No AJAX handlers detected
- plugin-cleanup - No AJAX handlers detected
- resource-hints - No AJAX handlers detected
- setup-checks - Check "run_setup_checks" AJAX handler

---

## Permission Hierarchy Summary

```
┌─────────────────────────────────────────┐
│ Super Admin (manage_options)            │
│ • Can configure all plugin features      │
│ • Can repair files & settings            │
│ • Can view all diagnostics              │
│ • Can create support links              │
└──────────────┬──────────────────────────┘
               │
        ┌──────┴──────┐
        │             │
┌───────▼────────┐   ┌─────────────────┐
│ Editor/Author  │   │ Update Manager  │
│ (edit_posts)   │   │ (update_core)   │
│ • Pre-publish  │   │ • Fix stuck     │
│   review       │   │   updates       │
│ • Paste clean  │   │ • Cleanup temp  │
│ • Optimizer    │   │   files         │
└───────┬────────┘   └─────────────────┘
        │
        │
┌───────▼────────────────────────┐
│ All Users / Frontend Features   │
│ • Lazy loading                  │
│ • Dark mode preference          │
│ • Skip links                    │
│ • Accessibility features        │
└────────────────────────────────┘
```

---

## Practical Use Cases

### Blog Site Setup
- Admin: All features enabled (manage_options)
- Editor: pre-publish-review, content-optimizer, paste-cleanup (edit_posts)
- Author: Same as editor (edit_posts)
- Subscriber: None (frontend features only)

### News/Media Site Setup
- Admin: All features + setup-checks
- Editor: pre-publish-review, broken-link-checker, content-optimizer
- Contributor: paste-cleanup, content-optimizer (view only)
- Subscriber: None

### E-Commerce (WooCommerce) Setup
- Admin: All features
- Shop Manager: Simple-cache management, plugin-cleanup insights
- Product Editor: pre-publish-review, paste-cleanup, image-lazy-loading
- Customer: None (frontend features only)

### Community Site (BuddyPress/bbPress)
- Admin: All features
- Moderator: a11y-audit, color-contrast-checker (read-only)
- Member: Dark mode, accessibility features

---

## Security Recommendations

### ✓ Currently Well-Protected
- File system operations: core-integrity, hotlink-protection
- Configuration: setup-checks, core-diagnostics
- Admin tools: magic-link-support, tips-coach

### ⚠️ Review Recommended
- Features with "Needs investigation" status
- AJAX handlers without explicit nonce validation
- Features missing explicit `current_user_can()` checks

### 🔒 Best Practices Implemented
- Pre-publish-review correctly uses `edit_posts`
- Maintenance-cleanup correctly uses `update_core`
- All sensitive admin AJAX handlers have explicit capability checks
- Magic link uses token-based verification (time-limited)

---

## Default Enabled Status Reference

**Enabled by Default**
- core-integrity, core-diagnostics, consent-checks (cookie scanning, banner, script blocking)
- dark-mode, skiplinks, color-contrast-checker
- content-optimizer, paste-cleanup, head-cleanup, jquery-cleanup, nav-accessibility
- emergency-support, image-lazy-loading

**Disabled by Default**
- external-fonts-disabler, hotlink-protection, iframe-busting
- block-cleanup, css-class-cleanup, html-cleanup
- pre-publish-review, simple-cache
- consent-checks (audit trail, customizable banner)
- core-integrity (auto-repair, email alerts)

**Status Unknown** (Needs verification)
- a11y-audit, broken-link-checker, http-ssl-audit, interactivity-cleanup
- magic-link-support, mobile-friendliness, plugin-cleanup, resource-hints
- setup-checks, tips-coach

---

Generated: 2026-01-19
Documentation Source: Direct analysis of feature class files in `/workspaces/wpshadow/`

