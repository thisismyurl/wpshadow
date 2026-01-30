# [Enhancement] Automatic Scheduled Scans - Make Default Behavior Clear

**Labels:** `enhancement`, `guardian`, `scheduling`, `ux`
**Assignee:** TBD
**Milestone:** v1.3

## Current State
Found evidence of scheduled scan capability:
- `guardian_deep_scan` in Activity Logger actions
- `schedule-overnight-fix-handler.php` exists
- Guardian mentions "scheduled health checks" in inactive notice

However, it's **unclear** if:
1. Scans run automatically by default or require manual enabling
2. What the default schedule is (daily/weekly/custom)
3. How users configure scan frequency
4. If scheduled scans respect server resources (time limits, memory)

## Enhancement Needed
Make automatic scanning behavior **crystal clear** and **configurable** from first install.

## Proposed Solution

### 1. First Run Setup (Onboarding)
During plugin activation/welcome screen:
- Run automatic health scans
  - Daily (recommended for most sites)
  - Weekly (for low-traffic sites)
  - Manual only (you'll run scans yourself)

### 2. Guardian Settings Page
Add clear configuration:
- Enable Automatic Scans: [checkbox]
- Scan Frequency: [Daily/Weekly dropdown]
- Scan Time: [2:00 AM default] (user's timezone)
- Scan Depth: [Quick/Standard/Deep dropdown]
- Only scan when site is low-traffic: [checkbox]

### 3. Dashboard Widget
Show next scheduled scan with status and controls

## Implementation Checklist

### Core Functionality
- [ ] Create `includes/guardian/class-scan-scheduler.php`
- [ ] Register WordPress cron event: `wpshadow_scheduled_scan`
- [ ] Add cron schedules: `wpshadow_daily`, `wpshadow_weekly`
- [ ] Hook cron to `Diagnostic_Registry::run_all()`
- [ ] Implement scan depth parameter (quick/standard/deep)
- [ ] Add resource limiting (max_execution_time safety)

### Settings Integration
- [ ] Add to `includes/settings/` - Guardian tab
- [ ] Settings fields: enable, frequency, time, depth
- [ ] Default: Daily at 2 AM (least disruptive time)
- [ ] Settings Registry integration
- [ ] AJAX handler for saving schedule preferences

### UI/UX
- [ ] Add to onboarding flow (First Run Consent)
- [ ] Dashboard widget showing next scheduled scan
- [ ] Admin notice if scans disabled: "Guardian sleeping" warning
- [ ] WP-Cron status check (warn if cron not working)
- [ ] Activity Logger entry after each scheduled scan

### Safety & Performance
- [ ] Check if `wp_get_schedules()` already has our events
- [ ] Implement `wp_doing_cron()` check to avoid nested scans
- [ ] Add `DISABLE_WP_CRON` detection (warn user if problematic)
- [ ] Timeout protection (use `set_time_limit(0)` safely)
- [ ] Memory limit check before deep scans

## Philosophy Alignment
✅ **Philosophy #8**: Inspire confidence - automatic health checks without nagging
✅ **Philosophy #9**: Show value - track scan frequency and issues prevented
✅ **Philosophy #2**: Free forever - uses WordPress cron (no external services)

## User Stories
- **New User**: "I just installed WPShadow. Is it scanning automatically or do I need to do something?"
- **Power User**: "I want weekly scans at 3 AM, not daily at 2 AM"
- **Agency**: "I need to disable crons on client sites and run scans via WP-CLI instead"

## Success Metrics
- % of users with auto-scan enabled (target: >80%)
- Scan completion rate (target: >95%)
- Average time between scans (should match configured frequency)
- User confusion reports (target: <2% support tickets about scheduling)

## Related Files
- `includes/admin/class-guardian-inactive-notice.php` (mentions scheduled scans)
- `includes/admin/ajax/schedule-overnight-fix-handler.php` (overnight fix scheduling)
- `includes/admin/class-guardian-dashboard.php` (references `guardian_deep_scan`)

## Future Enhancements (Pro)
- Cloud-based scanning (offload from user's server)
- Multi-site network-wide scheduling
- Scan reports via email/Slack after each run
- Smart scheduling (scan when traffic is lowest)
