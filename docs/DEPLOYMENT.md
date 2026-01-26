# WPShadow Deployment Guide

**Version:** 1.0  
**Date:** January 22, 2026  
**Status:** Production Ready

---

## 📦 Release Process

### Pre-Release Checklist

**Code Quality**
- [ ] All tests passing locally
- [ ] No PHP syntax errors: `find includes -name "*.php" -print0 | xargs -0 php -l`
- [ ] No WordPress coding standard violations: `composer phpcs`
- [ ] Static analysis clean: `composer phpstan`

**Philosophy Check**
- [ ] All new diagnostics link to KB articles
- [ ] All new treatments link to training videos
- [ ] KPI tracking added (if applicable)
- [ ] No paywalls on free tier features
- [ ] UX copy reviewed (plain English, no jargon)
- [ ] Privacy/consent verified (if data collection)

**Documentation**
- [ ] TECHNICAL_STATUS.md updated
- [ ] CHANGELOG.md entry added
- [ ] New features documented in FEATURE_MATRIX_*.md
- [ ] KB articles updated/created
- [ ] GitHub issues closed or linked

**Testing**
- [ ] Functionality tested in GitHub Codespaces environment
- [ ] Dashboard displays correctly
- [ ] Treatments execute with proper undo
- [ ] AJAX handlers respond correctly
- [ ] Multisite compatibility verified
- [ ] No fatal errors in WordPress error log

### Build Process

```bash
# 1. Update version in wpshadow.php
# Format: 1.YYMM.DDHH (e.g., 1.2601.2112 = 2026-01-22 21:12)

# 2. Commit changes
git add .
git commit -m "Release v1.YYMM.DDHH - [Brief Description]"

# 3. Create annotated tag
git tag -a v1.YYMM.DDHH -m "WPShadow v1.YYMM.DDHH - [Description]"

# 4. Push to GitHub
git push origin main --tags

# 5. GitHub Actions will:
#    - Run tests
#    - Create release notes from commits
#    - Build plugin package
```

---

## 🚀 Deployment Environments

### Development
- **Location:** WordPress site (wpshadow-site)
- **Database:** MySQL 8.0
- **PHP Version:** 8.3.30
- **Updates:** Real-time from git

### Staging (Optional)
- **Purpose:** Final QA before production release
- **WordPress Version:** Latest stable
- **Multisite:** Enabled
- **Testing:** All 57 diagnostics, 44 treatments

### Production
- **Distribution:** WordPress plugin repository
- **Installation:** Via WordPress admin > Plugins > Add New
- **Update Mechanism:** Automatic via WordPress.org

---

## 📋 Release Types

### Patch Release (1.YYMM.0001 → 1.YYMM.0002)
**Use for:** Bug fixes, security patches  
**Example:** Fixing smart quote issues, PHP syntax error

**Process:**
```bash
# Review: Was it tested? Is it safe?
# Check: No new features, no architecture changes
# Deploy: Can go directly to production
```

### Minor Release (1.YYMM.XXYY → 1.YYMM+1.0001)
**Use for:** New features, improvements  
**Example:** New diagnostic category, treatment additions

**Process:**
```bash
# Review: Does it follow architecture?
# Check: Philosophy verified? Tests pass?
# Stage: Test in staging environment first
# Deploy: With release notes
```

### Major Release (1.XXYY → 1.XXYY+1)
**Use for:** Architecture changes, significant refactoring  
**Example:** Base class introduction, complete rewrite of module

**Process:**
```bash
# Review: 2+ approval from senior devs
# Check: Complete test coverage
# Stage: Extended staging period
# Deploy: With detailed migration guide
```

---

## 🔐 Security Protocol

**Before ANY deployment:**

