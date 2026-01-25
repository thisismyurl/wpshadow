#!/bin/bash

# WPShadow Test KPI Tracker
# ==========================
# Tracks test runs and celebrates maintaining quality through testing
#
# Testing is a critical part of quality software development. This script
# helps you see the impact of your testing efforts over time.

# Source the KPI tracking library
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
source "$SCRIPT_DIR/dev-kpis.sh"

# Initialize KPIs if needed
init_kpis

# Increment test run counter
update_kpi "tests_run" 1

# If tests passed, increment that counter too
# Note: This is a simplified version. In a real implementation,
# you'd parse PHPUnit output to determine pass/fail status
if [ "${PHPUNIT_EXIT_CODE:-0}" -eq 0 ]; then
    update_kpi "tests_passed" 1
fi

# Estimate time saved (manual testing would take ~10 minutes)
update_kpi "estimated_time_saved_minutes" 10

# Show a quick confirmation
echo ""
echo "✅ Test run tracked! Run 'composer kpi' to see your stats."
echo ""
