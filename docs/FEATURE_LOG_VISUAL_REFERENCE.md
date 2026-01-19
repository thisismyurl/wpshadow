# Feature Log Widget - Visual Reference

## Widget Appearance

The Feature Log widget appears in the right sidebar of feature detail pages with a clean, VS Code-inspired timeline design.

```
┌─────────────────────────────────────┐
│ Feature Log                      ▼ │
├─────────────────────────────────────┤
│                                     │
│  ●── Enabled                  now   │
│  │   by admin                       │
│  │                                  │
│  ●── Settings Updated    5 mins ago │
│  │   Advanced settings updated      │
│  │   by admin                       │
│  │                                  │
│  ●── Sub-feature Enabled  10 mins ago│
│  │   Buffer cleanup activated       │
│  │   by admin                       │
│  │                                  │
│  ●── Settings Updated    30 mins ago │
│  │   Whitelist modified             │
│  │   by admin                       │
│  │                                  │
│  ●── Disabled              1 hour ago│
│  │   by admin                       │
│  │                                  │
│  ●── Enabled              2 hours ago│
│      by admin                       │
│                                     │
│     ┌──────────────┐                │
│     │  Load More   │                │
│     └──────────────┘                │
└─────────────────────────────────────┘
```

## Color Coding

### Dot Colors (Action Types)

```
🟢 Enabled                 (#00a32a - green)
🔴 Disabled                (#d63638 - red)
🔵 Settings Updated        (#2271b1 - blue)
🔵 Sub-feature Enabled     (#2271b1 - blue)
🔵 Sub-feature Disabled    (#2271b1 - blue)
🔴 Error (pulsing)         (#d63638 - red with animation)
⚫ Action Performed        (#646970 - gray)
```

## Typography

```
Action Label:  14px, bold, #1d2327
Timestamp:     12px, regular, #646970
Message:       13px, regular, #50575e
User:          12px, italic, #787c82
```

## Layout Measurements

```
Timeline:
- Left padding: 30px (for dot space)
- Dot size: 10px diameter
- Dot position: 6px from left
- Line width: 2px
- Line color: #dcdcde (light gray)
- Entry spacing: 20px bottom padding

Hover State:
- Background: #f6f7f7 (light gray)
- Padding: 4px 8px
- Border radius: 3px
```

## Responsive Behavior

### Desktop (> 800px)
```
┌────────────────────────────────────────────────────────┐
│  ┌──────────────────────┬──────────────────┐          │
│  │  Feature Settings    │  Feature Info    │          │
│  │        (66%)         │  Feature Log     │          │
│  │                      │       (33%)      │          │
│  └──────────────────────┴──────────────────┘          │
└────────────────────────────────────────────────────────┘
```

### Mobile (< 800px)
```
┌────────────────────────────┐
│  Feature Settings          │
│  (100% width)              │
├────────────────────────────┤
│  Feature Info              │
│  (100% width)              │
├────────────────────────────┤
│  Feature Log               │
│  (100% width)              │
└────────────────────────────┘
```

## Interaction States

### Normal State
```css
.wpshadow-log-entry {
    opacity: 1;
    transition: background 0.2s;
}
```

### Hover State
```css
.wpshadow-log-entry:hover .wpshadow-log-content {
    background: #f6f7f7;
    padding: 4px 8px;
    border-radius: 3px;
}
```

### Error State (Pulsing)
```css
@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}
```

## Data Flow Diagram

```
┌─────────────────┐
│  User Action    │
│  (Toggle/Save)  │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│  AJAX Handler   │
│  (PHP)          │
└────────┬────────┘
         │
         ▼
┌─────────────────────────────┐
│ wpshadow_log_feature_activity()│
│ - Captures timestamp         │
│ - Records action type        │
│ - Stores user info           │
│ - Adds message               │
└────────┬────────────────────┘
         │
         ▼
┌─────────────────────────────┐
│ WordPress Options API        │
│ wpshadow_feature_logs        │
└────────┬────────────────────┘
         │
         ▼
┌─────────────────────────────┐
│ wpshadow_get_feature_logs()  │
│ - Retrieves entries          │
│ - Sorts by timestamp         │
│ - Formats dates              │
│ - Adds action labels         │
└────────┬────────────────────┘
         │
         ▼
┌─────────────────────────────┐
│ wpshadow_render_feature_log_widget()│
│ - Generates HTML timeline    │
│ - Applies CSS classes        │
│ - Adds Load More button      │
└────────┬────────────────────┘
         │
         ▼
┌─────────────────┐
│  Browser        │
│  (Displayed)    │
└─────────────────┘
```

