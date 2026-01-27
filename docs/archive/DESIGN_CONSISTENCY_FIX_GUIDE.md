# Design Consistency - Quick Fix Guide

**Status:** Ready to apply  
**Estimated Time:** 90 minutes  
**Impact:** Brings consistency from 73% → 100%

---

## Fix #1: Tips & Coach Tool Buttons

**File:** `includes/views/tools/tips-coach.php`  
**Time:** 5 minutes  
**Lines:** 58, 61

### Current Code (Lines 58-62):
```php
<button id="wpshadow-enable-all-tips" class="button button-secondary" style="margin-right: 10px;">
    <?php esc_html_e( 'Enable All Tips', 'wpshadow' ); ?>
</button>
<button id="wpshadow-disable-all-tips" class="button button-secondary">
    <?php esc_html_e( 'Disable All Tips', 'wpshadow' ); ?>
</button>
```

### Fixed Code:
```php
<button id="wpshadow-enable-all-tips" class="wps-btn wps-btn--secondary" style="margin-right: 10px;">
    <?php esc_html_e( 'Enable All Tips', 'wpshadow' ); ?>
</button>
<button id="wpshadow-disable-all-tips" class="wps-btn wps-btn--secondary">
    <?php esc_html_e( 'Disable All Tips', 'wpshadow' ); ?>
</button>
```

**Changes:**
- Replace `class="button button-secondary"` with `class="wps-btn wps-btn--secondary"`
- Keep the inline `margin-right: 10px;` (could be refactored to CSS later)

---

## Fix #2: Workflow Wizard - Review Page Buttons & Inputs

**File:** `includes/views/workflow-wizard-steps/review.php`  
**Time:** 10 minutes  
**Lines:** 47, 56, 59

### Issue 1: Input Class (Line 47)
**Current:**
```php
<input type="text" id="workflow_name" name="workflow_name" 
    class="regular-text" />
```

**Fixed:**
```php
<input type="text" id="workflow_name" name="workflow_name" 
    class="wps-input" />
```

### Issue 2: Back Button (Line 59)
**Current:**
```html
<a href="..." class="button button-large">
```

**Fixed:**
```html
<a href="..." class="wps-btn wps-btn--secondary">
```

### Issue 3: Save Button (Line 56)
**Current:**
```php
<button type="submit" class="button button-primary button-large button-hero">
```

**Fixed:**
```php
<button type="submit" class="wps-btn wps-btn--primary">
```

**Note:** Remove `button-large` and `button-hero` - WPShadow doesn't use size variants. All buttons are properly sized by the CSS.

---

## Fix #3: Workflow Wizard - Trigger Config Page

**File:** `includes/views/workflow-wizard-steps/trigger-config.php`  
**Time:** 5 minutes  
**Line:** 171

### Current Code:
```php
<button type="submit" class="button button-primary button-large">
```

### Fixed Code:
```php
<button type="submit" class="wps-btn wps-btn--primary">
```

**Changes:**
- Replace `class="button button-primary button-large"` with `class="wps-btn wps-btn--primary"`

---

## Fix #4: Workflow Wizard - Action Selection Page

**File:** `includes/views/workflow-wizard-steps/action-selection.php`  
**Time:** 5 minutes  
**Line:** 71

### Current Code:
```php
<button type="button" id="continue-to-review" class="button button-primary button-large">
```

### Fixed Code:
```php
<button type="button" id="continue-to-review" class="wps-btn wps-btn--primary">
```

---

## Fix #5: Workflow Wizard - Action Config Page

**File:** `includes/views/workflow-wizard-steps/action-config.php`  
**Time:** 5 minutes  
**Line:** 149

### Current Code:
```javascript
const $submitBtn = $('<button type="submit" class="button button-primary button-large">');
```

### Fixed Code:
```javascript
const $submitBtn = $('<button type="submit" class="wps-btn wps-btn--primary">');
```

**Note:** This is JavaScript string that creates a button dynamically, so update the class string within the quotes.

---

## Fix #6: Workflow Wizard - Trigger Selection Page

**File:** `includes/views/workflow-wizard-steps/trigger-selection.php`  
**Time:** 5 minutes  
**Lines:** [To be located by grep]

### Pattern to Find & Replace:
```php
class="button button-primary"  →  class="wps-btn wps-btn--primary"
class="button button-secondary" → class="wps-btn wps-btn--secondary"
class="button button-large" → class="wps-btn"
class="regular-text" → class="wps-input"
```

---

## Fix #7: Workflow Email Recipients Form

**File:** `includes/views/workflow-email-recipients.php`  
**Time:** 15 minutes  
**Line:** 149

### Current Pattern (form-table):
```php
<table class="form-table">
    <tr>
        <th>...</th>
        <td>...</td>
    </tr>
</table>
```

### Replace With (wps-card + wps-form-group):
```php
<div class="wps-card">
    <div class="wps-form-group">
        <label for="field-id">Label</label>
        <input type="text" id="field-id" class="wps-input" />
    </div>
</div>
```