1. **Security Audit**
   - [ ] No hardcoded credentials
   - [ ] All inputs validated/sanitized
   - [ ] All outputs escaped
   - [ ] No SQL injection vulnerabilities
   - [ ] Nonce verification on AJAX
   - [ ] Capability checks in place
   - [ ] See [CODING_STANDARDS.md](CODING_STANDARDS.md#security-patterns)

2. **Data Privacy Review**
   - [ ] No personal data collection without consent
   - [ ] GDPR compliance verified
   - [ ] Privacy policy updated if needed
   - [ ] User data handling documented

3. **Vulnerability Scan**
   - [ ] Dependencies up to date: `composer update`
   - [ ] No known CVEs
   - [ ] Plugin linting clean: `wp-cli plugin-search`

---

## 📊 Monitoring Post-Deployment

### First 24 Hours
- Monitor error logs for fatal errors
- Check dashboard for UI glitches
- Verify AJAX endpoints responding
- Monitor plugin activation failures

### First Week
- Track user bug reports
- Monitor performance metrics
- Check update adoption rates
- Verify no incompatibilities reported

### Ongoing
- Weekly: Review error logs
- Weekly: Check KB/training engagement
- Monthly: Review KPI metrics
- Monthly: Security audit

---

## 🔄 Rollback Procedure

If critical issue found post-deployment:

```bash
# 1. Identify previous stable version
git tag -l | sort -V | tail -3

# 2. Create hotfix branch
git checkout -b hotfix/critical-issue v1.2601.0001

# 3. Fix the issue
# ... make changes ...

# 4. Test locally
# Restart WordPress/PHP-FPM if needed
# ... verify fix ...

# 5. Commit and tag
git add .
git commit -m "Hotfix: [Issue Description]"
git tag v1.2601.0002
git push origin hotfix/critical-issue --tags

# 6. Users will auto-update within 24 hours
```

---

## 📈 Performance Benchmarks

**Target Metrics (Phase 4+):**
- Dashboard load time: < 2 seconds
- Diagnostic run time: < 5 seconds
- Treatment execution: < 3 seconds
- AJAX response time: < 500ms

**Measurement:**
```php
// Use KPI_Tracker to monitor
KPI_Tracker::record_diagnostic_run('ssl', true, $duration_ms);
```

---

## 🎯 Release Notes Template

```markdown
# WPShadow v1.YYMM.DDHH

**Release Date:** [Date]  
**Status:** [Stable | RC | Beta]

## ✨ What's New

### Features
- Feature 1: Description
- Feature 2: Description

### Improvements
- Improvement 1: Description
- Improvement 2: Description

### Bug Fixes
- Fixed: Issue description
- Fixed: Another issue

### Security
- [Security fix description if applicable]

## 🔗 Resources
- [Knowledge Base](https://wpshadow.com/kb)
- [Training Videos](https://wpshadow.com/training)
- [Full Changelog](https://github.com/thisismyurl/wpshadow/releases)

## 📋 Requirements
- WordPress: 5.8+
- PHP: 7.4+
- MySQL: 5.7+

## ⚠️ Breaking Changes
[If applicable, list any backwards-incompatible changes]

## 🙏 Thanks
Thanks to [contributors] for this release!
```

---

## 📚 Related Documentation

- [ROADMAP.md](ROADMAP.md) - Phase delivery timeline
- [TECHNICAL_STATUS.md](TECHNICAL_STATUS.md) - Current feature status
- [CODING_STANDARDS.md](CODING_STANDARDS.md) - Code quality requirements
- [GITHUB_WORKFLOW.md](GITHUB_WORKFLOW.md) - Issue and label workflow
- [PRODUCT_PHILOSOPHY.md](PRODUCT_PHILOSOPHY.md) - Philosophy verification

---

## 🚨 Emergency Contacts

For critical production issues:
1. Review error logs immediately
2. Check [GitHub Issues](https://github.com/thisismyurl/wpshadow/issues)
3. Post security issues privately to [security contact]

---

**Version:** 1.0  
**Last Updated:** January 22, 2026
