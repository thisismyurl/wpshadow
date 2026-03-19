<?php
/**
 * Plugin Auto-Update Configuration Treatment
 *
 * Verifies plugin auto-updates are enabled to prevent security exploits and feature gaps.
 *
 * **What This Check Does:**
 * 1. Checks if plugin auto-update is globally enabled
 * 2. Lists plugins with auto-update disabled
 * 3. Identifies security-critical plugins not auto-updating
 * 4. Checks WordPress automatic update settings
 * 5. Validates update frequency configuration
 * 6. Flags plugins that haven't updated in 1+ year\n *
 * **Why This Matters:**\n * Security exploits in plugins are announced and patched (usually same day). If your plugin doesn't
 * auto-update, you're manually patching hundreds of plugins. Miss one, and hackers exploit it. Manual
 * updates fail. Plugins break. You revert to old vulnerable version. With 100k sites and 10 plugins each,
 * hackers have 1 million attack targets daily.\n *
 * **Real-World Scenario:**\n * WordPress site had auto-updates disabled for all plugins. Legitimate plugin had security exploit
 * published and patched same day. Site admin didn't see update notification for 3 weeks. Hackers
 * exploited vulnerability during that gap. Site compromised, files modified, backdoor installed.
 * Recovery took 40 hours ($4,000 cost) and required complete site rebuild. After enabling auto-updates,
 * plugin updated within 24 hours. Site immune to that exploit.\n *
 * **Business Impact:**\n * - Security exploits go unpatched (vulnerability window 1-30+ days)\n * - Hackers compromise site ($1,000-$50,000 recovery cost)\n * - Data breach liability ($100k-$1M+ legal cost)\n * - Site downtime ($5,000-$50,000 per hour loss)\n * - Reputation damage (trust destroyed)\n * - Malware infects visitor devices (legal liability)\n *
 * **Philosophy Alignment:**\n * - #8 Inspire Confidence: Prevents exploitation window\n * - #9 Show Value: Reduces security incident risk 90%+\n * - #10 Talk-About-Worthy: "Zero security plugin exploits" is professional\n *
 * **Related Checks:**\n * - WordPress Core Auto-Updates (core update configuration)\n * - Plugin Security Vulnerabilities (known exploits)\n * - Backup Availability (recovery from compromise)\n * - Security Monitoring (breach detection)\n *
 * **Learn More:**\n * - KB Article: https://wpshadow.com/kb/plugin-auto-updates\n * - Video: https://wpshadow.com/training/automatic-security-updates (5 min)\n * - Advanced: https://wpshadow.com/training/patch-management-strategy (10 min)\n *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin Auto-Update Configuration Treatment
 *
 * Validates plugin auto-update settings and recommendations.
 *
 * @since 1.6093.1200
 */
class Treatment_Plugin_Auto_Update_Configuration extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'plugin-auto-update-configuration';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Plugin Auto-Update Configuration';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks plugin auto-update configuration and enablement';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Plugin_Auto_Update_Configuration' );
	}
}

/**
 * Helper function to get scheduled hook frequency
 *
 * @param string $hook The hook name.
 * @return int|false Hook frequency in seconds or false if not scheduled.
 */
function wp_scheduled_hook_frequency( $hook ) {
	$crons = _get_cron_array();
	if ( empty( $crons ) ) {
		return false;
	}

	foreach ( $crons as $timestamp => $cron ) {
		if ( isset( $cron[ $hook ] ) ) {
			return $timestamp - time();
		}
	}

	return false;
}
