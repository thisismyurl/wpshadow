# Experience Improvement Issues - Repository Routing Plan

**Goal:** Assign each of the 10 UX/experience gaps to the correct repo(s) for implementation  
**Date:** January 2026

**Architecture (updated):**
- wpshadow/ = FREE base plugin (all core improvements live here; SaaS features optional)
- wpshadow-pro/ = PAID wrapper (requires wpshadow; loads paid features/modules)
- Modules are loaded by wpshadow-pro: wpshadow-pro-vault, wpshadow-pro-integration, wpshadow-pro-wpadmin-media

**UX Model Direction:**
- Adopt a visual IF → THEN rule builder (Zapier/IFTTT style) as the primary interaction model for low-tech users.
- Use the same rule-based UI for feature automation and for scheduling/cron tasks (e.g., reports, scans, cleanups), so novices get one consistent control surface and power users get flexible scoping and timing.
- Legacy settings remain during rollout; rules become the preferred front door once proven.

---

## 🔴 CRITICAL PRIORITY ISSUES

### Issue 1: ROLLBACK/UNDO SYSTEM
**Core Feature:** Enable users to undo any change if needed

#### Primary Implementation: **wpshadow/**
- Create snapshot system before applying fixes
- Implement undo/rollback UI in core dashboard
- Store change history with revert capability
- Test across all feature types

**Secondary Updates:**
- `wpshadow-pro-vault/` - Add versioning for vault backups
- `wpshadow-pro-wpadmin-media/` - Add media version rollback capability
- `wpshadow-pro-wpadmin-media-image/` - Track image enhancement history
- `wpshadow-pro-wpadmin-media-video/` - Track video edit history
- `wpshadow-pro-wpadmin-media-document/` - Track document version history

**GitHub Issue Assignment:**
- Main: `wpshadow/#524` (Rollback/Undo System - Core Implementation)
- Secondary: Link from each pro module with their specific rollback requirements

**Why:** 
- Rollback is fundamental to ALL operations
- Must work for core fixes AND all pro modules
- Biggest fear remover for users

---

### Issue 2: CHANGE TRANSPARENCY
**Core Feature:** Show users exactly what changed and why

#### Primary Implementation: **wpshadow/**
- Enhance activity log with before/after values
- Add visual diff viewer for changes
- Show which files were affected
- Display expected impact on site

**Secondary Updates:**
- `wpshadow-pro-vault/` - Show file encryption/storage changes
- `wpshadow-pro-wpadmin-media/` - Show media optimization changes
- `wpshadow-pro-wpadmin-media-image/` - Show image transformation details
- `wpshadow-pro-wpadmin-media-video/` - Show video processing details
- `wpshadow-pro-wpadmin-media-document/` - Show document versioning changes

**GitHub Issue Assignment:**
- Main: `wpshadow/#525` (Change Transparency - Enhanced Activity Logging)
- Subtasks: Per-module transparency for media, vault, integrations

**Why:**
- Users need to understand what happened
- Builds trust through transparency
- Reduces support questions

---

### Issue 3: CONFLICT DETECTION
**Core Feature:** Warn users before enabling incompatible features

#### Primary Implementation: **wpshadow/**
- Pre-enable conflict checking system
- Build conflict matrix between features
- Show warnings before enabling
- Suggest remediation (disable conflicting feature)

**Secondary Updates:**
- `wpshadow-pro-wpadmin-media/` - Detect conflicts between image/video/document
- `wpshadow-pro-integration/` - Detect conflicts with external tools

**GitHub Issue Assignment:**
- Main: `wpshadow/#526` (Conflict Detection and Prevention)
- Secondary: Media hub conflict checking

**Why:**
- Prevents accidental user mistakes
- Saves support time and frustration
- Makes product feel smart and safe

---

## 🟡 IMPORTANT PRIORITY ISSUES

### Issue 4: GRANULAR NOTIFICATIONS
**Core Feature:** Let users control what alerts they receive

#### Primary Implementation: **wpshadow/**
- Build notification preferences UI
- Allow per-alert-type opt-in/opt-out
- Support digest vs real-time modes
- Implement quiet hours (no 2am alerts)

**Secondary Updates:**
- All pro modules can use this system for their alerts

**Pro Tier Extensions (planned for later):**
- SMS notifications (paid feature)
- Slack/Teams integration (paid feature)
- Webhook support (paid feature)

**GitHub Issue Assignment:**
- Main: `wpshadow/#527` (Granular Notification System)
- Pro Enhancement: `wpshadow/#528` (Premium Notification Channels - SMS, Slack, Webhooks)

**Why:**
- Users get overwhelmed by too many alerts
- Better engagement when they choose what they hear
- Clear free tier / pro tier separation

---

### Issue 5: SCHEDULING FOR AUTO-FIXES
**Core Feature:** Let users control when fixes run

#### Primary Implementation: **wpshadow/**
- Add quiet hours configuration (e.g., 2am-4am)
- Schedule auto-fixes during quiet hours only
- Show when a fix will be applied
- Mark dangerous fixes as requiring approval

**Secondary Updates:**
- All pro modules respect the scheduling system

**Pro Tier Extensions (planned for later):**
- Per-feature scheduling rules
- Timezone awareness
- Manual run buttons with approvals

**GitHub Issue Assignment:**
- Main: `wpshadow/#529` (Scheduling System for Auto-Fixes)
- Pro Enhancement: `wpshadow/#530` (Advanced Scheduling - Per-Feature Rules)

**Why:**
- Prevents 2am emergency wakeups
- Gives users control over their workflow
- Makes automation safe and predictable

---

### Issue 6: REAL-TIME IMPACT DISPLAY
**Core Feature:** Show users the value they're getting

