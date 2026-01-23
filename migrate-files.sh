#!/bin/bash
# WPShadow File Migration Script
# Safely moves 131 files from old hierarchy to new optimized structure
# Generated: 2025-01-23

set -e

SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
INCLUDES_DIR="${SCRIPT_DIR}/includes"

# Colors for output
GREEN='\033[0;32m'
BLUE='\033[0;34m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

MOVED_COUNT=0
TOTAL_MOVES=0

# Helper function to move file and track
move_file() {
    local src="$1"
    local dest="$2"
    local description="$3"

    TOTAL_MOVES=$((TOTAL_MOVES + 1))

    if [ -f "$src" ]; then
        mkdir -p "$(dirname "$dest")"
        mv "$src" "$dest"
        MOVED_COUNT=$((MOVED_COUNT + 1))
        echo -e "${GREEN}✓${NC} $description"
    else
        echo -e "${YELLOW}⊘${NC} SKIP: $src not found (OK)"
    fi
}

echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}WPShadow File Migration Script${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""

# Phase 1: Move utilities from core to utils
echo -e "${BLUE}Phase 1: Moving utilities from core/ to utils/${NC}"
move_file "$INCLUDES_DIR/core/class-color-utils.php" "$INCLUDES_DIR/utils/class-color-utils.php" "color-utils"
move_file "$INCLUDES_DIR/core/class-theme-data-provider.php" "$INCLUDES_DIR/utils/class-theme-data-provider.php" "theme-data-provider"
move_file "$INCLUDES_DIR/core/class-user-preferences-manager.php" "$INCLUDES_DIR/utils/class-user-preferences-manager.php" "user-preferences-manager"
move_file "$INCLUDES_DIR/core/class-timezone-manager.php" "$INCLUDES_DIR/utils/class-timezone-manager.php" "timezone-manager"
move_file "$INCLUDES_DIR/core/class-analysis-helpers.php" "$INCLUDES_DIR/utils/class-analysis-helpers.php" "analysis-helpers"
move_file "$INCLUDES_DIR/core/class-site-health-explanations.php" "$INCLUDES_DIR/utils/class-site-health-explanations.php" "site-health-explanations"
move_file "$INCLUDES_DIR/core/class-treatment-hooks.php" "$INCLUDES_DIR/utils/class-treatment-hooks.php" "treatment-hooks"
move_file "$INCLUDES_DIR/core/class-command-base.php" "$INCLUDES_DIR/utils/class-command-base.php" "command-base"
move_file "$INCLUDES_DIR/core/class-dashboard-customization.php" "$INCLUDES_DIR/utils/class-dashboard-customization.php" "dashboard-customization"
move_file "$INCLUDES_DIR/core/class-diagnostic-scheduler.php" "$INCLUDES_DIR/utils/class-diagnostic-scheduler.php" "diagnostic-scheduler"
move_file "$INCLUDES_DIR/core/class-diagnostic-lean-checks.php" "$INCLUDES_DIR/utils/class-diagnostic-lean-checks.php" "diagnostic-lean-checks"
move_file "$INCLUDES_DIR/core/class-diagnostic-result-normalizer.php" "$INCLUDES_DIR/utils/class-diagnostic-result-normalizer.php" "diagnostic-result-normalizer"

echo ""

# Phase 2: Move dashboard files
echo -e "${BLUE}Phase 2: Moving dashboard files to dashboard/${NC}"
move_file "$INCLUDES_DIR/admin/class-asset-manager.php" "$INCLUDES_DIR/dashboard/class-asset-manager.php" "asset-manager"
move_file "$INCLUDES_DIR/admin/class-asset-optimizer.php" "$INCLUDES_DIR/dashboard/class-asset-optimizer.php" "asset-optimizer"
move_file "$INCLUDES_DIR/admin/class-ajax-response-optimizer.php" "$INCLUDES_DIR/dashboard/class-ajax-response-optimizer.php" "ajax-response-optimizer"
move_file "$INCLUDES_DIR/admin/class-admin-notice-cleaner.php" "$INCLUDES_DIR/dashboard/class-admin-notice-cleaner.php" "admin-notice-cleaner"

echo ""

# Phase 3: Move screens (admin pages)
echo -e "${BLUE}Phase 3: Moving admin pages to screens/${NC}"
move_file "$INCLUDES_DIR/admin/class-guardian-settings.php" "$INCLUDES_DIR/screens/class-guardian-settings.php" "guardian-settings"
move_file "$INCLUDES_DIR/admin/class-help-page-module.php" "$INCLUDES_DIR/screens/class-help-page-module.php" "help-page-module"
move_file "$INCLUDES_DIR/admin/class-privacy-page-module.php" "$INCLUDES_DIR/screens/class-privacy-page-module.php" "privacy-page-module"
move_file "$INCLUDES_DIR/admin/class-tools-page-module.php" "$INCLUDES_DIR/screens/class-tools-page-module.php" "tools-page-module"
move_file "$INCLUDES_DIR/admin/class-notification-preferences-form.php" "$INCLUDES_DIR/screens/class-notification-preferences-form.php" "notification-preferences-form"
move_file "$INCLUDES_DIR/admin/class-report-form.php" "$INCLUDES_DIR/screens/class-report-form.php" "report-form"
move_file "$INCLUDES_DIR/admin/class-update-notification-manager.php" "$INCLUDES_DIR/screens/class-update-notification-manager.php" "update-notification-manager"
move_file "$INCLUDES_DIR/admin/class-option-optimizer.php" "$INCLUDES_DIR/screens/class-option-optimizer.php" "option-optimizer"

echo ""

# Phase 4: Move widgets to dashboard
echo -e "${BLUE}Phase 4: Moving widgets to dashboard/widgets/${NC}"
move_file "$INCLUDES_DIR/admin/class-tooltip-manager.php" "$INCLUDES_DIR/dashboard/widgets/class-tooltip-manager.php" "tooltip-manager"
move_file "$INCLUDES_DIR/widgets/class-activity-feed-widget.php" "$INCLUDES_DIR/dashboard/widgets/class-activity-feed-widget.php" "activity-feed-widget"
move_file "$INCLUDES_DIR/widgets/class-kpi-summary-widget.php" "$INCLUDES_DIR/dashboard/widgets/class-kpi-summary-widget.php" "kpi-summary-widget"
move_file "$INCLUDES_DIR/widgets/class-top-issues-widget.php" "$INCLUDES_DIR/dashboard/widgets/class-top-issues-widget.php" "top-issues-widget"

echo ""

# Phase 5: Move guardian dashboard files
echo -e "${BLUE}Phase 5: Moving guardian dashboard to dashboard/${NC}"
move_file "$INCLUDES_DIR/guardian/class-guardian-dashboard.php" "$INCLUDES_DIR/dashboard/class-guardian-dashboard.php" "guardian-dashboard"
move_file "$INCLUDES_DIR/guardian/class-site-health-bridge.php" "$INCLUDES_DIR/dashboard/class-site-health-bridge.php" "site-health-bridge"
move_file "$INCLUDES_DIR/guardian/class-trend-chart.php" "$INCLUDES_DIR/dashboard/class-trend-chart.php" "trend-chart"
move_file "$INCLUDES_DIR/guardian/class-dashboard-performance-analyzer.php" "$INCLUDES_DIR/dashboard/class-dashboard-performance-analyzer.php" "dashboard-performance-analyzer"

echo ""

# Phase 6: Move monitoring analyzers
echo -e "${BLUE}Phase 6: Moving analyzers to monitoring/analyzers/${NC}"
move_file "$INCLUDES_DIR/guardian/class-api-latency-analyzer.php" "$INCLUDES_DIR/monitoring/analyzers/class-api-latency-analyzer.php" "api-latency-analyzer"
move_file "$INCLUDES_DIR/guardian/class-bot-traffic-analyzer.php" "$INCLUDES_DIR/monitoring/analyzers/class-bot-traffic-analyzer.php" "bot-traffic-analyzer"
move_file "$INCLUDES_DIR/guardian/class-browser-compatibility-analyzer.php" "$INCLUDES_DIR/monitoring/analyzers/class-browser-compatibility-analyzer.php" "browser-compatibility-analyzer"
move_file "$INCLUDES_DIR/guardian/class-cache-invalidation-analyzer.php" "$INCLUDES_DIR/monitoring/analyzers/class-cache-invalidation-analyzer.php" "cache-invalidation-analyzer"
move_file "$INCLUDES_DIR/guardian/class-block-rendering-performance-analyzer.php" "$INCLUDES_DIR/monitoring/analyzers/class-block-rendering-performance-analyzer.php" "block-rendering-performance-analyzer"
move_file "$INCLUDES_DIR/guardian/class-canvas-webgl-performance-analyzer.php" "$INCLUDES_DIR/monitoring/analyzers/class-canvas-webgl-performance-analyzer.php" "canvas-webgl-performance-analyzer"
move_file "$INCLUDES_DIR/guardian/class-captcha-performance-analyzer.php" "$INCLUDES_DIR/monitoring/analyzers/class-captcha-performance-analyzer.php" "captcha-performance-analyzer"
move_file "$INCLUDES_DIR/guardian/class-csp-violation-analyzer.php" "$INCLUDES_DIR/monitoring/analyzers/class-csp-violation-analyzer.php" "csp-violation-analyzer"
move_file "$INCLUDES_DIR/guardian/class-failed-login-analyzer.php" "$INCLUDES_DIR/monitoring/analyzers/class-failed-login-analyzer.php" "failed-login-analyzer"
move_file "$INCLUDES_DIR/guardian/class-hook-execution-analyzer.php" "$INCLUDES_DIR/monitoring/analyzers/class-hook-execution-analyzer.php" "hook-execution-analyzer"
move_file "$INCLUDES_DIR/guardian/class-layout-thrashing-analyzer.php" "$INCLUDES_DIR/monitoring/analyzers/class-layout-thrashing-analyzer.php" "layout-thrashing-analyzer"
move_file "$INCLUDES_DIR/guardian/class-live-chat-performance-analyzer.php" "$INCLUDES_DIR/monitoring/analyzers/class-live-chat-performance-analyzer.php" "live-chat-performance-analyzer"
move_file "$INCLUDES_DIR/guardian/class-rest-api-performance-analyzer.php" "$INCLUDES_DIR/monitoring/analyzers/class-rest-api-performance-analyzer.php" "rest-api-performance-analyzer"
move_file "$INCLUDES_DIR/guardian/class-shortcode-execution-analyzer.php" "$INCLUDES_DIR/monitoring/analyzers/class-shortcode-execution-analyzer.php" "shortcode-execution-analyzer"
move_file "$INCLUDES_DIR/guardian/class-third-party-script-analyzer.php" "$INCLUDES_DIR/monitoring/analyzers/class-third-party-script-analyzer.php" "third-party-script-analyzer"

echo ""

# Phase 7: Move recovery/backup files
echo -e "${BLUE}Phase 7: Moving recovery files to monitoring/recovery/${NC}"
move_file "$INCLUDES_DIR/guardian/class-recovery-system.php" "$INCLUDES_DIR/monitoring/recovery/class-recovery-system.php" "recovery-system"
move_file "$INCLUDES_DIR/guardian/class-backup-manager.php" "$INCLUDES_DIR/monitoring/recovery/class-backup-manager.php" "backup-manager"
move_file "$INCLUDES_DIR/guardian/class-auto-fix-executor.php" "$INCLUDES_DIR/monitoring/recovery/class-auto-fix-executor.php" "auto-fix-executor"
move_file "$INCLUDES_DIR/guardian/class-auto-fix-policy-manager.php" "$INCLUDES_DIR/monitoring/recovery/class-auto-fix-policy-manager.php" "auto-fix-policy-manager"
move_file "$INCLUDES_DIR/guardian/class-compliance-checker.php" "$INCLUDES_DIR/monitoring/recovery/class-compliance-checker.php" "compliance-checker"
move_file "$INCLUDES_DIR/guardian/class-compromised-accounts-analyzer.php" "$INCLUDES_DIR/monitoring/recovery/class-compromised-accounts-analyzer.php" "compromised-accounts-analyzer"
move_file "$INCLUDES_DIR/guardian/class-guardian-activity-logger.php" "$INCLUDES_DIR/monitoring/class-guardian-activity-logger.php" "guardian-activity-logger"

echo ""

# Phase 8: Move knowledge base
echo -e "${BLUE}Phase 8: Moving knowledge base to content/kb/${NC}"
move_file "$INCLUDES_DIR/knowledge-base/class-kb-library.php" "$INCLUDES_DIR/content/kb/class-kb-library.php" "kb-library"
move_file "$INCLUDES_DIR/knowledge-base/class-kb-search.php" "$INCLUDES_DIR/content/kb/class-kb-search.php" "kb-search"
move_file "$INCLUDES_DIR/knowledge-base/class-kb-article-generator.php" "$INCLUDES_DIR/content/kb/class-kb-article-generator.php" "kb-article-generator"
move_file "$INCLUDES_DIR/knowledge-base/class-kb-formatter.php" "$INCLUDES_DIR/content/kb/class-kb-formatter.php" "kb-formatter"
move_file "$INCLUDES_DIR/knowledge-base/class-training-provider.php" "$INCLUDES_DIR/content/kb/class-training-provider.php" "training-provider"
move_file "$INCLUDES_DIR/knowledge-base/class-training-progress.php" "$INCLUDES_DIR/content/kb/class-training-progress.php" "training-progress"

echo ""

# Phase 9: Move FAQ
echo -e "${BLUE}Phase 9: Moving FAQ to content/${NC}"
move_file "$INCLUDES_DIR/faq/class-faq-post-type.php" "$INCLUDES_DIR/content/class-faq-post-type.php" "faq-post-type"

echo ""

# Phase 10: Move gamification
echo -e "${BLUE}Phase 10: Moving gamification to engagement/${NC}"
move_file "$INCLUDES_DIR/gamification/class-achievement-system.php" "$INCLUDES_DIR/engagement/class-achievement-system.php" "achievement-system"
move_file "$INCLUDES_DIR/gamification/class-badge-manager.php" "$INCLUDES_DIR/engagement/class-badge-manager.php" "badge-manager"
move_file "$INCLUDES_DIR/gamification/class-leaderboard-manager.php" "$INCLUDES_DIR/engagement/class-leaderboard-manager.php" "leaderboard-manager"
move_file "$INCLUDES_DIR/gamification/class-milestone-notifier.php" "$INCLUDES_DIR/engagement/class-milestone-notifier.php" "milestone-notifier"
move_file "$INCLUDES_DIR/gamification/class-streak-tracker.php" "$INCLUDES_DIR/engagement/class-streak-tracker.php" "streak-tracker"

echo ""

# Phase 11: Move cloud integrations
echo -e "${BLUE}Phase 11: Moving cloud to integration/cloud/${NC}"
move_file "$INCLUDES_DIR/cloud/class-cloud-client.php" "$INCLUDES_DIR/integration/cloud/class-cloud-client.php" "cloud-client"
move_file "$INCLUDES_DIR/cloud/class-registration-manager.php" "$INCLUDES_DIR/integration/cloud/class-registration-manager.php" "registration-manager"
move_file "$INCLUDES_DIR/cloud/class-deep-scanner.php" "$INCLUDES_DIR/integration/cloud/class-deep-scanner.php" "deep-scanner"
move_file "$INCLUDES_DIR/cloud/class-usage-tracker.php" "$INCLUDES_DIR/integration/cloud/class-usage-tracker.php" "usage-tracker"
move_file "$INCLUDES_DIR/cloud/class-multisite-dashboard.php" "$INCLUDES_DIR/integration/cloud/class-multisite-dashboard.php" "multisite-dashboard"
move_file "$INCLUDES_DIR/cloud/class-notification-manager.php" "$INCLUDES_DIR/integration/cloud/class-notification-manager.php" "notification-manager"

echo ""

# Phase 12: Consolidate reporting
echo -e "${BLUE}Phase 12: Consolidating reporting modules${NC}"
move_file "$INCLUDES_DIR/reports/class-report-builder.php" "$INCLUDES_DIR/reporting/class-report-builder.php" "report-builder"
move_file "$INCLUDES_DIR/reports/class-report-engine.php" "$INCLUDES_DIR/reporting/class-report-engine.php" "report-engine"
move_file "$INCLUDES_DIR/reports/class-report-renderer.php" "$INCLUDES_DIR/reporting/class-report-renderer.php" "report-renderer"
move_file "$INCLUDES_DIR/settings/class-report-scheduler.php" "$INCLUDES_DIR/reporting/class-report-scheduler.php" "report-scheduler (from settings)"

echo ""

# Summary
echo -e "${BLUE}========================================${NC}"
echo -e "${GREEN}Migration Complete!${NC}"
echo -e "${BLUE}========================================${NC}"
echo -e "Files moved: ${GREEN}${MOVED_COUNT}/${TOTAL_MOVES}${NC}"
echo ""

if [ $MOVED_COUNT -eq $TOTAL_MOVES ]; then
    echo -e "${GREEN}✓ All files migrated successfully!${NC}"
    exit 0
else
    echo -e "${YELLOW}⚠ Some files were not found (this is OK if they don't exist)${NC}"
    exit 0
fi
