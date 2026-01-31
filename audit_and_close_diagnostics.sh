#!/bin/bash
set -e

echo "======================================================================"
echo "DIAGNOSTIC AUDIT AND CLOSURE SCRIPT"
echo "======================================================================"

# Step 1: Count implemented diagnostics
echo ""
echo "[1/4] Scanning implemented diagnostics..."
IMPLEMENTED_COUNT=$(find includes/diagnostics/tests -name "*.php" -type f | wc -l)
echo "✓ Found $IMPLEMENTED_COUNT diagnostic files"

# Step 2: Get unique diagnostic slugs and store them
echo ""
echo "[2/4] Extracting diagnostic slugs from files..."
grep -rh "protected static \$slug = '" includes/diagnostics/tests --include="*.php" | sed "s/.*protected static \\\$slug = '//;s/'.*$//" | sort | uniq > /tmp/implemented_slugs.txt
SLUG_COUNT=$(wc -l < /tmp/implemented_slugs.txt)
echo "✓ Found $SLUG_COUNT unique diagnostic slugs"

# Step 3: Get open diagnostic issues from GitHub
echo ""
echo "[3/4] Fetching open diagnostic issues from GitHub..."
curl -s -H "Authorization: token $GITHUB_TOKEN" "https://api.github.com/repos/thisismyurl/wpshadow/issues?labels=diagnostic&state=open&per_page=100" | jq -r '.[] | "\(.number)|\(.title)"' > /tmp/open_issues.txt
ISSUE_COUNT=$(wc -l < /tmp/open_issues.txt)
echo "✓ Found $ISSUE_COUNT open diagnostic issues"

# Step 4: Match and close
echo ""
echo "[4/4] Matching and closing issues..."
CLOSED_COUNT=0
MATCHED_COUNT=0

while read -r slug; do
    # Convert slug to title pattern (e.g., plugin-conflict-detection -> Plugin Conflict Detection)
    pattern=$(echo "$slug" | sed 's/-/ /g')
    
    # Find matching issue
    issue_line=$(grep -i "$pattern" /tmp/open_issues.txt | head -1)
    
    if [ ! -z "$issue_line" ]; then
        issue_num=$(echo "$issue_line" | cut -d'|' -f1)
        issue_title=$(echo "$issue_line" | cut -d'|' -f2)
        
        echo ""
        echo "  Matched: $slug"
        echo "    Issue #$issue_num: $issue_title"
        
        # Close the issue
        RESPONSE=$(curl -s -X PATCH -H "Authorization: token $GITHUB_TOKEN" -H "Accept: application/vnd.github.v3+json" \
            -d "{\"state\":\"closed\",\"labels\":[\"diagnostic\",\"implemented\"]}" \
            "https://api.github.com/repos/thisismyurl/wpshadow/issues/$issue_num")
        
        if echo "$RESPONSE" | jq -e '.state == "closed"' > /dev/null 2>&1; then
            echo "    ✓ Closed"
            ((CLOSED_COUNT++))
        else
            echo "    ✗ Failed to close"
        fi
        
        ((MATCHED_COUNT++))
    fi
done < /tmp/implemented_slugs.txt

# Summary
echo ""
echo "======================================================================"
echo "SUMMARY"
echo "======================================================================"
echo "Implemented diagnostics: $SLUG_COUNT"
echo "Open issues: $ISSUE_COUNT"
echo "Matched: $MATCHED_COUNT"
echo "Closed: $CLOSED_COUNT"
echo ""
echo "✓ Audit complete! $CLOSED_COUNT issues closed."
echo "======================================================================"

# Get updated count
echo ""
echo "Verifying remaining open diagnostics..."
REMAINING=$(curl -s -H "Authorization: token $GITHUB_TOKEN" "https://api.github.com/repos/thisismyurl/wpshadow/issues?labels=diagnostic&state=open&per_page=1" | jq '.[] | {number, title}' | grep -c "number" || echo 0)
echo "Remaining open diagnostic issues: $(curl -s -H "Authorization: token $GITHUB_TOKEN" "https://api.github.com/search/issues?q=repo:thisismyurl/wpshadow+label:diagnostic+state:open" | jq -r '.total_count // "unknown"' 2>/dev/null)"
