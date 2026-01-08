# Support Dashboard User Guide

Welcome to the **Support Dashboard**—the command center for managing the @thisismyurl Support Suite on your WordPress site.

---

## Table of Contents

1. [Overview](#overview)
2. [Dashboard Guide](#dashboard-guide)
3. [Module Management](#module-management)
4. [Settings & Configuration](#settings--configuration)
5. [Multisite Administration](#multisite-administration)
6. [Troubleshooting & FAQ](#troubleshooting--faq)

---

## Overview

### What is Core Support?

**Core Support** is the Hub of the @thisismyurl Support Suite. It provides:

- **Foundation Architecture:** Multi-engine image processing fallback (Imagick/GD)
- **Encryption & Security:** Data protection, CSRF/XSS prevention, capability-based access control
- **Module Registry:** Auto-discovery and management of format-specific Spokes (AVIF, WebP, HEIC, RAW, SVG, TIFF, BMP, GIF)
- **Cloud Bridge:** Integration with remote licensing and updates
- **Killer Features:** Pixel-Sovereign fingerprinting, Smart Focus-Point detection, The Vault for originals, Surgical Scrubbing for metadata, Broken Link Guardian for self-healing 404s

### Role in the Suite

The Suite operates on a **Hub & Spoke** model:

- **Hub (Core Support):** Shared infrastructure, security, multi-engine logic
- **Spokes (Format Plugins):** Lean, format-specific transcoders
- **Suite Handshake:** `Suite: thisismyurl-media-suite-2026` ensures only compatible modules load

**Your Role:** As an admin, you activate/deactivate modules, configure site-wide policies, and monitor the health of the suite on your site.

---

## Dashboard Guide

### Accessing the Dashboard

1. Log in to WordPress Admin
2. Click **Support** in the left admin menu
3. Select **Dashboard** (first submenu item)

The Dashboard appears as a lightweight **overview page** with:
- Quick statistics about active modules
- A button linking to the **Modules** page (where all management happens)
- Recent activity notifications and pending items
- A link to **Go to Modules** for full workspace access

### Dashboard Components

#### Statistics Cards

At the top of the Dashboard, you'll see six metric cards:

| Card | Shows | Purpose |
|------|-------|---------|
| **Total Modules** | All Hubs + Spokes in the catalog | Tells you how many modules are available |
| **Enabled** | Modules currently active on this site | Shows your active module count |
| **Available** | Installed but not activated modules | Modules ready to use but off |
| **Updates Available** | Modules with newer versions | Which modules need updating |
| **Hubs** | Count of Hub plugins (core engines) | Usually 1–2 (Core Support, Image Support, etc.) |
| **Spokes** | Count of Spoke plugins (format-specific) | AVIF, WebP, HEIC, BMP, GIF, etc. |

**Multisite Note:**
- On single-site: Numbers reflect only locally active plugins
- On multisite sub-site: Numbers include both site-active and network-active plugins
- On network admin: Numbers reflect network-wide modules across all sites

#### Activity Log

The **Activity Log** section displays recent plugin and vault events in a table:

| Column | Content |
|--------|---------|
| **Time** | When the event occurred (formatted for your locale) |
| **Level** | `info` (normal), `warning` (noteworthy), or `error` (failed action) |
| **Task** | What happened: `install`, `update`, `toggle`, `vault-capture`, etc. |
| **File** | Affected filename or attachment ID |
| **User** | WordPress user who triggered the action |
| **Details** | Optional reason or context (e.g., "Updated to v1.2.601") |

**Example Log Entries:**
- _Time:_ Jan 8, 2:30 PM | _Level:_ info | _Task:_ install | _File:_ avif-spoke.zip | _User:_ admin | _Details:_ Installed via dashboard
- _Time:_ Jan 8, 1:15 PM | _Level:_ warning | _Task:_ update-failed | _File:_ webp-spoke | _User:_ admin | _Details:_ Disk space low; retry needed

#### Scheduled Tasks

The **Scheduled Tasks** section shows upcoming maintenance jobs:

| Column | Meaning |
|--------|---------|
| **Task** | Name of the scheduled job (e.g., "License Validation Check") |
| **Next Run** | When the task will execute (e.g., "Jan 9, 3:00 AM") |
| **Last Run** | When it last completed (e.g., "Jan 8, 3:00 AM") |
| **Status** | Current state: `Scheduled`, `Running`, or error message |
| **Action** | **Run now** button to execute immediately |

**Common Scheduled Tasks:**
- **License Validation:** Validates your license key against thisismyurl.com servers
- **Update Check:** Checks for module updates
- **Vault Cleanup:** Deletes old originals based on retention policy
- **Audit Log Cleanup:** Archives old audit entries

**To Run a Task Immediately:**
1. Locate the task in the table
2. Click **Run now**
3. Wait 5–10 seconds for it to complete
4. Refresh the page to see updated "Last Run" time

#### Pending Contributor Uploads

If your site accepts contributor uploads (e.g., from authors, editors):

| Column | Meaning |
|--------|---------|
| **File** | Filename or post title |
| **Uploaded by** | Contributor's display name |
| **Uploaded at** | Upload date/time |
| **Optimized** | `Yes` or `No` — whether Core Support auto-optimized the file |
| **Action** | **Review** button → Opens the attachment editor |

**What This Means:**
- Contributors upload media, which Core Support automatically optimizes
- Editors+ review and approve before files go live
- Click **Review** to examine the optimized file and approve/reject

### Dashboard as a Landing Zone

The Dashboard is intentionally **lightweight** because:
1. **Onboarding:** New admins land here and see at-a-glance health
2. **Quick Link:** The **Go to Modules** button takes you to the full management interface
3. **Notifications:** Urgent alerts (failed license, low disk space) appear here first
4. **Status Overview:** Check activity and recent events without opening the full workspace

**Next Step:** Click **Go to Modules** to access the full Modules page where you'll do most of your work (activate/deactivate, install, update).

---

## Module Management

### The Modules Workspace

The **Modules** page is your primary interface for managing plugins. Access it:

1. Go to **Support** > **Dashboard**
2. Click **Go to Modules**, or
3. Directly: **Support** > **Modules**

### Modules Page Layout

The page is organized top-to-bottom:

#### 1. Page Header
- Title: **Modules**
- Help icon with tooltip explaining network-active behavior
- Quick description of the page purpose

#### 2. Stat Cards (Responsive Grid)
Same statistics as the Dashboard:
- **Total, Enabled, Available, Updates, Hubs, Spokes**
- Numbers update live when you activate/deactivate modules
- Click a card to filter the modules table (future feature)

#### 3. Modules Table (Grouped & Collapsible)

**Structure:**

```
Hub: Image Support (Hub row - click ▼ arrow to collapse/expand)
├─ AVIF Spoke [Status] [Version] [Author]
├─ WebP Spoke [Status] [Version] [Author]
└─ HEIC Spoke [Status] [Version] [Author]

Hub: Core Support (no spokes; always expanded)
└─ [Status] [Version] [Author]
```

**Columns:**

| Column | Content |
|--------|---------|
| **Module** | Name + description; hub rows have a ▼ toggle arrow |
| **Requires** | Dependency (e.g., "Core 1.2.600" for spokes, "-" for hubs) |
| **Status** | Active/Inactive; includes badges, action links |
| **Version** | Current version; **Update** link if newer is available |
| **Author** | Author name (linked to GitHub) |

#### 4. Collapsible Hubs

Hub rows have a **toggle arrow** (▼):

- **Click arrow** to expand and show child Spokes
- **Collapsed state:** Only shows the Hub; Spokes hidden
- **Expanded state:** Shows Hub + all Spokes indented
- **Your choice persists:** Page remembers your expand/collapse state

#### 5. Status Column Details

The **Status** column is information-rich:

**Module not installed:**
- Shows: **[Install and Activate]** button
- What it does: Downloads, installs, and activates in one click
- After click: Page reloads; module is now active

**Module installed but inactive:**
- Shows: **Installed** (plain text)
- Shows: **activate** link below it
- Click to activate immediately

**Module is site-active (locally enabled):**
- Shows: **Active** (plain text)
- Status is clear; no action link needed
- For network-active modules, see below

**Module is network-active:**
- Shows: **Active** (plain text)
- Shows: **[Network Active]** badge (blue)
- If you're Network Admin: **Deactivate Network** link appears
- If you're Site Admin: No deactivate link; module is locked

**Module has update available:**
- Shows version number
- Shows: **[Update]** link below version
- Click to download and install new version
- Module stays active; no downtime

**Badges:**

| Badge | Meaning | Action |
|-------|---------|--------|
| **Network Active** | Plugin is activated network-wide | Only Network Admin can deactivate |
| **Override Locked** | Network policy forbids site customization | Contact Network Admin to unlock |

### Managing Modules Step-by-Step

#### Activating a Module

**Scenario:** You want to enable AVIF support.

**Steps:**
1. Find **AVIF Spoke** in the modules table (under **Image Support** hub)
2. Look at the **Status** column
3. Click **activate** (small link)
4. Page refreshes automatically (~2 seconds)
5. Status now shows **Active**
6. Your site now processes AVIF images

**What happens behind the scenes:**
- WordPress registers the plugin's hooks and filters
- Core Support (Hub) discovers the AVIF Spoke
- Image uploads are now routed through the AVIF processor
- Statistics update; **Enabled** count increases

#### Deactivating a Module

**Scenario:** You want to disable WebP temporarily for testing.

**Steps:**
1. Find **WebP Spoke** in the modules table
2. Status shows **Active**
3. A **deactivate** link appears (small text)
4. Click it
5. Page refreshes
6. Status shows **Installed**
7. WebP processing stops; existing WebP files remain in the media library

**Note:** Deactivation does not delete anything; it just stops processing new uploads.

#### Installing a New Module

**Scenario:** You want to add HEIC support but it's not installed yet.

**Prerequisites:**
- You have **Edit Plugins** capability (Admin or Super Admin)
- Your hosting provides at least 50MB free disk space
- Core Support (Hub) is already active

**Steps:**
1. Find the module in the table (search the page with Ctrl+F)
2. Status shows: **[Install and Activate]** button
3. Click the button
4. A confirmation dialog appears showing:
   - Module name and description
   - File size (~3–15 MB for Spokes)
   - Required PHP/WordPress versions
5. Click **Proceed** to install
6. Page shows a progress indicator (brief)
7. Page reloads automatically
8. Module is now **Active**

**If installation fails:**
- A red error message appears with the reason
- Common causes: Disk space, permissions, network timeout
- Retry after waiting a few seconds
- Contact support if errors persist

#### Updating a Module

**Scenario:** AVIF Spoke shows version 1.2.500, but 1.2.601 is available.

**Prerequisites:**
- The module is already installed and active
- You have **Manage Plugins** capability

**Steps:**
1. Find **AVIF Spoke** in the table
2. **Version** column shows: `1.2.500` + **[Update]** link
3. Click **[Update]**
4. A progress indicator appears
5. Page reloads automatically after ~10 seconds
6. **Version** now shows: `1.2.601`
7. The module remains **Active**; no downtime

**Update Safety:**
- Your media library and settings are never touched
- The module's hooks/filters are seamlessly updated
- If an update causes issues, see [Troubleshooting](#troubleshooting--faq)

#### Viewing Module Details

Each module row includes a **Details** link:

- Click to open the module's **GitHub repository** or **official documentation**
- Opens in a new tab with security (`rel="noopener noreferrer"`)
- Useful for:
  - Reading change logs
  - Reporting bugs
  - Learning how the module works
  - Viewing source code (open-source modules)

---

## Settings & Configuration

### Accessing Settings

1. **Single-site admin:** Click **Support** > **Settings**
2. **Network admin:** Click **Support** > **Network Settings**

### Settings Page Overview

The Settings page provides site-wide (or network-wide) configuration:

#### Suite Registration

**Section: Suite Registration**

- **License Key:** Enter your @thisismyurl license key here
- **Status:** Shows registration status (Valid, Invalid, Not Registered)
- **Last Checked:** Timestamp of the most recent validation attempt

**Why register?**
- Unlock premium Spokes (e.g., RAW, HEIC, advanced compression)
- Receive priority support
- Auto-updates are faster and more reliable

**If you're unregistered:**
- A reminder notice appears in your admin area every 6–12 months
- Core features remain functional, but premium Spokes are locked
- Click the notice to go straight to the registration form

#### Vault Configuration

**Section: The Vault**

- **Enable Vault:** Toggle original file storage
- **Vault Location:** Where originals are stored (`/vault/` or custom path)
- **Cleanup Policy:** Auto-delete originals after X days (default: 90 days)
- **Backup:** Enable automated daily backups of Vault contents

**What is The Vault?**
- Secure storage for original uploaded files before processing
- Enables lossless recovery if a format Spoke fails
- Encrypted and hidden from public access
- Includes metadata like upload source, processed formats, and history

**Best Practice:**
- Enable The Vault for production sites
- Set cleanup to 30–90 days depending on your storage budget
- Enable backups if you run a critical site

#### Privacy & GDPR Settings

**Section: Privacy & Compliance**

- **EXIF Stripping:** Automatically remove GPS and privacy metadata from all uploads (default: enabled)
- **Brand Metadata:** Inject copyright notice, site URL, and custom metadata (default: enabled)
- **Audit Logging:** Log all Vault access and data export requests (default: enabled)

**Audit Trail:**
- View the audit log under **Support** > **Audit Log**
- Logs record: Who exported/erased what, when, and why
- Useful for GDPR compliance documentation

### Multisite Settings (Network Admin Only)

If you're a Super Admin managing a multisite network, the **Network Settings** page provides:

#### Network Policies

**Section: Global Policies**

- **Network-Activate by Default:** Modules activated network-wide on all new sub-sites
- **Lock Module Settings:** Prevent site admins from deactivating certain modules
- **Enforce Vault Policy:** All sites must use the same Vault location and cleanup schedule
- **License Model:** Site-specific or network-wide licensing

#### Policy Precedence

Network policies take **highest priority**:

| Setting | Scope | Precedence |
|---------|-------|-----------|
| Module Activation | Network-wide | 1 (overrides site setting) |
| Vault Location | Network-wide | 1 (all sites must follow) |
| EXIF Stripping | Network-wide | 1 (site can only tighten, not loosen) |
| Site-Specific Overrides | Per-site | 2 (allowed only if Network Admin permits) |

#### Allowing Site Overrides

To let individual sites customize certain settings:

1. Go to **Network Settings**
2. Under **Policy Overrides**, toggle:
   - **Allow sub-sites to disable modules** (recommended: OFF for stability)
   - **Allow sub-sites to customize Vault** (recommended: OFF for security)
   - **Allow sub-sites to set their own license** (recommended: ON for flexibility)
3. Save changes
4. Site admins on each sub-site can now customize those specific settings

---

## Multisite Administration

### Network Admin Workflow

As a Super Admin on a multisite network:

#### 1. Configure Network Policies

1. Go to **Support** > **Network Settings** (Network Admin menu)
2. Set global defaults for all sub-sites
3. Define which settings can be overridden per-site
4. Save

#### 2. Activate Core & Required Modules Network-Wide

1. Go to **Support** > **Modules**
2. For each essential module (e.g., Image Hub, AVIF Spoke), click **Activate**
3. A checkbox appears: **Activate for entire network**
4. Check it and confirm
5. The module is now active on all current and future sub-sites

#### 3. Monitor Sub-site Compliance

1. From Network Admin, hover over **Support** and select **Modules**
2. You see **all modules across all sub-sites**
3. Inactive modules appear grayed out
4. Network-active modules show a **Network Active** badge
5. If a site admin tries to deactivate a locked module, an override notice appears

#### 4. Push Updates to Network

1. Check **Modules** for the **Updates** card count
2. For each module with an update available:
   - Click **Update**
   - The update applies to all sites running that module
   - No down-time; currently processing media is unaffected

### Site Admin Workflow (Sub-site Context)

As a site admin on a multisite sub-site:

#### 1. Check Inherited Policies

1. Go to **Support** > **Settings**
2. Grayed-out fields indicate settings locked by Network Admin
3. Enabled fields let you override (if the Network Admin allows)

#### 2. Manage Local Modules

1. Go to **Support** > **Modules**
2. Locally installed modules can be activated/deactivated freely
3. Network-active modules show **Network Active** badge and locked deactivate button
4. Install/update locally installed modules as needed

#### 3. Configure Site-Specific Settings

1. Go to **Support** > **Settings**
2. Adjust Vault location, cleanup policy, and privacy settings (if unlocked)
3. Save
4. Changes apply only to this site; other sites are unaffected

---

## Troubleshooting & FAQ

### Common Issues

#### Issue: Module counts look incorrect

**Symptoms:** Stat cards show wrong totals, or a module appears enabled but isn't working.

**Cause:** Plugin cache or multisite activation context mismatch.

**Solution:**
1. Go to **Support** > **Modules**
2. Click **Refresh Stats** button (top right)
3. Wait 2–3 seconds for the page to recalculate
4. If the issue persists:
   - Deactivate Core Support
   - Delete `/wp-content/cache/timu_*` transients
   - Reactivate Core Support

**For Network Admins:**
- Ensure you're checking the **Network Modules** page, not a sub-site page
- Use **Network Admin** > **Support** > **Modules**, not the sub-site Modules page

#### Issue: Actions (Activate, Update, Install) are missing or grayed out

**Symptoms:** No buttons to activate/deactivate modules; can't install new ones.

**Cause:** Insufficient permissions or Core Support is inactive.

**Solution:**
1. Check that you're logged in as an Admin (site) or Super Admin (network)
2. Ensure Core Support itself is active
3. Clear browser cache (Ctrl+Shift+Del) and reload the Modules page
4. If still missing, check [Permissions](#permissions) section below

#### Issue: Module keeps deactivating after activation

**Symptoms:** You click **Activate**, but the module reverts to inactive.

**Cause:** Usually a fatal error in the module's code, or missing dependency.

**Solution:**
1. Check `/wp-content/debug.log` for errors mentioning the module
2. If errors are PHP-related:
   - Deactivate the module
   - Contact @thisismyurl support with the error message
3. If errors mention "dependency not found":
   - Ensure the Hub (Core Support) is active before activating Spokes
   - Install any missing dependency modules first

#### Issue: Vault is getting too large

**Symptoms:** Disk space is filling up quickly; Vault storage shows high usage.

**Cause:** Cleanup policy is too lenient, or cleanup is not running.

**Solution:**
1. Go to **Support** > **Settings**
2. Under **Vault Configuration**, set **Cleanup Policy** to a shorter duration (e.g., 30 days instead of 90)
3. Click **Run Cleanup Now** to delete old originals immediately
4. Monitor disk usage over the next week
5. If still growing, check the audit log for unexpected uploads

**For Network Admins:**
- Enforce the same cleanup policy across all sites to prevent one site from consuming all storage
- Go to **Network Settings** > **Enforce Vault Policy** > toggle ON

#### Issue: Multisite site admin can't enable/disable modules

**Symptoms:** **Deactivate** button is missing on network-active modules; can't change settings.

**Cause:** Network Admin has locked the modules or policies.

**Solution (if you're the site admin):**
- Contact your Network Admin and request unlocking specific modules or settings
- The Network Admin can allow overrides in **Network Settings** > **Policy Overrides**

**Solution (if you're the Network Admin):**
1. Go to **Network Settings** > **Policy Overrides**
2. Toggle **Allow sub-sites to disable modules** (if you want to allow deactivation)
3. Save changes
4. Site admins can now deactivate modules on their sites

### FAQ

#### Q: Can I use the Support Suite on a single-site WordPress installation?

**A:** Yes! All features work on single-site. The Multisite features are optional.

---

#### Q: What if a module update breaks something?

**A:**
1. Deactivate the broken module immediately
2. Check the debug log for error details
3. Email support@thisismyurl.com with the error and module name
4. Meanwhile, activate an alternative Spoke if available (e.g., use WebP instead of AVIF)

To **downgrade** a module:
1. Go to **Modules** > click the module's **Details** link
2. On the GitHub releases page, download the previous version
3. Delete the current module folder via SFTP
4. Upload the previous version
5. Go to **Modules** and click **Activate**

---

#### Q: How do I know if my license is valid?

**A:**
1. Go to **Support** > **Settings**
2. Look at the **Suite Registration** section
3. **Status** shows one of:
   - **Valid** = License is active; all features unlocked
   - **Invalid** = License key exists but failed validation; check for typos or expiration
   - **Not Registered** = No license key entered; premium features locked
4. **Last Checked** shows when the license was last validated (once per site load)

If status is Invalid:
1. Recheck your license key spelling
2. Ensure your subscription is not expired (check thisismyurl.com/account)
3. Try re-entering the key and saving

---

#### Q: Can I move The Vault to a different location?

**A:** Yes, but carefully:
1. Stop all media uploads (set the site to maintenance mode)
2. Create a **backup** of the current Vault folder
3. Go to **Support** > **Settings**
4. Change **Vault Location** to the new path (must be writable)
5. Click **Migrate Vault** (if available; otherwise, manually copy files)
6. Wait for migration to complete
7. Verify all files are in the new location
8. Take the site out of maintenance mode

**For Network Admins:**
- If you enforce Vault policy, all sites will use the same path
- Ensure enough disk space on the target location for all sites' originals

---

#### Q: What's the difference between network-active and site-active modules?

**A:**

| Context | Plugin | Behavior |
|---------|--------|----------|
| **Network-Active** | Activated on Network Admin > Plugins | Enabled on **all** sub-sites (site admin can't disable) |
| **Site-Active** | Activated on a single site's Plugins page | Enabled **only** on that site; Network Admin sees it as site-active |

**Best Practice:**
- Network-activate stable, essential modules (e.g., Image Hub, Core Support)
- Site-activate optional or experimental modules (e.g., niche Spokes)

---

#### Q: How often are modules checked for updates?

**A:**
- Updates are checked **once per day** (via WordPress's cron)
- Manual check: Go to **Modules** and click **Check for Updates** button
- Updates appear in the **Updates** stat card and in the module row

---

#### Q: Can I disable The Vault to save disk space?

**A:** Yes, but we recommend against it:
1. Go to **Support** > **Settings**
2. Under **Vault Configuration**, toggle **Enable Vault** to OFF
3. Save changes

**Consequences:**
- Original files are no longer backed up
- If a Spoke fails or is uninstalled, you can't recover the original file
- Recommended only for testing or very small sites

To re-enable The Vault:
1. Toggle **Enable Vault** back ON
2. Any future uploads will store originals again
3. Past uploads without originals cannot be recovered

---

#### Q: How do I export my media data for GDPR compliance?

**A:**
1. Go to **Tools** > **Export Personal Data** (WordPress core feature)
2. Enter a user email address
3. Select **Core Support** in the data export options
4. Click **Request Data Export**
5. WordPress will prepare a ZIP with all media metadata, Vault records, and audit logs for that user
6. Download the ZIP from the confirmation email or admin page

For more info, see [Privacy & User Data](#privacy--user-data) in the main README.

---

#### Q: Can sub-sites on multisite have different licenses?

**A:** Yes, if the Network Admin allows it:
1. Go to **Network Settings** > **Policy Overrides**
2. Toggle **Allow sub-sites to set their own license**
3. Site admins can now enter their own license keys in **Support** > **Settings**
4. Each site's license is validated independently

---

## Getting Help

### Resources

- **Documentation:** https://thisismyurl.com/core-support-thisismyurl/
- **GitHub Issues:** https://github.com/thisismyurl/core-support-thisismyurl/issues
- **Email Support:** support@thisismyurl.com
- **Community Forum:** (coming soon)

### Reporting a Bug

1. Gather information:
   - Your WordPress version, PHP version
   - List of active modules (from **Modules** page)
   - Error message from `/wp-content/debug.log` (if available)
2. Go to [GitHub Issues](https://github.com/thisismyurl/core-support-thisismyurl/issues)
3. Click **New Issue**
4. Title: Brief description (e.g., "Module activation fails on multisite")
5. Description: Include the information from step 1
6. Submit

### Requesting a Feature

Have an idea for the Dashboard or modules?
1. Go to [GitHub Issues](https://github.com/thisismyurl/core-support-thisismyurl/issues)
2. Click **New Issue**
3. Title: What you want (e.g., "Add bulk enable/disable for modules")
4. Description: Why it would be useful
5. Label: Add the `enhancement` label
6. Submit

---

## Appendix: Permissions

### Site Admin Capabilities

To manage the Support Dashboard, you need the **manage_options** capability:

- **Users > Add New**, set Role to **Administrator** to grant this capability
- **Editor** and **Author** roles do not have access to the Dashboard

### Network Admin Capabilities

To manage Network Settings, you need the **manage_network_options** capability:

- Only **Super Admin** users have this by default
- Go to **Users > Super Admins** (Network Admin) to designate additional Super Admins

### Custom Role Support

If you use a custom role plugin (e.g., Members, PublishPress):
- Add the **manage_options** capability to custom roles that should access the Dashboard
- For network settings, add **manage_network_options**

---

*For the latest updates, visit [thisismyurl.com](https://thisismyurl.com/?source=core-support-thisismyurl).*
