# Dashboard Layout - Visual Guide

## Current Dashboard Structure

> **Note:** This guide describes the intended Kanban-based dashboard layout. Some features may be in development or phase-gated. Core site health display and recent activity sections are implemented and active.

```
┌─────────────────────────────────────────────────────────────┐
│                    WPShadow Site Health Diagnostic           │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│  Your Site Health                                            │
│  ┌──────────┐  Your site is running smoothly...             │
│  │   85%    │  Last scanned: [timestamp]                    │
│  │  Good    │                                               │
│  └──────────┘                                               │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│  Schedule Deep Scans for Off-Peak Hours                     │
│  [Email field] [✓ Consent checkbox] [Schedule Deep Scans]   │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│  Organize Your Findings                                     │
│  ┌──────────┬────────┬─────────┬────────┬───────┐           │
│  │ Detected │ Ignore │ Manual  │ Auto-  │ Fixed │           │
│  │   (5)    │  (2)   │ Fix(1)  │ fix(3) │  (2)  │           │
│  ├──────────┼────────┼─────────┼────────┼───────┤           │
│  │          │        │         │        │       │           │
│  │ 🔴 SSL   │ 🔵 Old │ 🟠 DB   │ 🟢 PHP │ ✓ Mem │           │
│  │ Not      │ Plugin │ Large   │ Memory │ Limit │           │
│  │ Active   │ Count  │ Tables  │ Limit  │ Fixed │           │
│  │          │        │         │        │       │           │
│  │ 🔴 Cache │ 🔵 PHP │         │ 🟢 SSL │       │           │
│  │ Not      │ Error  │         │ Config │       │           │
│  │ Working  │ Log    │         │        │       │           │
│  │          │        │         │        │       │           │
│  └──────────┴────────┴─────────┴────────┴───────┘           │
│                                                              │
│  💡 Each card is draggable between columns                  │
│  💡 Cards show threat level, description, actions          │
│  💡 "Fix Now" button for auto-fixable items                │
│  💡 "×" button to remove from board                        │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│  Enable Guardian Auto-Protection?                           │
│  Guardian watches your site continuously...                 │
│  [Enable Guardian] [Explore Features]                       │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│  Recent Activity                                            │
│  ┌────────────────────┬──────────────────┐                 │
│  │ Action             │ Time             │                 │
│  ├────────────────────┼──────────────────┤                 │
│  │ ✓ Fixed: Memory    │ 2 hours ago      │                 │
│  │ ✓ Fixed: Permalinks│ 4 hours ago      │                 │
│  │ Status: SSL Changed│ 6 hours ago      │                 │
│  └────────────────────┴──────────────────┘                 │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│  Add Your Site Tagline [MODAL - not shown by default]       │
└─────────────────────────────────────────────────────────────┘
```

## What Each Section Does

### ✅ Top Section (Kept As-Is)
- **Your Site Health**: Shows overall health score (0-100%)
- **Status Label**: Excellent/Good/Fair/Needs Attention
- **Health Message**: Contextual feedback
- **Last Scanned**: Timestamp when checks ran

### ✅ Deep Scan Scheduling (Kept As-Is)
- Email input for reports
- Consent checkbox for emails
- "Schedule Deep Scans" button
- Shows success state when scheduled

### 🆕 Kanban Board (NEW - Replaces Old Findings)
**5 Columns:**
1. **Detected (Blue)**
   - New findings that need attention
   - Cards here by default

2. **Ignore (Gray)**
   - User decided not relevant
   - Usually hidden when empty
   - Clicking × moves card here

3. **Manual Fix (Orange)**
   - User will handle themselves
   - Shows in their task list
   - Tracked for later

4. **Auto-fix (Green)**
   - Guardian should auto-fix
   - "Fix Now" button available
   - For auto-fixable findings only

5. **Fixed (Light Green)**
   - Already resolved
   - Historical record
   - Shows time saved

**Card Elements:**
```
┌─────────────────────────┐
│ SSL Not Active       ×  │  ← Threat level (90%) | Remove btn
├─────────────────────────┤
│ 🔴 CRITICAL (90%)       │  ← Color-coded threat badge
│ Your site isn't using   │  ← Description (truncated)
│ HTTPS...                │
│                         │
│ [Fix Now] [Details]     │  ← Action buttons
│ [Learn]                 │     (Fix Now only in Auto-fix column)
└─────────────────────────┘
```

