#!/bin/bash
# WPShadow Auto-Commit Helper
# Quickly commit and push changes with smart defaults

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

# Check if message provided
if [ -z "$1" ]; then
    echo -e "${RED}Usage: $0 \"commit message\"${NC}"
    echo ""
    echo "Examples:"
    echo "  $0 \"feat: add new diagnostic for SSL\""
    echo "  $0 \"fix: resolve PHP warning in treatment\""
    echo "  $0 \"docs: update roadmap status\""
    echo "  $0 \"refactor: consolidate color utils\""
    exit 1
fi

COMMIT_MESSAGE="$1"

# Show what will be committed
echo -e "${YELLOW}→ Files to be committed:${NC}"
git status --short | head -20
TOTAL_FILES=$(git status --porcelain | wc -l)
if [ "$TOTAL_FILES" -gt 20 ]; then
    echo "... and $(($TOTAL_FILES - 20)) more files"
fi
echo ""

# Confirm
read -p "Commit these changes? (y/n) " -n 1 -r
echo
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo -e "${YELLOW}Aborted${NC}"
    exit 0
fi

# Add all changes
echo -e "${YELLOW}→ Adding all changes...${NC}"
git add -A

# Commit
echo -e "${YELLOW}→ Committing...${NC}"
git commit -m "$COMMIT_MESSAGE"

# Get current branch
BRANCH=$(git rev-parse --abbrev-ref HEAD)

# Push
echo -e "${YELLOW}→ Pushing to origin/${BRANCH}...${NC}"
git push origin "$BRANCH"

echo ""
echo -e "${GREEN}✓ Changes committed and pushed!${NC}"
echo ""

# Show status
echo -e "${YELLOW}→ Final status:${NC}"
git log --oneline -3
echo ""

exit 0
