# WP Support - Production MVP Plan
**Version:** 1.2601.73001  
**Target:** Live sites WITHOUT modules  
**Goal:** Stable, secure, WordPress.org-ready core plugin

---

## Current State Assessment

### Core Features (Module-Independent)
✅ **Dashboard System**
- Custom dashboard with draggable widgets
- Hierarchical navigation (Core > Hub > Spoke)
- Metabox-based settings UI

✅ **Site Management**
- Activity Logger (audit trail)
- Site Health integration
- Snapshot Manager (site backups & rollback)
- Site Audit (performance/security analysis)
- Site Documentation Manager (blueprints & exports)

✅ **Support Tools**
- Hidden Diagnostic API (secure support access tokens)
- Emergency Support page (critical error surfaces)
- Guided Walkthroughs (step-by-step assistance)
- WP-CLI commands (`wp wps modules`, `wp wps settings`)

✅ **Update Management**
- Update Simulator (safe testing environment)
- Staging Manager (isolated testing)
- Backup Verification (recovery drills)
- Plugin Upgrader (install/update flows)

✅ **Infrastructure**
- Module Registry & Toggles (for future expansion)
- License System (single-site + network broadcast)
- Feature Registry (flexible dependencies)
- Settings API (network + site with overrides)
- Notice Manager (persistent dismissal)
- Capability Mapping system

### Dependencies on Modules
⚠️ **Vault references** - Core loads vault files (class-wps-vault.php, class-wps-vault-size-monitor.php)
⚠️ **Module Loader** - Always initializes even without modules

---

## MVP Production Phases

### PHASE 1: Security & Stability Audit
**Goal:** Ensure zero vulnerabilities before public release  
**Timeline:** 2-3 days

#### 1.1 Security Hardening
- [ ] **Nonce Verification Audit**
  - Scan all AJAX handlers for `check_ajax_referer()` or `wp_verify_nonce()`
  - Verify all forms have nonce fields
  - Tools: `grep -r "wp_ajax_" includes/` + manual review

- [ ] **Capability Checks Audit**
  - Ensure `current_user_can()` on ALL admin pages
  - Verify AJAX handlers check permissions BEFORE nonces
  - Check file operations require `manage_options` or stricter

- [ ] **Input Sanitization Audit**
  - All `$_POST`, `$_GET`, `$_REQUEST` must be sanitized
  - Use: `sanitize_text_field()`, `sanitize_email()`, `intval()`, `absint()`
  - No `extract()`, `eval()`, or `unserialize()` on user input

- [ ] **Output Escaping Audit**
  - All echoed variables must use: `esc_html()`, `esc_attr()`, `esc_url()`, `wp_kses_post()`
  - Check admin views for escaped translation strings
  - Verify JSON responses use `wp_send_json_*()` functions

- [ ] **SQL Injection Prevention**
  - All `$wpdb` queries MUST use `->prepare()`
  - No direct variable concatenation in queries
  - Review: class-wps-activity-logger.php, class-wps-snapshot-manager.php

- [ ] **File Upload Security**
  - Verify MIME type checking (not just extension)
  - Check file paths are validated (no `../` traversal)
  - Ensure upload directory has proper permissions

- [ ] **Sensitive Data Exposure**
  - License keys never logged or displayed unmasked
  - Diagnostic tokens expire and are revocable
  - Activity log doesn't capture passwords/keys
  - Debug mode disabled in production

#### 1.2 Error Handling
- [ ] **Graceful Degradation**
  - All `file_get_contents()` calls have error checks
  - All `json_decode()` calls check `json_last_error()`
  - All `wp_remote_get()` calls check `is_wp_error()`

- [ ] **PHP 8.1+ Compatibility**
  - All files have `declare(strict_types=1);`
  - No deprecated function calls
  - Type hints on all functions
  - Run: `composer analyze` (PHPStan Level 6)

- [ ] **Fatal Error Prevention**
  - All class checks with `class_exists()` before instantiation
  - All function checks with `function_exists()` before calling
  - All array access checks with `isset()` or null coalescing

#### 1.3 Multisite Testing
- [ ] **Network Admin Menu**
  - Verify network-only settings accessible to Super Admins only
  - Test license broadcast from network to sub-sites
  - Confirm site-level overrides work correctly

- [ ] **Option Storage**
  - Network options use `get_site_option()` / `update_site_option()`
  - Site options use `get_option()` / `update_option()`
  - No accidental cross-contamination

