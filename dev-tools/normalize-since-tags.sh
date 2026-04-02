#!/bin/bash
#
# WPShadow @since Tag Normalization Script
#
# This script normalizes all non-conforming @since and @deprecated version tags
# to match the required format: 1.YDDD.HHMM
#
# Format: 1.{last_year_digit}{julian_day}.{hour}{minute}
# Example: 1.6035.0948 (Feb 4, 2026 at 09:48 Toronto time)
#
# Usage:
#   bash dev-tools/normalize-since-tags.sh           # Dry run (shows changes)
#   bash dev-tools/normalize-since-tags.sh --apply   # Apply changes
#
# Date: February 4, 2026
# Version: 1.6035.0948

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"

DRY_RUN=true
if [[ "$1" == "--apply" ]]; then
    DRY_RUN=false
fi

echo "=========================================="
echo "WPShadow @since Tag Normalization"
echo "=========================================="
echo ""
echo "Project root: $PROJECT_ROOT"
echo "Mode: $([ "$DRY_RUN" = true ] && echo "DRY RUN (preview only)" || echo "APPLY CHANGES")"
echo ""

# Backup function
backup_file() {
    local file="$1"
    if [ ! -f "${file}.bak" ]; then
        cp "$file" "${file}.bak"
        echo "  ✓ Backup created: ${file}.bak"
    fi
}

# Counter for changes
TOTAL_FILES=0
TOTAL_CHANGES=0

# Pattern transformations
# 1.602.XXXX -> 1.6020.XXXX (add missing zero in middle)
# 1.603.XXXX -> 1.6030.XXXX (add missing zero in middle)
# 1.6030.XX -> 1.6030.00XX (add leading zeros to timestamp)

echo "Pattern 1: Fixing 1.602.XXXX -> 1.6020.XXXX"
echo "-------------------------------------------"
FILES_602=$(grep -rl '@\(since\|deprecated\)\s\+1\.602\.\d\{4\}' "$PROJECT_ROOT/includes" 2>/dev/null || true)
if [ -n "$FILES_602" ]; then
    for file in $FILES_602; do
        if [ -f "$file" ]; then
            MATCHES=$(grep -c '@\(since\|deprecated\)\s\+1\.602\.' "$file" 2>/dev/null || echo "0")
            if [ "$MATCHES" -gt 0 ]; then
                echo "  File: ${file#$PROJECT_ROOT/}"
                echo "    Changes: $MATCHES occurrence(s)"
                TOTAL_FILES=$((TOTAL_FILES + 1))
                TOTAL_CHANGES=$((TOTAL_CHANGES + MATCHES))
                
                if [ "$DRY_RUN" = false ]; then
                    backup_file "$file"
                    perl -i -pe 's/(@(?:since|deprecated)\s+)1\.602\.(\d{4})/${1}1.6020.$2/g' "$file"
                    echo "    ✓ Applied"
                fi
            fi
        fi
    done
else
    echo "  No files found with this pattern"
fi
echo ""

echo "Pattern 2: Fixing 1.603.XXXX -> 1.6030.XXXX"
echo "-------------------------------------------"
FILES_603=$(grep -rl '@\(since\|deprecated\)\s\+1\.603\.\d\{4\}' "$PROJECT_ROOT/includes" 2>/dev/null || true)
if [ -n "$FILES_603" ]; then
    for file in $FILES_603; do
        if [ -f "$file" ]; then
            MATCHES=$(grep -c '@\(since\|deprecated\)\s\+1\.603\.' "$file" 2>/dev/null || echo "0")
            if [ "$MATCHES" -gt 0 ]; then
                echo "  File: ${file#$PROJECT_ROOT/}"
                echo "    Changes: $MATCHES occurrence(s)"
                TOTAL_FILES=$((TOTAL_FILES + 1))
                TOTAL_CHANGES=$((TOTAL_CHANGES + MATCHES))
                
                if [ "$DRY_RUN" = false ]; then
                    backup_file "$file"
                    perl -i -pe 's/(@(?:since|deprecated)\s+)1\.603\.(\d{4})/${1}1.6030.$2/g' "$file"
                    echo "    ✓ Applied"
                fi
            fi
        fi
    done
else
    echo "  No files found with this pattern"
fi
echo ""

