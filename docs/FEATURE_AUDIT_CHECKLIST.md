# WPShadow Feature Audit Checklist

## Pre-Implementation Review

- [ ] Read FEATURE_AUDIT_REPORT.md - full details
- [ ] Read FEATURE_AUDIT_SUMMARY.md - quick overview  
- [ ] Read FEATURE_AUDIT_IMPLEMENTATION.md - specific code fixes
- [ ] Back up current codebase
- [ ] Create feature branch: `git checkout -b audit/security-hardening`

---

## Phase 1: Security Hardening (Week 1)

### Add Nonce Verification to AJAX Handlers

#### Core Diagnostics
- [ ] File: `includes/features/class-wps-feature-core-diagnostics.php`
- [ ] Handler: `ajax_run_diagnostics()`
- [ ] Add: `check_ajax_referer( 'wpshadow_run_diagnostics_nonce', 'nonce' );`
- [ ] Test with invalid nonce
- [ ] Test with valid nonce

#### Core Integrity
- [ ] File: `includes/features/class-wps-feature-core-integrity.php`
- [ ] Handler 1: `ajax_scan_core_files()`
- [ ] Handler 2: `ajax_repair_core_file()`
- [ ] Handler 3: `ajax_repair_all_core_files()`
- [ ] Add nonce to each handler
- [ ] Test file scanning
- [ ] Test file repair

#### Dark Mode
- [ ] File: `includes/features/class-wps-feature-dark-mode.php`
- [ ] Handler: `ajax_set_dark_mode()`
- [ ] Add nonce verification
- [ ] Test toggle functionality

#### External Fonts Disabler
- [ ] File: `includes/features/class-wps-feature-external-fonts-disabler.php`
- [ ] Handler: `ajax_save_external_fonts_settings()`
- [ ] Add nonce verification
- [ ] Test settings save

#### Feature Search
- [ ] File: `includes/class-wps-feature-search.php`
- [ ] Handler: `ajax_search_features()`
- [ ] Add nonce verification
- [ ] Test search functionality

#### A11y Audit
- [ ] File: `includes/features/class-wps-feature-a11y-audit.php`
- [ ] Handler: `ajax_audit_page()`
- [ ] Verify nonce handling (already partially implemented)
- [ ] Ensure proper nonce placement
- [ ] Test audit functionality

#### Color Contrast Checker
- [ ] File: `includes/features/class-wps-feature-color-contrast-checker.php`
- [ ] Handler: `ajax_check_contrast()`
- [ ] Add nonce verification
- [ ] Test contrast checking

#### Content Optimizer
- [ ] File: `includes/features/class-wps-feature-content-optimizer.php`
- [ ] Handler: `ajax_run_content_check()`
- [ ] Add nonce verification
- [ ] Test content checking

#### Pre-Publish Review
- [ ] File: `includes/features/class-wps-feature-pre-publish-review.php`
- [ ] Handler 1: `ajax_run_pre_publish_check()`
- [ ] Handler 2: `ajax_save_review_preferences()`
- [ ] Add nonce to both handlers
- [ ] Test pre-publish validation
- [ ] Test user preferences

#### Setup Checks
- [ ] File: `includes/features/class-wps-feature-setup-checks.php`
- [ ] Handler 1: `ajax_run_setup_checks()`
- [ ] Handler 2: `ajax_dismiss_setup_notice()`
- [ ] Add nonce to both handlers
- [ ] Test setup checks
- [ ] Test notice dismissal

#### Tips Coach
- [ ] File: `includes/features/class-wps-feature-tips-coach.php`
- [ ] Handler 1: `ajax_dismiss_tip()`
- [ ] Handler 2: `ajax_apply_tip_action()`
- [ ] Add nonce to both handlers
- [ ] Test tip dismissal
- [ ] Test tip actions

### JavaScript Updates

- [ ] Update all AJAX calls to include nonce
- [ ] Update `assets/js/*.js` files
- [ ] Search for `fetch(` and add nonce parameter
- [ ] Search for `jQuery.post(` and add nonce
- [ ] Search for `wp.apiFetch` and add nonce header
- [ ] Test each AJAX call in browser console
- [ ] Check browser Network tab for nonce in requests

### Verification

