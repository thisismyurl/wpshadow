#!/bin/bash
#
# GitHub Rate Limit Daily Report
# Generates a summary of GitHub API usage
# Usage: ./scripts/daily-rate-limit-report.sh
#

set -e

echo "╔════════════════════════════════════════════════════════════╗"
echo "║     GitHub API Usage Report - $(date '+%Y-%m-%d %H:%M')     ║"
echo "╚════════════════════════════════════════════════════════════╝"
echo ""

if ! gh auth status &>/dev/null; then
    echo "❌ GitHub CLI not authenticated"
    exit 1
fi

RATE_DATA=$(gh api rate_limit 2>/dev/null)

# Function to display resource stats
show_resource() {
    local name="$1"
    local data="$2"
    
    local limit=$(echo "$data" | jq -r '.limit')
    local used=$(echo "$data" | jq -r '.used')
    local remaining=$(echo "$data" | jq -r '.remaining')
    local reset=$(echo "$data" | jq -r '.reset')
    local reset_time=$(date -d "@$reset" "+%H:%M:%S")
    
    local percent=$((100 * remaining / limit))
    
    # Status indicator
    if [ $percent -ge 75 ]; then
        status="✅"
    elif [ $percent -ge 50 ]; then
        status="⚡"
    elif [ $percent -ge 25 ]; then
        status="⚠️ "
    else
        status="🚨"
    fi
    
    printf "%-25s %s\n" "$name" "$status"
    printf "  %-20s %'d / %'d\n" "Limit:" $limit
    printf "  %-20s %'d (%d%%)\n" "Used:" $used $((100 - percent))
    printf "  %-20s %'d (%d%%)\n" "Remaining:" $remaining $percent
    printf "  %-20s %s\n" "Resets at:" "$reset_time"
    echo ""
}

# Core API
echo "📊 Core API (REST)"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
CORE_DATA=$(echo "$RATE_DATA" | jq '.resources.core')
show_resource "Status" "$CORE_DATA"

# Search API
echo "🔍 Search API"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
SEARCH_DATA=$(echo "$RATE_DATA" | jq '.resources.search')
show_resource "Status" "$SEARCH_DATA"

# GraphQL API
echo "📈 GraphQL API"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
GRAPHQL_DATA=$(echo "$RATE_DATA" | jq '.resources.graphql')
show_resource "Status" "$GRAPHQL_DATA"

# Code Search
echo "🔬 Code Search API"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
CODE_SEARCH_DATA=$(echo "$RATE_DATA" | jq '.resources.code_search')
show_resource "Status" "$CODE_SEARCH_DATA"

# Cache statistics
echo "💾 Cache Statistics"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
CACHE_DIR="/workspaces/wpshadow/.cache/github"
if [ -d "$CACHE_DIR" ]; then
    CACHE_FILES=$(find "$CACHE_DIR" -name "*.cache" -type f | wc -l)
    CACHE_SIZE=$(du -sh "$CACHE_DIR" 2>/dev/null | cut -f1)
    echo "  Cached responses:    $CACHE_FILES"
    echo "  Cache size:          $CACHE_SIZE"
    
    # Fresh cache count (< 5 minutes old)
    FRESH_CACHE=$(find "$CACHE_DIR" -name "*.cache" -type f -mmin -5 | wc -l)
    echo "  Fresh cache hits:    $FRESH_CACHE"
else
    echo "  No cache directory found"
fi
echo ""

# Recommendations
echo "💡 Recommendations"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

CORE_REMAINING=$(echo "$CORE_DATA" | jq -r '.remaining')
CORE_LIMIT=$(echo "$CORE_DATA" | jq -r '.limit')
CORE_PERCENT=$((100 * CORE_REMAINING / CORE_LIMIT))

if [ $CORE_PERCENT -lt 25 ]; then
    echo "  ⚠️  Rate limit low ($CORE_PERCENT% remaining)"
    echo "  • Switch to local operations only"
    echo "  • Use grep_search instead of GitHub search"
    echo "  • Enable cache: export GH_CACHE_VERBOSE=1"
elif [ $CORE_PERCENT -lt 50 ]; then
    echo "  ⚡ Moderate usage ($CORE_PERCENT% remaining)"
    echo "  • Prefer local operations when possible"
    echo "  • Use gh-cached.sh wrapper for repeated queries"
else
    echo "  ✅ Healthy usage ($CORE_PERCENT% remaining)"
    echo "  • Continue normal operations"
    echo "  • Consider using gh-cached.sh for efficiency"
fi

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "Report generated: $(date)"
