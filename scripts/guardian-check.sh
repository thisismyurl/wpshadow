#!/bin/bash
# WPShadow Guardian Angel - Comprehensive Pre-Work Check
# Catches issues before they become problems

set +e  # Don't exit on errors - we want to continue checking

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
MAGENTA='\033[0;35m'
CYAN='\033[0;36m'
NC='\033[0m'

ISSUES=0

echo "╔══════════════════════════════════════════════════════════════════╗"
echo "║         🛡️  WPShadow Guardian Angel - Environment Check         ║"
echo "╚══════════════════════════════════════════════════════════════════╝"
echo ""

# === 1. Git Sync Check ===
echo -e "${BLUE}→ Checking git sync status...${NC}"
if ! git rev-parse --git-dir > /dev/null 2>&1; then
    echo -e "${RED}✗ Not in a git repository${NC}"
    ((ISSUES++))
else
    git fetch origin --quiet

    UNCOMMITTED=$(git status --porcelain | wc -l)
    UNPUSHED=$(git log origin/$(git rev-parse --abbrev-ref HEAD)..HEAD --oneline 2>/dev/null | wc -l)
    UNPULLED=$(git log HEAD..origin/$(git rev-parse --abbrev-ref HEAD) --oneline 2>/dev/null | wc -l)

    if [ "$UNCOMMITTED" -gt 0 ]; then
        echo -e "${YELLOW}⚠ ${UNCOMMITTED} uncommitted changes${NC}"
        ((ISSUES++))
    else
        echo -e "${GREEN}✓ No uncommitted changes${NC}"
    fi

    if [ "$UNPUSHED" -gt 0 ]; then
        echo -e "${YELLOW}⚠ ${UNPUSHED} unpushed commits${NC}"
        ((ISSUES++))
    else
        echo -e "${GREEN}✓ All commits pushed${NC}"
    fi

    if [ "$UNPULLED" -gt 0 ]; then
        echo -e "${YELLOW}⚠ ${UNPULLED} commits on remote not pulled${NC}"
        ((ISSUES++))
    else
        echo -e "${GREEN}✓ Up to date with remote${NC}"
    fi
fi
echo ""

# === 2. PHP Syntax Check ===
echo -e "${BLUE}→ Checking PHP syntax...${NC}"
if ! command -v php &> /dev/null; then
    echo -e "${YELLOW}⚠ PHP not installed - skipping syntax check${NC}"
else
    SYNTAX_ERRORS=0
    while IFS= read -r file; do
        if ! php -l "$file" > /dev/null 2>&1; then
            echo -e "${RED}✗ Syntax error in: $file${NC}"
            php -l "$file"
            ((SYNTAX_ERRORS++))
            ((ISSUES++))
        fi
    done < <(find includes wpshadow.php -name '*.php' -not -path '*/vendor/*' -not -path '*/documented/*' 2>/dev/null)

    if [ "$SYNTAX_ERRORS" -eq 0 ]; then
        echo -e "${GREEN}✓ No PHP syntax errors${NC}"
    fi
fi
echo ""

# === 3. Composer Dependencies ===
echo -e "${BLUE}→ Checking composer dependencies...${NC}"
if [ -f "composer.json" ]; then
    if [ ! -d "vendor" ]; then
        echo -e "${YELLOW}⚠ Vendor directory missing - run: composer install${NC}"
        ((ISSUES++))
    else
        echo -e "${GREEN}✓ Composer dependencies installed${NC}"
    fi
else
    echo -e "${YELLOW}⚠ No composer.json found${NC}"
fi
echo ""

# === 4. Docker Status ===
echo -e "${BLUE}→ Checking Docker environment...${NC}"
if command -v docker &> /dev/null; then
    if docker info > /dev/null 2>&1; then
        if docker ps --format '{{.Names}}' | grep -q "wpshadow-test"; then
            echo -e "${GREEN}✓ Docker running, wpshadow-test container active${NC}"
        else
            echo -e "${YELLOW}⚠ Docker running but wpshadow-test not found${NC}"
            echo -e "   Run: docker-compose up -d"
            ((ISSUES++))
        fi
    else
        echo -e "${YELLOW}⚠ Docker not running${NC}"
        ((ISSUES++))
    fi
else
    echo -e "${YELLOW}⚠ Docker not installed${NC}"
    ((ISSUES++))
fi
echo ""

# === 5. File Permissions ===
echo -e "${BLUE}→ Checking file permissions...${NC}"
PERMISSION_ISSUES=0
for script in scripts/*.sh; do
    if [ -f "$script" ] && [ ! -x "$script" ]; then
        echo -e "${YELLOW}⚠ Not executable: $script${NC}"
        chmod +x "$script"
        echo -e "   Fixed: Made executable"
        ((PERMISSION_ISSUES++))
    fi
done

if [ "$PERMISSION_ISSUES" -eq 0 ]; then
    echo -e "${GREEN}✓ All scripts executable${NC}"
fi
echo ""

# === 6. Disk Space ===
echo -e "${BLUE}→ Checking disk space...${NC}"
DISK_USAGE=$(df -h . | awk 'NR==2 {print $5}' | sed 's/%//')
if [ "$DISK_USAGE" -gt 90 ]; then
    echo -e "${RED}✗ Disk usage critical: ${DISK_USAGE}%${NC}"
    ((ISSUES++))
elif [ "$DISK_USAGE" -gt 80 ]; then
    echo -e "${YELLOW}⚠ Disk usage high: ${DISK_USAGE}%${NC}"
    ((ISSUES++))
else
    echo -e "${GREEN}✓ Disk space OK: ${DISK_USAGE}% used${NC}"
fi
echo ""

# === 7. Recent Activity ===
echo -e "${BLUE}→ Recent activity summary...${NC}"
if git rev-parse --git-dir > /dev/null 2>&1; then
    echo -e "${CYAN}Last 3 commits:${NC}"
    git log --oneline -3 | sed 's/^/   /'
fi
echo ""

# === Summary ===
echo "╔══════════════════════════════════════════════════════════════════╗"
if [ "$ISSUES" -eq 0 ]; then
    echo -e "║  ${GREEN}✅ ALL CHECKS PASSED - Ready to work!${NC}                          ║"
    echo "╚══════════════════════════════════════════════════════════════════╝"
    echo ""
    echo -e "${CYAN}Quick commands:${NC}"
    echo "  Commit: ./scripts/git-auto-commit.sh \"message\""
    echo "  Test:   composer phpcs && composer phpstan"
    echo "  Docker: docker-compose up -d"
    echo ""
    exit 0
else
    echo -e "║  ${YELLOW}⚠️  FOUND ${ISSUES} ISSUES - Please review above${NC}                      ║"
    echo "╚══════════════════════════════════════════════════════════════════╝"
    echo ""
    echo -e "${YELLOW}Fix suggestions:${NC}"
    echo "  Uncommitted changes: git add -A && git commit -m 'message'"
    echo "  Unpushed commits: git push origin main"
    echo "  Behind remote: git pull origin main"
    echo "  PHP errors: Check the files listed above"
    echo "  Docker: docker-compose up -d"
    echo ""
    exit 1
fi
