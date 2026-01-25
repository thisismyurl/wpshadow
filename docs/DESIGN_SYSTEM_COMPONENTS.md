# WPShadow Design System - Component Library

**Version:** 2.0 (2026 Redesign)  
**Last Updated:** January 25, 2026  
**Reference:** GitHub, Stripe Dashboard, Replit, Linear, Tailwind Compass

---

## 📖 Table of Contents

1. [Design Philosophy](#design-philosophy)
2. [CSS Variables](#css-variables)
3. [Component Library](#component-library)
4. [Accessibility Guidelines](#accessibility-guidelines)
5. [Usage Examples](#usage-examples)
6. [Migration Guide](#migration-guide)

---

## 🎨 Design Philosophy

Per WPShadow's guiding principles:

### Core Principles
- **Commandment #7**: Ridiculously Good for Free (better UX than premium plugins)
- **Commandment #8**: Inspire Confidence (clear feedback, empowering interface)
- **Commandment #11**: Talk-About-Worthy (features users want to recommend)
- **CANON**: Accessibility First, Learning Inclusive, Culturally Respectful

### Design Aesthetic
- **Clean white backgrounds** with subtle shadows
- **Modern UI controls** (toggles, sliders, styled selects)
- **Consistent spacing** using design system variables
- **Professional polish** that makes users say "wow"
- **Full accessibility** (WCAG 2.1 AA compliant)

### Inspiration
- **GitHub**: Clean, professional, accessible
- **Stripe Dashboard**: Excellent form controls and cards
- **Replit**: Modern project management UI
- **Linear**: Beautiful, minimalist design
- **Tailwind Compass**: Simplicity and clarity

---

## 🎨 CSS Variables

All design tokens are defined in `assets/css/design-system.css`:

### Color Palette

```css
/* Primary Brand Colors */
--wps-primary: #123456;          /* WPShadow branded color */
--wps-primary-hover: #0d1f2d;
--wps-primary-light: #e8f0f5;
--wps-primary-dark: #0a1620;

/* Neutral Palette */
--wps-gray-50: #f9fafb;          /* Lightest */
--wps-gray-100: #f3f4f6;
--wps-gray-200: #e5e7eb;
--wps-gray-300: #d1d5db;
--wps-gray-400: #9ca3af;
--wps-gray-500: #6b7280;
--wps-gray-600: #4b5563;
--wps-gray-700: #374151;
--wps-gray-800: #1f2937;
--wps-gray-900: #111827;         /* Darkest */

/* Semantic Colors */
--wps-success: #10b981;          /* Green */
--wps-success-light: #d1fae5;
--wps-success-dark: #047857;

--wps-warning: #f59e0b;          /* Orange/Yellow */
--wps-warning-light: #fef3c7;
--wps-warning-dark: #d97706;

--wps-danger: #ef4444;           /* Red */
--wps-danger-light: #fee2e2;
--wps-danger-dark: #dc2626;

--wps-info: #3b82f6;             /* Blue */
--wps-info-light: #dbeafe;
--wps-info-dark: #2563eb;
```

### Typography

```css
/* Font Families */
--wps-font-sans: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
--wps-font-mono: "SF Mono", Monaco, "Cascadia Code", "Roboto Mono", Consolas, "Courier New", monospace;

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
--wps-space-1: 0.25rem;  /* 4px */
--wps-space-2: 0.5rem;   /* 8px */
--wps-space-3: 0.75rem;  /* 12px */
--wps-space-4: 1rem;     /* 16px */
--wps-space-5: 1.25rem;  /* 20px */
--wps-space-6: 1.5rem;   /* 24px */
--wps-space-8: 2rem;     /* 32px */
--wps-space-10: 2.5rem;  /* 40px */
--wps-space-12: 3rem;    /* 48px */
--wps-space-16: 4rem;    /* 64px */
```

### Border Radius

```css
--wps-radius-sm: 0.375rem;  /* 6px */
--wps-radius-md: 0.5rem;    /* 8px */
--wps-radius-lg: 0.75rem;   /* 12px */
--wps-radius-xl: 1rem;      /* 16px */
--wps-radius-full: 9999px;  /* Full round */
```

### Shadows

```css
--wps-shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
--wps-shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
--wps-shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
--wps-shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
--wps-shadow-focus: 0 0 0 3px rgba(18, 52, 86, 0.2);  /* Accessible focus ring */
```

---

## 📦 Component Library

### 1. Cards

Modern card components with hover effects and flexible layouts.

**Classes:**
- `.wps-card` - Base card component
- `.wps-card-interactive` - Adds hover lift effect
- `.wps-card-header` - Card header section
- `.wps-card-title` - Card title
- `.wps-card-description` - Card description
- `.wps-card-body` - Card body content
- `.wps-card-footer` - Card footer section

**Example:**
```html
<div class="wps-card">
    <div class="wps-card-header">
        <h3 class="wps-card-title">
            <span class="dashicons dashicons-admin-generic"></span>
            Card Title
        </h3>
        <p class="wps-card-description">Optional description text</p>
    </div>
    <div class="wps-card-body">
        <p>Card content goes here.</p>
    </div>
    <div class="wps-card-footer">
        <button class="wps-btn wps-btn-primary">Action</button>
    </div>
</div>
```

**✅ Do:**
- Use cards to group related content
- Keep card content focused and concise
- Use interactive cards for clickable items
- Include ARIA labels for screen readers

**❌ Don't:**
- Nest cards more than 2 levels deep
- Use cards for single-line content (use list items)
- Forget hover states on interactive cards

---

### 2. Buttons

Modern button system with clear visual hierarchy.

**Classes:**
- `.wps-btn` - Base button
- `.wps-btn-primary` - Primary action (blue)
- `.wps-btn-secondary` - Secondary action (gray)
- `.wps-btn-success` - Success action (green)
- `.wps-btn-danger` - Danger action (red)
- `.wps-btn-ghost` - Minimal button
- `.wps-btn-sm` - Small button
- `.wps-btn-lg` - Large button
- `.wps-btn-icon` - Icon-only button

**Example:**
```html
<!-- Primary action -->
<button class="wps-btn wps-btn-primary">
    <span class="dashicons dashicons-saved"></span>
    Save Changes
</button>

<!-- Secondary action -->
<button class="wps-btn wps-btn-secondary">Cancel</button>

<!-- Danger action -->
<button class="wps-btn wps-btn-danger">
    <span class="dashicons dashicons-trash"></span>
    Delete
</button>

<!-- Icon-only button -->
<button class="wps-btn wps-btn-icon wps-btn-secondary" aria-label="Settings">
    <span class="dashicons dashicons-admin-generic"></span>
</button>
```

**✅ Do:**
- Use primary buttons for main actions
- Use danger buttons for destructive actions
- Include icons for clarity (left-aligned)
- Add aria-label for icon-only buttons

**❌ Don't:**
- Use more than one primary button per section
- Mix button styles unnecessarily
- Create buttons without accessible labels

---

### 3. Form Controls

Modern form inputs with consistent styling.

#### Text Input

```html
<div class="wps-form-group">
    <label for="site-name" class="wps-form-label">Site Name</label>
    <input type="text" id="site-name" class="wps-input" placeholder="Enter site name">
    <p class="wps-form-help">This will be displayed in the header.</p>
</div>
```

#### Select Dropdown

```html
<div class="wps-form-group">
    <label for="frequency" class="wps-form-label">Scan Frequency</label>
    <select id="frequency" class="wps-select">
        <option value="daily">Daily</option>
        <option value="weekly">Weekly</option>
        <option value="monthly">Monthly</option>
    </select>
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

#### Range Slider

```html
<div class="wps-form-group">
    <label for="threat-level" class="wps-form-label">Threat Level Threshold</label>
    <input type="range" id="threat-level" class="wps-slider" min="0" max="100" value="50">
    <p class="wps-form-help">Current value: <span id="threat-level-value">50</span></p>
</div>
```

**✅ Do:**
- Always pair inputs with labels
- Use placeholder text sparingly
- Include help text for complex fields
- Use toggles instead of checkboxes
- Make all inputs keyboard accessible

**❌ Don't:**
- Use placeholder as a label
- Create unlabeled form fields
- Forget focus states
- Use red borders for errors (use ARIA + text)

---

### 4. Badges & Tags

Small, colored labels for status and categories.

**Classes:**
- `.wps-badge` - Base badge
- `.wps-badge-success` - Green badge
- `.wps-badge-warning` - Yellow badge
- `.wps-badge-danger` - Red badge
- `.wps-badge-info` - Blue badge
- `.wps-badge-neutral` - Gray badge

**Example:**
```html
<span class="wps-badge wps-badge-success">Active</span>
<span class="wps-badge wps-badge-warning">Pending</span>
<span class="wps-badge wps-badge-danger">Critical</span>
<span class="wps-badge wps-badge-info">New</span>
```

---

### 5. Alerts & Notifications

Contextual messages with semantic colors.

**Classes:**
- `.wps-alert` - Base alert
- `.wps-alert-success` - Success message
- `.wps-alert-warning` - Warning message
- `.wps-alert-danger` - Error message
- `.wps-alert-info` - Info message

**Example:**
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
- Include clear action buttons when needed
- Use semantic colors appropriately
- Make alerts dismissible when appropriate

**❌ Don't:**
- Overuse danger alerts (causes alert fatigue)
- Use alerts for permanent content
- Forget to test with screen readers

---

### 6. Tables

Modern table system inspired by Stripe.

**Classes:**
- `.wps-table-container` - Wrapper with overflow and border
- `.wps-table` - Base table
- `.wps-table-striped` - Alternating row colors
- `.wps-table-compact` - Smaller padding
- `.wps-table-borderless` - Remove borders

**Example:**
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

---

### 7. Modals

Modern modal dialogs with backdrop.

**Classes:**
- `.wps-modal` - Modal backdrop
- `.wps-modal-content` - Modal content container
- `.wps-modal-close` - Close button
- `.wps-modal-small` - Small modal variant

**Example:**
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

**JavaScript:**
```javascript
// Open modal
document.getElementById('my-modal').classList.add('active');

// Close modal
document.getElementById('my-modal').classList.remove('active');
```

---

### 8. Toast Notifications

Temporary notification messages.

**Classes:**
- `.wps-toast` - Base toast
- `.wps-toast-success` - Success toast
- `.wps-toast-warning` - Warning toast
- `.wps-toast-danger` - Error toast
- `.wps-toast-info` - Info toast
- `.wps-toast-show` - Show state

**Example:**
```html
<div class="wps-toast wps-toast-success" role="alert" aria-live="polite">
    <span class="dashicons dashicons-yes-alt"></span>
    <span class="wps-toast-message">Settings saved successfully!</span>
</div>
```

---

### 9. Loading States

Spinners and skeleton screens.

**Classes:**
- `.wps-spinner` - Rotating spinner
- `.wps-skeleton` - Skeleton loader

**Example:**
```html
<!-- Spinner -->
<div class="wps-loading-message">
    <span class="dashicons dashicons-update wps-spinner"></span>
    <div>
        <h2>Loading...</h2>
        <p>Please wait while we scan your site.</p>
    </div>
</div>

<!-- Skeleton -->
<div class="wps-skeleton" style="height: 100px; width: 100%;"></div>
```

---

## ♿ Accessibility Guidelines

### Keyboard Navigation
- **All interactive elements must be keyboard accessible**
- Use `tabindex="0"` for custom interactive elements
- Never use `tabindex` > 0
- Ensure logical tab order

### Focus Indicators
- **Never remove focus outlines** (`:focus`)
- Use visible focus indicators (2px minimum)
- Focus indicators must have 3:1 contrast ratio

### Screen Readers
- **Always include ARIA labels** for icon-only buttons
- Use semantic HTML (`<button>`, `<nav>`, `<main>`)
- Use `role` attributes appropriately
- Use `aria-live` for dynamic content

### Color Contrast
- **Text:** 4.5:1 contrast ratio (WCAG AA)
- **Large text:** 3:1 contrast ratio
- **UI components:** 3:1 contrast ratio
- Never rely on color alone for meaning

### Touch Targets
- **Minimum 44x44px touch targets** on mobile
- Adequate spacing between interactive elements
- Larger targets for primary actions

---

## 📝 Usage Examples

### Example 1: Settings Page with Cards

```html
<div class="wps-page-container">
    <div class="wps-page-header">
        <h1 class="wps-page-title">
            <span class="dashicons dashicons-admin-settings"></span>
            Settings
        </h1>
        <p class="wps-page-subtitle">Configure WPShadow to match your needs</p>
    </div>

    <div class="wps-card">
        <div class="wps-card-header">
            <h3 class="wps-card-title">General Settings</h3>
        </div>
        <div class="wps-card-body">
            <div class="wps-form-group">
                <label for="site-name" class="wps-form-label">Site Name</label>
                <input type="text" id="site-name" class="wps-input" value="My Website">
            </div>
            
            <div class="wps-toggle-wrapper">
                <label class="wps-toggle" for="enable-guardian">
                    <input type="checkbox" id="enable-guardian" checked />
                    <span class="wps-toggle-slider"></span>
                </label>
                <label for="enable-guardian" class="wps-toggle-label">Enable Guardian Auto-Fix</label>
            </div>
        </div>
        <div class="wps-card-footer">
            <button class="wps-btn wps-btn-primary">Save Changes</button>
            <button class="wps-btn wps-btn-secondary">Cancel</button>
        </div>
    </div>
</div>
```

### Example 2: Dashboard with KPI Cards

```html
<div class="wps-page-container">
    <div class="wps-page-header">
        <h1 class="wps-page-title">Dashboard</h1>
        <p class="wps-page-subtitle">Your site health at a glance</p>
    </div>

    <div class="wps-grid-3">
        <div class="wps-card">
            <div class="wps-stat-card-icon">
                <span class="dashicons dashicons-shield-alt"></span>
            </div>
            <div class="wps-stat-card-label">Security Score</div>
            <div class="wps-stat-card-value">94%</div>
        </div>
        
        <div class="wps-card">
            <div class="wps-stat-card-icon">
                <span class="dashicons dashicons-performance"></span>
            </div>
            <div class="wps-stat-card-label">Performance</div>
            <div class="wps-stat-card-value">A+</div>
        </div>
        
        <div class="wps-card">
            <div class="wps-stat-card-icon">
                <span class="dashicons dashicons-yes-alt"></span>
            </div>
            <div class="wps-stat-card-label">Issues Fixed</div>
            <div class="wps-stat-card-value">127</div>
        </div>
    </div>
</div>
```

---

## 🔄 Migration Guide

### From Old Form Tables to Cards

**Before:**
```html
<table class="form-table">
    <tr>
        <th scope="row">Setting Name</th>
        <td>
            <input type="text" name="setting" />
        </td>
    </tr>
</table>
```

**After:**
```html
<div class="wps-card">
    <div class="wps-form-group">
        <label for="setting" class="wps-form-label">Setting Name</label>
        <input type="text" id="setting" name="setting" class="wps-input" />
    </div>
</div>
```

### From Old Checkboxes to Toggle Switches

**Before:**
```html
<input type="checkbox" id="enable-feature" name="enable_feature" />
<label for="enable-feature">Enable Feature</label>
```

**After:**
```html
<div class="wps-toggle-wrapper">
    <label class="wps-toggle" for="enable-feature">
        <input type="checkbox" id="enable-feature" name="enable_feature" />
        <span class="wps-toggle-slider"></span>
        <span class="screen-reader-text">Enable Feature</span>
    </label>
    <label for="enable-feature" class="wps-toggle-label">Enable Feature</label>
</div>
```

---

## 📚 Additional Resources

- [Product Philosophy (11 Commandments)](PRODUCT_PHILOSOPHY.md)
- [Accessibility & Inclusivity Canon](ACCESSIBILITY_AND_INCLUSIVITY_CANON.md)
- [Coding Standards](CODING_STANDARDS.md)
- [Architecture Overview](ARCHITECTURE.md)

---

**Questions?** Open an issue on GitHub or consult the Knowledge Base at https://wpshadow.com/kb/ui-redesign-2026
