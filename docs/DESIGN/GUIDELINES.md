# WPShadow Design Guidelines - Implementation Checklist

**Version:** 1.0 (2026 Redesign)  
**Created:** January 26, 2026  
**Related Issues:** #660-#680 (Design Modernization Series)  
**Status:** Reference & Validation Guide

---

## 📖 Overview

This document consolidates all design guidelines that were established in GitHub issues #660-680 and provides a comprehensive checklist for validating that these guidelines have been properly applied across all plugin pages.

The design system is inspired by:
- **GitHub:** Clean, professional, accessible
- **Stripe Dashboard:** Excellent form controls and cards
- **Replit:** Modern project management UI
- **Linear:** Beautiful, minimalist design
- **Tailwind Compass:** Simplicity and clarity

---

## 🎯 Core Design Philosophy

Per WPShadow's 11 Commandments:
- **#7**: Ridiculously Good for Free (better UX than premium plugins)
- **#8**: Inspire Confidence (clear feedback, empowering interface)
- **#11**: Talk-About-Worthy (features users want to recommend)

### CANON Principles
- 🌍 **Accessibility First** (WCAG 2.1 AA Compliant)
- 🎓 **Learning Inclusive** (multiple documentation formats)
- 🌐 **Culturally Respectful** (global, inclusive design)

---

## 🎨 Design System Tokens

All design tokens are defined in `assets/css/design-system.css`.

### Color Palette

```css
/* Primary Brand Colors */
--wps-primary: #123456;
--wps-primary-hover: #0d1f2d;
--wps-primary-light: #e8f0f5;
--wps-primary-dark: #0a1620;

/* Neutral Palette (9 levels) */
--wps-gray-50: #f9fafb;      /* Lightest */
--wps-gray-100: #f3f4f6;
--wps-gray-200: #e5e7eb;
--wps-gray-300: #d1d5db;
--wps-gray-400: #9ca3af;     /* UI: 3.86:1 ✅ | Text: ❌ */
--wps-gray-500: #6b7280;     /* Text: 5.14:1 ✅ */
--wps-gray-600: #4b5563;     /* Text: 6.77:1 ✅ */
--wps-gray-700: #374151;     /* Text: 8.59:1 ✅ */
--wps-gray-800: #1f2937;
--wps-gray-900: #111827;     /* Darkest */

/* Semantic Colors */
--wps-success: #10b981;       /* Green base */
--wps-success-dark: #047857;  /* For text: 4.97:1 ✅ */
--wps-success-light: #d1fae5;

--wps-warning: #f59e0b;       /* Orange base */
--wps-warning-dark: #d97706;  /* For text: 4.6:1+ ✅ */
--wps-warning-light: #fef3c7;

--wps-danger: #ef4444;        /* Red base */
--wps-danger-dark: #dc2626;   /* For text: 4.5:1+ ✅ */
--wps-danger-light: #fee2e2;

--wps-info: #3b82f6;          /* Blue base */
--wps-info-dark: #2563eb;     /* For text: 4.5:1+ ✅ */
--wps-info-light: #dbeafe;
```