echo "Pattern 3: Fixing 1.6030.XX -> 1.6030.00XX (short timestamps)"
echo "-------------------------------------------------------------"
FILES_SHORT=$(grep -rl '@\(since\|deprecated\)\s\+1\.6030\.\d\{1,3\}\s' "$PROJECT_ROOT/includes" 2>/dev/null || true)
if [ -n "$FILES_SHORT" ]; then
    for file in $FILES_SHORT; do
        if [ -f "$file" ]; then
            # Count only short timestamps (1-3 digits after 1.6030.)
            MATCHES=$(grep -o '@\(since\|deprecated\)\s\+1\.6030\.\d\{1,3\}\s' "$file" 2>/dev/null | wc -l || echo "0")
            if [ "$MATCHES" -gt 0 ]; then
                echo "  File: ${file#$PROJECT_ROOT/}"
                echo "    Changes: $MATCHES occurrence(s)"
                TOTAL_FILES=$((TOTAL_FILES + 1))
                TOTAL_CHANGES=$((TOTAL_CHANGES + MATCHES))
                
                if [ "$DRY_RUN" = false ]; then
                    backup_file "$file"
                    # Pad single digit: 1.6030.1 -> 1.6030.0001
                    perl -i -pe 's/(@(?:since|deprecated)\s+1\.6030\.)(\d)(\s)/${1}000$2$3/g' "$file"
                    # Pad two digits: 1.6030.21 -> 1.6030.0021
                    perl -i -pe 's/(@(?:since|deprecated)\s+1\.6030\.)(\d{2})(\s)/${1}00$2$3/g' "$file"
                    # Pad three digits: 1.6030.123 -> 1.6030.0123
                    perl -i -pe 's/(@(?:since|deprecated)\s+1\.6030\.)(\d{3})(\s)/${1}0$2$3/g' "$file"
                    echo "    ✓ Applied"
                fi
            fi
        fi
    done
else
    echo "  No files found with this pattern"
fi
echo ""

echo "Pattern 4: Checking for malformed versions (6+ digits)"
echo "-------------------------------------------------------"
FILES_MALFORMED=$(grep -rl '@\(since\|deprecated\)\s\+1\.\d\{4\}\.\d\{5,\}' "$PROJECT_ROOT/includes" 2>/dev/null || true)
if [ -n "$FILES_MALFORMED" ]; then
    echo "  ⚠️  WARNING: Found malformed version tags (6+ digits in timestamp)"
    echo "  These require MANUAL review:"
    echo ""
    for file in $FILES_MALFORMED; do
        if [ -f "$file" ]; then
            echo "    File: ${file#$PROJECT_ROOT/}"
            grep -n '@\(since\|deprecated\)\s\+1\.\d\{4\}\.\d\{5,\}' "$file" | head -5
            echo ""
        fi
    done
else
    echo "  ✓ No malformed versions found"
fi
echo ""

echo "Pattern 5: Fixing test file (ImageSizeConsistencyTest.php)"
echo "----------------------------------------------------------"
TEST_FILE="$PROJECT_ROOT/tests/Unit/ImageSizeConsistencyTest.php"
if [ -f "$TEST_FILE" ]; then
    MATCHES=$(grep -c '@since\s\+1\.6030\.21' "$TEST_FILE" 2>/dev/null || echo "0")
    if [ "$MATCHES" -gt 0 ]; then
        echo "  File: tests/Unit/ImageSizeConsistencyTest.php"
        echo "    Changes: $MATCHES occurrence(s)"
        TOTAL_FILES=$((TOTAL_FILES + 1))
        TOTAL_CHANGES=$((TOTAL_CHANGES + MATCHES))
        
        if [ "$DRY_RUN" = false ]; then
            backup_file "$TEST_FILE"
            perl -i -pe 's/(@since\s+1\.6030\.)21(\s)/${1}0021$2/g' "$TEST_FILE"
            echo "    ✓ Applied"
        fi
    else
        echo "  ✓ Already normalized or not found"
    fi
else
    echo "  File not found: $TEST_FILE"
fi
echo ""

echo "=========================================="
echo "Summary"
echo "=========================================="
echo "Total files to update: $TOTAL_FILES"
echo "Total changes: $TOTAL_CHANGES"
echo ""

if [ "$DRY_RUN" = true ]; then
    echo "This was a DRY RUN. No files were modified."
    echo ""
    echo "To apply these changes, run:"
    echo "  bash dev-tools/normalize-since-tags.sh --apply"
    echo ""
    echo "Backup files will be created automatically (.bak extension)"
else
    echo "✅ All changes applied successfully!"
    echo ""
    echo "Backup files created with .bak extension"
    echo "To restore a file: mv file.php.bak file.php"
    echo ""
    echo "To remove all backups: find includes/ tests/ -name '*.bak' -delete"
fi
echo ""
