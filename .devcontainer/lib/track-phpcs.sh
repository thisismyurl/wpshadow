#!/bin/bash

# WPShadow PHPCS KPI Tracker
# ===========================
# Tracks code quality checks and celebrates maintaining standards
#
# This script runs after PHPCS checks to track your commitment to
# code quality. Every check you run is a step toward better software.

# Source the KPI tracking library
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
source "$SCRIPT_DIR/dev-kpis.sh"

# Initialize KPIs if needed
init_kpis

# Increment PHPCS run counter
update_kpi "phpcs_runs" 1

# Estimate time saved (manual code review would take ~5 minutes)
update_kpi "estimated_time_saved_minutes" 5

# Show a quick confirmation
echo ""
echo "✅ Code quality check tracked! Run 'composer kpi' to see your stats."
echo ""