- [ ] **Activation/Deactivation**
  - Test on main site, sub-site, and network-wide
  - Verify cleanup doesn't delete network data on sub-site deactivation
  - Check transient cache keys are site-specific where needed

---

### PHASE 2: Module Decoupling
**Goal:** Core runs perfectly without any modules installed  
**Timeline:** 1-2 days

#### 2.1 Remove Hard Dependencies
- [ ] **Vault References**
  - Wrap all `WPS_Vault` calls in `class_exists()` checks
  - Make `class-wps-vault-size-monitor.php` conditional on vault module
  - Update `wp_support_init()` to skip vault if not present
  - Add admin notice: "Vault module recommended for X features"

- [ ] **Module Loader**
  - Make module discovery fully optional
  - Skip module catalog refresh if no modules installed
  - Dashboard should show "No modules installed" state gracefully

- [ ] **Dashboard Widgets**
  - Hide module-related widgets when no modules present
  - Show "Getting Started" widget for standalone core
  - Link to module catalog (future: WordPress.org search)

#### 2.2 Standalone Core Experience
- [ ] **Clear Value Proposition**
  - Update plugin description to highlight core-only benefits
  - List features that work WITHOUT modules
  - Explain optional module expansion

- [ ] **Documentation Updates**
  - Create "Core Only Mode" documentation
  - FAQ: "Do I need modules?" → No, optional
  - Explain what each module adds (when/if user needs it)

---

### PHASE 3: WordPress.org Preparation
**Goal:** Meet all WordPress.org plugin guidelines  
**Timeline:** 2-3 days

#### 3.1 Repository Requirements
- [ ] **readme.txt Compliance**
  - Valid header with all required fields
  - "Tested up to" set to latest WP version (6.9)
  - "Stable tag" matches plugin version
  - License: GPL2 or later (already set)
  - Clear description (150 chars max for short description)

- [ ] **File Structure**
  - No executable files (.exe, .sh with +x)
  - No compiled libraries (unless absolutely necessary + documented)
  - Assets folder for wordpress.org: banner-1544x500, icon-256x256, screenshots

- [ ] **Code Standards**
  - Pass: `composer phpcs` (WordPress-Extra standard)
  - Pass: `composer analyze` (PHPStan)
  - Fix all remaining violations
  - Document any intentional exceptions

- [ ] **Text Domain**
  - Verify `plugin-wp-support-thisismyurl` used everywhere
  - All strings wrapped in `__()`, `_e()`, `esc_html__()`, etc.
  - Translator comments where context needed

#### 3.2 Prohibited Practices
- [ ] **No "Powered by" Links**
  - Remove or make optional any external links in admin
  - User should control all branding

- [ ] **No Obfuscated Code**
  - All PHP readable and well-commented
  - No base64 encoding of code
  - No external code execution

- [ ] **No Tracking Without Consent**
  - Opt-in only for any usage stats
  - Clear disclosure in settings
  - Easy to disable

- [ ] **No Paid Features in Free Plugin**
  - Core functionality fully free
  - If license system used, it's for premium addons only
  - Clear distinction between free core and paid modules

#### 3.3 User Experience
- [ ] **Uninstall Cleanup**
  - Create `uninstall.php` (not just deactivation hook)
  - Option to keep or delete settings on uninstall
  - Clean up ALL database entries (options, transients, meta)
  - Clean up ALL uploaded files (if safe)

- [ ] **Activation Flow**
  - Show welcome screen on first activation
  - Highlight key features
  - Link to documentation
  - Optional: Quick setup wizard

- [ ] **Admin Notices**
  - Use `WPS_Notice_Manager` for dismissible notices
  - No persistent nagging (use transient-based timing)
  - Important notices only (errors, security, updates)

---

### PHASE 4: Performance Optimization
**Goal:** Fast, lightweight, no bloat  
**Timeline:** 1-2 days

#### 4.1 Database Optimization
- [ ] **Query Efficiency**
  - Audit all `$wpdb->get_results()` calls
  - Add indexes where needed (activity log, snapshots)
  - Limit default query results (pagination)
  - Use transients for expensive queries (catalog, health checks)

- [ ] **Transient Cache Strategy**
  - Define TTL for each transient type
  - Module catalog: 5 minutes (already set)
  - Site health: 1 hour
  - Activity log aggregates: 15 minutes