**Note:** This is more substantial. Review the pattern in other updated pages:
- See: `includes/views/tools/a11y-audit.php` for modern form examples
- See: `includes/views/privacy-consent.php` for card + form layout examples

---

## Fix #8: Dark Mode CSS Cleanup

**File:** `includes/views/tools/dark-mode.php`  
**Time:** 10 minutes  
**Lines:** 186-192, 230

### Remove These Selectors:
```css
/* Lines 186-192 - Remove this entire block */
body.wpshadow-dark-mode .button-primary {
    background: #1e1e2e;
    color: #fff;
    border-color: #3a3a4a;
}
body.wpshadow-dark-mode .button-primary:hover {
    background: #2a2a3a;
    color: #fff;
}

/* Line 230 - Remove or update this */
body.wpshadow-dark-mode .form-table th {
    color: #e0e0e0;
}
```

### Replace With (if needed):
```css
/* Only keep if you need dark mode styling for new components */
/* wps-btn and wps-input already support dark mode via CSS variables */
/* Usually no additional styling needed */
```

**Rationale:** The new `wps-btn` and `wps-input` components use CSS variables that automatically adapt to dark mode. No custom selectors needed.

---

## Fix #9: Workflow List CSS Cleanup

**File:** `includes/views/workflow-list.php`  
**Time:** 5 minutes  
**Line:** 553

### Remove This Selector:
```css
.workflow-buttons .button-primary {
    /* Remove entire rule */
}
```

**Rationale:** Once buttons are updated to `wps-btn wps-btn--primary`, this old selector is no longer needed and would cause confusion.

---

## Verification Checklist

After applying all fixes, run these checks:

### 1. Grep for Old Classes (Should Find 0 Results)
```bash
grep -r "button-primary\|button-secondary\|button-large" includes/views/ | grep -v "\.css:" | grep -v "\.svg"
```
**Expected Output:** Empty (no results)

### 2. Grep for Old Input Classes (Should Find 0 Results)
```bash
grep -r "regular-text\|form-table" includes/views/ | grep -v "\.css:"
```
**Expected Output:** Empty (no results)

### 3. Verify New Classes Are Used (Should Find Many Results)
```bash
grep -r "wps-btn\|wps-input\|wps-card" includes/views/ | wc -l
```
**Expected Output:** 150+ matches

### 4. Visual Testing on Live Site
- [ ] Tips & Coach page - buttons styled correctly
- [ ] Workflow wizard (all 5 steps) - buttons and inputs styled correctly
- [ ] Dark mode enabled - all components still look good
- [ ] Mobile view (responsive) - buttons and forms adapt correctly

---

## Testing Commands

Copy and paste these into terminal after fixes:

```bash
# Check for remaining old classes
echo "=== Checking for old button classes ==="
grep -r "button-primary\|button-secondary" /workspaces/wpshadow/includes/views/

echo "=== Checking for old form classes ==="
grep -r "form-table\|regular-text" /workspaces/wpshadow/includes/views/

echo "=== Count of new wps-btn classes ==="
grep -r "wps-btn" /workspaces/wpshadow/includes/views/ | wc -l

echo "=== Count of new wps-input classes ==="
grep -r "wps-input" /workspaces/wpshadow/includes/views/ | wc -l

echo "=== Count of new wps-card classes ==="
grep -r "wps-card" /workspaces/wpshadow/includes/views/ | wc -l
```

---

## Implementation Order

**Recommended order for clean git history:**

1. **Commit 1:** Fix all button classes (Fixes #1-5)
   - Title: `fix: update workflow wizard buttons to design system`
   - Files: `tips-coach.php`, `review.php`, `trigger-config.php`, `action-selection.php`, `action-config.php`

2. **Commit 2:** Fix form inputs and elements
   - Title: `fix: update workflow wizard form inputs to design system`
   - Files: `review.php`, `trigger-selection.php`

3. **Commit 3:** Refactor form markup
   - Title: `refactor: replace form-table with wps-card components`
   - Files: `workflow-email-recipients.php`

4. **Commit 4:** CSS cleanup
   - Title: `chore: remove legacy button/form CSS selectors`
   - Files: `dark-mode.php`, `workflow-list.php`

5. **Test & Verify:**
   - Run grep checks
   - Visual testing on live site
   - Mobile responsive testing
   - Dark mode testing

---

## Time Breakdown

| Task | Time | Status |
|------|------|--------|
| Fix button classes | 25 min | ⏱️ |
| Fix form inputs | 10 min | ⏱️ |
| Refactor form markup | 20 min | ⏱️ |
| CSS cleanup | 15 min | ⏱️ |
| Testing & verification | 20 min | ⏱️ |
| **TOTAL** | **90 min** | **Ready** |

---

**Last Updated:** January 27, 2026  
**Author:** WPShadow Audit Team  
**Status:** Ready to implement
