# WPShadow UI Migration Checklist

**Purpose:** Quick reference for migrating pages to the new design system  
**Version:** 1.0  
**Last Updated:** January 25, 2026

---

## 📋 Pre-Migration Checklist

Before starting UI migration on any page:

- [ ] Read [DESIGN_SYSTEM_COMPONENTS.md](DESIGN_SYSTEM_COMPONENTS.md)
- [ ] Review [PRODUCT_PHILOSOPHY.md](PRODUCT_PHILOSOPHY.md) (11 Commandments)
- [ ] Review [ACCESSIBILITY_AND_INCLUSIVITY_CANON.md](ACCESSIBILITY_AND_INCLUSIVITY_CANON.md)
- [ ] Ensure `assets/css/design-system.css` is enqueued
- [ ] Test with keyboard navigation only
- [ ] Have screen reader available (NVDA/JAWS/VoiceOver)

---

## 🔄 Common Migration Patterns

### Pattern 1: Replace Form Tables with Cards

**Old Code:**
```php
<table class="form-table">
    <tr>
        <th scope="row">
            <label for="setting_name"><?php esc_html_e( 'Setting Name', 'wpshadow' ); ?></label>
        </th>
        <td>
            <input type="text" id="setting_name" name="setting_name" value="<?php echo esc_attr( $value ); ?>" />
            <p class="description"><?php esc_html_e( 'Help text here', 'wpshadow' ); ?></p>
        </td>
    </tr>
</table>
```

**New Code:**
```php
<div class="wps-card">
    <div class="wps-card-header">
        <h3 class="wps-card-title"><?php esc_html_e( 'Section Title', 'wpshadow' ); ?></h3>
    </div>
    <div class="wps-card-body">
        <div class="wps-form-group">
            <label for="setting_name" class="wps-form-label">
                <?php esc_html_e( 'Setting Name', 'wpshadow' ); ?>
            </label>
            <input 
                type="text" 
                id="setting_name" 
                name="setting_name" 
                class="wps-input" 
                value="<?php echo esc_attr( $value ); ?>"
                aria-describedby="setting_name_help"
            />
            <p id="setting_name_help" class="wps-form-help">
                <?php esc_html_e( 'Help text here', 'wpshadow' ); ?>
            </p>
        </div>
    </div>
</div>
```

**Checklist:**
- [ ] Removed `<table class="form-table">`
- [ ] Added `.wps-card` wrapper
- [ ] Added `.wps-card-header` with title
- [ ] Wrapped form fields in `.wps-form-group`
- [ ] Added `.wps-form-label` class to labels
- [ ] Added `.wps-input` class to inputs
- [ ] Changed `.description` to `.wps-form-help`
- [ ] Added `aria-describedby` linking help text

---

### Pattern 2: Replace Checkboxes with Toggle Switches

**Old Code:**
```php
<input type="checkbox" id="enable_feature" name="enable_feature" <?php checked( $enabled ); ?> />
<label for="enable_feature"><?php esc_html_e( 'Enable Feature', 'wpshadow' ); ?></label>
```

**New Code:**
```php
<div class="wps-toggle-wrapper">
    <label class="wps-toggle" for="enable_feature">
        <input type="checkbox" id="enable_feature" name="enable_feature" <?php checked( $enabled ); ?> />
        <span class="wps-toggle-slider"></span>
        <span class="screen-reader-text"><?php esc_html_e( 'Enable Feature', 'wpshadow' ); ?></span>
    </label>
    <label for="enable_feature" class="wps-toggle-label">
        <?php esc_html_e( 'Enable Feature', 'wpshadow' ); ?>
    </label>
</div>
```

**Checklist:**
- [ ] Wrapped in `.wps-toggle-wrapper`
- [ ] Moved label to `.wps-toggle` wrapper
- [ ] Added `.wps-toggle-slider` span
- [ ] Added screen-reader-text for accessibility
- [ ] Added visible label with `.wps-toggle-label`
- [ ] Tested keyboard navigation (Space to toggle)
- [ ] Tested with screen reader

---

### Pattern 3: Standardize Buttons

**Old Code:**
```php
<input type="submit" class="button button-primary" value="<?php esc_attr_e( 'Save Changes', 'wpshadow' ); ?>" />
<a href="#" class="button">Cancel</a>
```

**New Code:**
```php
<button type="submit" class="wps-btn wps-btn-primary">
    <span class="dashicons dashicons-saved"></span>
    <?php esc_html_e( 'Save Changes', 'wpshadow' ); ?>
</button>
<a href="#" class="wps-btn wps-btn-secondary">
    <?php esc_html_e( 'Cancel', 'wpshadow' ); ?>
</a>
```

