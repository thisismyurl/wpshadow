#!/bin/bash
#
# Cached GitHub CLI Wrapper
# Caches GitHub API responses for 5 minutes to reduce API calls
# Usage: ./scripts/gh-cached.sh [gh command args]
#
# Example: ./scripts/gh-cached.sh api repos/thisismyurl/wpshadow/issues
#

set -e

CACHE_DIR="/workspaces/wpshadow/.cache/github"
CACHE_TTL=300  # 5 minutes

# Create cache directory
mkdir -p "$CACHE_DIR"

# Don't cache certain commands
NOCACHE_PATTERNS="auth|rate_limit|pr create|issue create|release create"

# Check if command should bypass cache
COMMAND="$*"
if echo "$COMMAND" | grep -qE "$NOCACHE_PATTERNS"; then
    gh "$@"
    exit $?
fi

# Generate cache key from full command
CACHE_KEY=$(echo "$COMMAND" | md5sum | cut -d' ' -f1)
CACHE_FILE="$CACHE_DIR/$CACHE_KEY.cache"
CACHE_META="$CACHE_DIR/$CACHE_KEY.meta"

# Check if cache exists and is fresh
if [ -f "$CACHE_FILE" ]; then
    CACHE_AGE=$(($(date +%s) - $(stat -c %Y "$CACHE_FILE")))
    
    if [ $CACHE_AGE -lt $CACHE_TTL ]; then
        # Cache hit
        REMAINING=$((CACHE_TTL - CACHE_AGE))
        
        # Show cache hint if verbose
        if [ -n "$VERBOSE" ] || [ -n "$GH_CACHE_VERBOSE" ]; then
            echo "# [CACHE HIT] Expires in ${REMAINING}s" >&2
            if [ -f "$CACHE_META" ]; then
                cat "$CACHE_META" >&2
            fi
            echo "" >&2
        fi
        
        cat "$CACHE_FILE"
        exit 0
    fi
fi

# Cache miss - execute command
if [ -n "$VERBOSE" ] || [ -n "$GH_CACHE_VERBOSE" ]; then
    echo "# [CACHE MISS] Fetching from GitHub API..." >&2
fi

# Save metadata
echo "# Command: gh $COMMAND" > "$CACHE_META"
echo "# Cached at: $(date)" >> "$CACHE_META"

# Execute and cache
if gh "$@" > "$CACHE_FILE.tmp"; then
    mv "$CACHE_FILE.tmp" "$CACHE_FILE"
    cat "$CACHE_FILE"
    exit 0
else
    EXIT_CODE=$?
    rm -f "$CACHE_FILE.tmp"
    exit $EXIT_CODE
fi
