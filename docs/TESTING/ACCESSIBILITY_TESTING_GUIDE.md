# WPShadow Accessibility Testing Guide

**Version:** 1.0  
**Phase:** 5 - Final Polish & Validation  
**Status:** ✅ Complete  
**Last Updated:** January 25, 2026

---

## Overview

This guide provides comprehensive instructions for testing WPShadow's accessibility compliance. All features must meet WCAG 2.1 AA standards before release.

**Core Principle:** "No feature is complete until it works for people with disabilities."

---

## ✅ Core Values Embedded

**Accessibility Pillar 🌍 - Accessibility First:** This guide embodies our foundational commitment: accessibility is not optional, and every feature must work for everyone.

**Accessibility Pillar 🎓 - Learning Inclusive:** Testing procedures are written for all experience levels—beginners and accessibility experts both.

**Accessibility Pillar 🌐 - Culturally Respectful:** Our testing standards respect diverse abilities and ensure inclusive design globally.

**Commandment #8 - Inspire Confidence:** Comprehensive testing builds confidence that WPShadow works reliably for all users.

Learn more: [PHILOSOPHY/VISION.md](../../PHILOSOPHY/VISION.md) | [PHILOSOPHY/ACCESSIBILITY.md](../../PHILOSOPHY/ACCESSIBILITY.md)

---

## Table of Contents