#### Primary Implementation: **wpshadow/**
- Calculate and display metric changes (load time, security score, etc.)
- Show on feature cards in real-time
- Track cumulative impact
- Make it tangible (time saved, issues fixed, etc.)

**Secondary Updates:**
- `wpshadow-pro-wpadmin-media/` - Show storage saved by optimizations
- `wpshadow-pro-wpadmin-media-image/` - Show image size reduction
- `wpshadow-pro-wpadmin-media-video/` - Show video encoding improvements

**Integrates with:** KPI Tracking system (already in roadmap #511)

**GitHub Issue Assignment:**
- Main: `wpshadow/#531` (Real-Time Impact Display on Feature Cards)
- Links to: Existing issue #511 (KPI Tracking)

**Why:**
- Makes benefits visible and concrete
- Key to Pro tier value proposition
- Users see ROI immediately

---

### Issue 7: BATCH OPERATIONS
**Core Feature:** Fix multiple issues in one go

#### Primary Implementation: **wpshadow/**
- Show "Fix all X issues" button when multiple problems exist
- Add multi-select for specific issues
- Batch confirmation with clear summary
- Batch undo capability

**Secondary Updates:**
- Works across all modules that use core system

**GitHub Issue Assignment:**
- Main: `wpshadow/#532` (Batch Operations and Bulk Fixes)

**Why:**
- Saves time for users with many issues
- Makes the tool feel powerful
- Reduces clicks to resolve problems

---

### Issue 8: SITE HEALTH SNAPSHOT
**Core Feature:** One-page overview of overall site health

#### Primary Implementation: **wpshadow/**
- Create simplified health score page
- Show top 5 issues instead of all 40 widgets
- Color-coded status (green/yellow/red)
- Clear action items

**Secondary Updates:**
- `wpshadow-pro-vault/` - Add backup status to snapshot
- `wpshadow-pro-wpadmin-media/` - Add media health to snapshot

**GitHub Issue Assignment:**
- Main: `wpshadow/#533` (Site Health Snapshot Dashboard)

**Why:**
- New users get overwhelmed by complexity
- One-page view makes it actionable
- Encourages daily check-ins

---

### Issue 9: BACKUP INTEGRATION CHECK
**Core Feature:** Verify backups exist before risky operations

#### Primary Implementation: **wpshadow/**
- Check for popular backup plugins (BackWPup, Jetpack, VaultPress, etc.)
- Display last backup timestamp
- Show backup status on risky operations
- Warn if no backups detected

**Secondary Updates:**
- `wpshadow-pro-vault/` - Can serve as backup option
- `wpshadow-pro-wpadmin-media/` - Media versioning as backup strategy

**GitHub Issue Assignment:**
- Main: `wpshadow/#534` (Backup Integration and Safety Checks)

**Why:**
- Makes users feel safe about risky operations
- Encourages best practices
- Prevents data loss

---

### Issue 10: AUTO-CONFLICT RESOLUTION (Pro Feature)
**Core Feature:** Detect and automatically resolve feature conflicts

#### Primary Implementation: **wpshadow/**
- Build conflict resolution matrix
- Identify safe vs unsafe resolutions
- Add "Auto-Fix" button for conflicts
- Show what will be changed

**Secondary Updates:**
- All modules integrate with resolution system

**Pro Tier:** Advanced conflict resolution strategies

**GitHub Issue Assignment:**
- Main: `wpshadow/#535` (Intelligent Conflict Resolution)
- Pro: Advanced resolution rules (paid feature)

**Why:**
- Shows advanced intelligence
- Saves support time
- Makes product feel pro-active

---

## Summary: Issue Assignments

| # | Issue | Primary Repo | Secondary Updates | Issue # |
|---|-------|--------------|-------------------|---------|
| 1 | Rollback/Undo | wpshadow/ | wpshadow-pro (modules adapt rollback) | #524 |
| 2 | Change Transparency | wpshadow/ | wpshadow-pro (module-specific details) | #525 |
| 3 | Conflict Detection | wpshadow/ | wpshadow-pro-wpadmin-media (cross-module) | #526 |
| 4 | Granular Notifications | wpshadow/ | — | #527 |
| 5 | Scheduling | wpshadow/ | — | #529 |
| 6 | Real-Time Impact | wpshadow/ | wpshadow-pro modules (metrics) | #531 |
| 7 | Batch Operations | wpshadow/ | — | #532 |
| 8 | Health Snapshot | wpshadow/ | wpshadow-pro modules (contribute) | #533 |
| 9 | Backup Integration | wpshadow/ | wpshadow-pro-vault | #534 |
| 10 | Auto-Conflict Resolution | wpshadow/ | — | #535 |

**Result:** All experience improvements live in the free base (wpshadow/). Pro users benefit automatically; module-specific enhancements are added inside wpshadow-pro and its loaded modules.

---

## Next Steps

1. ✅ Confirm architecture: wpshadow (free) + wpshadow-pro (paid wrapper/loader)
2. ⏳ Create 10 issues in wpshadow/ (#524-535; #528 and #530 are Pro-tier features tracked here)
3. ⏳ Link module-specific enhancements inside wpshadow-pro (vault, integration, media)
4. ⏳ Ensure all experience improvements ship in the free base by default
5. ⏳ Add labels/cross-links for module follow-ups in wpshadow-pro
6. ⏳ Prototype rule-based UI as proof-of-concept, including scheduling/cron flows (e.g., “If weekly → send report”, “If off-peak window → run cache warmup”).

**Owner:** Core work in wpshadow/ (free). Module adaptations live in wpshadow-pro and its loaded modules.
