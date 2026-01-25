#!/bin/bash

# WPShadow Developer KPI Tracker
# ===============================
# Commandment #9: Everything Has a KPI
# Tracks your development progress and celebrates achievements
#
# This isn't about pressure - it's about showing you the concrete value
# of your work and the time you're saving with our tools. We believe in
# demonstrating measurable improvement.
#
# Learn more: https://docs.wpshadow.com/philosophy/kpis

KPI_FILE="${KPI_FILE:-.devcontainer/.dev-kpis.json}"

# Initialize KPI tracking file if it doesn't exist
init_kpis() {
    if [ ! -f "$KPI_FILE" ]; then
        cat > "$KPI_FILE" <<EOF
{
  "first_setup": "$(date -Iseconds)",
  "tests_run": 0,
  "tests_passed": 0,
  "commits": 0,
  "phpcs_runs": 0,
  "phpcs_fixes": 0,
  "learning_resources_accessed": 0,
  "estimated_time_saved_minutes": 0,
  "last_updated": "$(date -Iseconds)"
}
EOF
    fi
}

# Update a specific KPI counter
# Parameters:
#   $1 - kpi_name: The KPI field to update
#   $2 - increment: How much to add (default: 1)
update_kpi() {
    local kpi_name=$1
    local increment=${2:-1}
    
    init_kpis
    
    # Read current value
    local current_value=$(jq -r ".$kpi_name" "$KPI_FILE")
    
    # Handle null or non-numeric values
    if [ "$current_value" = "null" ] || [ -z "$current_value" ]; then
        current_value=0
    fi
    
    # Calculate new value
    local new_value=$((current_value + increment))
    
    # Update the JSON file
    jq ".$kpi_name = $new_value | .last_updated = \"$(date -Iseconds)\"" "$KPI_FILE" > "${KPI_FILE}.tmp"
    mv "${KPI_FILE}.tmp" "$KPI_FILE"
}

# Get achievement level based on KPI totals
get_achievement_level() {
    init_kpis
    
    local tests=$(jq -r '.tests_run' "$KPI_FILE")
    local commits=$(jq -r '.commits' "$KPI_FILE")
    local phpcs=$(jq -r '.phpcs_runs' "$KPI_FILE")
    
    local total_actions=$((tests + commits + phpcs))
    
    if [ $total_actions -ge 100 ]; then
        echo "🏆 Master Developer"
    elif [ $total_actions -ge 50 ]; then
        echo "⭐ Advanced Developer"
    elif [ $total_actions -ge 20 ]; then
        echo "🌟 Intermediate Developer"
    elif [ $total_actions -ge 5 ]; then
        echo "✨ Getting Started"
    else
        echo "🌱 New Developer"
    fi
}

# Get motivational message based on progress
get_motivation_message() {
    local achievement=$(get_achievement_level)
    
    case "$achievement" in
        *"Master"*)
            echo "You're crushing it! Your dedication to quality is inspiring."
            ;;
        *"Advanced"*)
            echo "Excellent progress! You're becoming a WPShadow expert."
            ;;
        *"Intermediate"*)
            echo "Great work! You're building strong development habits."
            ;;
        *"Getting Started"*)
            echo "Nice start! Every expert was once a beginner."
            ;;
        *)
            echo "Welcome! You're on your way to mastering WordPress development."
            ;;
    esac
}

# Display the KPI dashboard
show_kpis() {
    init_kpis
    
    local achievement=$(get_achievement_level)
    local motivation=$(get_motivation_message)
    
    echo ""
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo "📊 Your Development KPIs"
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo ""
    echo "Current Level: $achievement"
    echo ""
    echo "📈 Activity Metrics:"
    echo "  ✅ Tests Run:       $(jq -r '.tests_run' $KPI_FILE)"
    echo "  ✨ Tests Passed:    $(jq -r '.tests_passed' $KPI_FILE)"
    echo "  ✓  PHPCS Checks:    $(jq -r '.phpcs_runs' $KPI_FILE)"
    echo "  🔧 Auto-fixes:      $(jq -r '.phpcs_fixes' $KPI_FILE)"
    echo "  💻 Commits:         $(jq -r '.commits' $KPI_FILE)"
    echo "  📚 KB Articles:     $(jq -r '.learning_resources_accessed' $KPI_FILE)"
    echo ""
    echo "⏱️  Estimated Time Saved: $(jq -r '.estimated_time_saved_minutes' $KPI_FILE) minutes"
    echo ""
    echo "💬 \"$motivation\""
    echo ""
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo ""
    echo "💡 Tip: Run 'composer kpi' anytime to see your progress!"
    echo ""
}

# Show a compact KPI summary (for post-commit hooks)
show_kpi_summary() {
    init_kpis
    
    local achievement=$(get_achievement_level)
    
    echo ""
    echo "📊 Quick Stats: $achievement | Tests: $(jq -r '.tests_run' $KPI_FILE) | Commits: $(jq -r '.commits' $KPI_FILE) | Time Saved: $(jq -r '.estimated_time_saved_minutes' $KPI_FILE)m"
    echo ""
}

# Export functions for use in other scripts
export -f init_kpis
export -f update_kpi
export -f show_kpis
export -f show_kpi_summary
export -f get_achievement_level
export -f get_motivation_message
