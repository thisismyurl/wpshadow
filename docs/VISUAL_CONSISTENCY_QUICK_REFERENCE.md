# WPShadow Visual Consistency - Quick Reference

## Color Palette

### Primary Brand
- `var(--wps-primary)` = #123456 (WPShadow branded blue)
- `.wps-icon-primary` or `.wps-text-primary`

### Semantic Colors
```
Success:  var(--wps-success) = #10b981    (.wps-text-success, .wps-icon-success)
Warning:  var(--wps-warning) = #f59e0b    (.wps-text-warning, .wps-icon-warning)
Danger:   var(--wps-danger)   = #ef4444   (.wps-text-danger, .wps-icon-danger)
Info:     var(--wps-info)     = #3b82f6   (.wps-text-info, .wps-icon-info)
```

### Gray Scale (10 levels)
```
Gray-50   → Gray-100 → Gray-200 → Gray-300 → Gray-400 → Gray-500
(lightest)                                               (neutral)

Gray-600 → Gray-700 → Gray-800 → Gray-900
(neutral)                        (darkest)
```

**Common Mappings:**
- Text on light backgrounds: `.wps-text-gray-900` or `.wps-text-gray-800`
- Muted text: `.wps-text-gray-600` or `.wps-text-gray-500`
- Helper text: `.wps-text-gray-500` or `.wps-text-gray-400`
- Borders: `var(--wps-gray-200)` or `var(--wps-gray-300)`
- Backgrounds: `var(--wps-gray-50)` or `var(--wps-gray-100)`

---

## Spacing Scale

```
1   → 4px   (.wps-m-1, .wps-p-1, .wps-gap-1)
2   → 8px   (.wps-m-2, .wps-p-2, .wps-gap-2)
3   → 12px  (.wps-m-3, .wps-p-3, .wps-gap-3)
4   → 16px  (.wps-m-4, .wps-p-4, .wps-gap-4)
5   → 20px  (.wps-m-5, .wps-p-5, .wps-gap-5)
6   → 24px  (.wps-m-6, .wps-p-6, .wps-gap-6)
8   → 32px  (.wps-m-8, .wps-p-8, .wps-gap-8)
10  → 40px  (.wps-m-10, .wps-p-10, .wps-gap-10)
```

**Directional Variants:** `.wps-mt-*`, `.wps-mr-*`, `.wps-mb-*`, `.wps-ml-*`, `.wps-pt-*`, `.wps-pr-*`, `.wps-pb-*`, `.wps-pl-*`

---

## Typography

### Font Sizes
```
xs    → 12px  (.wps-text-xs)
sm    → 14px  (.wps-text-sm)
base  → 16px  (.wps-text-base)
lg    → 18px  (.wps-text-lg)
xl    → 20px  (.wps-text-xl)
2xl   → 24px  (.wps-text-2xl)
3xl   → 30px  (.wps-text-3xl)
4xl   → 36px  (.wps-text-4xl)
```

### Font Weights
```
normal      → 400  (.wps-font-normal)
medium      → 500  (.wps-font-medium)
semibold    → 600  (.wps-font-semibold)
bold        → 700  (.wps-font-bold)
```

### Text Styling
```
Uppercase:    .wps-uppercase
Lowercase:    .wps-lowercase
Capitalized:  .wps-capitalize
Tracking:     .wps-tracking-tight, .wps-tracking-normal, .wps-tracking-wide
Line Height:  .wps-leading-tight (1.3), .wps-leading-normal (1.5), .wps-leading-relaxed (1.6)
```

---

## Icons

### Icon Sizes
```
xs   → 12px  (.wps-icon-xs)
sm   → 16px  (.wps-icon-sm)
md   → 24px  (.wps-icon-md)
lg   → 32px  (.wps-icon-lg)
xl   → 40px  (.wps-icon-xl)
```

### Icon Colors
```
.wps-icon-primary   (use --wps-primary color)
.wps-icon-success   (use --wps-success color)
.wps-icon-warning   (use --wps-warning color)
.wps-icon-danger    (use --wps-danger color)
.wps-icon-info      (use --wps-info color)
.wps-icon-gray-400  (use --wps-gray-400 color)
.wps-icon-gray-500  (use --wps-gray-500 color)
.wps-icon-white     (use white color)
```