**Checklist:**
- [ ] Changed `button button-primary` to `wps-btn wps-btn-primary`
- [ ] Changed `<input type="submit">` to `<button type="submit">`
- [ ] Added icons using Dashicons (left-aligned)
- [ ] Used semantic button hierarchy (primary, secondary, danger)
- [ ] Ensured all buttons have visible labels
- [ ] Added `aria-label` for icon-only buttons

---

### Pattern 4: Modernize Select Dropdowns

**Old Code:**
```php
<select name="frequency">
    <option value="daily"><?php esc_html_e( 'Daily', 'wpshadow' ); ?></option>
    <option value="weekly"><?php esc_html_e( 'Weekly', 'wpshadow' ); ?></option>
</select>
```

**New Code:**
```php
<div class="wps-form-group">
    <label for="frequency" class="wps-form-label">
        <?php esc_html_e( 'Scan Frequency', 'wpshadow' ); ?>
    </label>
    <select id="frequency" name="frequency" class="wps-select">
        <option value="daily"><?php esc_html_e( 'Daily', 'wpshadow' ); ?></option>
        <option value="weekly"><?php esc_html_e( 'Weekly', 'wpshadow' ); ?></option>
    </select>
</div>
```

**Checklist:**
- [ ] Added `.wps-form-group` wrapper
- [ ] Added label with `.wps-form-label`
- [ ] Added `.wps-select` class to select element
- [ ] Added unique `id` attribute
- [ ] Linked label to select using `for` attribute

---

### Pattern 5: Update Page Headers

**Old Code:**
```php
<div class="wrap">
    <h1><?php esc_html_e( 'Page Title', 'wpshadow' ); ?></h1>
</div>
```

**New Code:**
```php
<div class="wrap wps-page-container">
    <div class="wps-page-header">
        <h1 class="wps-page-title">
            <span class="dashicons dashicons-admin-settings"></span>
            <?php esc_html_e( 'Page Title', 'wpshadow' ); ?>
        </h1>
        <p class="wps-page-subtitle">
            <?php esc_html_e( 'Descriptive subtitle explaining the page purpose', 'wpshadow' ); ?>
        </p>
    </div>
    <!-- Page content -->
</div>
```

**Checklist:**
- [ ] Added `.wps-page-container` to `.wrap`
- [ ] Added `.wps-page-header` wrapper
- [ ] Added `.wps-page-title` to `<h1>`
- [ ] Added icon using Dashicons
- [ ] Added `.wps-page-subtitle` with description
- [ ] Ensured proper heading hierarchy

---

### Pattern 6: Add Alerts/Notifications

**Old Code:**
```php
<div class="notice notice-success">
    <p><?php esc_html_e( 'Settings saved.', 'wpshadow' ); ?></p>
</div>
```

**New Code:**
```php
<div class="wps-alert wps-alert-success" role="alert">
    <strong><?php esc_html_e( 'Success!', 'wpshadow' ); ?></strong>
    <?php esc_html_e( 'Your settings have been saved successfully.', 'wpshadow' ); ?>
</div>
```

**Checklist:**
- [ ] Changed `notice` classes to `wps-alert`
- [ ] Added `role="alert"` for screen readers
- [ ] Used strong tag for emphasis
- [ ] Provided clear, actionable message
- [ ] Used appropriate semantic color (success/warning/danger/info)

---

### Pattern 7: Modernize Tables

**Old Code:**
```php
<table class="widefat">
    <thead>
        <tr>
            <th><?php esc_html_e( 'Name', 'wpshadow' ); ?></th>
            <th><?php esc_html_e( 'Status', 'wpshadow' ); ?></th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Item Name</td>
            <td>Active</td>
        </tr>
    </tbody>
</table>
```

**New Code:**
```php
<div class="wps-table-container">
    <table class="wps-table wps-table-striped">
        <thead>
            <tr>
                <th><?php esc_html_e( 'Name', 'wpshadow' ); ?></th>
                <th><?php esc_html_e( 'Status', 'wpshadow' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Item Name</td>
                <td>
                    <span class="wps-badge wps-badge-success">
                        <?php esc_html_e( 'Active', 'wpshadow' ); ?>
                    </span>
                </td>
            </tr>
        </tbody>
    </table>
</div>
```

**Checklist:**
- [ ] Wrapped table in `.wps-table-container`
- [ ] Changed `widefat` to `wps-table`
- [ ] Added `.wps-table-striped` for alternating rows
- [ ] Used badges for status indicators
- [ ] Ensured table is responsive (horizontal scroll on mobile)

---

## ♿ Accessibility Testing Checklist

After migrating each page, verify:

### Keyboard Navigation
- [ ] Tab through all interactive elements in logical order
- [ ] All buttons/links are reachable with Tab key
- [ ] Enter/Space activates buttons
- [ ] Escape closes modals/dropdowns
- [ ] No keyboard traps

