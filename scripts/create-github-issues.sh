#!/bin/bash
# Create GitHub Issues from Template Files
#
# This script creates GitHub issues in the appropriate repositories
# from the markdown templates in .github/ISSUE_TEMPLATES/
#
# Prerequisites: GitHub CLI (gh) must be installed and authenticated
# Install: https://cli.github.com/

set -e  # Exit on error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo "======================================"
echo "WPShadow Issue Creator"
echo "======================================"
echo ""

# Check if gh is installed
if ! command -v gh &> /dev/null; then
    echo -e "${RED}ERROR: GitHub CLI (gh) is not installed${NC}"
    echo "Install it from: https://cli.github.com/"
    exit 1
fi

# Check if gh is authenticated
if ! gh auth status &> /dev/null; then
    echo -e "${RED}ERROR: GitHub CLI is not authenticated${NC}"
    echo "Run: gh auth login"
    exit 1
fi

echo -e "${GREEN}✓ GitHub CLI is installed and authenticated${NC}"
echo ""

# Array of template files and their repositories
declare -a TEMPLATES=(
    "enhancement-email-notifications.md:thisismyurl/wpshadow"
    "enhancement-scheduled-scans.md:thisismyurl/wpshadow"
    "enhancement-pdf-reports.md:thisismyurl/wpshadow"
    "enhancement-complete-rollback.md:thisismyurl/wpshadow"
    "feature-health-history-dashboard.md:thisismyurl/wpshadow"
)

TEMPLATE_DIR=".github/ISSUE_TEMPLATES"
CREATED_COUNT=0
FAILED_COUNT=0

# Function to extract metadata from markdown
extract_labels() {
    grep -A 1 "^**Labels:**" "$1" | tail -1 | sed 's/`//g' | tr -d ' ' | tr ',' ','
}

extract_title() {
    head -1 "$1" | sed 's/^# //'
}

# Process each template
for TEMPLATE_INFO in "${TEMPLATES[@]}"; do
    TEMPLATE_FILE="${TEMPLATE_INFO%%:*}"
    REPO="${TEMPLATE_INFO##*:}"
    TEMPLATE_PATH="${TEMPLATE_DIR}/${TEMPLATE_FILE}"

    if [ ! -f "$TEMPLATE_PATH" ]; then
        echo -e "${RED}✗ Template not found: ${TEMPLATE_PATH}${NC}"
        ((FAILED_COUNT++))
        continue
    fi

    # Extract metadata
    TITLE=$(extract_title "$TEMPLATE_PATH")
    LABELS=$(extract_labels "$TEMPLATE_PATH")

    echo -e "${YELLOW}Creating issue in ${REPO}:${NC}"
    echo "  Title: ${TITLE}"
    echo "  Labels: ${LABELS}"

    # Create the issue
    if gh issue create \
        --repo "$REPO" \
        --title "$TITLE" \
        --body-file "$TEMPLATE_PATH" \
        --label "$LABELS" > /dev/null 2>&1; then

        echo -e "${GREEN}  ✓ Issue created successfully${NC}"
        ((CREATED_COUNT++))
    else
        echo -e "${RED}  ✗ Failed to create issue${NC}"
        ((FAILED_COUNT++))
    fi

    echo ""
done

# Summary
echo "======================================"
echo "Summary:"
echo "  Created: ${CREATED_COUNT}"
echo "  Failed: ${FAILED_COUNT}"
echo "======================================"

if [ $FAILED_COUNT -gt 0 ]; then
    exit 1
fi

exit 0
