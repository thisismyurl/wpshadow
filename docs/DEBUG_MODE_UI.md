# Debug Mode Toggles - UI Overview

## Admin Menu Location

The Debug Tools page is accessible from:
```
WordPress Admin → Support → Debug Tools
```

## Page Layout

### Header
```
┌─────────────────────────────────────────────────────────────┐
│ Debug Tools                                                  │
└─────────────────────────────────────────────────────────────┘
```

### Backend Logging Section (Recommended)
```
┌─────────────────────────────────────────────────────────────┐
│ Backend Logging (Recommended)                                │
│ These settings enable error logging without displaying       │
│ errors to visitors. Safe for production use.                 │
│                                                               │
│ Enable error logging           [●────] ☑ ON                 │
│ WP_DEBUG: Enable WordPress debug mode for error logging      │
│                                                               │
│ Write errors to debug.log      [●────] ☑ ON                 │
│ WP_DEBUG_LOG: Write errors to wp-content/debug.log          │
│                                                               │
│ Use unminified scripts         [○────] ☐ OFF                │
│ SCRIPT_DEBUG: Use unminified JavaScript and CSS files       │
│                                                               │
│ Log database queries           [○────] ☐ OFF                │
│ SAVEQUERIES: Log all database queries for analysis          │
└─────────────────────────────────────────────────────────────┘
```

### Frontend Display Section (Admins Only)
```
┌─────────────────────────────────────────────────────────────┐
│ Frontend Display (Admins Only)                               │
│ These settings show debug information on screen. Only        │
│ visible to administrators with the debug cookie.             │
│                                                               │
│ Show errors on screen          [○────] ☐ OFF                │
│ Display errors on screen (you only, via cookie)             │
│                                                               │
│ Show query information         [●────] ☑ ON                 │
│ Show database query count and time in debug bar             │
│                                                               │
│ Show memory usage              [●────] ☑ ON                 │
│ Show peak memory usage in debug bar                         │
└─────────────────────────────────────────────────────────────┘
```

### Current Status
```
┌─────────────────────────────────────────────────────────────┐
│ Current Status                                               │
│                                                               │
│ Current Mode: [Debug]                                        │
│ Auto-disable in: [00:58:32]                                 │
└─────────────────────────────────────────────────────────────┘
```

### Error Log Viewer
```
┌─────────────────────────────────────────────────────────────┐
│ Error Log                                                    │
│                                                               │
│ [🔄 Refresh Log]  [🗑️ Clear Log]                            │
│                                                               │
│ ┌───────────────────────────────────────────────────────┐  │
│ │ [2026-01-13 22:31:15] PHP Warning: Undefined...      │  │
│ │ [2026-01-13 22:30:42] PHP Notice: Trying to get...   │  │
│ │ [2026-01-13 22:29:18] PHP Fatal error: Call to...    │  │
│ │                                                        │  │
│ │ (scrollable log content - last 100 lines)             │  │
│ └───────────────────────────────────────────────────────┘  │
│                                                               │
│ Log size: 2.5 MB                                            │
└─────────────────────────────────────────────────────────────┘
```

## Floating Debug Bar (Frontend)

When frontend display is enabled, administrators see a floating bar at the top:

```
┌─────────────────────────────────────────────────────────────┐
│ Debug:  ⚠️ 1 Errors  💾 42 Queries (0.0234s)  📊 128.5 MB  │
└─────────────────────────────────────────────────────────────┘
```

The bar displays:
- Error count (red warning icon)
- Database query count and time (green database icon)
- Peak memory usage (blue dashboard icon)

## Toggle Switch Behavior

The toggle switches provide instant visual feedback:

```
OFF State:  [○────]  Gray background, slider on left
ON State:   [●────]  Blue background, slider on right
```

When clicked:
1. Switch animates to new position
2. Setting is saved via AJAX
3. Configuration file is updated
4. Page reflects new state immediately

## Current Mode Indicator

```
Production Mode:  [Production]  Green background
Debug Mode:       [Debug]       Yellow background
```

## Auto-disable Countdown

When debug mode is active, a countdown timer displays:
```
Auto-disable in: [00:58:32]
                  HH:MM:SS
```

The timer:
- Updates every second
- Shows time remaining until auto-disable (1 hour)
- Page auto-refreshes when reaching 00:00:00

## Color Scheme

- **Toggles**: Blue (#2271b1) when ON, Gray (#ccc) when OFF
- **Production Mode**: Green (#d4edda background, #155724 text)
- **Debug Mode**: Yellow (#fff3cd background, #856404 text)
- **Error Count**: Red (#dc3232)
- **Query Info**: Green (#46b450)
- **Memory Usage**: Blue (#00a0d2)
- **Log Viewer**: Dark theme (#1e1e1e background, #d4d4d4 text)

## Responsive Design

On mobile devices (< 782px):
- Sections stack vertically
- Toggle rows take full width
- Status items stack vertically
- Action buttons take full width
- Log viewer maintains readability

## Accessibility

- All controls keyboard accessible
- ARIA labels on interactive elements
- Clear visual feedback for all actions
- High contrast for readability
- Proper heading hierarchy

## User Experience Flow

### First Time Setup
1. User navigates to Support → Debug Tools
2. Sees all toggles in OFF state
3. Mode shows "Production"
4. Enables desired backend logging options
5. Mode changes to "Debug" with countdown timer
6. Error log viewer shows recent errors

### Enabling Frontend Display
1. User toggles "Show errors on screen"
2. Cookie is set for admin user
3. User refreshes any page
4. Floating debug bar appears at top
5. Shows real-time error, query, and memory info

### Viewing and Clearing Logs
1. User scrolls to Error Log section
2. Clicks "Refresh Log" to see latest entries
3. Reviews error messages in dark-themed viewer
4. Clicks "Clear Log" to reset
5. Confirms deletion
6. Log viewer shows empty state

### Auto-disable
1. User enables debug mode
2. Countdown timer starts at 01:00:00
3. Timer counts down every second
4. At 00:00:00, page auto-refreshes
5. All backend logging disabled
6. Mode returns to "Production"
7. Activity log records auto-disable event