- [ ] Test each AJAX handler with invalid nonce (should fail)
- [ ] Test each AJAX handler with valid nonce (should work)
- [ ] Test with `WP_DEBUG` enabled
- [ ] Check error logs for nonce warnings
- [ ] Verify no console errors in browser

---

## Phase 2: Capability Alignment (Week 2)

### Content Optimizer
- [ ] File: `includes/features/class-wps-feature-content-optimizer.php`
- [ ] Find: `'id' => 'content-optimizer'`
- [ ] Add: `'minimum_capability' => 'edit_posts',`
- [ ] Test with Admin (should work)
- [ ] Test with Editor (should work after fix)
- [ ] Test with Author (should work after fix)
- [ ] Test with Contributor (should NOT work)

### Pre-Publish Review
- [ ] File: `includes/features/class-wps-feature-pre-publish-review.php`
- [ ] Find: `'id' => 'pre-publish-review'`
- [ ] Add: `'minimum_capability' => 'edit_posts',`
- [ ] Test with Admin (should work)
- [ ] Test with Editor (should work after fix)
- [ ] Test with Author (should work after fix)
- [ ] Test with Contributor (should NOT work)

### Paste Cleanup
- [ ] File: `includes/features/class-wps-feature-paste-cleanup.php`
- [ ] Find: `'id' => 'paste-cleanup'`
- [ ] Add: `'minimum_capability' => 'edit_posts',`
- [ ] Test paste cleanup with non-admin user
- [ ] Verify content filtering works in editor
- [ ] Test with WP-CLI if available

### Role Testing Checklist

For each updated feature, test with these roles:

**Admin**
- [ ] Can see feature in dashboard
- [ ] Can toggle feature on/off
- [ ] Can access all settings
- [ ] Can use feature in editor

**Editor**
- [ ] Can see feature in dashboard ✓ (after fix)
- [ ] Cannot toggle feature on/off ✓
- [ ] Can access feature in editor ✓ (after fix)
- [ ] Feature works correctly in editor ✓

**Author**
- [ ] Can see feature in dashboard ✓ (after fix)
- [ ] Cannot toggle feature on/off ✓
- [ ] Can access feature in editor ✓ (after fix)
- [ ] Feature works correctly in editor ✓

**Contributor**
- [ ] Cannot see feature in dashboard ✓
- [ ] Cannot access feature in editor ✓

---

## Phase 3: Comprehensive Testing (Week 3)

### Functional Testing

#### Core Features
- [ ] Test Content Optimizer with various content types
- [ ] Test Pre-Publish Review with all checks enabled
- [ ] Test Paste Cleanup with Word document content
- [ ] Test Core Diagnostics report generation
- [ ] Test Core Integrity file scanning

#### Dashboard
- [ ] Enable all 39 features
- [ ] Dashboard loads without errors
- [ ] Feature list displays correctly
- [ ] Feature search works
- [ ] Command palette integration works

#### AJAX Security
- [ ] Test all 17 handlers with missing nonce
- [ ] Test all 17 handlers with invalid nonce
- [ ] Test all 17 handlers with valid nonce
- [ ] Verify error messages are helpful
- [ ] Check no sensitive data leaked in errors

#### Role-Based Access
- [ ] Test 3 content features with Editor role
- [ ] Test admin features restricted to admins
- [ ] Test front-end features accessible to all
- [ ] Test with custom roles if applicable

### Performance Testing

- [ ] Load dashboard with all features enabled
- [ ] Measure initial page load time
- [ ] Measure AJAX response times
- [ ] Check memory usage with all features
- [ ] Profile slow operations

### Multisite Testing (if applicable)

- [ ] Test features on main site
- [ ] Test features on secondary site
- [ ] Verify cache isolation between sites
- [ ] Test network admin features
- [ ] Test site-specific settings isolation

### Browser Testing

- [ ] Chrome (latest)
- [ ] Firefox (latest)
- [ ] Safari (latest)
- [ ] Edge (latest)
- [ ] Mobile browsers (iOS Safari, Chrome Mobile)

### PHP Version Testing

- [ ] PHP 7.4
- [ ] PHP 8.0
- [ ] PHP 8.1
- [ ] PHP 8.2

---

## Phase 4: Deployment Prep (Week 4)

### Documentation

