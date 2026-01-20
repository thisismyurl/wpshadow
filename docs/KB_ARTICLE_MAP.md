# WPShadow Knowledge Base Article Map

This document tracks all diagnostic findings and their corresponding knowledge base articles to be created on wpshadow.com.

## Security & Performance Findings

### 1. No Backup Solution Detected
- **Finding ID:** `backup-missing`
- **Issue:** Your site has no automated backup plugin active. Regular backups are critical for recovery.
- **Link:** `https://wpshadow.com/kb/how-to-set-up-automated-backups/`
- **Status:** Article needed
- **Priority:** Critical
- **Content outline:**
  - Why backups matter
  - Recommended backup plugins (UpdraftPlus, Jetpack Backup, Backwpup)
  - How to configure auto-backups
  - Offsite storage options
  - Restoration process

### 2. SSL Certificate Not Active
- **Finding ID:** `ssl-missing`
- **Issue:** Your site is not using HTTPS. This reduces security and may impact SEO.
- **Link:** `https://wpshadow.com/kb/enable-https-ssl-on-your-site/`
- **Status:** Article needed
- **Priority:** Critical
- **Content outline:**
  - Benefits of HTTPS
  - How to get a free SSL certificate
  - Hosting provider instructions
  - WordPress configuration (Jetpack, plugins)
  - SEO impact

### 3. You Have N Outdated Plugins
- **Finding ID:** `outdated-plugins`
- **Issue:** Outdated plugins can cause security vulnerabilities and conflicts. Update them as soon as possible.
- **Link:** `https://wpshadow.com/kb/how-to-safely-update-plugins/`
- **Status:** Article needed
- **Priority:** High
- **Content outline:**
  - Why plugin updates matter
  - Testing updates on staging
  - Safe update process
  - Troubleshooting broken updates
  - Reverting broken updates

### 4. PHP Memory Limit Too Low
- **Finding ID:** `memory-limit-low`
- **Issue:** Your PHP memory limit is NMB. Recommended: 64MB+ (256MB ideal). This can cause plugin conflicts and timeouts.
- **Link:** `https://wpshadow.com/kb/increase-php-memory-limit/`
- **Status:** Article needed
- **Priority:** Medium
- **Content outline:**
  - What PHP memory is
  - How to check current limit
  - Contact hosting provider template
  - How to modify wp-config.php
  - Recommended limits by site type

### 5. Permalink Structure Not Set
- **Finding ID:** `permalinks-plain`
- **Issue:** Your site is using plain permalinks (/?p=123). This hurts SEO and user experience. Switch to a prettier structure.
- **Link:** `https://wpshadow.com/kb/configure-wordpress-permalinks-for-seo/`
- **Status:** Article needed
- **Priority:** Medium
- **Content outline:**
  - Why pretty permalinks matter
  - Available permalink structures
  - How to change in WordPress Settings
  - SEO best practices
  - Redirects needed (if changing existing site)

### 6. Site Tagline is Empty
- **Finding ID:** `tagline-empty`
- **Issue:** Add a tagline (Settings → General) to improve SEO and help visitors understand your site quickly.
- **Link:** `https://wpshadow.com/kb/write-an-effective-site-tagline/`
- **Status:** Article needed
- **Priority:** Low
- **Content outline:**
  - What is a site tagline
  - Why it helps SEO
  - Examples of good taglines
  - Where to find it in WordPress
  - Best practices (length, keywords)

### 7. Debug Mode Enabled
- **Finding ID:** `debug-mode-enabled`
- **Issue:** WordPress debug mode is active. Disable it on live sites for better security.
- **Link:** `https://wpshadow.com/kb/disable-wordpress-debug-mode/`
- **Status:** Article needed
- **Priority:** High
- **Content outline:**
  - What debug mode does
  - Security risks of debug mode
  - How to disable in wp-config.php
  - When to enable (local/staging only)
  - Viewing logs safely

### 8. WordPress Update Available
- **Finding ID:** `wordpress-outdated`
- **Issue:** You're running WordPress N. Updating improves security and performance.
- **Link:** `https://wpshadow.com/kb/how-to-update-wordpress-safely/`
- **Status:** Article needed
- **Priority:** High
- **Content outline:**
  - Why WordPress updates matter
  - Pre-update checklist (backup, compatibility)
  - Step-by-step update process
  - Rollback if something breaks
  - Common update issues

### 9. High Plugin Count (50+)
- **Finding ID:** `plugin-count-high`
- **Issue:** You have many plugins active. Consider auditing for unused ones—each adds overhead.
- **Link:** `https://wpshadow.com/kb/audit-and-optimize-your-wordpress-plugins/`
- **Status:** Article needed
- **Priority:** Medium
- **Content outline:**
  - Why too many plugins slow sites
  - How to audit installed plugins
  - Unused plugin removal
  - Plugin consolidation (reducing count)
  - Finding alternative all-in-one solutions
  - Performance testing before/after

---

## Guardian Auto-Fix Articles

### Enable Guardian Auto-Protection
- **Article Link:** `https://wpshadow.com/kb/what-is-guardian-and-how-does-it-work/`
- **Content outline:**
  - What Guardian does
  - Auto-fixes it can apply
  - Undo/rollback capability
  - Monitoring and alerts
  - When to manually intervene

---

## Usage Notes

- Update this map as new findings are added
- Prioritize Critical and High priority articles first
- All links should be lowercase with hyphens
- Each article should take 3-5 minutes to read
- Include "What to do if..." troubleshooting sections
- Link to each other where relevant (cross-linking)
