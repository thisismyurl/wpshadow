#!/bin/bash
# Diagnostic Verification Script
# Checks which GitHub issues have real implementations vs stubs

echo "=== WPShadow Diagnostic Implementation Verification ==="
echo "Date: $(date)"
echo ""

# Get all diagnostic files
TOTAL_FILES=$(find includes/diagnostics/tests -name "*.php" -type f | wc -l)
echo "Total diagnostic files: $TOTAL_FILES"

# Count stubs (files with TODO: Implement detection logic)
STUB_FILES=$(grep -r "TODO: Implement detection logic" includes/diagnostics/tests/ --include="*.php" -l | wc -l)
echo "Stub files: $STUB_FILES"

# Count production files
PRODUCTION_FILES=$((TOTAL_FILES - STUB_FILES))
echo "Production files: $PRODUCTION_FILES"
echo ""

# Get all stub file slugs
echo "=== Extracting stub file slugs ==="
grep -r "TODO: Implement detection logic" includes/diagnostics/tests/ --include="*.php" -l | while read file; do
    # Extract slug from protected static $slug = 'slug-name';
    slug=$(grep "protected static \$slug = " "$file" | head -1 | sed "s/.*= '//;s/';//" | tr -d ' ')
    if [ -n "$slug" ]; then
        echo "STUB: $slug"
    fi
done > /tmp/stub_slugs.txt

echo "Stub slugs extracted to /tmp/stub_slugs.txt"
echo ""

# Get all production file slugs
echo "=== Extracting production file slugs ==="
find includes/diagnostics/tests -name "*.php" -type f | while read file; do
    # Check if it's NOT a stub
    if ! grep -q "TODO: Implement detection logic" "$file"; then
        slug=$(grep "protected static \$slug = " "$file" | head -1 | sed "s/.*= '//;s/';//" | tr -d ' ')
        if [ -n "$slug" ]; then
            echo "PRODUCTION: $slug"
        fi
    fi
done > /tmp/production_slugs.txt

echo "Production slugs extracted to /tmp/production_slugs.txt"
echo ""

echo "=== Summary ==="
echo "Stub slugs: $(wc -l < /tmp/stub_slugs.txt)"
echo "Production slugs: $(wc -l < /tmp/production_slugs.txt)"
