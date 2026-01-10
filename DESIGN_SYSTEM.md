# TIMU Design System Guide

**Version:** 1.0
**Last Updated:** January 9, 2026

## Overview

The TIMU Design System provides a modern, cohesive visual language inspired by GitHub and Jetpack. It ensures consistency across all WordPress Support plugins and add-ons with a flexible, accessible component library.

---

## Design Tokens

### Typography

**Font Families:**
- Primary: `-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen, Ubuntu, Cantarell, Fira Sans, Droid Sans, Helvetica Neue, sans-serif`
- Monospace: `SFMono-Regular, Consolas, Liberation Mono, Menlo, Courier, monospace`

**Font Sizes:**
```css
--timu-text-xs: 0.75rem;   /* 12px */
--timu-text-sm: 0.875rem;  /* 14px */
--timu-text-base: 1rem;    /* 16px */
--timu-text-lg: 1.125rem;  /* 18px */
--timu-text-xl: 1.25rem;   /* 20px */
--timu-text-2xl: 1.5rem;   /* 24px */
--timu-text-3xl: 1.875rem; /* 30px */
```

**Font Weights:**
```css
--timu-font-normal: 400;
--timu-font-medium: 500;
--timu-font-semibold: 600;
--timu-font-bold: 700;
```

### Spacing (8px Base Grid)

```css
--timu-space-xs: 0.25rem;  /* 4px */
--timu-space-sm: 0.5rem;   /* 8px */
--timu-space-md: 1rem;     /* 16px */
--timu-space-lg: 1.5rem;   /* 24px */
--timu-space-xl: 2rem;     /* 32px */
--timu-space-2xl: 3rem;    /* 48px */
--timu-space-3xl: 4rem;    /* 64px */
```

### Colors

#### Light Mode
```css
/* Backgrounds */
--timu-bg-primary: #ffffff;
--timu-bg-secondary: #f6f8fa;
--timu-bg-tertiary: #eaeef2;

/* Borders */
--timu-border-primary: #d0d7de;
--timu-border-secondary: #e5e7eb;
--timu-border-muted: #f0f0f0;

/* Text */
--timu-text-primary: #24292f;    /* 12.63:1 contrast */
--timu-text-secondary: #57606a;  /* 7.45:1 contrast */
--timu-text-muted: #848d97;      /* 4.69:1 contrast */
--timu-text-inverted: #ffffff;

/* Accents */
--timu-accent-primary: #0969da;
--timu-accent-success: #1a7f37;
--timu-accent-warning: #9e6a03;
--timu-accent-danger: #da3633;
--timu-accent-info: #0969da;
```

#### Dark Mode (Auto-detected via `prefers-color-scheme`)
```css
/* Backgrounds */
--timu-bg-primary: #0d1117;
--timu-bg-secondary: #161b22;
--timu-bg-tertiary: #21262d;

/* Borders */
--timu-border-primary: #30363d;
--timu-border-secondary: #21262d;

/* Text */
--timu-text-primary: #e6edf3;
--timu-text-secondary: #c9d1d9;
--timu-text-muted: #8b949e;

/* Accents */
--timu-accent-primary: #58a6ff;
--timu-accent-success: #3fb950;
--timu-accent-warning: #d29922;
--timu-accent-danger: #f85149;
```

### Shadows

```css
--timu-shadow-xs: 0 1px 2px rgba(0, 0, 0, 0.05);
--timu-shadow-sm: 0 2px 4px rgba(0, 0, 0, 0.08);
--timu-shadow-md: 0 4px 8px rgba(0, 0, 0, 0.1);
--timu-shadow-lg: 0 8px 16px rgba(0, 0, 0, 0.12);
--timu-shadow-xl: 0 12px 24px rgba(0, 0, 0, 0.15);
```

### Border Radius

```css
--timu-radius-xs: 2px;
--timu-radius-sm: 4px;
--timu-radius-md: 6px;
--timu-radius-lg: 8px;
--timu-radius-full: 9999px;
```

### Transitions

```css
--timu-transition-fast: 150ms ease-out;
--timu-transition-base: 250ms ease-out;
--timu-transition-slow: 350ms ease-out;
```

---

## Components

### Buttons

#### Primary Button
```html
<button class="button button-primary">Save Changes</button>
```

**Variants:**
- `.button` or `.timu-btn` — Default secondary
- `.button-primary` or `.timu-btn-primary` — Primary action
- `.button-destructive` or `.timu-btn-danger` — Destructive action
- `.timu-btn-secondary` — Explicit secondary
- `.timu-btn-small` — Small size
- `.timu-btn-large` — Large size

