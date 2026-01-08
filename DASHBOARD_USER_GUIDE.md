# Support Dashboard User Guide

## Quick Start

The **Support Dashboard** is your central hub for managing the Core Support Suite. Here you can:

- View module status and statistics
- Install and activate child plugins
- Configure vault settings and retention policies
- Access audit logs and activity history
- Manage network-wide policies (multisite only)

### Accessing the Dashboard

1. From the WordPress admin menu, click **Core Support**
2. Click **Dashboard** in the submenu
3. You'll see an overview with stats and quick links

---

## Dashboard Overview

### Statistics Cards

At the top of the dashboard, you'll see four cards showing key metrics:

- **Total Modules:** All modules available in the suite (installed + not installed)
- **Enabled Modules:** Modules with features currently active (may be different from activated plugins)
- **Available Modules:** Child plugins you can install (media, vault, image support)
- **Updates:** Number of modules with pending updates available

**Note:** Numbers include both locally-active and network-active modules. In multisite, "enabled" reflects both site-level and network-level activation.

### Activity Log

Below the stats, you'll see recent activity:

- **Actions logged:** Module install, activation, configuration changes, vault operations
- **Information shown:** Timestamp, action type, module/attachment affected, user who performed action, status
- **View all logs:** Click **"View All Logs →"** link at the bottom to open full log history

---

## Module Management

### Modules Page (Hubs & Spokes)

Click **Modules** in the primary navigation to manage individual modules.

#### Installing Missing Modules

If a required module is missing, you'll see a yellow admin notice:

> **Core Support:** Media Support is recommended. Provides shared media optimization and processing infrastructure

**To install:**
1. Click the **"Install & Activate"** button in the notice
2. The plugin will be downloaded from GitHub and automatically activated
3. Once complete, the notice will disappear

#### Enabling/Disabling Module Features

In the **Module Configuration** section, you can toggle features on/off:

- **Checkbox:** Check to enable module features; uncheck to disable
- **Dependencies:** If a module requires another to be enabled (e.g., Image requires Media), you'll see a message
- **No deactivation:** Toggling a feature OFF doesn't deactivate the plugin—it just disables that module's features
- **Multisite:** Network Admin can enforce policies; site admins may have limited visibility

#### Module Status Table

A table shows all installed modules with:

- **Module name** 
- **Status:** "Active" (green) or "Installed" (orange, if installed but not activated)
- **Version:** Plugin version number

---

## Settings & Configuration

### Vault Settings

