# CSS Consolidation Plan

## Files with Duplicate `.wps-card` Components

### 1. `/assets/css/design-system.css` (MASTER FILE - Keep)
**Lines 285-350:**
- `.wps-card` - Base card styling
- `.wps-card-header` - Header section
- `.wps-card-title` - Title styling
- `.wps-card-description` - Description styling
- `.wps-card-body` - Body content
- `a.wps-card` - Link card variant

**Status:** ✅ Master definitions - keep these

### 2. `/assets/css/kanban-board-consolidated.css` (Duplicates)
**Lines 471-650:**
- `.wps-card-header` - DUPLICATE (line 471)
- `.wps-card-title` - DUPLICATE (line 624)
- `.wps-card-description` - DUPLICATE (line 693)
- `.wps-card-footer` - DUPLICATE (line 729)

**Lines 1567-1622:**
- `.wps-card-header` - DUPLICATE AGAIN (line 1567)
- `.wps-card-title` - DUPLICATE AGAIN (line 1598)
- `.wps-card-description` - DUPLICATE AGAIN (line 1608)
- `.wps-card-footer` - DUPLICATE AGAIN (line 1622)

**Action:** Remove duplicates, keep only kanban-specific extensions

### 3. `/assets/css/admin-pages.css`
**Lines 479-551:**
- `.wps-card .wps-activity-timeline` - Activity-specific variant (OK to keep)
- `.wps-card .wps-activity-item` - Activity-specific variant (OK to keep)
- Other `.wps-card .wps-activity-*` rules - Specific to activity logs

**Status:** ✅ These are OK - they're specific extensions, not duplicates

### 4. `/includes/views/reports/site-dna.php`
**Lines 264-296:**
- Inline `<style>` blocks with `.wps-badge-new`, `.wps-card.has-badge`, `.wps-button-link`

**Action:** Extract to separate CSS file or move to design-system.css

## Consolidation Strategy

### Phase 1: Remove Duplicate Card Components (PRIORITY)
1. Remove duplicate `.wps-card-*` from `kanban-board-consolidated.css`
2. Keep only kanban-specific variants (threat indicators, category badges, dragging states)
3. Ensure kanban relies on design-system.css for base card styling

### Phase 2: Extract Inline Styles
1. Extract inline styles from `site-dna.php`
2. Move to `design-system.css` or create `site-dna.css`

### Phase 3: Verify No Breakage
1. Test Kanban board rendering
2. Test Site DNA page rendering
3. Test all card usages across admin pages

## Implementation

### Step 1: Update kanban-board-consolidated.css
**Remove lines 471-729 (first duplicate block):**
- Remove `.wps-card-header` (line 471)
- Remove `.wps-card-title` (line 624)
- Remove `.wps-card-description` (line 693)
- Remove `.wps-card-footer` (line 729)

**Remove lines 1567-1622 (second duplicate block):**
- Remove second `.wps-card-header` (line 1567)
- Remove second `.wps-card-title` (line 1598)
- Remove second `.wps-card-description` (line 1608)
- Remove second `.wps-card-footer` (line 1622)

**Keep kanban-specific classes:**
- `.wps-category-badge` (line 478)
- `.wps-threat-indicator` (line 527)
- `.finding-card.dragging` (line 598)
- `.kanban-column.drag-over` (line 605)
- `.finding-remove-btn` (line 653)

### Step 2: Add dependency comment to kanban CSS
Add at top of file:
```css
/**
 * Kanban Board Styles
 *
 * Depends on: design-system.css for base .wps-card components
 * This file contains only kanban-specific extensions and modifications
 */
```

### Step 3: Extract inline styles from site-dna.php
Create `/assets/css/site-dna.css` or add to design-system.css

## Estimated Impact
- **Lines removed:** ~300-400 lines
- **Files modified:** 2-3
- **CSS size reduction:** ~15-20%
- **Maintenance improvement:** Single source of truth for card components
