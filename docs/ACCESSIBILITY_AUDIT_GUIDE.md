# WPShadow Accessibility Audit & Testing Guide

**Purpose:** Comprehensive accessibility testing and validation guide  
**Standard:** WCAG 2.1 Level AA Compliance  
**Last Updated:** January 25, 2026

---

## 📖 Table of Contents

1. [Overview](#overview)
2. [Testing Tools](#testing-tools)
3. [Component-by-Component Tests](#component-by-component-tests)
4. [Keyboard Navigation Tests](#keyboard-navigation-tests)
5. [Screen Reader Tests](#screen-reader-tests)
6. [Color Contrast Tests](#color-contrast-tests)
7. [Common Issues & Fixes](#common-issues--fixes)
8. [Automated Testing](#automated-testing)

---

## 🎯 Overview

Per WPShadow's CANON principles, accessibility is **non-negotiable**:

> **"No feature complete until accessible"** - CANON Pillar #1

### Compliance Requirements

- **WCAG 2.1 Level AA** (minimum)
- **Keyboard navigation** for all interactive elements
- **Screen reader compatible** (NVDA, JAWS, VoiceOver)
- **Color contrast** validated (4.5:1 for text, 3:1 for UI)
- **Focus indicators** visible and high-contrast
- **No time limits** on interactions
- **No auto-play** or flashing content

---

## 🛠️ Testing Tools

### Browser Extensions

**Required:**
- **axe DevTools** (Chrome/Firefox) - Automated accessibility scanner
- **WAVE** (Chrome/Firefox) - Visual accessibility checker
- **Lighthouse** (Chrome DevTools) - Comprehensive audit

**Recommended:**
- **Color Contrast Analyzer** - Check contrast ratios
- **HeadingsMap** - Validate heading hierarchy
- **Accessibility Insights** - Microsoft's testing suite

### Screen Readers

**Required (test with at least one):**
- **NVDA** (Windows, free) - https://www.nvaccess.org/
- **JAWS** (Windows, trial available) - https://www.freedomscientific.com/
- **VoiceOver** (macOS/iOS, built-in) - Cmd+F5 to enable

**Recommended:**
- **ORCA** (Linux, free)
- **TalkBack** (Android, built-in)

### Command-Line Tools

```bash
# Install axe-core for automated testing
npm install -g axe-core

# Install pa11y for CI/CD integration
npm install -g pa11y
```

---

## 🧩 Component-by-Component Tests

### Buttons

**Visual Test:**
- [ ] Focus indicator visible (min 2px outline)
- [ ] Focus indicator has 3:1 contrast with background
- [ ] Text color has 4.5:1 contrast with button background
- [ ] Icon-only buttons are 44x44px minimum

**Keyboard Test:**
- [ ] Tab navigates to button
- [ ] Enter/Space activates button
- [ ] Focus indicator visible during keyboard navigation

**Screen Reader Test:**
- [ ] Button purpose is clear from label
- [ ] Icon-only buttons have aria-label
- [ ] Button state announced (pressed, expanded, etc.)

**Code Example:**
```html
<!-- ✅ Accessible button -->
<button class="wps-btn wps-btn-primary" type="submit">
    <span class="dashicons dashicons-saved" aria-hidden="true"></span>
    Save Changes
</button>

<!-- ✅ Accessible icon-only button -->
<button class="wps-btn wps-btn-icon" aria-label="Edit settings">
    <span class="dashicons dashicons-edit" aria-hidden="true"></span>
</button>
```

---

### Form Inputs

**Visual Test:**
- [ ] Label clearly associated with input
- [ ] Focus indicator visible (3px glow minimum)
- [ ] Error states use text, not just color
- [ ] Help text color has 4.5:1 contrast

**Keyboard Test:**
- [ ] Tab navigates to input
- [ ] Arrow keys work in dropdowns
- [ ] Form can be submitted with Enter

**Screen Reader Test:**
- [ ] Label announced before input
- [ ] Help text announced (aria-describedby)
- [ ] Error messages announced (aria-invalid + role="alert")
- [ ] Required fields announced

**Code Example:**
```html
<!-- ✅ Accessible text input -->
<div class="wps-form-group">
    <label for="site-name" class="wps-form-label">
        Site Name
        <span class="required" aria-label="required">*</span>
    </label>
    <input 
        type="text" 
        id="site-name" 
        name="site_name" 
        class="wps-input" 
        aria-required="true"
        aria-describedby="site-name-help"
    />
    <p id="site-name-help" class="wps-form-help">
        This will be displayed in the header
    </p>
</div>

<!-- ✅ Accessible input with error -->
<div class="wps-form-group">
    <label for="email" class="wps-form-label">Email Address</label>
    <input 
        type="email" 
        id="email" 
        name="email" 
        class="wps-input" 
        aria-invalid="true"
        aria-describedby="email-error"
    />
    <p id="email-error" class="wps-form-error" role="alert">
        Please enter a valid email address
    </p>
</div>
```

---

### Toggle Switches

**Visual Test:**
- [ ] Focus indicator visible around entire switch
- [ ] Toggle state visually clear (on/off)
- [ ] Label color has 4.5:1 contrast
- [ ] Switch is 44x44px minimum (touch target)

**Keyboard Test:**
- [ ] Tab navigates to toggle
- [ ] Space toggles on/off
- [ ] State change is immediate

**Screen Reader Test:**
- [ ] Toggle purpose announced from label
- [ ] Current state announced (checked/unchecked)
- [ ] State change announced when toggled

**Code Example:**
```html
<!-- ✅ Accessible toggle switch -->
<div class="wps-toggle-wrapper">
    <label class="wps-toggle" for="enable-guardian">
        <input 
            type="checkbox" 
            id="enable-guardian" 
            name="enable_guardian"
            role="switch"
            aria-checked="false"
        />
        <span class="wps-toggle-slider" aria-hidden="true"></span>
        <span class="screen-reader-text">Enable WPShadow Guardian</span>
    </label>
    <label for="enable-guardian" class="wps-toggle-label">
        Enable WPShadow Guardian
    </label>
</div>
```

---

### Modals

**Visual Test:**
- [ ] Modal has visible close button
- [ ] Backdrop dims content behind modal
- [ ] Modal content readable (contrast)
- [ ] Close button is 44x44px minimum

**Keyboard Test:**
- [ ] Tab cycles within modal (focus trap)
- [ ] Escape closes modal
- [ ] Focus returns to trigger element after close

**Screen Reader Test:**
- [ ] Modal title announced on open
- [ ] Modal role announced (dialog)
- [ ] Modal is modal (aria-modal="true")
- [ ] Close button labeled

**Code Example:**
```html
<!-- ✅ Accessible modal -->
<div 
    class="wps-modal" 
    id="settings-modal" 
    role="dialog" 
    aria-labelledby="modal-title"
    aria-describedby="modal-description"
    aria-modal="true"
    style="display: none;"
>
    <div class="wps-modal-content">
        <button 
            class="wps-modal-close" 
            aria-label="Close settings modal"
            type="button"
        >
            <span aria-hidden="true">&times;</span>
        </button>
        <h2 id="modal-title">Settings</h2>
        <p id="modal-description">Configure your preferences</p>
        <!-- Modal content -->
    </div>
</div>
```

**JavaScript Requirements:**
```javascript
// Focus trap implementation
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    const focusableElements = modal.querySelectorAll(
        'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
    );
    const firstFocusable = focusableElements[0];
    const lastFocusable = focusableElements[focusableElements.length - 1];
    
    modal.style.display = 'flex';
    firstFocusable.focus();
    
    // Trap focus within modal
    modal.addEventListener('keydown', (e) => {
        if (e.key === 'Tab') {
            if (e.shiftKey) {
                if (document.activeElement === firstFocusable) {
                    e.preventDefault();
                    lastFocusable.focus();
                }
            } else {
                if (document.activeElement === lastFocusable) {
                    e.preventDefault();
                    firstFocusable.focus();
                }
            }
        }
        if (e.key === 'Escape') {
            closeModal(modalId);
        }
    });
}
```

---

### Tables

**Visual Test:**
- [ ] Column headers have sufficient contrast
- [ ] Row hover state is clear
- [ ] Cell content readable
- [ ] Responsive on mobile (horizontal scroll)

**Keyboard Test:**
- [ ] Table is navigable with Tab
- [ ] Focus indicator visible in cells
- [ ] Interactive elements in cells accessible

**Screen Reader Test:**
- [ ] Column headers announced
- [ ] Row headers announced (if applicable)
- [ ] Table structure is clear
- [ ] Table has caption/summary

**Code Example:**
```html
<!-- ✅ Accessible table -->
<div class="wps-table-container" role="region" aria-labelledby="table-caption">
    <table class="wps-table">
        <caption id="table-caption">Security scan results</caption>
        <thead>
            <tr>
                <th scope="col">Check Name</th>
                <th scope="col">Status</th>
                <th scope="col">Actions</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>SSL Certificate</td>
                <td>
                    <span class="wps-badge wps-badge-success">
                        <span class="screen-reader-text">Status: </span>
                        Valid
                    </span>
                </td>
                <td>
                    <button class="wps-btn wps-btn-sm" aria-label="View SSL certificate details">
                        View
                    </button>
                </td>
            </tr>
        </tbody>
    </table>
</div>
```

---

### Alerts/Notifications

**Visual Test:**
- [ ] Alert is visually distinct
- [ ] Icon color has 3:1 contrast
- [ ] Text color has 4.5:1 contrast
- [ ] Not relying on color alone

**Screen Reader Test:**
- [ ] Alert announced immediately (role="alert")
- [ ] Alert severity announced
- [ ] Alert content is clear

**Code Example:**
```html
<!-- ✅ Accessible alert -->
<div class="wps-alert wps-alert-warning" role="alert">
    <span class="dashicons dashicons-warning" aria-hidden="true"></span>
    <strong>Warning:</strong>
    Your WordPress version is outdated. Please update to the latest version.
</div>

<!-- ✅ Dismissible alert -->
<div class="wps-alert wps-alert-info" role="alert">
    <span class="dashicons dashicons-info" aria-hidden="true"></span>
    <span>New features available in WPShadow 2.0!</span>
    <button 
        class="wps-alert-close" 
        aria-label="Dismiss notification"
        type="button"
    >
        <span aria-hidden="true">&times;</span>
    </button>
</div>
```

---

## ⌨️ Keyboard Navigation Tests

### Essential Keyboard Patterns

**Tab Navigation:**
- [ ] Tab moves forward through interactive elements
- [ ] Shift+Tab moves backward
- [ ] Tab order is logical (top to bottom, left to right)
- [ ] No keyboard traps

**Activation:**
- [ ] Enter activates buttons/links
- [ ] Space activates buttons/checkboxes
- [ ] Space toggles toggle switches

**Dropdowns/Selects:**
- [ ] Arrow keys navigate options
- [ ] Enter selects option
- [ ] Escape closes dropdown

**Modals:**
- [ ] Escape closes modal
- [ ] Focus trapped within modal
- [ ] Focus returns to trigger after close

### Testing Procedure

1. **Unplug mouse** (or don't use it)
2. Start at top of page
3. Press Tab repeatedly
4. Verify focus indicator visible at each stop
5. Verify focus order is logical
6. Test activation keys (Enter/Space)
7. Test escape mechanisms (Escape key)

---

## 🔊 Screen Reader Tests

### NVDA (Windows)

**Basic Navigation:**
- [ ] H key navigates headings
- [ ] F key navigates form fields
- [ ] B key navigates buttons
- [ ] K key navigates links
- [ ] T key navigates tables
- [ ] L key navigates lists

**Testing Procedure:**
1. Start NVDA (Ctrl+Alt+N)
2. Navigate to WPShadow page
3. Use H to read heading hierarchy
4. Use F to navigate all form fields
5. Verify labels are announced
6. Verify help text is announced
7. Test form submission
8. Stop NVDA (Insert+Q)

### VoiceOver (macOS)

**Basic Navigation:**
- [ ] Cmd+F5 starts VoiceOver
- [ ] VO+Right Arrow moves forward
- [ ] VO+Left Arrow moves backward
- [ ] VO+Space activates

**Testing Procedure:**
1. Start VoiceOver (Cmd+F5)
2. Navigate to WPShadow page
3. Use VO+U to open rotor
4. Navigate headings (Left/Right arrows)
5. Navigate form fields
6. Verify labels announced
7. Stop VoiceOver (Cmd+F5)

---

## 🎨 Color Contrast Tests

### Required Contrast Ratios

**WCAG AA Requirements:**
- **Normal text (<18pt):** 4.5:1 minimum
- **Large text (≥18pt or ≥14pt bold):** 3:1 minimum
- **UI components:** 3:1 minimum
- **Graphical objects:** 3:1 minimum

### Design System Colors - Validated Combinations

**✅ Safe Text Combinations:**
```css
/* Dark text on light backgrounds */
--wps-gray-900 on white       /* 15.46:1 ✅ */
--wps-gray-800 on white       /* 11.94:1 ✅ */
--wps-gray-700 on white       /* 8.59:1 ✅ */
--wps-gray-600 on white       /* 5.74:1 ✅ */
--wps-gray-500 on white       /* 4.61:1 ✅ */

/* White text on dark backgrounds */
white on --wps-primary        /* 6.2:1 ✅ */
white on --wps-success        /* 3.2:1 ✅ */
white on --wps-danger         /* 4.5:1 ✅ */
white on --wps-warning        /* 1.8:1 ❌ (use dark text) */

/* Dark text on light semantic backgrounds */
--wps-gray-900 on --wps-success-light  /* 12.1:1 ✅ */
--wps-gray-900 on --wps-warning-light  /* 14.2:1 ✅ */
--wps-gray-900 on --wps-danger-light   /* 13.8:1 ✅ */
```

**⚠️ Warning Combinations (needs adjustment):**
```css
/* These need darker text or different background */
--wps-gray-400 on white       /* 3.86:1 ❌ - too low for normal text */
white on --wps-warning        /* 1.8:1 ❌ - use dark text instead */
```

### Testing Tools

**Browser Extensions:**
- **Color Contrast Analyzer** - Instant contrast checking
- **WAVE** - Shows all contrast failures
- **axe DevTools** - Automated contrast validation

**Online Tools:**
- https://webaim.org/resources/contrastchecker/
- https://contrast-ratio.com/

---

## 🐛 Common Issues & Fixes

### Issue 1: Missing Form Labels

**Problem:**
```html
<!-- ❌ Bad -->
<input type="text" placeholder="Enter your name" />
```

**Fix:**
```html
<!-- ✅ Good -->
<label for="user-name">Name</label>
<input type="text" id="user-name" placeholder="e.g., John Doe" />
```

---

### Issue 2: Icon-Only Buttons Without Labels

**Problem:**
```html
<!-- ❌ Bad -->
<button class="wps-btn wps-btn-icon">
    <span class="dashicons dashicons-edit"></span>
</button>
```

**Fix:**
```html
<!-- ✅ Good -->
<button class="wps-btn wps-btn-icon" aria-label="Edit settings">
    <span class="dashicons dashicons-edit" aria-hidden="true"></span>
</button>
```

---

### Issue 3: Focus Indicator Removed

**Problem:**
```css
/* ❌ Bad */
button:focus {
    outline: none;
}
```

**Fix:**
```css
/* ✅ Good */
button:focus {
    outline: 2px solid var(--wps-primary);
    outline-offset: 2px;
}
```

---

### Issue 4: Color-Only Information

**Problem:**
```html
<!-- ❌ Bad - status only indicated by color -->
<span style="color: red;">Critical</span>
```

**Fix:**
```html
<!-- ✅ Good - status indicated by text + badge -->
<span class="wps-badge wps-badge-danger">
    <span class="screen-reader-text">Status: </span>
    Critical
</span>
```

---

### Issue 5: Insufficient Contrast

**Problem:**
```css
/* ❌ Bad - only 2.1:1 contrast */
.wps-text-muted {
    color: #999999; /* on white background */
}
```

**Fix:**
```css
/* ✅ Good - 4.6:1 contrast */
.wps-text-muted {
    color: var(--wps-gray-500); /* #6b7280 */
}
```

---

## 🤖 Automated Testing

### Lighthouse CI

```javascript
// lighthouse.config.js
module.exports = {
    ci: {
        collect: {
            url: ['http://localhost:9000/wp-admin/admin.php?page=wpshadow'],
            numberOfRuns: 3,
        },
        assert: {
            assertions: {
                'categories:accessibility': ['error', {minScore: 0.9}],
                'color-contrast': 'error',
                'button-name': 'error',
                'label': 'error',
                'aria-required-attr': 'error',
            },
        },
    },
};
```

### pa11y CI

```json
// .pa11yci.json
{
    "defaults": {
        "standard": "WCAG2AA",
        "timeout": 10000,
        "wait": 1000
    },
    "urls": [
        "http://localhost:9000/wp-admin/admin.php?page=wpshadow",
        "http://localhost:9000/wp-admin/admin.php?page=wpshadow-settings",
        "http://localhost:9000/wp-admin/admin.php?page=wpshadow-tools"
    ]
}
```

**Run tests:**
```bash
pa11y-ci --config .pa11yci.json
```

---

## 📊 Success Criteria

A page is considered **accessibility compliant** when:

- [ ] **Lighthouse Accessibility Score:** 90+ (ideally 100)
- [ ] **axe DevTools:** 0 violations
- [ ] **WAVE:** 0 errors, 0 contrast errors
- [ ] **Keyboard navigation:** All interactive elements reachable
- [ ] **Screen reader:** All content and functionality accessible
- [ ] **Color contrast:** All text meets WCAG AA
- [ ] **Focus indicators:** Visible and high-contrast
- [ ] **Manual review:** Tested by team member with disabilities (if possible)

---

## 📚 Additional Resources

- [WCAG 2.1 Guidelines](https://www.w3.org/WAI/WCAG21/quickref/)
- [WebAIM Articles](https://webaim.org/articles/)
- [A11y Project Checklist](https://www.a11yproject.com/checklist/)
- [Inclusive Components](https://inclusive-components.design/)
- [WPShadow Accessibility Canon](ACCESSIBILITY_AND_INCLUSIVITY_CANON.md)

---

**Last Updated:** January 25, 2026  
**Maintainer:** WPShadow Accessibility Team  
**Questions?** Open an issue with the `accessibility` label
