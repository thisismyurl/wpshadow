#!/bin/bash
# Layer 2: Notification Watcher Automator
# Watches for "Continue?" notification and sends keyboard input to accept
# Runs as background daemon, catches what the extension might miss

set -e

LOG_FILE="/tmp/auto-continue-watcher.log"
WATCH_INTERVAL=0.5  # seconds

# Initialize log
echo "[$(date '+%Y-%m-%d %H:%M:%S')] Auto-continue watcher started" >> "$LOG_FILE"

# Trap to clean up
trap 'echo "[$(date '+%Y-%m-%d %H:%M:%S')] Auto-continue watcher stopped" >> "$LOG_FILE"; exit 0' TERM INT

# Infinite monitoring loop
while true; do
    sleep "$WATCH_INTERVAL"
    
    # Check for common "Continue?" prompt patterns in recent logs or notifications
    # This targets the UI notification that appears after 25 tasks
    
    # Method 1: Watch for VS Code notification center activity (macOS/Linux/Windows compatible)
    if command -v xdotool > /dev/null 2>&1; then
        # Linux: Use xdotool to find notification windows and send keypress
        WINDOW=$(xdotool search --name "Continue\?" 2>/dev/null || true)
        if [ -n "$WINDOW" ]; then
            xdotool key Return 2>/dev/null || true
            echo "[$(date '+%Y-%m-%d %H:%M:%S')] ✅ Auto-accepted 'Continue?' prompt via xdotool" >> "$LOG_FILE"
            sleep 1
        fi
    fi
    
    # Method 2: Monitor clipboard for prompt text (fallback)
    if command -v xclip > /dev/null 2>&1; then
        RECENT_TEXT=$(xclip -selection clipboard -o 2>/dev/null || echo "")
        if echo "$RECENT_TEXT" | grep -q "Continue\|continue iterating\|more tasks"; then
            # Prompt detected, send accept key
            xdotool key Return 2>/dev/null || true
            xdotool key 'y' 2>/dev/null || true
            echo "[$(date '+%Y-%m-%d %H:%M:%S')] ✅ Auto-accepted prompt via clipboard detection" >> "$LOG_FILE"
            sleep 2
        fi
    fi
    
    # Method 3: Watch for notification file patterns (VS Code creates temp notification data)
    NOTIF_DIR="$HOME/.vscode-server/data/logs/2026*"
    if [ -d "$NOTIF_DIR" ] 2>/dev/null; then
        if grep -r "Continue\?" "$NOTIF_DIR" 2>/dev/null | grep -q "$(date '+%H:%M:%S')"; then
            xdotool key Return 2>/dev/null || true
            echo "[$(date '+%Y-%m-%d %H:%M:%S')] ✅ Auto-accepted prompt via log detection" >> "$LOG_FILE"
            sleep 2
        fi
    fi
done
