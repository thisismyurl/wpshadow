#!/bin/bash
#
# WPShadow Diagnostic Reorganization Script
# Reorganizes all diagnostic folders to match the 10 primary gauges structure
# Uses git mv to preserve file history
#

set -e

DIAGNOSTICS_DIR="/workspaces/wpshadow/includes/diagnostics/tests"

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${BLUE}═══════════════════════════════════════════════════════════${NC}"
echo -e "${BLUE}  WPShadow Diagnostic Folder Reorganization${NC}"
echo -e "${BLUE}═══════════════════════════════════════════════════════════${NC}"
echo ""

# Verify we're in the right directory
if [ ! -d "$DIAGNOSTICS_DIR" ]; then
    echo -e "${RED}Error: $DIAGNOSTICS_DIR not found${NC}"
    exit 1
fi

cd "$DIAGNOSTICS_DIR"

# Define reorganization mappings
declare -a MOVES=(
    "analytics:performance/analytics"
    "backup:monitoring/backup"
    "compliance:security/compliance"
    "content:design/content"
    "conversion:seo/conversion"
    "customer-feedback:monitoring/customer-feedback"
    "customer-retention:performance/customer-retention"
    "customer-support:monitoring/customer-support"
    "database:performance/database"
    "developer:code-quality/developer"
    "dns:settings/dns"
    "downtime-prevention:monitoring/downtime-prevention"
    "ecommerce:settings/ecommerce"
    "email:settings/email"
    "enterprise:security/enterprise"
    "file-permissions:security/file-permissions"
    "functionality:settings/functionality"
    "hosting:performance/hosting"
    "internationalization:settings/internationalization"
    "learning:design/learning"
    "marketing:seo/marketing"
    "pricing-optimization:seo/pricing-optimization"
    "privacy:security/privacy"
    "promotional-strategy:seo/promotional-strategy"
    "publisher:seo/publisher"
    "real-user-monitoring:monitoring/real-user-monitoring"
    "reliability:performance/reliability"
    "retention-optimization:performance/retention-optimization"
    "revenue-optimization:seo/revenue-optimization"
    "social-media:seo/social-media"
    "ssl:security/ssl"
    "ux:design/ux"
)

# Helper function to move folder
move_folder() {
    local from="$1"
    local to="$2"
    
    if [ ! -d "$from" ]; then
        echo -e "${YELLOW}⚠️  Skipping: $from (not found)${NC}"
        return 0
    fi
    
    # Create parent directory if needed
    local parent_dir=$(dirname "$to")
    if [ ! -d "$parent_dir" ]; then
        mkdir -p "$parent_dir"
        echo -e "${GREEN}✓${NC} Created: $parent_dir"
    fi
    
    # Move using git mv to preserve history
    if git mv "$from" "$to" 2>/dev/null; then
        echo -e "${GREEN}✓${NC} Moved: $from → $to"
    else
        echo -e "${RED}✗${NC} Failed to move: $from → $to"
        return 1
    fi
}

echo -e "${YELLOW}Moving ${#MOVES[@]} folders...${NC}"
echo ""

moved=0
failed=0

for move in "${MOVES[@]}"; do
    IFS=':' read -r from to <<< "$move"
    
    if move_folder "$from" "$to"; then
        moved=$((moved + 1))
    else
        failed=$((failed + 1))
    fi
done

echo ""
echo -e "${BLUE}═══════════════════════════════════════════════════════════${NC}"
echo -e "${GREEN}✅ Reorganization Complete${NC}"
echo -e "${BLUE}═══════════════════════════════════════════════════════════${NC}"
echo ""
echo "Results:"
echo "  Moved: $moved folders"
if [ $failed -gt 0 ]; then
    echo -e "  ${RED}Failed: $failed folders${NC}"
fi
echo ""

# Show new structure
echo -e "${YELLOW}New Directory Structure:${NC}"
ls -d */ 2>/dev/null | sort

echo ""
echo -e "${YELLOW}Next Steps:${NC}"
echo "  1. Review the changes: git status"
echo "  2. Commit the reorganization: git commit -m 'Reorganize diagnostics by primary gauge'"
echo "  3. Verify diagnostic families are still correct"
echo ""
