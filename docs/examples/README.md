# Design System Examples

This directory contains visual examples and showcases of the WPShadow design system components.

## Files

### `design-system-showcase.html`

A comprehensive visual showcase of all design system components. This file demonstrates:

- **Design Tokens**: Color palette and gray scale
- **Typography**: Heading hierarchy and text styles
- **Buttons**: All button variants, sizes, and states
- **Form Controls**: Inputs, toggles, sliders, selects, checkboxes
- **Cards**: Card layouts and variants
- **Tables**: Modern table styles (Stripe-inspired)
- **Alerts**: Success, warning, danger, and info notifications
- **Loading States**: Spinners, progress bars, and skeleton screens
- **Grid System**: Responsive grid layouts

## How to View

### Option 1: Direct Browser Access

1. Navigate to the file in your browser:
   ```
   file:///path/to/wpshadow/docs/examples/design-system-showcase.html
   ```

2. Or open it from your file system by double-clicking the HTML file

### Option 2: Local Development Server

If you have a local development server running:

```bash
# From the project root
cd docs/examples
python3 -m http.server 8000
```

Then visit: `http://localhost:8000/design-system-showcase.html`

### Option 3: WordPress Admin Context

To see the components in the actual WordPress admin context:

1. Copy this file to a WordPress installation
2. Create a custom admin page that includes the design-system.css
3. View the components with WordPress admin styles loaded

## Purpose

This showcase serves multiple purposes:

1. **Visual Reference**: Quick visual lookup for all available components
2. **Developer Guide**: See live examples of how to implement each component
3. **Testing**: Verify that design system changes work correctly
4. **Documentation**: Complement the written documentation in `UI_COMPONENTS.md`
5. **Stakeholder Reviews**: Share visual examples with designers and product managers

## Interactive Features

The showcase includes interactive elements:

- **Range Sliders**: Drag sliders to see value updates
- **Toggle Switches**: Click to toggle on/off states
- **Hover States**: Hover over buttons, cards, and table rows to see interactive feedback
- **Focus States**: Tab through elements to see keyboard focus indicators

## Accessibility Testing

Use this showcase to test accessibility:

1. **Keyboard Navigation**: Tab through all interactive elements
2. **Screen Reader**: Test with NVDA, JAWS, or VoiceOver
3. **Color Contrast**: Verify all text meets WCAG AA standards
4. **Zoom**: Test at 200% zoom level
5. **Mobile**: View on mobile devices to test responsive design

## Related Documentation

- [UI_COMPONENTS.md](../UI_COMPONENTS.md) - Complete component documentation
- [ACCESSIBILITY_AND_INCLUSIVITY_CANON.md](../ACCESSIBILITY_AND_INCLUSIVITY_CANON.md) - Accessibility guidelines
- [ASSETS_DEVELOPER_GUIDE.md](../ASSETS_DEVELOPER_GUIDE.md) - Asset usage guide
- [design-system.css](../../assets/css/design-system.css) - CSS source file

## Contributing

When adding new components to the design system:

1. Add the component styles to `design-system.css`
2. Document the component in `UI_COMPONENTS.md`
3. Add a visual example to this showcase file
4. Test for accessibility compliance
5. Update this README if needed

---

**Note**: This showcase uses CDN-hosted Dashicons for icons. In production WordPress environments, Dashicons are automatically available.
