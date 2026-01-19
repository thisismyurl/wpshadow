#!/bin/bash
# Generate sample feature logs for testing the Feature Log widget

echo "Generating sample feature logs..."

# Use WP-CLI if available, otherwise provide instructions
if command -v wp &> /dev/null; then
    wp eval-file test-feature-logs.php
    echo "✓ Sample logs generated successfully!"
    echo ""
    echo "View the logs at:"
    echo "  → /wp-admin/admin.php?page=wpshadow&wpshadow_tab=features&feature=external-fonts-disabler"
else
    echo "WP-CLI not found. To generate logs manually:"
    echo "1. Copy the code from test-feature-logs.php"
    echo "2. Run it in WordPress (via plugins, theme functions.php, or direct inclusion)"
    echo "3. Or visit: /wp-admin/admin.php?page=wpshadow-test-logs&generate=1"
fi