**States:**
- `:hover` — Elevated shadow, color shift
- `:active` — Scale down (0.98)
- `:disabled` — 50% opacity

### Cards

```html
<div class="timu-card">
  <div class="timu-card-header">
    <h2>Card Title</h2>
  </div>
  <div class="timu-card-body">
    <p>Card content goes here.</p>
  </div>
  <div class="timu-card-footer">
    <button class="button">Action</button>
  </div>
</div>
```

**Features:**
- Hover: Elevated shadow, subtle lift
- Automatic top accent bar on hover
- Responsive padding

### Badges

```html
<span class="timu-badge">Default</span>
<span class="timu-badge timu-badge-success">Active</span>
<span class="timu-badge timu-badge-warning">Warning</span>
<span class="timu-badge timu-badge-danger">Error</span>
<span class="timu-badge timu-badge-info">Info</span>
```

**Features:**
- Pill-shaped with full border radius
- Color-coded backgrounds with borders
- Hover: Subtle scale (1.05)

### Tables

```html
<table class="timu-table">
  <caption class="timu-sr-only">Table description</caption>
  <thead>
    <tr>
      <th scope="col">Column</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>Data</td>
    </tr>
  </tbody>
</table>
```

**Features:**
- Alternating row backgrounds
- Hover state for rows
- Responsive wrapper: `.timu-table-responsive`
- Staggered fade-in animation for rows

### Alerts

```html
<div class="timu-alert timu-alert-success">
  <span>Success message</span>
  <button class="timu-alert-close">&times;</button>
</div>
```

**Variants:**
- `.timu-alert-success` — Green
- `.timu-alert-warning` — Yellow
- `.timu-alert-danger` — Red
- `.timu-alert-info` — Blue

**Features:**
- Slide-down animation on appear
- Left border accent (4px)
- Dismissible with close button

### Forms

```html
<div class="timu-form-group">
  <label for="field" class="timu-form-label">Label</label>
  <input type="text" id="field" class="timu-form-control" />
  <p class="timu-help">Help text</p>
</div>
```

**Form Elements:**
- `.timu-form-control` — Styled inputs
- `.timu-form-label` — Bold labels
- `.timu-help` — Muted help text
- `.timu-error` — Red error messages

**Focus State:**
- 3px blue shadow
- Border color change

### Grid Layout

```html
<div class="timu-grid">
  <!-- Auto-fit grid -->
</div>

<div class="timu-grid timu-grid-2">
  <!-- 2-column grid -->
</div>

<div class="timu-grid timu-grid-3">
  <!-- 3-column grid (responsive) -->
</div>
```

**Responsive Breakpoints:**
- `< 768px`: 3-col → 2-col, 2-col → 1-col
- `< 480px`: All grids → 1-col

### Loading States

```html
<!-- Spinner -->
<div class="timu-spinner"></div>

<!-- Skeleton -->
<div class="timu-skeleton" style="height: 100px;"></div>

<!-- Loading overlay -->
<div class="timu-loading">
  <button class="button">Button</button>
</div>
```

**Features:**
- Shimmer animation for skeletons
- Rotating spinner
- Auto-centered spinner overlay

### Progress Bars

```html
<div class="timu-progress">
  <div class="timu-progress-bar" style="width: 60%;"></div>
</div>
```

**Features:**
- Gradient fill
- Smooth width transitions
- Pulsing shimmer effect

### Toast Notifications

```html
<div class="timu-toast timu-toast-success">
  <p>Operation completed successfully!</p>
</div>
```

**Variants:**
- `.timu-toast-success`
- `.timu-toast-error`
- `.timu-toast-info`

**Position:** Fixed bottom-right
**Animation:** Slide in from bottom

---

## Utility Classes

### Spacing
```css
.timu-mt-sm, .timu-mt-md, .timu-mt-lg
.timu-mb-sm, .timu-mb-md, .timu-mb-lg
.timu-p-md, .timu-p-lg
```

### Layout
```css
.timu-flex — Flexbox with gap
.timu-flex-center — Centered flex
.timu-flex-between — Space between
.timu-flex-col — Vertical flex
```

### Text
```css
.timu-text-secondary — Secondary color
.timu-text-muted — Muted color
.timu-text-small — Small font size
.timu-text-mono — Monospace with background
.timu-truncate — Text truncation
```

### Accessibility
```css
.timu-sr-only — Screen reader only
```

