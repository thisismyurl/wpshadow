#!/bin/bash

# WPShadow Post-Start Script
# ===========================
# Commandment #1: Helpful Neighbor Experience
# Commandment #8: Inspire Confidence
#
# This script runs every time you start your development container.
#
# Why it runs every time:
# - Ensures services are ready (WordPress, MySQL)
# - Checks for updates to dependencies
# - Displays helpful reminders and tips
# - Shows your development progress
#
# It's like a friendly "good morning" from your dev environment!
#
# Learn more: https://docs.wpshadow.com/dev-environment/lifecycle
#
# ===========================

# Source our helpful message system
if [ -f "$(dirname "$0")/lib/helpful-errors.sh" ]; then
    source "$(dirname "$0")/lib/helpful-errors.sh"
fi

# Source KPI tracking
if [ -f "$(dirname "$0")/lib/dev-kpis.sh" ]; then
    source "$(dirname "$0")/lib/dev-kpis.sh"
fi

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "👋 Welcome back to WPShadow!"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

# Check if WordPress is running
if wp core is-installed --allow-root 2>/dev/null; then
    echo "✅ WordPress is ready at http://localhost:8080"
else
    echo "⏳ WordPress is starting up..."
fi

# Show quick KPI summary if available
if [ -f ".devcontainer/.dev-kpis.json" ]; then
    show_kpi_summary
fi

# Setup deployment configuration (SSH keys, Git remote)
if [ -f ".devcontainer/setup-deployment.sh" ]; then
    bash .devcontainer/setup-deployment.sh
fi

# Helpful tip of the day
TIPS=(
    "💡 Tip: Run 'composer kpi' to see your development progress"
    "💡 Tip: Use 'composer phpcs' to check code quality anytime"
    "💡 Tip: Check .devcontainer/LEARNING_RESOURCES.md for learning paths"
    "💡 Tip: Join our free Office Hours every Tuesday at 2pm UTC"
    "💡 Tip: All our educational resources are free forever"
)

# Select random tip
RANDOM_TIP=${TIPS[$RANDOM % ${#TIPS[@]}]}
echo "$RANDOM_TIP"
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