- [ ] Update README with new capability requirements
- [ ] Update CHANGELOG with all fixes
- [ ] Add security advisory note
- [ ] Document nonce implementation pattern
- [ ] Add troubleshooting guide for common issues

### Code Review

- [ ] Self-review all changes
- [ ] Check for consistent code style
- [ ] Verify all nonces are properly registered
- [ ] Check for any missed handlers
- [ ] Verify no debug code left in commits

### Git Management

- [ ] Create proper commit messages
  ```
  Fix: Add nonce verification to AJAX handlers
  
  - Addresses CSRF vulnerability
  - Adds defense-in-depth security
  - Fixes: 17 AJAX handlers across 11 files
  ```
  
  ```
  Feature: Update content editor capability requirements
  
  - Content Optimizer now uses edit_posts
  - Pre-Publish Review now uses edit_posts
  - Paste Cleanup now uses edit_posts
  - Improves usability for non-admin editors
  ```

- [ ] Squash commits if needed
- [ ] Rebase on latest main
- [ ] Push to feature branch
- [ ] Create Pull Request

### Pre-Deploy Checklist

- [ ] All tests passing locally
- [ ] No console errors
- [ ] No PHP notices/warnings
- [ ] WPCS standards pass
- [ ] PHPStan analysis clean
- [ ] Code review approved

### Deployment

- [ ] Create Git tag: `v1.x.x-security-patch`
- [ ] Update version in main plugin file
- [ ] Update version in readme.txt
- [ ] Generate release notes
- [ ] Deploy to production
- [ ] Monitor error logs
- [ ] Verify all features still work
- [ ] Announce security updates

### Post-Deployment

- [ ] Monitor for user issues
- [ ] Check error logs for next 24 hours
- [ ] Verify analytics show no anomalies
- [ ] Collect user feedback
- [ ] Prepare hotfix if needed

---

## Sign-Off Checklist

### Development Complete
- [ ] All code changes implemented
- [ ] All tests written and passing
- [ ] Code review completed
- [ ] Documentation updated

### QA Complete
- [ ] Security testing passed
- [ ] Functional testing passed
- [ ] Performance testing passed
- [ ] Browser compatibility verified
- [ ] Mobile compatibility verified

### Business Ready
- [ ] Product owner approved
- [ ] Release notes prepared
- [ ] Communication plan ready
- [ ] Support team briefed
- [ ] Monitoring set up

### Deployment Ready
- [ ] Backup created
- [ ] Rollback plan documented
- [ ] Deployment checklist reviewed
- [ ] Team notified of deployment time
- [ ] On-call support assigned

---

## Issue Tracking

### Block Trackers

| Issue | Status | Notes |
|-------|--------|-------|
| Nonce on all AJAX | ⬜ TODO | 17 handlers |
| Capability updates | ⬜ TODO | 3 features |
| JS nonce integration | ⬜ TODO | Multiple files |
| Testing | ⬜ TODO | Comprehensive suite |
| Documentation | ⬜ TODO | Guides and changelog |

### Testing Results Log

```
[DATE] - [FEATURE] - [RESULT] - [NOTES]
---
2026-01-19 - Core Diagnostics - PASS - Nonce verification working
2026-01-19 - Content Optimizer - PASS - Editor role can now access
```

---

## Contact & Escalation

### Questions About Audit
- Review: FEATURE_AUDIT_REPORT.md
- TL;DR: FEATURE_AUDIT_SUMMARY.md
- Implementation: FEATURE_AUDIT_IMPLEMENTATION.md

### Technical Issues
- Check error logs: `wp-content/debug.log`
- Check browser console for JS errors
- Monitor PHP error log
- Test with `WP_DEBUG = true`

### Security Concerns
- Review nonce implementation pattern
- Verify all handlers protected
- Test with invalid nonces
- Monitor for bypass attempts

---

## Approval Sign-Off

**Audit Date:** January 19, 2026

**Reviewed By:**
- [ ] Development Lead
- [ ] Security Team
- [ ] QA Lead
- [ ] Project Manager

**Implementation Status:**
- [ ] Ready to implement
- [ ] In progress
- [ ] Complete

**Notes:**
```
[Space for implementation notes and blockers]
```

---

**Last Updated:** January 19, 2026  
**Audit Version:** 1.0  
**Status:** Ready for Implementation
