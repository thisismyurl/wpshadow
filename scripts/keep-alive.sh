#!/bin/bash
# WPShadow Keep-Alive Script
# Prevents Codespaces idle timeout by performing light activity every 5 minutes
# Runs silently in background

set -e

# Configuration
INTERVAL=300  # 5 minutes in seconds
LOG_FILE="/tmp/wpshadow-keep-alive.log"

# Function to log activity
log_activity() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" >> "$LOG_FILE"
}

# Initialize log
log_activity "🔄 Keep-alive started (interval: ${INTERVAL}s)"

# Trap errors
trap 'log_activity "⚠️ Keep-alive stopped"; exit 0' TERM INT

# Infinite loop - keep session alive by fetching from git every N minutes
while true; do
    sleep "$INTERVAL"

    # Light git operation to signal activity (no actual changes)
    if git rev-parse --git-dir > /dev/null 2>&1; then
        git fetch origin --quiet 2>/dev/null || true
        log_activity "✓ Fetch executed (idle time reset)"
    fi
done
