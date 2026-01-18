#!/bin/bash

echo "================================"
echo "WPShadow Dark Mode Test Script"
echo "================================"
echo ""

# Check if files exist
echo "✓ Checking if dark mode files exist..."

if [ -f "/workspaces/wpshadow/features/class-wps-feature-dark-mode.php" ]; then
    echo "  ✓ Dark mode feature class found"
else
    echo "  ✗ Dark mode feature class NOT found"
fi

if [ -f "/workspaces/wpshadow/assets/css/dark-mode.css" ]; then
    echo "  ✓ Dark mode CSS found"
else
    echo "  ✗ Dark mode CSS NOT found"
fi

if [ -f "/workspaces/wpshadow/assets/js/dark-mode.js" ]; then
    echo "  ✓ Dark mode JavaScript found"
else
    echo "  ✗ Dark mode JavaScript NOT found"
fi

echo ""
echo "✓ Checking feature registration in wpshadow.php..."

# Check if feature is required
if grep -q "class-wps-feature-dark-mode.php" /workspaces/wpshadow/wpshadow.php; then
    echo "  ✓ Dark mode feature require_once found"
else
    echo "  ✗ Dark mode feature require_once NOT found"
fi

# Check if feature is registered
if grep -q "WPSHADOW_Feature_Dark_Mode" /workspaces/wpshadow/wpshadow.php; then
    echo "  ✓ Dark mode feature registration found"
else
    echo "  ✗ Dark mode feature registration NOT found"
fi

echo ""
echo "✓ Checking dashboard widget integration..."

# Check if widget is added to dashboard
if grep -q "widget_dark_mode" /workspaces/wpshadow/includes/class-wps-dashboard-widgets.php; then
    echo "  ✓ Dark mode widget method found"
else
    echo "  ✗ Dark mode widget method NOT found"
fi

echo ""
echo "================================"
echo "Dark Mode Implementation Summary"
echo "================================"
echo ""
echo "Files created:"
echo "  1. features/class-wps-feature-dark-mode.php (PHP feature class)"
echo "  2. assets/css/dark-mode.css (Dark mode CSS variables)"
echo "  3. assets/js/dark-mode.js (Client-side toggle logic)"
echo ""
echo "Integration points:"
echo "  1. Feature registered in wpshadow.php"
echo "  2. Dashboard widget added to class-wps-dashboard-widgets.php"
echo ""
echo "Features:"
echo "  • Auto-detection from WordPress color schemes"
echo "  • Manual toggle (Auto/Light/Dark modes)"
echo "  • Admin bar quick toggle"
echo "  • Dashboard widget with mode display"
echo "  • System preference detection"
echo ""
echo "Supported WordPress color schemes (dark):"
echo "  • Midnight"
echo "  • Ectoplasm"
echo "  • Coffee"
echo ""
echo "To test:"
echo "  1. Enable 'Dark Mode Support' feature in WPShadow Features page"
echo "  2. Visit WPShadow Dashboard to see the widget"
echo "  3. Click toggle button to cycle through modes"
echo "  4. Check admin bar for 🌙 / ☀️ toggle"
echo ""
