#!/bin/bash
# Convert diagnostic files in treatments directory to proper treatments
# These files have Treatment class names but still contain diagnostic code

set -e

DESIGN_DIR="/workspaces/wpshadow/includes/treatments/design"
LOG_FILE="/tmp/treatment-conversion.log"

echo "🔧 Converting diagnostic files to proper treatments..."
echo "Started: $(date)" > "$LOG_FILE"

# Get list of files that still have check() method
files_to_convert=$(grep -l "public static function check()" "$DESIGN_DIR"/*.php)

count=0
total=$(echo "$files_to_convert" | wc -l)

for file in $files_to_convert; do
    count=$((count + 1))
    basename_file=$(basename "$file")

    echo "[$count/$total] Processing: $basename_file"

    # Extract slug from check() method return array
    slug=$(grep -A 50 "public static function check()" "$file" | grep -m 1 "'id'" | sed -E "s/.*'id'[[:space:]]*=>[[:space:]]*self::\\\$slug,//" | sed -E "s/.*'id'[[:space:]]*=>[[:space:]]*'([^']+)'.*/\1/" || echo "")

    if [ -z "$slug" ]; then
        # Try to extract from protected static $slug
        slug=$(grep "protected static \$slug = " "$file" | sed -E "s/.*'\([^']+\)'.*/\1/" || echo "unknown-$(basename "$file" .php)")
    fi

    echo "  Slug: $slug" >> "$LOG_FILE"
    echo "  ✓ Identified: $slug"
done

echo ""
echo "✅ Analysis complete! Check $LOG_FILE for details"
echo "Total files needing conversion: $total"
