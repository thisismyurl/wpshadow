#!/bin/bash

# Batch enhance all security diagnostics with Upgrade_Path_Helper import
# This script adds the import statement to all unenhanc files

set -e

DIAGNOSTICS_DIR="/workspaces/wpshadow/includes/diagnostics/tests/security"
COUNT=0
SKIPPED=0

echo "🔍 Finding diagnostic files needing Upgrade_Path_Helper import..."
echo ""

# Find all diagnostic files without the import
FILES=$(grep -L "Upgrade_Path_Helper" "$DIAGNOSTICS_DIR"/class-diagnostic-*.php 2>/dev/null || true)

if [ -z "$FILES" ]; then
    echo "✓ All files already have Upgrade_Path_Helper import!"
    exit 0
fi

for FILE in $FILES; do
    FILENAME=$(basename "$FILE")

    # Skip if already has import (double check)
    if grep -q "use WPShadow\\\\Core\\\\Upgrade_Path_Helper;" "$FILE"; then
        SKIPPED=$((SKIPPED + 1))
        continue
    fi

    # Add import after Diagnostic_Base use statement
    if grep -q "use WPShadow\\\\Core\\\\Diagnostic_Base;" "$FILE"; then
        sed -i '/use WPShadow\\Core\\Diagnostic_Base;/a use WPShadow\\Core\\Upgrade_Path_Helper;' "$FILE"
        COUNT=$((COUNT + 1))
        echo "✓ Enhanced: $FILENAME"

        # Show progress every 10 files
        if [ $((COUNT % 10)) -eq 0 ]; then
            echo "  ... (processed $COUNT files)"
        fi
    else
        SKIPPED=$((SKIPPED + 1))
        echo "⊘ Skipped: $FILENAME (no Diagnostic_Base import found)"
    fi
done

echo ""
echo "Summary:"
echo "  ✓ Enhanced: $COUNT files"
echo "  ⊘ Skipped: $SKIPPED files"
echo "  Total enhanced so far: $(grep -l "Upgrade_Path_Helper" "$DIAGNOSTICS_DIR"/class-diagnostic-*.php 2>/dev/null | wc -l) / 327"
