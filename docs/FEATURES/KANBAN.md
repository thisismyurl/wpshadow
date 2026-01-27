# Kanban Board UI Implementation Guide

## Visual Design

The Kanban board displays findings organized by user action/status in a GitHub Projects-like interface.

### Basic Layout

```html
<div class="wpshadow-kanban-board">
  <div class="kanban-column">
    <h3>Detected (5)</h3>
    <!-- New findings discovered -->
  </div>
  
  <div class="kanban-column">
    <h3>Ignore (2)</h3>
    <!-- User decided not to deal with -->
  </div>
  
  <div class="kanban-column">
    <h3>Manual (1)</h3>
    <!-- User will fix manually -->
  </div>
  
  <div class="kanban-column">
    <h3>Automated (3)</h3>
    <!-- Guardian should auto-fix -->
  </div>
  
  <div class="kanban-column">
    <h3>Fixed (2)</h3>
    <!-- Already resolved -->
  </div>
</div>
```

### Finding Card Layout

```
┌─────────────────────────────┐
│ 🔴 SSL Not Active           │
├─────────────────────────────┤
│ Threat: 90% Critical        │
│ Your site isn't using HTTPS │
│ [Learn More] [Auto-Fix] [×] │
└─────────────────────────────┘
```

### CSS Classes for Styling

```css
.wpshadow-kanban-board {
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  gap: 15px;
  padding: 20px;
  background: #f5f5f5;
  border-radius: 8px;
}

.kanban-column {
  background: white;
  border-radius: 6px;
  padding: 15px;
  min-height: 600px;
  border: 1px solid #e0e0e0;
}

.kanban-column h3 {
  margin-top: 0;
  color: #333;
  font-size: 14px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.finding-card {
  background: white;
  border: 1px solid #ddd;
  border-left: 4px solid;
  border-radius: 4px;
  padding: 12px;
  margin-bottom: 10px;
  cursor: move;
  transition: all 0.2s;
}

.finding-card.dragging {
  opacity: 0.5;
  transform: rotate(2deg);
}

.finding-card:hover {
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.finding-card.critical {
  border-left-color: #f44336;
  background: #ffebee;
}

.finding-card.high {
  border-left-color: #ff9800;
  background: #fff3e0;
}

.finding-card.medium {
  border-left-color: #2196f3;
  background: #e3f2fd;
}

.finding-card.low {
  border-left-color: #4caf50;
  background: #f1f8e9;
}

.finding-title {
  font-weight: 600;
  font-size: 13px;
  margin: 0 0 8px 0;
  color: #333;
}

.finding-threat {
  display: inline-block;
  font-size: 11px;
  color: #666;
  background: #f5f5f5;
  padding: 4px 8px;
  border-radius: 3px;
  margin-bottom: 8px;
}

.finding-actions {
  display: flex;
  gap: 5px;
  margin-top: 8px;
}

.finding-actions button {
  font-size: 11px;
  padding: 4px 8px;
}
```

### JavaScript Drag & Drop

```javascript
jQuery(document).ready(function($) {
  // Make cards draggable
  $('.finding-card').draggable({
    revert: 'invalid',
    zIndex: 100,
    cursor: 'move',
    helper: function() {
      return $(this).clone().css({
        opacity: 0.7,
        width: $(this).width()
      });
    }
  });
  
  // Make columns droppable
  $('.kanban-column').droppable({
    accept: '.finding-card',
    drop: function(event, ui) {
      var findingId = ui.draggable.data('finding-id');
      var newStatus = $(this).data('status');
      
      // Send AJAX to update status
      $.post(ajaxurl, {
        action: 'wpshadow_update_finding_status',
        nonce: wpshadowNonce,
        finding_id: findingId,
        status: newStatus
      }, function(response) {
        if (response.success) {
          // Animate card to new column
          ui.draggable.appendTo(
            $(event.target).find('.kanban-cards')
          );
          
          // Update count
          updateColumnCounts();
        }
      });
    },
    over: function() {
      $(this).addClass('drag-over');
    },
    out: function() {
      $(this).removeClass('drag-over');
    }
  });
  
  function updateColumnCounts() {
    $('.kanban-column h3').each(function() {
      var status = $(this).parent().data('status');
      var count = $(this).parent().find('.finding-card').length;
      var label = status.charAt(0).toUpperCase() + status.slice(1);
      $(this).text(label + ' (' + count + ')');
    });
  }
});
```

## Data Flow

### 1. Load Findings
```php
$findings = wpshadow_get_site_findings();
$statuses = Finding_Status_Manager::get_findings_by_status();
```

### 2. Render Columns
For each status (detected, ignored, manual, automated, fixed):
- Get findings in that status
- Count them
- Display as cards

### 3. User Interaction
User drags card from one column to another:
- Card moves visually
- AJAX sends new status
- Status updated in database
- KPI tracking updated

### 4. AJAX Handler
```php
add_action( 'wp_ajax_wpshadow_update_finding_status', function() {
    $finding_id = sanitize_text_field( $_POST['finding_id'] ?? '' );
    $status = sanitize_text_field( $_POST['status'] ?? '' );
    
    Finding_Status_Manager::set_finding_status( $finding_id, $status );
    KPI_Tracker::log_finding_detected( $finding_id, $status );
    
    wp_send_json_success();
});
```

## Column Meanings

### Detected (New)
- Findings recently discovered
- No action taken yet
- Asks user to decide approach

### Ignore (Won't Fix)
- User reviewed and chose to skip
- Example: "Using older TLS by choice"
- Can be re-enabled if priority changes

### Manual (User Will Fix)
- User taking responsibility for fix
- Example: "I'll update WordPress myself"
- Won't trigger auto-fix
- Can be marked "Fixed" when done

### Automated (Guardian Fixes)
- Findings to auto-fix when possible
- Guardian will apply fix automatically
- Requires "Allow Auto-fixes" permission
- Moves to "Fixed" when resolved

### Fixed (Done)
- Issue has been resolved
- User manually confirmed OR
- Guardian auto-applied fix
- Historical tracking of what was fixed

## Interactive Features

### Bulk Actions
```
[✓] All Detected    [Ignore All] [Manual All] [Automate All]
[✓] All Critical    [Mark Fixed] [Add Note]   [Export]
```

### Column Filters
```
Filter by: [Severity ▼] [Type ▼] [Date ▼] [Search...]
```

### Statistics Overlay
```
┌────────────────────────────┐
│ Auto-fixes Ready: 3        │
│ Needs Manual Work: 1       │
│ Ignored: 2                 │
│ Est. Time to Complete: 15m │
└────────────────────────────┘
```

### Quick Actions on Card Hover
```
Finding Card
└─ [📋 Details] [🔗 KB Article] [⚙️ Settings] [×] Close
```

## Mobile Responsive

On mobile (< 768px), switch to:
- Vertical scroll instead of horizontal
- Tabs for each column
- Swipe to navigate columns
- Simplified card view

## Accessibility

- Keyboard navigation (arrow keys between columns)
- Screen reader support (aria labels)
- High contrast colors
- Focus indicators
- WCAG 2.1 AA compliant

## Performance Considerations

- Lazy load finding details
- Virtualize long lists (only render visible cards)
- Cache column counts
- Debounce drag operations
- Batch AJAX updates

## Future Enhancements

- [ ] Timeline view (issues over time)
- [ ] Filter and search
- [ ] Custom statuses
- [ ] Bulk operations
- [ ] Undo/redo history
- [ ] Export to CSV
- [ ] Integration with issue trackers
