# Design Consistency - Exact Code Changes (Copy-Paste Ready)

**Format:** Before → After  
**Ready to Apply:** Yes ✅  
**Time per change:** 30 seconds each

---

## File 1: includes/views/tools/tips-coach.php

### Change 1.1 (Line 58)

**Before:**
```php
		<button id="wpshadow-enable-all-tips" class="button button-secondary" style="margin-right: 10px;">
```

**After:**
```php
		<button id="wpshadow-enable-all-tips" class="wps-btn wps-btn--secondary" style="margin-right: 10px;">
```

**What to replace:**
- `class="button button-secondary"` → `class="wps-btn wps-btn--secondary"`

---

### Change 1.2 (Line 61)

**Before:**
```php
		<button id="wpshadow-disable-all-tips" class="button button-secondary">
```

**After:**
```php
		<button id="wpshadow-disable-all-tips" class="wps-btn wps-btn--secondary">
```

**What to replace:**
- `class="button button-secondary"` → `class="wps-btn wps-btn--secondary"`

---

## File 2: includes/views/workflow-wizard-steps/review.php

### Change 2.1 (Line 47)

**Before:**
```php
			<input
				type="text" 
				id="workflow_name" 
				name="workflow_name" 
				placeholder="<?php esc_attr_e( 'Leave blank for a randomly generated name', 'wpshadow' ); ?>"
				class="regular-text"
			>
```

**After:**
```php
			<input
				type="text" 
				id="workflow_name" 
				name="workflow_name" 
				placeholder="<?php esc_attr_e( 'Leave blank for a randomly generated name', 'wpshadow' ); ?>"
				class="wps-input"
			>
```

**What to replace:**
- `class="regular-text"` → `class="wps-input"`

---

### Change 2.2 (Line 55-59 - Back button)

**Before:**
```php
		<div class="form-actions">
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-workflows' . ( ! empty( $workflow_id ) ? '&action=edit&workflow=' . $workflow_id : '&action=create' ) . '&step=action&trigger=' . $trigger_id ) ); ?>" class="button button-large">
```

**After:**
```php
		<div class="form-actions">
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-workflows' . ( ! empty( $workflow_id ) ? '&action=edit&workflow=' . $workflow_id : '&action=create' ) . '&step=action&trigger=' . $trigger_id ) ); ?>" class="wps-btn wps-btn--secondary">
```

**What to replace:**
- `class="button button-large"` → `class="wps-btn wps-btn--secondary"`

---

### Change 2.3 (Line 56 - Submit button)

**Before:**
```php
			<button type="submit" class="button button-primary button-large button-hero">
```

**After:**
```php
			<button type="submit" class="wps-btn wps-btn--primary">
```

**What to replace:**
- `class="button button-primary button-large button-hero"` → `class="wps-btn wps-btn--primary"`

---

## File 3: includes/views/workflow-wizard-steps/trigger-config.php

### Change 3.1 (Line 171)

**Before:**
```php
				<button type="submit" class="button button-primary button-large">
```

**After:**
```php
				<button type="submit" class="wps-btn wps-btn--primary">
```

**What to replace:**
- `class="button button-primary button-large"` → `class="wps-btn wps-btn--primary"`

---

## File 4: includes/views/workflow-wizard-steps/action-selection.php

### Change 4.1 (Line 71)

**Before:**
```php
		<button type="button" id="continue-to-review" class="button button-primary button-large">
```

**After:**
```php
		<button type="button" id="continue-to-review" class="wps-btn wps-btn--primary">
```

**What to replace:**
- `class="button button-primary button-large"` → `class="wps-btn wps-btn--primary"`

---

## File 5: includes/views/workflow-wizard-steps/action-config.php

### Change 5.1 (Line 149)

**Before:**
```javascript
		const $submitBtn = $('<button type="submit" class="button button-primary button-large">');
```

**After:**
```javascript
		const $submitBtn = $('<button type="submit" class="wps-btn wps-btn--primary">');
```

**What to replace:**
- `class="button button-primary button-large"` → `class="wps-btn wps-btn--primary"`

---

## File 6: includes/views/workflow-wizard-steps/trigger-selection.php

### Change 6.1 (Find & Replace All)

**Search for:**
```
class="button button-primary button-large"
```

**Replace with:**
```
class="wps-btn wps-btn--primary"
```

---

### Change 6.2 (Find & Replace All)

**Search for:**
```
class="button button-secondary"
```

**Replace with:**
```
class="wps-btn wps-btn--secondary"
```

---

### Change 6.3 (Find & Replace All)

**Search for:**
```
class="regular-text"
```

**Replace with:**
```
class="wps-input"
```

---

## File 7: includes/views/workflow-email-recipients.php

### Change 7.1 (Line 149 - CSS Rule Removal)

**Before:**
```css
	.wpshadow-email-recipients .form-table th {
		padding: 12px 0;
		text-align: left;
		font-weight: 600;
		border-bottom: 1px solid #e0e0e0;
	}
```

**Action:** This needs more substantial refactoring.

**Better approach:** Review the HTML structure around line 149 and refactor from:
```html
<table class="form-table">
    <tr>
        <th>...</th>
        <td>...</td>
    </tr>
</table>
```

