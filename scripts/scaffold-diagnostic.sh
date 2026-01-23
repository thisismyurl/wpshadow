#!/bin/bash
# WPShadow Diagnostic/Treatment Scaffolder
# Generates boilerplate stubs with philosophy, KB links, and KPI tracking

set -e

# Colors
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m'

if [ $# -lt 2 ]; then
    echo "Usage: $0 <type> <name> [description]"
    echo "  type: diagnostic or treatment"
    echo "  name: Class name (e.g., ssl-check, memory-limit)"
    echo "  description: Human-readable description"
    echo ""
    echo "Example:"
    echo "  $0 diagnostic ssl-check 'Verify SSL certificate is valid'"
    echo "  $0 treatment memory-limit 'Increase PHP memory limit to 256MB'"
    exit 1
fi

TYPE="$1"
NAME="$2"
DESC="${3:-$NAME}"

# Convert to class name
CLASS_NAME=$(echo "$NAME" | sed -r 's/(^|_)([a-z])/\U\2/g' | sed 's/-//g')

if [ "$TYPE" = "diagnostic" ]; then
    NAMESPACE="WPShadow\\Diagnostics"
    EXTENDS="Diagnostic_Base"
    CATEGORY="general"  # User can override
    KB_TOPIC=$(echo "$NAME" | tr '-' '_')

    # Guess category from name
    if echo "$NAME" | grep -q "ssl\|security\|auth\|password"; then
        CATEGORY="security"
    elif echo "$NAME" | grep -q "memory\|cache\|performance\|speed"; then
        CATEGORY="performance"
    elif echo "$NAME" | grep -q "seo\|meta\|sitemap"; then
        CATEGORY="seo"
    fi

    DIR="/workspaces/wpshadow/includes/diagnostics/tests"
    FILENAME="class-test-${CATEGORY}-${NAME}.php"

    CONTENT="<?php
declare(strict_types=1);

namespace WPShadow\\Diagnostics;

use WPShadow\\Core\\Diagnostic_Base;

/**
 * Diagnostic: $DESC
 *
 * Philosophy: [TODO] Which commandments does this serve?
 * Examples:
 *   - Commandment #1 (Helpful Neighbor): Educate users why this matters
 *   - Commandment #8 (Inspire Confidence): Explain what we're checking and why
 *   - Commandment #9 (Show Value): Will track KPIs when users act on findings
 *
 * KB Article: https://wpshadow.com/kb/$KB_TOPIC
 * Training Video: https://wpshadow.com/training/$KB_TOPIC
 *
 * @todo Implement run() method - check $DESC
 * @todo Add KPI tracking when findings are resolved
 * @todo Link to specific KB section if multi-part article
 */
class Test_${CATEGORY^}_${CLASS_NAME} extends Diagnostic_Base {

    /**
     * Diagnostic ID
     */
    protected static string \$id = 'test-$CATEGORY-$NAME';

    /**
     * Category for grouping
     */
    protected static string \$category = '$CATEGORY';

    /**
     * Run the diagnostic check
     *
     * @return array Found issues (empty = healthy)
     */
    public static function run(): array {
        // TODO: Implement the check
        // Examples:
        //   - Query WordPress options/settings
        //   - Check server configuration
        //   - Validate security headers
        //   - Analyze plugin/theme data

        // Return format:
        // [
        //     'severity' => 'critical|warning|info',
        //     'message' => 'What we found',
        //     'recommendation' => 'How to fix it',
        //     'learning_link' => 'https://wpshadow.com/kb/...',
        // ]

        return [];
    }

    /**
     * Get display name (plain English, no jargon)
     */
    public static function get_name(): string {
        return __('$DESC', 'wpshadow');
    }

    /**
     * Get description with KB link (educational, helpful)
     */
    public static function get_description(): string {
        return sprintf(
            __('$DESC. <a href=\"%s\" target=\"_blank\">Learn why this matters</a>', 'wpshadow'),
            'https://wpshadow.com/kb/$KB_TOPIC'
        );
    }
}
"

elif [ "$TYPE" = "treatment" ]; then
    NAMESPACE="WPShadow\\Treatments"
    EXTENDS="Treatment_Base"
    CATEGORY="general"
    KB_TOPIC=$(echo "$NAME" | tr '-' '_')

    # Guess category from name
    if echo "$NAME" | grep -q "ssl\|security\|auth\|password"; then
        CATEGORY="security"
    elif echo "$NAME" | grep -q "memory\|cache\|performance\|speed"; then
        CATEGORY="performance"
    fi

    DIR="/workspaces/wpshadow/includes/treatments/tests"
    FILENAME="class-test-${CATEGORY}-${NAME}.php"

    CONTENT="<?php
declare(strict_types=1);

namespace WPShadow\\Treatments;

use WPShadow\\Core\\Treatment_Base;
use WPShadow\\Core\\KPI_Tracker;

/**
 * Treatment: $DESC
 *
 * Philosophy:
 *   - Commandment #2 (Free as Possible): All local treatments free forever
 *   - Commandment #7 (Ridiculously Good): Safe auto-fix with full undo capability
 *   - Commandment #9 (Show Value): Tracks time saved (estimate in minutes)
 *
 * Reversibility: 100% safe - full backup and undo capability
 *
 * KB Article: https://wpshadow.com/kb/$KB_TOPIC
 * Training Video: https://wpshadow.com/training/$KB_TOPIC
 *
 * @todo Implement apply() method
 * @todo Implement undo() method
 * @todo Test backup/restore cycle
 */
class Test_${CATEGORY^}_${CLASS_NAME} extends Treatment_Base {

    /**
     * Treatment ID
     */
    protected static string \$id = 'test-$CATEGORY-$NAME';

    /**
     * Category for grouping
     */
    protected static string \$category = '$CATEGORY';

    /**
     * Apply the treatment (fix the issue)
     *
     * @return bool Success status
     */
    public static function apply(): bool {
        // TODO: Implement the fix
        // Remember: This must be completely reversible

        // 1. Create backup if needed
        // \$backup = self::create_backup();

        // 2. Apply the fix
        // \$result = update_option('setting', 'value');

        // 3. Track KPI - how many minutes did we save? (estimate)
        // if (\$result) {
        //     KPI_Tracker::record_treatment_applied(__CLASS__, 5); // 5 minutes
        // }

        return false; // TODO: Change to true when implemented
    }

    /**
     * Undo the treatment (restore original state)
     *
     * CRITICAL: This must work perfectly or users lose trust
     *
     * @return bool Success status
     */
    public static function undo(): bool {
        // TODO: Implement the undo
        // Restore from backup or reverse the change

        return false; // TODO: Change to true when implemented
    }

    /**
     * Get display name (plain English, no jargon)
     */
    public static function get_name(): string {
        return __('$DESC', 'wpshadow');
    }

    /**
     * Get description with KB/training links
     *
     * This educates users WHY they should apply this fix
     */
    public static function get_description(): string {
        return sprintf(
            __('$DESC. <a href=\"%s\" target=\"_blank\">Learn more</a> | <a href=\"%s\" target=\"_blank\">Watch training</a>', 'wpshadow'),
            'https://wpshadow.com/kb/$KB_TOPIC',
            'https://wpshadow.com/training/$KB_TOPIC'
        );
    }
}
"

else
    echo "Error: type must be 'diagnostic' or 'treatment'"
    exit 1
fi

# Create directory if needed
mkdir -p "$DIR"

# Create file
FULL_PATH="$DIR/$FILENAME"

if [ -f "$FULL_PATH" ]; then
    echo -e "${YELLOW}⚠️  File already exists: $FULL_PATH${NC}"
    exit 1
fi

echo "$CONTENT" > "$FULL_PATH"

echo -e "${GREEN}✅ Created: $FULL_PATH${NC}"
echo ""
echo -e "${BLUE}Next steps:${NC}"
echo "1. Edit the file and implement TODO items"
echo "2. Add KB_TOPIC to .kb-index.json with link"
echo "3. For treatments: Implement apply() + undo() + test reversibility"
echo "4. Run: composer phpcs && composer phpstan"
echo "5. Commit with: feat: add $TYPE '$NAME'"
echo ""
echo -e "${YELLOW}Philosophy Reminders:${NC}"
echo "- Keep descriptions in plain English (no jargon)"
echo "- Always link to KB article for education"
echo "- Track KPIs (treatments: estimate minutes saved)"
echo "- Ensure treatments are 100% reversible"
echo ""