### Icon Spacing
```
Margin Right:  .wps-icon-mr-1, .wps-icon-mr-2, .wps-icon-mr-3, .wps-icon-mr-4
Margin Bottom: .wps-icon-mb-1, .wps-icon-mb-2, .wps-icon-mb-3
Margin Top:    .wps-icon-mt-1, .wps-icon-mt-2
```

---

## Components

### Buttons

```php
// Primary action
<button class="wps-btn wps-btn-primary">Action</button>

// Secondary action
<button class="wps-btn wps-btn-secondary">Action</button>

// Success action
<button class="wps-btn wps-btn-success">Complete</button>

// Destructive action
<button class="wps-btn wps-btn-danger">Delete</button>

// Ghost/minimal
<button class="wps-btn wps-btn-ghost">Cancel</button>

// Sizes
<button class="wps-btn wps-btn-primary wps-btn-sm">Small</button>
<button class="wps-btn wps-btn-primary">Normal</button>
<button class="wps-btn wps-btn-primary wps-btn-lg">Large</button>

// With icon
<button class="wps-btn wps-btn-primary">
  <span class="dashicons dashicons-check wps-icon-md"></span>
  Save Changes
</button>
```

### Cards

```php
// Basic card
<div class="wps-card">
  <div class="wps-card-header">
    <h3 class="wps-card-title">
      <span class="dashicons dashicons-shield-alt wps-icon-primary"></span>
      Card Title
    </h3>
  </div>
  <div class="wps-card-body">
    <p>Card content goes here</p>
  </div>
  <div class="wps-card-footer">
    <button class="wps-btn wps-btn-primary wps-btn-sm">Action</button>
  </div>
</div>

// Interactive card (with hover effect)
<div class="wps-card wps-card-interactive">
  <!-- content -->
</div>

// Card spacing utilities
<div class="wps-card wps-mb-4"><!-- removes default bottom margin --></div>
<div class="wps-card wps-mt-4"><!-- adds top margin --></div>
```

### Flexbox Utilities

```php
// Center content
<div class="wps-flex-center">
  <span class="dashicons dashicons-check wps-icon-primary wps-icon-lg"></span>
</div>

// Space between (header layout)
<div class="wps-flex-between">
  <h3>Title</h3>
  <button class="wps-btn wps-btn-sm">Action</button>
</div>

// Gap between items
<div class="wps-flex wps-gap-4">
  <div>Item 1</div>
  <div>Item 2</div>
</div>

// Vertical layout
<div class="wps-flex wps-flex-col wps-gap-3">
  <label>Field Label</label>
  <input type="text" />
</div>

// Align items
<div class="wps-flex wps-items-center wps-gap-2">
  <span class="dashicons dashicons-info"></span>
  <p>Information text</p>
</div>
```

---

## ❌ DON'T DO THIS

### ❌ Inline styles for spacing
```php
<div style="margin: 16px 0; padding: 12px;">
```
**✅ Instead:**
```php
<div class="wps-my-4 wps-p-3">
```

### ❌ Hardcoded hex colors
```php
<span style="color: #0073aa;">Text</span>
<span style="color: #666;">Text</span>
```
**✅ Instead:**
```php
<span class="wps-text-info">Text</span>
<span class="wps-text-gray-500">Text</span>
```

### ❌ Font size with inline style
```php
<h3 style="font-size: 24px;">Heading</h3>
```
**✅ Instead:**
```php
<h3 class="wps-text-2xl">Heading</h3>
```

### ❌ Icon sizing inline
```php
<span class="dashicons dashicons-check" style="font-size: 32px; width: 32px; height: 32px;"></span>
```
**✅ Instead:**
```php
<span class="dashicons dashicons-check wps-icon-lg"></span>
```

### ❌ Multiple inline style attributes
```php
<div style="font-weight: 600; font-size: 14px; color: #333; margin: 0 0 12px 0;">Text</div>
```
**✅ Instead:**
```php
<div class="wps-font-semibold wps-text-sm wps-text-gray-700 wps-mb-3">Text</div>
```

### ❌ Double class attributes
```php
<div class="wps-card-body" class="wps-flex-gap-16-items-center">
```
**✅ Instead:**
```php
<div class="wps-card-body wps-flex-gap-16-items-center">
```

---

## ✅ DO THIS