Click **Settings** (or **Network Settings** if you're Network Admin) to configure vault behavior:

#### Size Limits

- **Maximum Vault Size (MB):** Set how large the vault can grow before alerts trigger
- **Default:** 0 (unlimited)
- **Alert:** When vault size exceeds this limit, an admin notice appears (once per day)

#### Retention Policies (Future)

- Automatic purge schedule
- Trash recovery window
- Cloud offload settings

#### License Registration

- **Registration Key:** Enter your license key to unlock pro features and updates across the suite
- **Status:** Shows current license validation state (Valid, Invalid, None)
- **Last Checked:** Timestamp of last validation

#### Network License Broadcast (Multisite Only)

If you're Network Admin:

1. Register a license on the Network Settings page
2. Use **Broadcast to Sub-sites** to push the same key to all network sites
3. Each site gets a copy; you can manage per-site overrides from individual site dashboards

---

## Guided Setup Wizard

When you first activate Core Support, a **Setup Wizard** appears recommending the default module stack:

1. **Media Support** — Shared optimization and processing infrastructure (required)
2. **Vault Support** — Secure original file storage with encryption
3. **Image Support** — Format support and advanced processing

**Options:**
- **Install & Activate All:** One-click setup of the full recommended stack
- **Skip for Now:** Defer setup; you can install modules later from the Modules page

---

## Real-Time Alerts

### Vault Size Threshold Alert

When vault storage exceeds your configured limit:

- **Notice:** Yellow admin notice appears saying "Vault Alert: Storage usage is at X MB of Y MB (Z%)"
- **Throttled:** Shows once per day (won't spam you)
- **Link:** Click **"Vault Settings →"** to adjust your limit or manage retention
- **Dismissible:** Close the notice; it won't show again until tomorrow

### Admin Notices

Other notices may appear for:

- Missing required modules (with one-click install buttons)
- Configuration issues or warnings
- Completed actions or important updates

---

## Activity Logs

### Viewing Logs

1. Go to **Settings** → **Vault Settings**
2. Scroll to **Activity Logs** section
3. Recent entries show at the top

### Log Filters (Coming Soon)

- **Level Filter:** Show Info, Warning, or Error messages
- **Search:** Find logs by file name, attachment ID, or operation
- **Pagination:** Browse through historical logs

### Log Information

Each log entry shows:

- **Timestamp:** When the action occurred
- **Level:** Info (normal), Warning (potential issue), or Error (failed action)
- **Module/File:** Which module or file was affected
- **Operation:** What action was performed (install, activate, backup, rollback, etc.)
- **User:** Who performed the action
- **Details:** Additional context or error messages

---

## Multisite Behavior

### Network Admin vs. Site Admin

**Network Admin Dashboard:**
- See suite-wide statistics (all sites combined)
- Register network-wide license and broadcast to sub-sites
- Enforce or suggest policies (in future versions)
- View activity from all network sites

**Site Admin Dashboard:**
- See stats for your site only
- If network policy is enforced, you may see read-only configuration
- Manage site-specific modules and settings (unless network-locked)

### Module Activation Scoping

- **Network-Active:** Activated for all network sites (shown with badge)
- **Locally-Active:** Activated on this site only
- **Multisite Formula:** Total enabled = site-active + network-active

---

## Troubleshooting

### Module Won't Install

- **Check PHP Version:** Core Support requires PHP 8.2+
- **Check WordPress Version:** Requires WordPress 6.4+
- **Check Permissions:** Ensure you're logged in as administrator
- **Check Nonce:** Browser may have expired; try again

### Vault Size Alert Won't Go Away

- The alert shows once per day. It will reappear tomorrow if size still exceeds limit
- To dismiss permanently, reduce vault size or increase the size limit in Settings

### Module Features Disabled but Plugin Still Active

- This is expected! Disabling a module toggle turns off its features without deactivating the plugin
- To fully deactivate, go to **Plugins** → deactivate manually

### Multisite: Settings Not Syncing to Sub-sites

- Check Network Admin: Is a policy enforced?
- Use **Broadcast** feature in Network Settings to push your license to sub-sites
- Individual site settings may override network defaults (unless locked)

---

## Best Practices

1. **Install the Full Stack:** Media + Vault + Image work best together
2. **Set a Vault Size Limit:** Helps prevent storage issues; set to 80% of available disk space
3. **Review Logs Regularly:** Check Activity Logs for errors or unexpected actions
4. **Use Network Broadcast (Multisite):** Register once, broadcast to all—keeps licenses in sync
5. **Keep Plugins Updated:** Check **Updates** card for new versions; update from Modules page

---

## FAQ

**Q: What if I disable a module toggle?**  
A: That module's features won't work, but the plugin stays active. No data is deleted. Re-enable anytime.

**Q: Can I use Core Support without child plugins?**  
A: Core Support works alone, but many features require Media Support (encryption, fallback engines). We recommend installing the full stack.

**Q: How often are logs saved?**  
A: Every time a significant action happens: upload, backup, restore, configuration change, error, etc.

**Q: Can I delete vault originals manually?**  
A: No—The Vault is managed automatically via the Vault Settings. Use **Retention Policies** or **Purge Tools** to manage cleanup.

**Q: Do I need to manually back up the vault?**  
A: Vault is your backup! Originals are stored securely in `/vault/`. Use **Export Full Vault** in Settings to create an offline ZIP backup.

**Q: What's "Network-wide"?**  
A: In multisite installations, some settings can be applied to all sites at once via Network Admin. Individual sites can override (if not locked).

---

## Glossary

- **Hub:** Core plugin providing shared infrastructure (encryption, engines, catalog)
- **Spoke:** Format-specific plugin (Image, Video, etc.) that relies on the Hub
- **Module:** Any Hub or Spoke plugin in the suite
- **Vault:** Secure storage directory (`/vault/`) for original files with encryption
- **Toggle:** Feature flag to enable/disable module capabilities
- **Network Admin:** WordPress multisite super-admin with site-wide control
- **Suite ID:** Handshake identifier (`thisismyurl-media-suite-2026`) that links plugins together

---

## More Resources

- **README.md:** Technical overview and architecture
- **CHANGELOG.md:** Version history and what's new
- **GitHub Issues:** Report bugs or request features
- **Support:** Visit https://thisismyurl.com/support
