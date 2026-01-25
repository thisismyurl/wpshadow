#!/bin/bash

# WPShadow Helpful Neighbor Error Handler
# ========================================
# Commandment #1: Helpful Neighbor Experience
# Transforms technical errors into educational, friendly guidance
#
# This script embodies our philosophy of being a "helpful neighbor" - someone
# you'd trust to explain technical concepts in a way that empowers rather than overwhelms.
#
# Learn more about our philosophy:
# https://docs.wpshadow.com/philosophy/helpful-neighbor

# Terminal color codes for better readability
RED='\033[0;31m'
YELLOW='\033[1;33m'
GREEN='\033[0;32m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m'  # No Color

# Display a helpful error message with context and learning resources
# Parameters:
#   $1 - error_message: What went wrong technically
#   $2 - explanation: Why it happened and what it means
#   $3 - learn_more_url: Link to Knowledge Base article
helpful_error() {
    local error_message=$1
    local explanation=$2
    local learn_more_url=$3
    
    echo ""
    echo -e "${RED}❌ Something Went Wrong${NC}"
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo ""
    echo -e "${YELLOW}What Happened:${NC}"
    echo "  $error_message"
    echo ""
    echo -e "${BLUE}💡 Helpful Neighbor Explains:${NC}"
    echo "  $explanation"
    echo ""
    
    if [ -n "$learn_more_url" ]; then
        echo -e "${GREEN}📚 Learn More:${NC}"
        echo "  $learn_more_url"
        echo ""
    fi
    
    echo -e "${GREEN}🆘 Need Help?${NC}"
    echo "  • Community Forum: https://forum.wpshadow.com"
    echo "  • Office Hours: Tuesdays 2pm UTC (Free)"
    echo "  • Knowledge Base: https://docs.wpshadow.com"
    echo ""
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
}

# Display a success message with next steps
# Parameters:
#   $1 - message: What succeeded
#   $2 - next_step: (Optional) Suggested next action
success_message() {
    local message=$1
    local next_step=$2
    
    echo ""
    echo -e "${GREEN}✅ Success!${NC}"
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo "  $message"
    
    if [ -n "$next_step" ]; then
        echo ""
        echo -e "${BLUE}💡 What's Next?${NC}"
        echo "  $next_step"
    fi
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
}

# Display a progress message during long operations
# Parameters:
#   $1 - message: What's happening now
progress_message() {
    local message=$1
    echo -e "${CYAN}⏳ $message${NC}"
}

# Display an educational tip
# Parameters:
#   $1 - tip: The helpful tip to share
educational_tip() {
    local tip=$1
    echo ""
    echo -e "${BLUE}💡 Helpful Tip:${NC}"
    echo "  $tip"
    echo ""
}

# Export functions so they can be used by other scripts
export -f helpful_error
export -f success_message
export -f progress_message
export -f educational_tip
