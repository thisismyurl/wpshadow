#!/bin/bash

# Generate GitHub issues for all diagnostic files
# Usage: ./generate-diagnostic-issues.sh [OPTIONS]
#
# Options:
#   --batch N       Create only N issues at a time (default: all)
#   --start N       Start from issue number N (default: 1)
#   --filter PATTERN Only create issues matching pattern
#   --dry-run       Show what would be created without creating issues

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$(cd "$SCRIPT_DIR/../.." && pwd)"
DIAGNOSTICS_DIR="$REPO_ROOT/includes/diagnostics"

# Parse command line options
BATCH_SIZE=0
START_FROM=1
FILTER_PATTERN=""
DRY_RUN=false

while [[ $# -gt 0 ]]; do
    case $1 in
        --batch) BATCH_SIZE="$2"; shift 2 ;;
        --start) START_FROM="$2"; shift 2 ;;
        --filter) FILTER_PATTERN="$2"; shift 2 ;;
        --dry-run) DRY_RUN=true; shift ;;
        *)
            echo "Unknown option: $1"
            echo "Usage: $0 [--batch N] [--start N] [--filter PATTERN] [--dry-run]"
            exit 1
            ;;
    esac
done

# Check if gh CLI is installed
if ! command -v gh &> /dev/null; then
    echo "Error: GitHub CLI (gh) is not installed."
    exit 1
fi

# Find all diagnostic files
all_files=$(find "$DIAGNOSTICS_DIR" -name "class-diagnostic-*.php" | sort)

if [ -n "$FILTER_PATTERN" ]; then
    all_files=$(echo "$all_files" | grep -i "$FILTER_PATTERN" || true)
fi

total=$(echo "$all_files" | wc -l)
echo "Found $total diagnostic files"
[ "$DRY_RUN" = true ] && echo "🔍 DRY RUN MODE"
[ $BATCH_SIZE -gt 0 ] && echo "Creating batch $START_FROM to $((START_FROM + BATCH_SIZE - 1))"
echo ""

count=0
created=0
skipped=0

# Process each file
echo "$all_files" | while read -r file; do
    [ -z "$file" ] && continue
    count=$((count + 1))
    
    [ $count -lt $START_FROM ] && continue
    [ $BATCH_SIZE -gt 0 ] && [ $created -ge $BATCH_SIZE ] && break
    
    filename=$(basename "$file")
    diagnostic_name="${filename#class-diagnostic-}"
    diagnostic_name="${diagnostic_name%.php}"
    
    # Check if stub
    is_stub=false
    grep -qi "TODO\|FIXME\|STUB\|@todo" "$file" 2>/dev/null && is_stub=true
    [ $(wc -l < "$file") -lt 50 ] && is_stub=true
    
    if [ "$is_stub" = false ]; then
        skipped=$((skipped + 1))
        echo "[$count/$total] ⏭️  Skip: $filename"
        continue
    fi
    
    # Convert to title
    title=$(echo "$diagnostic_name" | sed 's/-/ /g' | awk '{for(i=1;i<=NF;i++) $i=toupper(substr($i,1,1)) tolower(substr($i,2))}1')
    
    # Extract details
    class_name=$(grep -m 1 "^class Diagnostic_" "$file" 2>/dev/null | sed 's/class //' | sed 's/ .*//' || echo "Unknown")
    namespace=$(grep -m 1 "^namespace " "$file" 2>/dev/null | sed 's/namespace //' | sed 's/;.*//' || echo "Unknown")
    
    # Create issue body
    issue_body="## Diagnostic Information

**File:** \`$filename\`  
**Class:** \`$class_name\`  
**Namespace:** \`$namespace\`

## Description

Tests the $title diagnostic functionality (STUB - needs implementation)

## Testing Objectives

- [ ] Implement the diagnostic check() method
- [ ] Verify correct identification of conditions
- [ ] Test edge cases and boundary conditions
- [ ] Validate error handling
- [ ] Ensure integration with WPShadow core

## Implementation Tasks

1. **Define Detection Logic**
   - Determine what conditions trigger this diagnostic
   - Define threat level (0-100)
   - Identify if auto-fixable

2. **Implement check() Method**
   - Write detection code
   - Return proper finding structure
   - Handle edge cases

3. **Testing**
   - Test positive detection
   - Test negative detection
   - Test edge cases

4. **Documentation**
   - Add PHPDoc blocks
   - Update feature matrix
   - Create KB article

## Related Files

- **Diagnostic:** \`includes/diagnostics/$filename\`
- **Treatment:** \`includes/treatments/class-treatment-$diagnostic_name.php\` (create if auto-fixable)
- **Tests:** \`tests/diagnostics/test-diagnostic-$diagnostic_name.php\` (to be created)

---

**Priority:** Medium  
**Type:** Implementation  
**Component:** Diagnostics"
    
    if [ "$DRY_RUN" = true ]; then
        echo "[$count/$total] 🔍 Would create: $title"
        created=$((created + 1))
    else
        echo "[$count/$total] ✨ Creating: $title"
        if gh issue create --title "Implement Diagnostic: $title" --body "$issue_body" --label "diagnostics,enhancement" 2>&1 >/dev/null; then
            created=$((created + 1))
            echo "   ✅ Created"
        else
            echo "   ⚠️  Failed"
        fi
        sleep 1
    fi
done

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "✅ Completed!"
echo "   Created: $created"
echo "   Skipped: $skipped"
if [ $BATCH_SIZE -gt 0 ]; then
    echo ""
    echo "Next batch: --start $((START_FROM + BATCH_SIZE))"
fi
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