- [ ] **Autoload Prevention**
  - Set `'autoload' => false` on large options
  - Activity log settings: no autoload
  - Snapshot metadata: no autoload
  - License keys: yes autoload (small, frequently accessed)

#### 4.2 Asset Loading
- [ ] **Conditional Enqueuing**
  - Only load CSS/JS on plugin admin pages
  - Use `$hook_suffix` in `admin_enqueue_scripts`
  - No front-end assets unless needed

- [ ] **Minification**
  - Minify CSS: `assets/css/*.css` → `*.min.css`
  - Minify JS: `assets/js/*.js` → `*.min.js`
  - Enqueue `.min` versions in production
  - Keep source files for development

- [ ] **Defer/Async Scripts**
  - Non-critical JS should be deferred
  - Use `wp_script_add_data( $handle, 'defer', true )`

#### 4.3 Code Loading
- [ ] **Lazy Loading Classes**
  - Only load what's needed per page
  - Example: Don't load `WPS_Snapshot_Manager` on license page
  - Use hooks to load contextually

- [ ] **Remove Debug Code**
  - No `var_dump()`, `print_r()` in production
  - Use `error_log()` or `WPS_Activity_Logger` instead
  - Clean up commented-out code blocks

---

### PHASE 5: Documentation & Support
**Goal:** Users can self-solve 80% of issues  
**Timeline:** 2-3 days

#### 5.1 In-Plugin Help
- [ ] **Contextual Help Tabs**
  - Add `get_current_screen()->add_help_tab()` on all admin pages
  - Explain each setting/feature
  - Link to external docs for deep dives

- [ ] **Inline Documentation**
  - Settings fields have `description` parameters
  - Dashboard widgets have help icons
  - Tooltips for technical terms

- [ ] **Guided Walkthroughs** (Already implemented)
  - Verify walkthroughs cover:
    - Setting up first snapshot
    - Creating diagnostic token for support
    - Running site audit
    - Understanding site health results

#### 5.2 External Documentation
- [ ] **README.md** (GitHub)
  - Installation instructions
  - Feature overview with screenshots
  - FAQ section
  - Contribution guidelines
  - Support contact info

- [ ] **User Guide** (Wiki or separate site)
  - Getting Started
  - Feature Documentation (each feature = 1 page)
  - Troubleshooting Common Issues
  - Advanced Configuration

- [ ] **Developer Documentation**
  - Hook reference (filters/actions)
  - Class/method reference (auto-generated from PHPDoc)
  - How to extend the plugin
  - Module development guide (future)

#### 5.3 Support Channels
- [ ] **GitHub Issues**
  - Issue templates (bug report, feature request)
  - Labels for triage
  - Contributing guidelines

- [ ] **WordPress.org Support Forum**
  - Monitor and respond within 48 hours
  - Common questions → FAQ

- [ ] **Emergency Support** (Already implemented)
  - Diagnostic API tested and documented
  - Support team trained on token usage

---

### PHASE 6: Pre-Launch Checklist
**Goal:** Final verification before WordPress.org submission  
**Timeline:** 1 day

#### 6.1 Testing Matrix
- [ ] **WordPress Versions**
  - Test on WP 6.4 (minimum required)
  - Test on WP 6.9 (latest)
  - Test on WP beta/RC (if available)

- [ ] **PHP Versions**
  - Test on PHP 8.1.29 (minimum required)
  - Test on PHP 8.2
  - Test on PHP 8.3
  - Test on PHP 8.4 (current)

- [ ] **Server Environments**
  - Apache + mod_php
  - Nginx + PHP-FPM
  - Localhost (Local by Flywheel, XAMPP, etc.)

- [ ] **Hosting Providers** (if possible)
  - Shared hosting (limited resources)
  - Managed WordPress hosting
  - VPS (full control)

#### 6.2 Final Code Review
- [ ] **Security Scan**
  - Run: `composer audit` (check for vulnerable dependencies)
  - Review: All file operations, database queries, AJAX handlers
  - Test: CSRF protection on all forms

- [ ] **Code Quality**
  - Pass: `composer phpcs` with ZERO errors
  - Pass: `composer analyze` with ZERO errors
  - Code coverage: Aim for 70%+ (run PHPUnit)

- [ ] **Performance Baseline**
  - Install on fresh WP site
  - Measure: Admin page load times (<1s)
  - Measure: Database query count per page (<50)
  - Measure: Memory usage (<10MB increase)