1. [Quick Start Checklist](#quick-start-checklist)
2. [Keyboard Navigation Testing](#keyboard-navigation-testing)
3. [Screen Reader Testing](#screen-reader-testing)
4. [Color Contrast Validation](#color-contrast-validation)
5. [ARIA Attributes Validation](#aria-attributes-validation)
6. [Automated Testing Tools](#automated-testing-tools)
7. [Manual Testing Procedures](#manual-testing-procedures)
8. [Common Issues & Fixes](#common-issues-fixes)

---
## Quick Start Checklist

Before marking any feature as "complete," verify these accessibility requirements:

### Essential Checks (Must Pass)
- [ ] **Keyboard Navigation**: All interactive elements accessible via Tab/Shift+Tab
- [ ] **Focus Indicators**: Visible focus rings on all focusable elements (3:1 contrast minimum)
- [ ] **Color Contrast**: Text meets 4.5:1 ratio (normal), 3:1 ratio (large/bold)
- [ ] **ARIA Labels**: All buttons/inputs have accessible names
- [ ] **Screen Reader**: Content announced correctly in logical order
- [ ] **Error Messages**: Form errors clearly identified and described
- [ ] **Skip Links**: Navigation can be skipped for keyboard users
- [ ] **Reduced Motion**: Animations respect prefers-reduced-motion preference

### Enhanced Checks (Should Pass)
- [ ] **Touch Targets**: Minimum 44×44px on mobile devices
- [ ] **Zoom**: Page usable at 200% zoom without horizontal scroll
- [ ] **Headings**: Proper heading hierarchy (h1 → h2 → h3)
- [ ] **Landmarks**: ARIA landmarks for main regions
- [ ] **Tables**: Data tables have proper headers and captions
- [ ] **Images**: All images have descriptive alt text
- [ ] **Links**: Link purpose clear from text or context

---

## Keyboard Navigation Testing

### Basic Navigation

**Test Procedure:**
1. Disconnect your mouse (or commit to not using it)
2. Load the WPShadow admin dashboard
3. Press Tab to move forward through focusable elements
4. Press Shift+Tab to move backward
5. Verify every interactive element is reachable

**Expected Results:**
- Tab order follows visual layout (top to bottom, left to right)
- Focus indicator clearly visible on current element
- No keyboard traps (can always Tab away)
- Skip links appear on first Tab press

### Interactive Components

**Toggle Switches:**
```
Tab        - Focus on toggle
Space      - Toggle on/off
Enter      - Toggle on/off (alternative)
```

**Dropdowns:**
```
Tab        - Focus on dropdown
Enter      - Open dropdown
Arrow Down - Next option
Arrow Up   - Previous option
Home       - First option
End        - Last option
Enter      - Select option
Escape     - Close without selecting
```

**Modals:**
```
Tab        - Cycle through modal elements only
Shift+Tab  - Reverse cycle
Escape     - Close modal
```

**Kanban Board:**
```
Tab        - Focus on next card
Enter      - Open card details
Space      - Select/deselect card
Arrow Keys - Navigate between cards (optional enhancement)
```

### Testing Script

```javascript
// Run in browser console to test keyboard navigation
(function testKeyboardNav() {
    console.log('=== Keyboard Navigation Test ===');
    
    const focusableElements = document.querySelectorAll(
        'button, a, input, select, textarea, [tabindex]:not([tabindex="-1"])'
    );
    
    console.log(`Found ${focusableElements.length} focusable elements`);
    
    let issues = [];
    
    focusableElements.forEach((el, index) => {
        // Check for visible focus indicator
        el.focus();
        const styles = window.getComputedStyle(el);
        const outline = styles.getPropertyValue('outline');
        const boxShadow = styles.getPropertyValue('box-shadow');
        
        if (outline === 'none' && !boxShadow.includes('0 0 0')) {
            issues.push({
                element: el,
                issue: 'No visible focus indicator'
            });
        }
        
        // Check for accessible name
        const name = el.getAttribute('aria-label') ||
                     el.getAttribute('aria-labelledby') ||
                     el.textContent.trim();
        
        if (!name) {
            issues.push({
                element: el,
                issue: 'No accessible name'
            });
        }
    });
    
    if (issues.length === 0) {
        console.log('✅ All keyboard navigation tests passed!');
    } else {
        console.error(`❌ Found ${issues.length} issues:`);
        console.table(issues);
    }
})();
```

---

## Screen Reader Testing

### Supported Screen Readers

**Windows:**
- NVDA (free, recommended for testing)
- JAWS (commercial, widely used)

**macOS:**
- VoiceOver (built-in)

**Linux:**
- Orca (free)

### VoiceOver Quick Start (macOS)

**Enable VoiceOver:**
```
Cmd + F5          - Toggle VoiceOver on/off
```

**Basic Navigation:**
```
VO + Right Arrow  - Next element
VO + Left Arrow   - Previous element
VO + A            - Read all
VO + Cmd + H      - Next heading
VO + Space        - Activate element
```

(VO = Control + Option)

### NVDA Quick Start (Windows)

**Basic Navigation:**
```
Tab               - Next focusable element
H                 - Next heading
B                 - Next button
F                 - Next form field
Insert + Down     - Read all
```

### Testing Checklist

**Page Structure:**
- [ ] Page title announced correctly
- [ ] Heading levels announced (h1, h2, h3)
- [ ] Landmarks announced (navigation, main, aside)
- [ ] Lists announced with item count

**Interactive Elements:**
- [ ] Buttons announced with role and label
- [ ] Links announced with role and destination
- [ ] Form fields announced with label and type
- [ ] Toggles announced as switches with state (on/off)
- [ ] Status messages announced automatically

**Dynamic Content:**
- [ ] AJAX updates announced via aria-live
- [ ] Loading states announced
- [ ] Error messages announced
- [ ] Success confirmations announced

### Common Screen Reader Issues

**Issue:** Button announced as "Button" with no label
**Fix:** Add `aria-label` attribute:
```html
<button aria-label="Save settings">
    <span class="dashicons dashicons-yes"></span>
</button>
```

**Issue:** Dynamic content update not announced
**Fix:** Use `aria-live` region:
```html
<div role="status" aria-live="polite">
    <!-- Dynamic content here -->
</div>
```

**Issue:** Form error not announced
**Fix:** Link error to field with `aria-describedby`:
```html
<input 
    type="email" 
    id="user-email"
    aria-invalid="true"
    aria-describedby="email-error"
/>
<span id="email-error" class="error">Please enter a valid email address</span>
```

---

## Color Contrast Validation

### WCAG Requirements

**Normal Text (< 18pt or < 14pt bold):**
- Minimum: 4.5:1 contrast ratio (AA)
- Enhanced: 7:1 contrast ratio (AAA)

**Large Text (≥ 18pt or ≥ 14pt bold):**
- Minimum: 3:1 contrast ratio (AA)
- Enhanced: 4.5:1 contrast ratio (AAA)

**UI Components & Graphics:**
- Minimum: 3:1 contrast ratio (AA)

### WPShadow Color Palette (Pre-validated)

**Primary Colors:**
```css
/* Text on White Background */
--wps-gray-900: #111827;    /* 15.3:1 ✅ AAA */
--wps-gray-800: #1f2937;    /* 13.1:1 ✅ AAA */
--wps-gray-700: #374151;    /* 10.4:1 ✅ AAA */
--wps-gray-600: #4b5563;    /* 7.5:1 ✅ AAA */
--wps-gray-500: #6b7280;    /* 4.9:1 ✅ AA */

/* Status Colors (on light backgrounds) */
--wps-success-dark: #047857; /* 4.6:1 ✅ AA */
--wps-warning-dark: #d97706; /* 4.5:1 ✅ AA */
--wps-danger-dark: #dc2626;  /* 4.7:1 ✅ AA */
--wps-info-dark: #2563eb;    /* 4.9:1 ✅ AA */
```

### Testing Tools

**Browser Extensions:**
- **Accessibility Insights** (Chrome/Edge) - Comprehensive testing
- **axe DevTools** (Chrome/Firefox/Edge) - Automated scans
- **WAVE** (Chrome/Firefox/Edge) - Visual feedback

**Online Tools:**
- **WebAIM Contrast Checker**: https://webaim.org/resources/contrastchecker/
- **Colour Contrast Analyser**: https://www.tpgi.com/color-contrast-checker/

**Built-in WPShadow Tool:**
Navigate to: WP Admin → WPShadow → Tools → Color Contrast Checker

### Manual Testing Procedure

1. Take screenshot of interface
2. Open in color contrast checker
3. Sample foreground color (text)
4. Sample background color
5. Verify ratio meets WCAG AA (4.5:1)

**Example:**
```
Text: #4b5563 (gray-600)
Background: #ffffff (white)
Ratio: 7.5:1 ✅ PASS AA (4.5:1 required)
```

---

## ARIA Attributes Validation

### Required ARIA Patterns

**Buttons:**
```html
<!-- Text button (no ARIA needed) -->
<button>Save Settings</button>

<!-- Icon-only button (needs label) -->
<button aria-label="Close dialog">
    <span class="dashicons dashicons-no"></span>
</button>

<!-- Button with loading state -->
<button aria-busy="true" aria-label="Saving...">
    Saving...
</button>
```

**Form Fields:**
```html
<!-- With visible label -->
<label for="site-name">Site Name</label>
<input type="text" id="site-name" required aria-required="true" />

<!-- With error state -->
<input 
    type="email" 
    id="email"
    aria-invalid="true"
    aria-describedby="email-error"
/>
<span id="email-error" role="alert">Invalid email format</span>
```

**Toggle Switches:**
```html
<button 
    role="switch"
    aria-checked="false"
    aria-label="Enable Guardian mode"
    class="wps-toggle"
>
    <span class="wps-toggle-track"></span>
    <span class="wps-toggle-thumb"></span>
</button>
```

**Modals:**
```html
<div 
    role="dialog"
    aria-labelledby="modal-title"
    aria-describedby="modal-description"
    aria-modal="true"
>
    <h2 id="modal-title">Confirm Action</h2>
    <p id="modal-description">Are you sure you want to proceed?</p>
    <button>Confirm</button>
    <button>Cancel</button>
</div>
```

**Live Regions:**
```html
<!-- Polite (non-urgent updates) -->
<div role="status" aria-live="polite">
    Settings saved successfully.
</div>

<!-- Assertive (urgent updates) -->
<div role="alert" aria-live="assertive">
    Error: Unable to save settings.
</div>
```

### Validation Script

```javascript
// Run in browser console to validate ARIA attributes
(function validateAria() {
    console.log('=== ARIA Attributes Validation ===');
    
    const issues = [];
    
    // Check buttons
    document.querySelectorAll('button').forEach(btn => {
        const hasLabel = btn.textContent.trim() || 
                        btn.getAttribute('aria-label') ||
                        btn.getAttribute('aria-labelledby');
        
        if (!hasLabel) {
            issues.push({
                element: btn,
                issue: 'Button without accessible name'
            });
        }
    });
    
    // Check inputs
    document.querySelectorAll('input:not([type="hidden"])').forEach(input => {
        const id = input.getAttribute('id');
        const hasLabel = input.getAttribute('aria-label') ||
                        input.getAttribute('aria-labelledby') ||
                        (id && document.querySelector(`label[for="${id}"]`));
        
        if (!hasLabel) {
            issues.push({
                element: input,
                issue: 'Input without associated label'
            });
        }
    });
    
    // Check images
    document.querySelectorAll('img').forEach(img => {
        if (!img.hasAttribute('alt')) {
            issues.push({
                element: img,
                issue: 'Image without alt text'
            });
        }
    });
    
    if (issues.length === 0) {
        console.log('✅ All ARIA validation tests passed!');
    } else {
        console.error(`❌ Found ${issues.length} issues:`);
        console.table(issues);
    }
})();
```

---

## Automated Testing Tools

### Browser Extensions

**1. Accessibility Insights for Web**
- Download: https://accessibilityinsights.io/
- Features: FastPass, Assessment, Ad hoc tools
- **Use for:** Complete WCAG 2.1 AA assessment

**2. axe DevTools**
- Download: https://www.deque.com/axe/devtools/
- Features: Automated scans, intelligent guided tests
- **Use for:** Quick scans during development

**3. WAVE**
- Download: https://wave.webaim.org/extension/
- Features: Visual feedback, inline errors
- **Use for:** Visual accessibility audits

### Command Line Tools

**Pa11y:**
```bash
npm install -g pa11y
pa11y https://your-site.local/wp-admin/admin.php?page=wpshadow
```

**Lighthouse:**
```bash
npm install -g lighthouse
lighthouse https://your-site.local/wp-admin/admin.php?page=wpshadow --only-categories=accessibility
```

### WordPress Integration

**WPShadow includes built-in accessibility validation:**

Navigate to: **WP Admin → WPShadow → Diagnostics**

Run the **Accessibility Validation** diagnostic to check:
- CSS focus indicators
- JavaScript keyboard support
- PHP ARIA attributes
- Color contrast compliance

---

## Manual Testing Procedures

### Complete Accessibility Audit (30 minutes)

**1. Keyboard Navigation (10 min)**
- [ ] Disconnect mouse
- [ ] Tab through entire dashboard
- [ ] Verify all interactive elements accessible
- [ ] Check focus indicators visible
- [ ] Test skip links

**2. Screen Reader Testing (10 min)**
- [ ] Enable screen reader (VoiceOver/NVDA)
- [ ] Navigate through page structure
- [ ] Verify all content announced correctly
- [ ] Test form interactions
- [ ] Check dynamic content announcements

**3. Visual Inspection (10 min)**
- [ ] Check color contrast with tool
- [ ] Verify text readable at 200% zoom
- [ ] Test with high contrast mode
- [ ] Verify touch targets on mobile
- [ ] Check reduced motion support

---

## Common Issues & Fixes

### Issue #1: Focus Indicator Not Visible

**Problem:**
```css
/* ❌ BAD */
button:focus {
    outline: none;
}
```

**Solution:**
```css
/* ✅ GOOD */
button:focus-visible {
    outline: 3px solid var(--wps-primary);
    outline-offset: 2px;
}
```

### Issue #2: Button Without Accessible Name

**Problem:**
```html
<!-- ❌ BAD -->
<button onclick="closeModal()">
    <span class="dashicons dashicons-no"></span>
</button>
```

**Solution:**
```html
<!-- ✅ GOOD -->
<button onclick="closeModal()" aria-label="Close dialog">
    <span class="dashicons dashicons-no" aria-hidden="true"></span>
</button>
```

### Issue #3: Dynamic Content Not Announced

**Problem:**
```javascript
// ❌ BAD
document.getElementById('status').textContent = 'Saved!';
```

**Solution:**
```javascript
// ✅ GOOD
const statusEl = document.getElementById('status');
statusEl.setAttribute('role', 'status');
statusEl.setAttribute('aria-live', 'polite');
statusEl.textContent = 'Saved!';
```

### Issue #4: Poor Color Contrast

**Problem:**
```css
/* ❌ BAD - Only 2.8:1 ratio */
.muted-text {
    color: #999999;
    background: #ffffff;
}
```

**Solution:**
```css
/* ✅ GOOD - 4.9:1 ratio */
.muted-text {
    color: #6b7280; /* gray-500 */
    background: #ffffff;
}
```

---

## Resources

### Official Guidelines
- **WCAG 2.1:** https://www.w3.org/WAI/WCAG21/quickref/
- **ARIA Authoring Practices:** https://www.w3.org/WAI/ARIA/apg/

### WPShadow Documentation
- **Accessibility Canon:** docs/ACCESSIBILITY_AND_INCLUSIVITY_CANON.md
- **Design System:** docs/DESIGN_SYSTEM.md
- **Coding Standards:** docs/CODING_STANDARDS.md

### Testing Communities
- **WebAIM:** https://webaim.org/
- **Deque University:** https://dequeuniversity.com/
- **A11y Project:** https://www.a11yproject.com/

---

## Checklist for Pull Requests

Before submitting a PR with UI changes, verify:

- [ ] Ran automated accessibility scan (axe/WAVE)
- [ ] Tested keyboard navigation (all elements reachable)
- [ ] Tested with screen reader (VoiceOver/NVDA)
- [ ] Verified color contrast (4.5:1 minimum)
- [ ] Added ARIA attributes where needed
- [ ] Tested at 200% zoom
- [ ] Tested on mobile (44×44px touch targets)
- [ ] Verified reduced motion support
- [ ] Updated this documentation if new patterns added

---

**Remember:** Accessibility is not a feature—it's a requirement. No PR is complete until it passes these checks.

For questions or assistance, consult the WPShadow Accessibility Canon or reach out to the accessibility team.