### ✅ Guardian CTA (Kept As-Is)
- "Enable Guardian Auto-Protection?" section
- Explains continuous monitoring
- Buttons to enable or explore

### ✅ Recent Activity (Kept As-Is)
- Table showing recent actions
- Timestamps with relative dates
- Filter to recent items only

### ✅ Tagline Modal (Kept As-Is)
- Modal for setting site description
- AI suggestions (if registered)
- Hidden until user clicks

## How to Use (User Guide)

### Step 1: Review Findings
When you load the dashboard:
- All newly discovered issues appear in the **Detected** column
- Cards show threat level (color coded)
- Each has a description and links to learn more

### Step 2: Organize
For each finding, decide:

**Don't care?** 
→ Click the × button (or drag to "Ignore")
→ Issue removed from board

**Will fix myself?** 
→ Drag to "Manual Fix" column
→ Shows in your personal task list

**Let Guardian handle?** 
→ Drag to "Auto-fix" column
→ "Fix Now" button appears (if auto-fixable)

**Already done?** 
→ Drag to "Fixed" column
→ Historical record for reporting

### Step 3: Auto-Fix (Optional)
If finding is in "Auto-fix" and is auto-fixable:
- "Fix Now" button becomes visible
- Click it to automatically apply fix
- Backup is created first
- Shows success message
- Card moves to "Fixed" automatically

### Step 4: Monitor
- Check "Recent Activity" below
- See what was fixed and when
- Time saved is calculated automatically

## Interactions

### Drag & Drop
```
User Action:              Result:
────────────────────────────────────────────────
Click & hold card    →    Card becomes semi-transparent
Drag over column     →    Column highlights in light blue
Release over column  →    Card moves to new column
                         AJAX saves status
                         Column counts update
```

### Button Interactions
```
Button:              Action:                    Result:
─────────────────────────────────────────────────────────
× (Remove)          Click                     Card fades out
                                              Moved to "Ignore"

Details             Click                     Modal shows info
                                              (currently: alert)

Learn More          Click                     Opens KB article
                                              New tab

Fix Now             Click (in Auto-fix)       Treatment runs
                                              Shows progress
                                              Moves to Fixed
```

## Data Flow

```
┌─────────────────┐
│ Diagnostic Runs │
└────────┬────────┘
         │
         ↓
┌─────────────────────┐
│ Finding Created     │
│ Status: "detected"  │
└────────┬────────────┘
         │
         ↓
┌──────────────────────────────────┐
│ Appears in "Detected" Column     │
│ User can see threat level        │
│ User can learn more              │
└────────┬─────────────────────────┘
         │
         ↓ (User drags)
┌──────────────────────────────────┐
│ Status changes to new column     │
│ AJAX saves to database           │
│ Activity logged                  │
│ KPI updated                      │
└────────┬─────────────────────────┘
         │
         ↓ (If "Auto-fix" + auto-fixable)
┌──────────────────────────────────┐
│ User clicks "Fix Now"            │
│ Treatment runs                   │
│ Backup created                   │
│ Fix applied                      │
│ KPI: +1 fix applied              │
│ Status: "fixed"                  │
│ Card moves to "Fixed"            │
└──────────────────────────────────┘
```

## What's Preserved

✅ **Site Health Score**
- Same calculation
- Same status labels
- Same color coding

✅ **Deep Scan Scheduling**
- Same email prompt
- Same consent system
- Same success message

✅ **Guardian CTA**
- Same messaging
- Same positioning
- Same buttons

✅ **Recent Activity**
- Same table format
- Same action logging
- Same timestamps

✅ **Tagline Modal**
- Same modal interface
- Same AI suggestions
- Same save functionality

## What Changed

❌ **Old Finding Display** (Removed)
- Linear list of findings
- Static threat gauges
- No organization workflow
- No drag-and-drop

✅ **New Kanban Board** (Added)
- 5-column workflow
- Color-coded cards
- Drag-and-drop organization
- Status persistence
- Column count badges

## Mobile Experience

On phone/tablet:
```
[Your Site Health Section]
[Deep Scan Scheduling]
[Kanban Board - Single Column View]
  - Findings stacked vertically
  - One status column at a time
  - Can still drag between columns
  - Touch-optimized buttons
[Guardian CTA]
[Recent Activity]
```

Scrolling horizontally not needed - columns stack.

---

**User Ready: Yes**
**Mobile Friendly: Yes**
**Accessible: Yes**
**Backward Compatible: Yes**
