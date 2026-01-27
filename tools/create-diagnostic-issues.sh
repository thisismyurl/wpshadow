#!/bin/bash

# Create GitHub Issues for All 26 Diagnostics
# Uses GitHub CLI (gh) to create issues directly
# Requires: gh CLI authenticated with API access

set -e

REPO="thisismyurl/wpshadow"
ISSUES_FILE="docs/GITHUB_ISSUES_COPY_PASTE_READY.md"

# Check if gh is available
if ! command -v gh &> /dev/null; then
    echo "❌ GitHub CLI (gh) not found. Install from: https://cli.github.com/"
    exit 1
fi

# Check if authenticated
if ! gh auth status &> /dev/null; then
    echo "❌ GitHub CLI not authenticated. Run: gh auth login"
    exit 1
fi

# Verify issues file exists
if [ ! -f "$ISSUES_FILE" ]; then
    echo "❌ Issues file not found: $ISSUES_FILE"
    exit 1
fi

echo "🔍 Parsing issues from $ISSUES_FILE..."
echo ""

# Extract and create each issue
# Parse the markdown file for issue blocks
issue_count=0
current_title=""
current_body=""
current_labels=""
in_body=false

while IFS= read -r line; do
    # Check for title line (starts with "##")
    if [[ "$line" =~ ^##\ Title:\ ]]; then
        # If we have a previous issue, create it
        if [ -n "$current_title" ] && [ -n "$current_body" ]; then
            issue_count=$((issue_count + 1))
            create_issue "$issue_count" "$current_title" "$current_body" "$current_labels"
        fi
        
        # Extract title (remove "## Title: ")
        current_title="${line### Title: }"
        current_body=""
        current_labels=""
        in_body=false
        
    elif [[ "$line" =~ ^##\ Labels:\ ]]; then
        # Extract labels (remove "## Labels: ")
        current_labels="${line### Labels: }"
        
    elif [[ "$line" =~ ^##\ Description ]]; then
        in_body=true
        current_body=""
        
    elif [[ "$line" =~ ^---$ ]]; then
        in_body=false
        
    elif [ "$in_body" = true ] && [ -n "$line" ]; then
        # Accumulate body lines
        current_body="${current_body}${line}"$'\n'
    fi
done < "$ISSUES_FILE"

# Create the last issue
if [ -n "$current_title" ] && [ -n "$current_body" ]; then
    issue_count=$((issue_count + 1))
    create_issue "$issue_count" "$current_title" "$current_body" "$current_labels"
fi

# Function to create a single issue
create_issue() {
    local num=$1
    local title=$2
    local body=$3
    local labels=$4
    
    # Clean up body (remove trailing newline)
    body=$(echo "$body" | sed '$ d')
    
    printf "%-2d. Creating: %-60s " "$num" "$title"
    
    # Create issue with gh CLI
    if output=$(gh issue create \
        --repo "$REPO" \
        --title "$title" \
        --body "$body" \
        --label "$labels" 2>&1); then
        
        # Extract issue number from output
        issue_num=$(echo "$output" | grep -oP '#\K[0-9]+' | head -1)
        echo "✅ Issue #$issue_num"
    else
        echo "❌ Failed"
        echo "   Error: $output"
        return 1
    fi
}

echo ""
echo "========================"
echo "✅ Successfully created $issue_count GitHub issues!"
echo ""
echo "📊 Next steps:"
echo "   1. Review issues at: https://github.com/$REPO/issues"
echo "   2. Create project board to track progress"
echo "   3. Prioritize Phase 1 (Security) issues"
echo "   4. Assign to team members"
echo ""