## Code Structure

### PHP Functions
```
wpshadow_log_feature_activity()         (Core logging function)
    └─> get_option('wpshadow_feature_logs')
    └─> update_option('wpshadow_feature_logs')

wpshadow_get_feature_logs()             (Retrieve & format)
    └─> get_option('wpshadow_feature_logs')
    └─> usort() by timestamp
    └─> array_slice() for pagination
    └─> human_time_diff() for relative time

wpshadow_get_log_action_label()         (Action labels)
    └─> Returns localized string

wpshadow_render_feature_log_widget()    (HTML rendering)
    └─> wpshadow_get_feature_logs()
    └─> Loops through entries
    └─> Outputs HTML timeline
```

### CSS Classes
```
.wpshadow-feature-log-timeline          (Container)
  └─> .wpshadow-log-entry               (Entry wrapper)
      ├─> .wpshadow-log-dot             (Colored dot)
      ├─> .wpshadow-log-line            (Connecting line)
      └─> .wpshadow-log-content         (Text content)
          ├─> .wpshadow-log-header      (Top row)
          │   ├─> .wpshadow-log-action  (Action name)
          │   └─> .wpshadow-log-time    (Timestamp)
          ├─> .wpshadow-log-message     (Optional message)
          └─> .wpshadow-log-user        (User attribution)
```

### AJAX Endpoints
```
wpshadow_toggle_feature                  (Feature toggle)
    └─> Logs: enabled/disabled

wpshadow_toggle_subfeature               (Sub-feature toggle)
    └─> Logs: sub_feature_enabled/disabled

wpshadow_save_external_fonts_settings    (Settings save)
    └─> Logs: settings_updated

wpshadow_load_more_logs                  (Pagination)
    └─> Returns: HTML for next 10 entries
```

## Example Timeline Entry (HTML)

```html
<div class="wpshadow-log-entry" data-action="enabled">
    <div class="wpshadow-log-dot"></div>
    <div class="wpshadow-log-line"></div>
    <div class="wpshadow-log-content">
        <div class="wpshadow-log-header">
            <span class="wpshadow-log-action">Enabled</span>
            <span class="wpshadow-log-time" title="January 19, 2026 3:45 PM">
                5 minutes ago
            </span>
        </div>
        <div class="wpshadow-log-message">
            Advanced settings updated
        </div>
        <div class="wpshadow-log-user">
            by admin
        </div>
    </div>
</div>
```

## Example Timeline Entry (CSS Result)

```
  [●]─┬─ Enabled ─────────────────── 5 minutes ago
      │  Advanced settings updated
      │  by admin
      │
```

Where:
- `[●]` = 10px colored dot
- `─┬─` = 2px vertical line
- `Enabled` = Bold action label
- `5 minutes ago` = Gray timestamp
- `Advanced settings updated` = Message text
- `by admin` = Italic user attribution

## Installation Verification

To verify the Feature Log widget is working:

1. Check metabox is registered:
   - Look for `wpshadow_feature_log` metabox ID
   - Should appear on feature detail pages only

2. Check logging functions exist:
   ```php
   function_exists('\WPShadow\CoreSupport\wpshadow_log_feature_activity')
   function_exists('\WPShadow\CoreSupport\wpshadow_get_feature_logs')
   ```

3. Check CSS is loaded:
   - View source on feature page
   - Look for `wpshadow-admin.css`
   - Search for `.wpshadow-feature-log-timeline`

4. Test logging:
   - Toggle a feature on/off
   - Check option: `get_option('wpshadow_feature_logs')`
   - Should see entry array

5. View widget:
   - Navigate to any feature detail page
   - Look for "Feature Log" metabox in right sidebar
   - Should see timeline or "No activity logged yet" message

---

**Complete Visual Reference for WPShadow Feature Log Widget**
