# Responsive Design System

This document describes the responsive design system implemented in the WordPress Support plugin.

## Overview

The plugin uses a **mobile-first approach** with comprehensive breakpoint coverage to ensure all admin interfaces work seamlessly across mobile, tablet, and desktop devices.

## Breakpoints

The system uses four primary breakpoints as specified in the requirements:

| Breakpoint | Width | Target Devices |
|------------|-------|----------------|
| `xs` | 320px+ | Mobile phones (portrait) |
| `sm` | 640px+ | Large phones, small tablets |
| `md` | 1024px+ | Tablets, small laptops |
| `lg` | 1280px+ | Desktops, large screens |

### CSS Custom Properties

```css
--wps-breakpoint-xs: 320px;
--wps-breakpoint-sm: 640px;
--wps-breakpoint-md: 1024px;
--wps-breakpoint-lg: 1280px;
```

## Mobile-First Approach

All styles are written **mobile-first**, meaning:

1. Base styles apply to the smallest screens (320px+)
2. Media queries progressively enhance for larger screens
3. No desktop-specific styles cascade down to mobile

### Example

```css
/* Base: Mobile (320px+) */
.wps-grid {
    grid-template-columns: 1fr;
    gap: 16px;
}

/* Small devices (640px+) */
@media (min-width: 640px) {
    .wps-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

/* Medium devices (1024px+) */
@media (min-width: 1024px) {
    .wps-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}
```

## Touch Targets

All interactive elements meet or exceed the **48px minimum touch target** requirement:

- Buttons: `min-height: 48px; min-width: 48px;`
- Form inputs: `min-height: 48px;`
- Links: `padding: 12px 20px;` (effective height ~48px)
- Adequate spacing: `12px` minimum between interactive elements

### Touch-Friendly Forms

Form inputs use `font-size: 16px` to prevent iOS zoom on focus:

```css
input[type="text"],
input[type="email"],
select,
textarea {
    min-height: 48px;
    font-size: 16px; /* Prevents iOS zoom */
    padding: 12px 16px;
}
```

## Mobile Navigation

### Hamburger Menu

On screens below 1024px, a hamburger menu button appears in the top-right corner:

- **Fixed position** at `top: 32px, right: 16px`
- **Touch-friendly** 48x48px button
- **Accessible** with ARIA labels
- **Animated** slide-in drawer from the right

### Features

1. **Navigation Drawer**
   - Slides in from right side
   - 280px wide (max 85% viewport)
   - Overlay backdrop with semi-transparent background
   - Touch-scrollable content

2. **Keyboard Accessible**
   - ESC key closes drawer
   - Focus management (first link focused on open)
   - Proper ARIA attributes

3. **Body Scroll Lock**
   - Prevents background scrolling when drawer is open
   - Restored on close

### JavaScript API

The mobile navigation is handled by `responsive-nav.js`:

```javascript
// Functions available
- initResponsiveNav()    // Initialize navigation
- openMobileNav()        // Open drawer
- closeMobileNav()       // Close drawer
- toggleMobileNav()      // Toggle drawer state
- createMobileNav()      // Create nav elements if missing
```

## Responsive Layouts

### Grids

Grids automatically adapt to screen size:

```css
/* Mobile: 1 column */
.wps-grid { grid-template-columns: 1fr; }

/* Tablet (640px+): 2 columns */
@media (min-width: 640px) {
    .wps-grid { grid-template-columns: repeat(2, 1fr); }
}

/* Desktop (1024px+): 3 columns */
@media (min-width: 1024px) {
    .wps-grid { grid-template-columns: repeat(3, 1fr); }
}
```

### Flexbox

Container elements use flexbox for flexible stacking:

```css
/* Mobile: Stack vertically */
.wps-card-footer {
    flex-direction: column;
}

/* Tablet+: Horizontal layout */
@media (min-width: 640px) {
    .wps-card-footer {
        flex-direction: row;
    }
}
```

### Tables

Tables are responsive with two strategies:

1. **Horizontal Scroll** (default)
   ```css
   .wps-table-responsive {
       overflow-x: auto;
       -webkit-overflow-scrolling: touch;
   }
   ```

2. **Card-Based Layout** (optional, for very narrow screens)
   ```html
   <div class="wps-table-responsive">
       <table class="wps-table wps-table-mobile-cards">
           <!-- table content -->
       </table>
   </div>
   ```

## No Horizontal Scrolling

The system ensures no horizontal scrolling through:

1. **Box-sizing border-box** for all elements
2. **Max-width 100%** for images, videos, iframes
3. **Overflow-x auto** for wide content (code blocks, tables)
4. **Proper viewport meta** (handled by WordPress)