### Screen Reader Testing
- [ ] All images have alt text
- [ ] All form inputs have labels
- [ ] Icon-only buttons have aria-label
- [ ] Dynamic content uses aria-live
- [ ] Page structure is logical (headings, landmarks)

### Visual Testing
- [ ] Focus indicators are visible (min 2px outline)
- [ ] Color contrast meets WCAG AA (4.5:1 for text)
- [ ] Text is readable at 200% zoom
- [ ] No information conveyed by color alone
- [ ] Touch targets are min 44x44px on mobile

### Testing Tools
- [ ] Run Lighthouse accessibility audit (score 90+)
- [ ] Run axe DevTools scan (0 violations)
- [ ] Test with NVDA/JAWS/VoiceOver
- [ ] Test with keyboard only (no mouse)

---

## 📱 Responsive Design Checklist

Test at these breakpoints:

### Desktop (1920px, 1440px, 1280px)
- [ ] All content visible without horizontal scroll
- [ ] Cards and grids display properly
- [ ] Spacing is consistent

### Tablet (768px, 1024px)
- [ ] Multi-column layouts stack appropriately
- [ ] Touch targets are 44x44px minimum
- [ ] Navigation is accessible

### Mobile (375px, 414px)
- [ ] Single-column layout
- [ ] Touch-friendly buttons
- [ ] Forms are easy to fill
- [ ] No horizontal scroll

---

## 🧪 Browser Testing Checklist

Test on these browsers:

- [ ] Chrome (latest)
- [ ] Firefox (latest)
- [ ] Safari (latest)
- [ ] Edge (latest)

**Common Issues to Check:**
- [ ] CSS Grid/Flexbox support
- [ ] Custom scrollbars
- [ ] Focus indicators
- [ ] CSS variables support

---

## 🎨 Visual Polish Checklist

Before marking a page as complete:

- [ ] All spacing uses design system variables
- [ ] Colors use design system variables
- [ ] Shadows use design system variables
- [ ] No inline styles in PHP
- [ ] Consistent border-radius
- [ ] Hover states on interactive elements
- [ ] Loading states for async actions
- [ ] Empty states for no data

---

## 📝 Code Quality Checklist

- [ ] All strings use text domain `'wpshadow'`
- [ ] All user input is escaped (`esc_html`, `esc_attr`, `esc_url`)
- [ ] All output is escaped
- [ ] No PHP notices/warnings
- [ ] Passes PHPCS (WordPress-Extra standard)
- [ ] No JavaScript console errors
- [ ] CSS is organized and commented

---

## 📊 Performance Checklist

- [ ] CSS file size is reasonable
- [ ] No unused CSS classes
- [ ] Images are optimized
- [ ] No layout shifts on load
- [ ] Animations use CSS transforms (not position)
- [ ] Debounced/throttled event handlers

---

## 📚 Documentation Checklist

After completing migration:

- [ ] Update page-specific documentation
- [ ] Add screenshots to changelog
- [ ] Update KB article if applicable
- [ ] Add migration notes for developers
- [ ] Update component usage examples

---

## 🚀 Deployment Checklist

Before merging:

- [ ] All checklists above completed
- [ ] Code reviewed by another developer
- [ ] Tested in staging environment
- [ ] Security scan passed (CodeQL)
- [ ] No regressions in existing functionality
- [ ] Changelog updated
- [ ] Version number bumped

---

## 📖 Page-Specific Migration Status

Track which pages have been migrated:

### High Priority
- [ ] Dashboard (`includes/views/dashboard/`)
- [ ] Guardian Settings (`includes/screens/class-guardian-settings.php`)
- [ ] Tools Page (`includes/views/tools/`)

### Medium Priority
- [ ] Kanban Board (`includes/kanban/`)
- [ ] Workflows (`includes/workflow/`)
- [ ] Reports (`includes/reporting/`)

### Lower Priority
- [ ] Onboarding Wizard (`includes/views/onboarding/`)
- [ ] Privacy Settings (`includes/screens/class-privacy-page-module.php`)
- [ ] Help Page (`includes/screens/class-help-page-module.php`)
- [ ] Settings Page (general)

---

## 🆘 Getting Help

If you encounter issues during migration:

1. Check [DESIGN_SYSTEM_COMPONENTS.md](DESIGN_SYSTEM_COMPONENTS.md) for examples
2. Review [ACCESSIBILITY_AND_INCLUSIVITY_CANON.md](ACCESSIBILITY_AND_INCLUSIVITY_CANON.md)
3. Search GitHub issues for similar problems
4. Create new issue with `ui-consistency` label

---

**Last Updated:** January 25, 2026  
**Version:** 1.0  
**Maintainer:** WPShadow Core Team
