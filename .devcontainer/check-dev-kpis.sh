#!/bin/bash

# WPShadow Developer KPI Dashboard
# =================================
# Quick access to your development progress and achievements
#
# This script provides an at-a-glance view of your development journey
# with WPShadow. Use it anytime to see how far you've come!
#
# Usage: composer kpi
#        OR: bash .devcontainer/check-dev-kpis.sh

# Get the directory where this script lives
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

# Source the KPI library
source "$SCRIPT_DIR/lib/dev-kpis.sh"

# Display the full KPI dashboard
show_kpis