### ✅ Use utility classes
```php
<div class="wps-p-4 wps-mb-6 wps-bg-white wps-rounded">
  <h3 class="wps-text-lg wps-font-bold wps-text-gray-900 wps-mb-2">
    <span class="dashicons dashicons-check wps-icon-primary wps-icon-mr-2"></span>
    Title
  </h3>
  <p class="wps-text-sm wps-text-gray-600 wps-leading-relaxed">
    Description text with consistent spacing and styling.
  </p>
</div>
```

### ✅ Use CSS variables for dynamic values
```php
// When you need dynamic color based on PHP logic
<span class="dashicons dashicons-check" style="color: <?php echo esc_attr( $status_color ); ?>; "></span>
// ^ Only inline style for dynamic value, size uses class
<span class="dashicons dashicons-check wps-icon-md" style="color: <?php echo esc_attr( $status_color ); ?>; "></span>
```

### ✅ Combine utility classes for complex layouts
```php
<div class="wps-flex wps-gap-4 wps-items-start">
  <div class="wps-flex-shrink-0 wps-w-10 wps-h-10 wps-flex-center wps-bg-blue-100 wps-rounded">
    <span class="dashicons dashicons-shield-alt wps-icon-primary"></span>
  </div>
  <div class="wps-flex-1">
    <h4 class="wps-text-base wps-font-semibold wps-text-gray-900 wps-mb-1">
      Item Title
    </h4>
    <p class="wps-text-sm wps-text-gray-600">
      Item description
    </p>
  </div>
</div>
```

---

## Common Patterns

### Alert/Notice Box
```php
<div class="wps-bg-warning-light wps-border-l-4 wps-border-warning wps-p-4 wps-rounded">
  <div class="wps-flex wps-gap-3">
    <span class="dashicons dashicons-warning wps-icon-warning wps-icon-lg wps-flex-shrink-0 wps-mt-1"></span>
    <div>
      <h4 class="wps-text-base wps-font-semibold wps-text-warning wps-mb-1">Warning Title</h4>
      <p class="wps-text-sm wps-text-gray-700">Warning message text</p>
    </div>
  </div>
</div>
```

### Success Message
```php
<div class="wps-bg-success-light wps-border-l-4 wps-border-success wps-p-4 wps-rounded">
  <div class="wps-flex wps-gap-3 wps-items-center">
    <span class="dashicons dashicons-yes-alt wps-icon-success wps-icon-lg"></span>
    <p class="wps-text-sm wps-text-gray-800">Operation completed successfully!</p>
  </div>
</div>
```

### Status Badge
```php
<span class="wps-inline-block wps-px-3 wps-py-1 wps-bg-primary-light wps-text-primary wps-text-xs wps-font-semibold wps-rounded">
  Active
</span>
```

### Stat Card
```php
<div class="wps-card">
  <div class="wps-mb-2">
    <div class="wps-text-xs wps-text-gray-500 wps-uppercase wps-tracking-wide wps-font-semibold">
      Total Time Saved
    </div>
  </div>
  <div class="wps-text-4xl wps-font-bold wps-text-gray-900 wps-mb-1">
    240 hrs
  </div>
  <div class="wps-text-xs wps-text-gray-600">
    ↑ 12% from last month
  </div>
</div>
```

---

## Testing Checklist

Before committing code with visual changes:

- [ ] Visual looks consistent with design system
- [ ] No inline style= attributes (except necessary dynamic values)
- [ ] All colors use CSS variables
- [ ] Spacing follows scale (no random pixels)
- [ ] Icons use utility classes for size/color
- [ ] Test in light mode
- [ ] Test in dark mode
- [ ] Test on mobile (if responsive)
- [ ] No double class attributes
- [ ] No deprecated CSS classes

---

## Resources

- **Full Design System:** [assets/css/design-system.css](../../assets/css/design-system.css)
- **Audit Report:** [docs/VISUAL_CONSISTENCY_AUDIT.md](../../docs/VISUAL_CONSISTENCY_AUDIT.md)
- **Remediation Roadmap:** [docs/VISUAL_CONSISTENCY_REMEDIATION_ROADMAP.md](../../docs/VISUAL_CONSISTENCY_REMEDIATION_ROADMAP.md)
- **Component Guide:** [docs/VISUAL_COMPONENT_GUIDE.md](../../docs/VISUAL_COMPONENT_GUIDE.md) (coming after Phase 3)

---

**Version:** 1.0
**Last Updated:** January 23, 2026
**Next Update:** After Phase 1 implementation