**IMPORTANT (Issue #664):** Use dark color variants for all text to meet WCAG AA 4.5:1 contrast requirement.

### Typography

```css
/* Font Families */
--wps-font-sans: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, ...
--wps-font-mono: "SF Mono", Monaco, "Cascadia Code", ...

/* Font Sizes */
--wps-text-xs: 0.75rem;    /* 12px */
--wps-text-sm: 0.875rem;   /* 14px */
--wps-text-base: 1rem;     /* 16px */
--wps-text-lg: 1.125rem;   /* 18px */
--wps-text-xl: 1.25rem;    /* 20px */
--wps-text-2xl: 1.5rem;    /* 24px */
--wps-text-3xl: 1.875rem;  /* 30px */
--wps-text-4xl: 2.25rem;   /* 36px */

/* Font Weights */
--wps-font-normal: 400;
--wps-font-medium: 500;
--wps-font-semibold: 600;
--wps-font-bold: 700;
```

### Spacing Scale

```css
--wps-space-1: 0.25rem;   /* 4px */
--wps-space-2: 0.5rem;    /* 8px */
--wps-space-3: 0.75rem;   /* 12px */
--wps-space-4: 1rem;      /* 16px */
--wps-space-5: 1.25rem;   /* 20px */
--wps-space-6: 1.5rem;    /* 24px */
--wps-space-8: 2rem;      /* 32px */
--wps-space-10: 2.5rem;   /* 40px */
--wps-space-12: 3rem;     /* 48px */
--wps-space-16: 4rem;     /* 64px */
```

### Border Radius

```css
--wps-radius-sm: 0.375rem;   /* 6px */
--wps-radius-md: 0.5rem;     /* 8px */
--wps-radius-lg: 0.75rem;    /* 12px */
--wps-radius-xl: 1rem;       /* 16px */
--wps-radius-full: 9999px;   /* Full round */
```

### Shadows

```css
--wps-shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
--wps-shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
--wps-shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
--wps-shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
--wps-shadow-focus: 0 0 0 3px rgba(18, 52, 86, 0.2);  /* 3px focus ring */
```

---

## 📦 Component Guidelines

### 1. Cards

**File:** `assets/css/design-system.css` (line 275+)

```css
.wps-card {
    background: #ffffff;
    border: 1px solid var(--wps-gray-200);
    border-radius: var(--wps-radius-lg);
    padding: var(--wps-space-6);        /* 24px */
    margin-bottom: var(--wps-space-6);  /* 24px */
    box-shadow: var(--wps-shadow-sm);
    transition: box-shadow 0.2s, transform 0.2s;
}

.wps-card:hover {
    box-shadow: var(--wps-shadow-md);
}

.wps-card-interactive {
    cursor: pointer;
}

.wps-card-interactive:hover {
    transform: translateY(-2px);        /* Lift effect */
    box-shadow: var(--wps-shadow-lg);
}
```

**✅ Do:**
- Use cards to group related content
- Apply hover lift effect for interactive cards
- Include card-header, card-body, card-footer sections
- Use ARIA labels for screen readers

**❌ Don't:**
- Nest cards more than 2 levels deep
- Use cards for single-line content
- Forget hover states on interactive cards

### 2. Buttons

**File:** `assets/css/design-system.css` (line 2356+)

```css
.wps-btn {
    display: inline-flex;
    align-items: center;
    gap: var(--wps-space-2);           /* 8px spacing */
    padding: var(--wps-space-2) var(--wps-space-4);  /* 8px 16px */
    font-size: var(--wps-text-sm);     /* 14px */
    font-weight: var(--wps-font-medium);
    border-radius: var(--wps-radius-md);
    cursor: pointer;
    transition: 0.2s ease;
}

.wps-btn:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgba(18, 52, 86, 0.2);  /* 3px focus ring */
}

.wps-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

/* Button Variants */
.wps-btn-primary {
    background: var(--wps-primary);
    color: white;
    border-color: var(--wps-primary);
}

.wps-btn-primary:hover:not(:disabled) {
    background: var(--wps-primary-hover);
    box-shadow: var(--wps-shadow-md);
    transform: translateY(-1px);
}

.wps-btn-secondary {
    background: white;
    color: var(--wps-gray-700);
    border-color: var(--wps-gray-300);
}

.wps-btn-secondary:hover:not(:disabled) {
    background: var(--wps-gray-50);
    border-color: var(--wps-gray-400);
}

.wps-btn-success {
    background: var(--wps-success-dark);  /* #047857 - 4.97:1 ✅ */
    color: white;
    border-color: var(--wps-success-dark);
}

.wps-btn-danger {
    background: var(--wps-danger);
    color: white;
    border-color: var(--wps-danger);
}

.wps-btn-danger:hover:not(:disabled) {
    background: var(--wps-danger-dark);
}

.wps-btn-ghost {
    background: transparent;
    color: var(--wps-gray-700);
    border-color: transparent;
}

.wps-btn-ghost:hover:not(:disabled) {
    background: var(--wps-gray-100);
}

/* Button Sizes */
.wps-btn-sm {
    padding: var(--wps-space-2) var(--wps-space-3);  /* 8px 12px */
    font-size: var(--wps-text-xs);
}

.wps-btn-lg {
    padding: var(--wps-space-4) var(--wps-space-6);  /* 16px 24px */
    font-size: var(--wps-text-base);
}

.wps-btn-icon {
    padding: var(--wps-space-3);
    aspect-ratio: 1;
}
```

**✅ Do:**
- Use primary buttons for main actions
- Use danger buttons for destructive actions
- Include icons for clarity (left-aligned)
- Add aria-label for icon-only buttons
- Use dark color variants for text (WCAG AA)

**❌ Don't:**
- Use more than one primary button per section
- Mix button styles unnecessarily
- Use gray-400 for text (insufficient contrast)
- Create buttons without accessible labels

### 3. Form Controls

**File:** `assets/css/design-system.css`

#### Text Input

```html
<div class="wps-form-group">
    <label for="site-name" class="wps-form-label">Site Name</label>
    <input type="text" id="site-name" class="wps-input" placeholder="Enter site name">
    <p class="wps-form-help">This will be displayed in the header.</p>
</div>
```

#### Toggle Switch

```html
<div class="wps-toggle-wrapper">
    <label class="wps-toggle" for="enable-feature">
        <input type="checkbox" id="enable-feature" />
        <span class="wps-toggle-slider"></span>
        <span class="screen-reader-text">Enable feature</span>
    </label>
    <label for="enable-feature" class="wps-toggle-label">Enable Auto-Fix</label>
</div>
```

**✅ Do:**
- Always pair inputs with labels
- Use toggles instead of checkboxes
- Include help text for complex fields
- Make all inputs keyboard accessible
- Use proper semantic HTML

**❌ Don't:**
- Use placeholder as a label
- Create unlabeled form fields
- Forget focus states

### 4. Alerts & Notifications

```html
<div class="wps-alert wps-alert-success" role="alert">
    <strong>Success!</strong> Your settings have been saved.
</div>

<div class="wps-alert wps-alert-warning" role="alert">
    <strong>Warning:</strong> This action cannot be undone.
</div>

<div class="wps-alert wps-alert-danger" role="alert">
    <strong>Error:</strong> Failed to connect to the server.
</div>
```

**✅ Do:**
- Use role="alert" for screen readers
- Use semantic colors appropriately
- Make alerts dismissible when appropriate
- Use clear, helpful messages

**❌ Don't:**
- Overuse danger alerts (causes alert fatigue)
- Use alerts for permanent content
- Use color alone for meaning

### 5. Badges

```html
<span class="wps-badge wps-badge-success">Active</span>
<span class="wps-badge wps-badge-warning">Pending</span>
<span class="wps-badge wps-badge-danger">Critical</span>
<span class="wps-badge wps-badge-info">New</span>
```

### 6. Tables

```html
<div class="wps-table-container">
    <table class="wps-table wps-table-striped">
        <thead>
            <tr>
                <th>Name</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>WordPress Version</td>
                <td><span class="wps-badge wps-badge-success">Up to date</span></td>
                <td><button class="wps-btn wps-btn-sm wps-btn-secondary">View</button></td>
            </tr>
        </tbody>
    </table>
</div>
```

### 7. Modals

```html
<div class="wps-modal" id="my-modal" role="dialog" aria-labelledby="modal-title" aria-modal="true">
    <div class="wps-modal-content">
        <button class="wps-modal-close" aria-label="Close modal">&times;</button>
        <h2 id="modal-title">Modal Title</h2>
        <p>Modal content goes here.</p>
        <div class="wps-modal-footer">
            <button class="wps-btn wps-btn-primary">Confirm</button>
            <button class="wps-btn wps-btn-secondary">Cancel</button>
        </div>
    </div>
</div>
```

### 8. Loading States

```html
<div class="wps-loading-message">
    <span class="dashicons dashicons-update wps-spinner"></span>
    <div>
        <h2>Loading...</h2>
        <p>Please wait while we scan your site.</p>
    </div>
</div>
```

---

## ♿ Accessibility Requirements

### WCAG 2.1 AA Compliance

#### Color Contrast (Issue #664)

**Text (4.5:1 minimum):**
```css
/* ✅ Use gray-500 or darker for text */
color: var(--wps-gray-500, #6b7280);   /* 5.14:1 ✅ */
color: var(--wps-gray-600, #4b5563);   /* 6.77:1 ✅ */
color: var(--wps-gray-700, #374151);   /* 8.59:1 ✅ */

/* ❌ DON'T use gray-400 for text */
color: var(--wps-gray-400, #9ca3af);   /* 3.86:1 ❌ FAILS */
```

**Semantic Colors for Text:**
```css
/* Use dark variants */
color: var(--wps-success-dark, #047857);  /* 4.97:1 ✅ */
color: var(--wps-warning-dark, #d97706);  /* 4.6:1+ ✅ */
color: var(--wps-danger-dark, #dc2626);   /* 4.5:1+ ✅ */
color: var(--wps-info-dark, #2563eb);     /* 4.5:1+ ✅ */
```

**UI Components (3:1 minimum):**
```css
/* ✅ OK: gray-400 for borders, icons */
border-color: var(--wps-gray-400, #9ca3af);  /* 3.86:1 ✅ */
color: var(--wps-gray-400, #9ca3af);        /* For UI ✅ */
```

#### Keyboard Navigation
- All interactive elements must be keyboard accessible
- Use logical tab order
- Never use `tabindex` > 0
- Ensure focus indicators visible (3px minimum)

#### Screen Readers
- Always include ARIA labels for icon-only buttons
- Use semantic HTML (`<button>`, `<nav>`, `<main>`)
- Use `role` attributes appropriately
- Use `aria-live` for dynamic content

#### Touch Targets
- Minimum 44x44px on mobile
- Adequate spacing between interactive elements
- Larger targets for primary actions

---

## ✅ Implementation Validation Checklist

### Per-Page Checklist

For each WPShadow admin page, verify:

#### Visual Design
- [ ] White backgrounds with subtle shadows (not gray)
- [ ] Consistent card styling (rounded corners, padding)
- [ ] Proper button variants (primary, secondary, danger, ghost)
- [ ] Correct spacing using design system variables
- [ ] Modern form controls (toggles, sliders, styled selects)

#### Colors
- [ ] Primary color (#123456) used for primary actions
- [ ] Semantic colors used appropriately (success, warning, danger, info)
- [ ] Gray palette consistent (gray-50 to gray-900)
- [ ] Dark color variants used for ALL text (#047857, #d97706, #dc2626, #2563eb)
- [ ] Gray-400 only used for borders/icons, NOT text

#### Accessibility
- [ ] All buttons have visible focus indicators (3px ring)
- [ ] All text meets 4.5:1 contrast ratio minimum
- [ ] Icon-only buttons have aria-label attributes
- [ ] Form labels properly associated with inputs
- [ ] Color not used alone for meaning (text + icons/indicators)

#### Components
- [ ] Cards have proper header/body/footer structure
- [ ] Buttons use consistent sizes and spacing
- [ ] Form groups have labels and help text
- [ ] Alerts use role="alert" attribute
- [ ] Tables are responsive and accessible
- [ ] Modals have proper ARIA attributes

#### Typography
- [ ] Consistent font family (system sans-serif)
- [ ] Proper font sizes from design system
- [ ] Proper line heights and spacing
- [ ] Font weights appropriate (400, 500, 600, 700)

---

## 📋 Pages to Review

Based on typical WordPress plugin architecture, these pages should be reviewed:

1. **Dashboard** - Main plugin page with KPI cards
2. **Diagnostics/Findings** - List of detected issues
3. **Kanban Board** - Findings organized by status
4. **Treatments** - Auto-fix recommendations
5. **Settings** - Configuration options
6. **Reports** - Generated reports and analytics
7. **Workflows** - Automation builder
8. **About/Help** - Plugin information
9. **Admin Bar** - Top admin bar menu items

---

## 🔧 Tools & Files

### CSS Files
- `assets/css/design-system.css` - Design tokens and components
- `assets/css/kanban-board.css` - Kanban board styling
- `assets/css/workflow-builder.css` - Workflow automation UI
- `assets/css/gauges.css` - KPI gauge components
- `assets/css/dashboard.css` - Dashboard styling
- `assets/css/reports.css` - Report generation UI
- `assets/css/guardian-dashboard-settings.css` - Guardian settings

### Documentation Files
- `docs/DESIGN_SYSTEM_COMPONENTS.md` - Full design system documentation
- `docs/UI_COMPONENTS.md` - UI component patterns
- `docs/WCAG_COMPLIANCE_QUICK_REF.md` - Accessibility requirements
- `docs/ASSETS_DEVELOPER_GUIDE.md` - Asset development guide

### Validation Tools
- Use DevTools Inspector to check computed styles
- Use WAVE browser extension to check accessibility
- Use Lighthouse DevTools for performance/accessibility
- Use WebAIM Contrast Checker for color contrast verification

---

## 🚀 Known Issues & Fixes

### Issue #664: Workflow Builder Color Contrast
**Status:** ✅ FIXED

Changed:
- Placeholder text: `gray-400` → `gray-500` (3.86:1 → 5.14:1)
- Warning icons: `warning` → `warning-dark` (2.18:1 → 4.6:1+)
- Remove buttons: `gray-400` → `gray-500` (3.86:1 → 5.14:1)

Files updated:
- `assets/css/workflow-builder.css`
- `assets/css/design-system.css`

---

## 📞 Questions?

For questions about design guidelines:
1. Check [DESIGN_SYSTEM_COMPONENTS.md](DESIGN_SYSTEM_COMPONENTS.md)
2. Check [UI_COMPONENTS.md](UI_COMPONENTS.md)
3. Check [WCAG_COMPLIANCE_QUICK_REF.md](WCAG_COMPLIANCE_QUICK_REF.md)
4. Review related GitHub issues #660-680

---

**Last Updated:** January 26, 2026  
**Maintained By:** WPShadow Development Team
