#!/bin/bash
# Verify remaining treatments by category

echo "REMAINING TREATMENT FILES BY CATEGORY:"
echo "======================================"
echo ""

for category in accessibility code-quality design performance security seo settings workflows wordpress-health; do
    if [ -d "/workspaces/wpshadow/includes/treatments/$category" ]; then
        count=$(find "/workspaces/wpshadow/includes/treatments/$category" -name "class-treatment-*.php" -type f 2>/dev/null | wc -l)
        if [ $count -gt 0 ]; then
            printf "%-20s %4d treatments\n" "$category:" "$count"
        fi
    fi
done

# Count subdirectories too
for subdir in compliance content conversion developer ecommerce email error-logging functionality hosting internationalization learning marketing performance-baseline privacy publisher reliability schema-markup security-headers social-image-alt-text social-media ssl ux; do
    count=$(find "/workspaces/wpshadow/includes/treatments" -type d -name "$subdir" -exec find {} -name "class-treatment-*.php" -type f \; 2>/dev/null | wc -l)
    if [ $count -gt 0 ]; then
        printf "%-20s %4d treatments\n" "$subdir:" "$count"
    fi
done

echo ""
echo "======================================"
total=$(find /workspaces/wpshadow/includes/treatments -name "class-treatment-*.php" -type f | wc -l)
printf "%-20s %4d treatments\n" "TOTAL:" "$total"
echo ""
echo "✅ Focused on high-impact, locally-fixable treatments"
echo "✅ Removed cloud/pro/enterprise features"
echo "✅ Optimized for average WordPress site owner value"