#### 6.3 User Acceptance Testing
- [ ] **Fresh Install**
  - Install on clean WP site
  - Activate plugin
  - Verify welcome screen appears
  - Check no errors in debug.log

- [ ] **Core Features**
  - Create a snapshot → restore it
  - Run site audit → view results
  - Generate diagnostic token → verify it works
  - View activity log → verify events logged

- [ ] **Settings**
  - Save settings → verify they persist
  - Network broadcast (multisite) → verify sub-site receives
  - Reset to defaults → verify cleanup

- [ ] **Deactivation/Uninstall**
  - Deactivate → verify no errors
  - Reactivate → verify settings preserved
  - Uninstall → verify ALL data cleaned up (if option selected)

---

## Success Metrics

### Before Launch
- ✅ Zero security vulnerabilities (WPVS scan)
- ✅ 100% WordPress Coding Standards compliance
- ✅ PHPStan Level 6 clean
- ✅ Works on PHP 8.1-8.4
- ✅ Works on WP 6.4-6.9+
- ✅ <10MB memory footprint
- ✅ <1s admin page loads

### After Launch (30 days)
- 📊 100+ active installations
- 📊 4+ star average rating
- 📊 <5% support threads unresolved
- 📊 Zero critical bugs reported
- 📊 Zero security issues reported

---

## Risk Mitigation

### High-Risk Areas
1. **Vault Integration** - If vault module not present, core should handle gracefully
   - Mitigation: Wrap all vault calls in `class_exists()` checks
   
2. **Multisite Complexity** - Network vs site-level settings confusion
   - Mitigation: Clear UI labels, contextual help, thorough testing
   
3. **Snapshot/Backup Feature** - Data loss risk if buggy
   - Mitigation: Extensive testing, warnings, dry-run mode, verification system
   
4. **Hidden Diagnostic API** - Security risk if tokens leaked
   - Mitigation: Short expiration, revocation, IP restrictions, audit logging

5. **Update Simulator** - Could break site if staging environment fails
   - Mitigation: Isolated staging, rollback mechanism, clear warnings

### Rollback Plan
- Keep previous stable version tagged in Git
- WordPress.org allows quick rollback via "Previous Versions" tab
- Emergency deactivation via FTP (rename plugin folder)

---

## Post-Launch Roadmap

### v1.27 - Module Ecosystem Launch
- Publish first modules to WordPress.org
- Document module development API
- Create module starter template

### v1.28 - Pro Features
- Premium module marketplace
- Advanced license management
- White-label options

### v1.29 - Integration Hub
- WooCommerce site audit extensions
- Multisite network topology visualization
- Third-party plugin compatibility database

---

## Deployment Checklist

### Pre-Submission
- [ ] Version number updated in plugin header
- [ ] Version number updated in readme.txt stable tag
- [ ] Changelog updated in readme.txt
- [ ] Git tag created: `git tag 1.2601.73001`
- [ ] Git pushed with tags: `git push origin main --tags`
- [ ] GitHub release created with changelog
- [ ] Assets prepared for WordPress.org (banner, icon, screenshots)

### WordPress.org Submission
- [ ] Create account on wordpress.org
- [ ] Submit plugin via: https://wordpress.org/plugins/developers/add/
- [ ] Wait for review (typically 3-14 days)
- [ ] Respond to reviewer feedback promptly
- [ ] Once approved, upload via SVN
- [ ] Monitor support forum daily

### Post-Launch
- [ ] Announce on social media (Twitter, LinkedIn)
- [ ] Post in relevant WordPress communities (Reddit, Facebook groups)
- [ ] Update personal site with case study
- [ ] Email existing users about stable release
- [ ] Monitor error logs and support tickets closely

---

## Notes

**Current Version:** 1.2601.73001  
**PHP Requirement:** 8.1.29+  
**WP Requirement:** 6.4+  
**License:** GPL2+  
**Author:** Christopher Ross (@thisismyurl)

**Development Philosophy:**
- Security first, features second
- Backward compatibility over breaking changes
- User experience over developer convenience
- Documentation is not optional
- Every setting has a sane default

**Core Principles (No Modules):**
1. The plugin must provide significant value WITHOUT modules
2. Modules are enhancements, not requirements
3. Core should be stable, lightweight, and secure
4. All features must be production-grade (no beta labels)

---

**Last Updated:** 2026-01-11  
**Status:** DRAFT - Awaiting team review
