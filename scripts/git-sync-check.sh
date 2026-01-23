#!/bin/bash
# WPShadow Git Sync Check
# Run this before starting work to ensure you're in sync with GitHub

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

echo "╔════════════════════════════════════════════════════════════╗"
echo "║           WPShadow Git Sync Check                         ║"
echo "╚════════════════════════════════════════════════════════════╝"
echo ""

# Check if we're in a git repo
if ! git rev-parse --git-dir > /dev/null 2>&1; then
    echo -e "${RED}✗ Not in a git repository${NC}"
    exit 1
fi

# Fetch latest from origin
echo -e "${BLUE}→ Fetching latest from origin...${NC}"
git fetch origin --quiet

# Check current branch
CURRENT_BRANCH=$(git rev-parse --abbrev-ref HEAD)
echo -e "${GREEN}✓ Current branch: ${CURRENT_BRANCH}${NC}"

# Check for uncommitted changes
UNCOMMITTED=$(git status --porcelain | wc -l)
if [ "$UNCOMMITTED" -gt 0 ]; then
    echo -e "${YELLOW}⚠ You have ${UNCOMMITTED} uncommitted changes${NC}"
    echo ""
    git status --short | head -20
    if [ "$UNCOMMITTED" -gt 20 ]; then
        echo "... and $(($UNCOMMITTED - 20)) more files"
    fi
    echo ""
    echo -e "${YELLOW}Options:${NC}"
    echo "  1. Commit: git add -A && git commit -m 'your message'"
    echo "  2. Stash:  git stash save 'WIP description'"
    echo "  3. Discard: git reset --hard HEAD (⚠️ DANGEROUS)"
    exit 1
fi

# Check for unpushed commits
UNPUSHED=$(git log origin/${CURRENT_BRANCH}..HEAD --oneline | wc -l)
if [ "$UNPUSHED" -gt 0 ]; then
    echo -e "${YELLOW}⚠ You have ${UNPUSHED} unpushed commits${NC}"
    echo ""
    git log origin/${CURRENT_BRANCH}..HEAD --oneline
    echo ""
    echo -e "${YELLOW}Push them:${NC} git push origin ${CURRENT_BRANCH}"
    exit 1
fi

# Check for commits on origin not in local
UNPULLED=$(git log HEAD..origin/${CURRENT_BRANCH} --oneline | wc -l)
if [ "$UNPULLED" -gt 0 ]; then
    echo -e "${YELLOW}⚠ Origin has ${UNPULLED} commits you don't have${NC}"
    echo ""
    git log HEAD..origin/${CURRENT_BRANCH} --oneline
    echo ""
    echo -e "${YELLOW}Pull them:${NC} git pull origin ${CURRENT_BRANCH}"
    exit 1
fi

# All good!
echo -e "${GREEN}✓ Local and remote are in sync${NC}"
echo -e "${GREEN}✓ No uncommitted changes${NC}"
echo -e "${GREEN}✓ Ready to work!${NC}"
echo ""

# Show recent commits
echo -e "${BLUE}Recent commits:${NC}"
git log --oneline -5
echo ""

exit 0
