#!/bin/bash
# Fetch next 100 diagnostic issues in descending order from #3420

START_ISSUE=3420
BATCH_SIZE=100
OUTPUT_FILE="/tmp/diagnostic_batch_issues.json"

echo "🔍 Fetching diagnostic issues from #$START_ISSUE down to #$((START_ISSUE - BATCH_SIZE))..."
echo "[" > "$OUTPUT_FILE"

FOUND_COUNT=0
for ((i=START_ISSUE; i>START_ISSUE-BATCH_SIZE && i>0; i--)); do
    RESPONSE=$(curl -s -H "Authorization: Bearer ${GITHUB_TOKEN}" \
        -H "Accept: application/vnd.github+json" \
        "https://api.github.com/repos/thisismyurl/wpshadow/issues/$i")
    
    # Check if it's a diagnostic issue that's open
    if echo "$RESPONSE" | jq -e '.labels[]? | select(.name == "diagnostic")' > /dev/null 2>&1; then
        STATE=$(echo "$RESPONSE" | jq -r '.state')
        if [ "$STATE" = "open" ]; then
            TITLE=$(echo "$RESPONSE" | jq -r '.title')
            echo "  ✓ #$i: $TITLE"
            
            # Add comma if not first item
            if [ $FOUND_COUNT -gt 0 ]; then
                echo "," >> "$OUTPUT_FILE"
            fi
            
            # Extract relevant fields
            echo "$RESPONSE" | jq '{number, title, body, labels: [.labels[].name], state}' | tr -d '\n' >> "$OUTPUT_FILE"
            FOUND_COUNT=$((FOUND_COUNT + 1))
        fi
    fi
    
    sleep 0.3  # Rate limiting
done

echo "" >> "$OUTPUT_FILE"
echo "]" >> "$OUTPUT_FILE"

echo ""
echo "📊 Found $FOUND_COUNT open diagnostic issues"
echo "💾 Saved to $OUTPUT_FILE"

# Show first 5 issues
echo ""
echo "🎯 First 5 issues to implement:"
jq -r '.[:5] | .[] | "#\(.number): \(.title)"' "$OUTPUT_FILE"