```css
* {
    box-sizing: border-box;
}

img, video, iframe {
    max-width: 100%;
    height: auto;
}
```

## Performance Optimizations

### Reduced Motion

Respects user preferences for reduced motion:

```css
@media (prefers-reduced-motion: reduce) {
    * {
        animation-duration: 0.01ms !important;
        transition-duration: 0.01ms !important;
    }
}
```

### Touch Device Detection

Optimizations for touch devices:

```css
@media (hover: none) and (pointer: coarse) {
    /* Remove hover effects */
    /* Increase touch targets */
    /* Remove subtle animations */
}
```

### Print Styles

Optimized for printing:

```css
@media print {
    .wps-mobile-nav-toggle,
    .wps-mobile-nav-drawer {
        display: none !important;
    }
}
```

## Utility Classes

### Visibility Control

```css
.wps-hide-mobile    /* Hide on mobile (< 640px) */
.wps-show-mobile    /* Show only on mobile */
.wps-hide-tablet    /* Hide on tablet (640px-1023px) */
.wps-hide-desktop   /* Hide on desktop (>= 1024px) */
```

### Example Usage

```html
<div class="wps-hide-mobile">
    <!-- Only visible on tablet and desktop -->
</div>

<div class="wps-show-mobile">
    <!-- Only visible on mobile -->
</div>
```

## Testing Checklist

- [ ] Test at 320px (iPhone SE)
- [ ] Test at 375px (iPhone 12)
- [ ] Test at 640px (Large phone)
- [ ] Test at 768px (iPad portrait)
- [ ] Test at 1024px (iPad landscape)
- [ ] Test at 1280px (Desktop)
- [ ] Test touch targets (all ≥ 48px)
- [ ] Test navigation on mobile
- [ ] Test form inputs (no zoom on iOS)
- [ ] Test landscape orientation
- [ ] Test with real touch devices
- [ ] Verify no horizontal scroll

## Browser Support

- Chrome/Edge (latest 2 versions)
- Firefox (latest 2 versions)
- Safari (latest 2 versions)
- iOS Safari 12+
- Chrome Android (latest)

## Files

| File | Purpose |
|------|---------|
| `assets/css/responsive.css` | Main responsive system |
| `assets/css/admin.css` | Admin page responsive enhancements |
| `assets/css/wps-ui-system.css` | UI system responsive breakpoints |
| `assets/css/tab-navigation.css` | Tab navigation responsive behavior |
| `assets/css/guided-tasks.css` | Guided tasks responsive layouts |
| `assets/css/dashboard-drag.css` | Dashboard metabox responsive handling |
| `assets/js/responsive-nav.js` | Mobile navigation JavaScript |

## Best Practices

### When Adding New Components

1. **Start with mobile** (320px) styles first
2. **Use rem/em** for scalable sizing
3. **Touch targets** must be ≥ 48px
4. **Test on real devices** when possible
5. **Use CSS custom properties** for consistency
6. **Avoid fixed widths** unless necessary
7. **Use flexbox/grid** for layouts
8. **Test in landscape** orientation

### Example Component

```css
/* Mobile-first component */
.wps-new-component {
    /* Base mobile styles (320px+) */
    display: flex;
    flex-direction: column;
    gap: 16px;
    padding: 16px;
}

.wps-new-component button {
    min-height: 48px;
    width: 100%;
}

/* Tablet+ (640px+) */
@media (min-width: 640px) {
    .wps-new-component {
        flex-direction: row;
        justify-content: space-between;
    }
    
    .wps-new-component button {
        width: auto;
    }
}

/* Desktop+ (1024px+) */
@media (min-width: 1024px) {
    .wps-new-component {
        padding: 24px;
        gap: 24px;
    }
}
```

## Changelog

### [1.2601.71920] - 2026-01-13
- Initial responsive design system implementation
- Mobile-first CSS architecture
- Breakpoint system (320px, 640px, 1024px, 1280px)
- 48px minimum touch targets
- Mobile navigation with hamburger menu
- Flexible layouts with proper stacking
- Performance optimizations
- Accessibility enhancements

## Support

For issues or questions about the responsive design system:
1. Check this documentation first
2. Test in browser DevTools with device emulation
3. Verify breakpoint values in CSS custom properties
4. Check console for JavaScript errors (mobile nav)

## Future Enhancements

- [ ] Progressive Web App (PWA) support
- [ ] Offline functionality
- [ ] Advanced touch gestures (swipe, pinch)
- [ ] Dynamic font scaling based on viewport
- [ ] Container queries (when widely supported)