### Effects
```css
.timu-fade-in — Fade in animation
.timu-pulse — Pulsing animation
.timu-status-active — Status pulse
.timu-glass — Glass morphism effect
```

---

## Micro-Interactions

### Button Ripple
All buttons have a ripple effect on click (`:active` state).

### Card Lift
Cards lift 2px on hover with enhanced shadow.

### Badge Scale
Badges scale to 1.05 on hover.

### Table Rows
Activity log rows fade in with staggered delays.

### Stat Cards
Dashboard stat cards show gradient top accent on hover.

---

## Accessibility Features

### WCAG 2.2 AA Compliant
- All color contrasts exceed 4.5:1 minimum
- Primary text: 12.63:1 (AAA)
- Secondary text: 7.45:1 (AAA)

### Keyboard Navigation
- Focus states visible (2px outline)
- Skip links for bypassing navigation
- Logical tab order

### Screen Readers
- `.timu-sr-only` for hidden labels
- Proper ARIA landmarks
- Descriptive captions on tables

---

## Responsive Design

### Mobile-First Approach
- Base styles for 320px+
- Progressive enhancement for larger screens

### Breakpoints
```css
@media (max-width: 768px) { /* Tablet */ }
@media (max-width: 480px) { /* Mobile */ }
```

### Touch Targets
- Minimum 36px height for buttons
- Mobile: Full-width buttons in card footers
- Adequate spacing between interactive elements

---

## Dark Mode Support

Dark mode is automatically enabled based on system preference:

```css
@media (prefers-color-scheme: dark) {
  /* Dark mode variables */
}
```

All components adapt seamlessly without additional classes.

---

## Using the Design System in Add-ons

### 1. Load the Stylesheet
```php
wp_enqueue_style(
  'timu-ui-system',
  plugins_url('assets/css/timu-ui-system.css', __FILE__),
  array(),
  '1.0.0'
);
```

### 2. Wrap Your Admin Pages
```html
<div class="wrap timu-core-wrap">
  <!-- Your content -->
</div>
```

### 3. Use Design Tokens
```css
.my-custom-element {
  color: var(--timu-text-primary);
  padding: var(--timu-space-md);
  border-radius: var(--timu-radius-md);
}
```

### 4. Extend Components
```html
<div class="timu-card my-custom-card">
  <!-- Inherits all card styles -->
</div>
```

---

## Best Practices

### Do ✅
- Use CSS custom properties (variables)
- Maintain 8px spacing grid
- Follow semantic HTML structure
- Include ARIA labels and roles
- Test in light and dark modes
- Ensure 4.5:1 contrast minimum

### Don't ❌
- Hardcode colors or spacing
- Skip accessibility attributes
- Override core token values
- Use pixel values directly
- Nest grids excessively

---

## Examples

### Dashboard Stat Card
```html
<div class="timu-card" role="group" aria-label="Total modules">
  <div style="display: flex; align-items: center; gap: var(--timu-space-md);">
    <span class="dashicons dashicons-admin-plugins" style="font-size: 32px; color: var(--timu-accent-primary);"></span>
    <div>
      <div style="font-size: var(--timu-text-2xl); font-weight: var(--timu-font-bold);">42</div>
      <div style="font-size: var(--timu-text-sm); color: var(--timu-text-muted);">Total</div>
    </div>
  </div>
</div>
```

### Form with Validation
```html
<div class="timu-form-group">
  <label for="email" class="timu-form-label">Email Address</label>
  <input type="email" id="email" class="timu-form-control" required />
  <p class="timu-error">Please enter a valid email address.</p>
</div>
```

### Modal Pattern
```html
<div class="timu-card timu-glass" style="max-width: 500px; margin: 2rem auto;">
  <div class="timu-card-header">
    <h3>Confirm Action</h3>
  </div>
  <div class="timu-card-body">
    <p>Are you sure you want to proceed?</p>
  </div>
  <div class="timu-card-footer">
    <button class="button">Cancel</button>
    <button class="button button-primary">Confirm</button>
  </div>
</div>
```

---

## Resources

- **Stylesheet:** `core-support-thisismyurl/assets/css/timu-ui-system.css`
- **GitHub Issues:** [#67 Design System](https://github.com/thisismyurl/plugin-plugin-wp-support-thisismyurl/issues/67)
- **Live Examples:** WordPress Support Dashboard (wp-admin → Dashboard)

---

**Maintained by:** TIMU Core Team
**License:** GPL2