To:
```html
<div class="wps-card">
    <div class="wps-form-group">
        <label>...</label>
        <div>...</div>
    </div>
</div>
```

**Reference:** Look at `includes/views/tools/a11y-audit.php` for the modern pattern.

---

## File 8: includes/views/tools/dark-mode.php

### Change 8.1 (Lines 186-192 - CSS Rule Removal)

**Delete this entire block:**
```css
	body.wpshadow-dark-mode .button-primary {
		background: #1e1e2e;
		color: #fff;
		border-color: #3a3a4a;
	}

	body.wpshadow-dark-mode .button-primary:hover {
		background: #2a2a3a;
		color: #fff;
	}
```

**Reason:** `wps-btn` already handles dark mode via CSS variables.

---

### Change 8.2 (Line 230 - CSS Rule Removal)

**Delete this rule or update it:**

**Before:**
```css
	body.wpshadow-dark-mode .form-table th {
		color: #e0e0e0;
	}
```

**After (Option 1 - Remove):**
```
// Delete the entire rule
```

**After (Option 2 - Update):**
```css
	body.wpshadow-dark-mode .wps-form-group label {
		color: #e0e0e0;
	}
```

**Recommendation:** Remove (Option 1) unless you find form-table styling elsewhere that needs this.

---

## File 9: includes/views/workflow-list.php

### Change 9.1 (Line 553 - CSS Rule Removal)

**Delete this rule:**
```css
.workflow-buttons .button-primary {
	// remove this rule entirely
}
```

**Reason:** Once all buttons use `wps-btn`, this selector is no longer needed.

---

## Quick Copy-Paste Summary

### Regex Search-Replace (For Text Editor)

If your editor supports regex:

#### Search 1: All `button-primary` + `button-large`
```regex
class="button button-primary button-large(?: button-hero)?"
```
**Replace with:**
```
class="wps-btn wps-btn--primary"
```

#### Search 2: All `button-secondary`
```regex
class="button button-secondary"
```
**Replace with:**
```
class="wps-btn wps-btn--secondary"
```

#### Search 3: All `regular-text` inputs
```regex
class="regular-text"
```
**Replace with:**
```
class="wps-input"
```

---

## Verification After Changes

Run these commands to verify:

```bash
# Should return 0 results
grep -r "button button-primary\|button button-secondary\|regular-text" /workspaces/wpshadow/includes/views/ | grep -v "\.css:"

# Should find all our new classes
grep -r "wps-btn\|wps-input" /workspaces/wpshadow/includes/views/ | wc -l
# Expected: 150+ matches

# Should have no more form-table in views (except dark-mode.php CSS)
grep -r "form-table" /workspaces/wpshadow/includes/views/ | grep -v "\.css:"
# Expected: 0 results (except maybe in comments)
```

---

## Commit Template

### Commit 1: Button Classes
```
fix: update all workflow wizard buttons to design system

- Replace 'button button-primary' with 'wps-btn wps-btn--primary'
- Replace 'button button-secondary' with 'wps-btn wps-btn--secondary'
- Remove button-large and button-hero classes
- Files: tips-coach.php, review.php, trigger-config.php, 
         action-selection.php, action-config.php, trigger-selection.php

Fixes design consistency issue #XXX
```

### Commit 2: Form Inputs
```
fix: update form inputs to design system

- Replace 'regular-text' with 'wps-input'
- Files: review.php, trigger-selection.php

Follows wps-input pattern from design system
```

### Commit 3: CSS Cleanup
```
chore: remove legacy CSS selectors

- Remove button-primary dark mode styling (wps-btn handles it)
- Remove form-table dark mode styling
- Remove workflow-buttons .button-primary selector
- Files: dark-mode.php, workflow-list.php

These are no longer needed after button class updates
```

---

## Timing

| Step | Time | File(s) |
|------|------|---------|
| 1. Button changes | 5 min | tips-coach.php |
| 2. Review page buttons | 5 min | review.php (2.2, 2.3) |
| 3. Trigger config button | 2 min | trigger-config.php |
| 4. Action selection button | 2 min | action-selection.php |
| 5. Action config button | 2 min | action-config.php |
| 6. Trigger selection buttons | 3 min | trigger-selection.php |
| 7. Review page input | 2 min | review.php (2.1) |
| 8. Trigger selection inputs | 3 min | trigger-selection.php |
| 9. Email recipients table | 15 min | workflow-email-recipients.php |
| 10. Dark mode CSS | 5 min | dark-mode.php |
| 11. Workflow list CSS | 2 min | workflow-list.php |
| 12. Testing/verification | 20 min | All files |
| **TOTAL** | **66 min** | — |

---

## Final Checklist

- [ ] Applied all button class changes
- [ ] Applied all input class changes
- [ ] Removed legacy CSS selectors
- [ ] Ran grep verification (0 old classes found)
- [ ] Ran grep verification (150+ new classes found)
- [ ] Tested on live site (Tips & Coach)
- [ ] Tested on live site (Workflow wizard - all steps)
- [ ] Tested dark mode
- [ ] Tested mobile responsive
- [ ] Committed changes with proper messages
- [ ] Updated audit documentation to 100% compliance

---

**Status:** ✅ Ready to implement  
**Last Updated:** January 27, 2026  
**Estimated Total Time:** 90 minutes (including testing)
