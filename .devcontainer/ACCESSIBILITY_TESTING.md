# Accessibility Testing Guide

## Quick Accessibility Tests

### 1. Keyboard Navigation (5 minutes)
```bash
# Unplug your mouse and test:
Tab       → Navigate forward
Shift+Tab → Navigate backward
Enter     → Activate links/buttons
Space     → Toggle checkboxes
Arrows    → Navigate menus/selects
Esc       → Close modals

✅ Can you complete all tasks without a mouse?
```

### 2. Screen Reader Test (10 minutes)
```bash
# Mac: Enable VoiceOver
Cmd + F5

# Windows: Download NVDA (free)
https://www.nvaccess.org/download/

# Test:
- Can screen reader read all text?
- Are images described (alt text)?
- Are buttons labeled clearly?
- Is navigation logical?
```

### 3. Zoom Test (2 minutes)
```bash
# Browser zoom to 200%
Ctrl/Cmd + "+" (6-8 times)

✅ Can you still read everything?
✅ Nothing overlaps or gets cut off?
```

### 4. Color Contrast Test (5 minutes)
```bash
# Use WebAIM Contrast Checker
https://webaim.org/resources/contrastchecker/

# Or browser extension:
# Chrome: WAVE Evaluation Tool
# Firefox: Accessibility Inspector

✅ Text contrast ≥ 4.5:1
✅ UI elements ≥ 3:1
```

### 5. Automated Testing
```bash
# Install Pa11y
npm install -g pa11y

# Test a page
pa11y http://localhost:8080

# Or use axe DevTools (browser extension)
```

## Common Issues & Fixes

| Issue | Fix |
|-------|-----|
| Button has no label | Add `aria-label="descriptive text"` |
| Image has no alt text | Add `alt="description"` to `<img>` |
| Low contrast | Darken text or lighten background |
| Can't Tab to element | Add `tabindex="0"` |
| Focus not visible | Add `:focus { outline: 2px solid blue; }` |
| Modal traps keyboard | Add `Esc` key handler to close |

## WordPress-Specific

### Admin Pages
```php
// Always add screen reader text
<span class="screen-reader-text"><?php esc_html_e( 'Skip to main content', 'wpshadow' ); ?></span>

// Use semantic HTML
<h1> → Page title
<h2> → Section headings
<h3> → Sub-sections

// Proper form labels
<label for="field-id">Label Text</label>
<input id="field-id" type="text">
```

### AJAX Actions
```javascript
// Show loading state
button.setAttribute('aria-busy', 'true');
button.textContent = 'Loading...';

// Announce result to screen readers
const status = document.createElement('div');
status.setAttribute('role', 'status');
status.setAttribute('aria-live', 'polite');
status.textContent = 'Action completed successfully';
```
