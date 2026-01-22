#!/bin/bash
#
# GitHub Rate Limit Checker
# Usage: ./scripts/check-rate-limits.sh [--verbose]
#

set -e

VERBOSE=false
if [ "$1" = "--verbose" ]; then
    VERBOSE=true
fi

# Check if gh is authenticated
if ! gh auth status &>/dev/null; then
    echo "❌ GitHub CLI not authenticated"
    echo "Run: gh auth login"
    exit 1
fi

# Get rate limit info
RATE_DATA=$(gh api rate_limit 2>/dev/null)

if [ -z "$RATE_DATA" ]; then
    echo "❌ Failed to fetch rate limit data"
    exit 1
fi

# Parse core API limits
CORE_LIMIT=$(echo "$RATE_DATA" | jq -r '.resources.core.limit')
CORE_USED=$(echo "$RATE_DATA" | jq -r '.resources.core.used')
CORE_REMAINING=$(echo "$RATE_DATA" | jq -r '.resources.core.remaining')
CORE_RESET=$(echo "$RATE_DATA" | jq -r '.resources.core.reset')

# Parse search API limits
SEARCH_LIMIT=$(echo "$RATE_DATA" | jq -r '.resources.search.limit')
SEARCH_REMAINING=$(echo "$RATE_DATA" | jq -r '.resources.search.remaining')
SEARCH_USED=$((SEARCH_LIMIT - SEARCH_REMAINING))

# Parse GraphQL limits
GRAPHQL_LIMIT=$(echo "$RATE_DATA" | jq -r '.resources.graphql.limit')
GRAPHQL_USED=$(echo "$RATE_DATA" | jq -r '.resources.graphql.used')
GRAPHQL_REMAINING=$(echo "$RATE_DATA" | jq -r '.resources.graphql.remaining')

# Calculate percentage remaining
CORE_PERCENT=$((100 * CORE_REMAINING / CORE_LIMIT))
SEARCH_PERCENT=$((100 * SEARCH_REMAINING / SEARCH_LIMIT))
GRAPHQL_PERCENT=$((100 * GRAPHQL_REMAINING / GRAPHQL_LIMIT))

# Reset time
RESET_TIME=$(date -d "@$CORE_RESET" "+%H:%M:%S")
RESET_IN=$(( (CORE_RESET - $(date +%s)) / 60 ))

# Determine health status
if [ $CORE_PERCENT -lt 10 ]; then
    STATUS="🚨 CRITICAL"
    COLOR="\033[1;31m"  # Red
elif [ $CORE_PERCENT -lt 25 ]; then
    STATUS="⚠️  WARNING"
    COLOR="\033[1;33m"  # Yellow
elif [ $CORE_PERCENT -lt 50 ]; then
    STATUS="⚡ CAUTION"
    COLOR="\033[1;34m"  # Blue
else
    STATUS="✅ HEALTHY"
    COLOR="\033[1;32m"  # Green
fi

RESET_COLOR="\033[0m"

# Output
echo -e "${COLOR}${STATUS}${RESET_COLOR} GitHub API Rate Limits"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "📊 Core API (REST):"
echo "   Used:      ${CORE_USED}/${CORE_LIMIT}"
echo "   Remaining: ${CORE_REMAINING} (${CORE_PERCENT}%)"
echo "   Resets:    ${RESET_TIME} (in ${RESET_IN} min)"
echo ""
echo "🔍 Search API:"
echo "   Used:      ${SEARCH_USED}/${SEARCH_LIMIT}"
echo "   Remaining: ${SEARCH_REMAINING} (${SEARCH_PERCENT}%)"
echo ""
echo "📈 GraphQL API:"
echo "   Used:      ${GRAPHQL_USED}/${GRAPHQL_LIMIT}"
echo "   Remaining: ${GRAPHQL_REMAINING} (${GRAPHQL_PERCENT}%)"

if [ "$VERBOSE" = true ]; then
    echo ""
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo "📋 All Resources:"
    echo "$RATE_DATA" | jq -r '.resources | to_entries[] | "\(.key): \(.value.remaining)/\(.value.limit)"'
fi

# Recommendations
echo ""
echo "💡 Recommendations:"
if [ $CORE_PERCENT -lt 25 ]; then
    echo "   • Switch to local git operations only"
    echo "   • Use grep_search instead of GitHub search"
    echo "   • Wait for rate limit reset at ${RESET_TIME}"
elif [ $SEARCH_PERCENT -lt 50 ]; then
    echo "   • Avoid GitHub search API (use grep_search)"
    echo "   • Search API resets every minute"
else
    echo "   • Continue normal operations"
    echo "   • Prefer local operations when possible"
fi

# Exit code based on status
if [ $CORE_PERCENT -lt 10 ]; then
    exit 2  # Critical
elif [ $CORE_PERCENT -lt 25 ]; then
    exit 1  # Warning
else
    exit 0  # Healthy
fi
