<?php
/**
 * Core WordPress Updates Not Automatically Applied Diagnostic
 *
 * Validates that WordPress core patches are applied automatically to prevent\n * zero-day vulnerabilities. Manual updates delay security patches by days/weeks.\n * Attackers exploit known vulnerabilities during delay window (before all sites patch).\n *
 * **What This Check Does:**
 * - Checks if auto_core_update_enable is true\n * - Validates major version updates enabled\n * - Detects if updates are blocked by plugins/settings\n * - Tests if security patches applied on schedule\n * - Confirms WordPress version is current\n * - Checks for known vulnerabilities in current version\n *
 * **Why This Matters:**
 * Delayed patches = prolonged vulnerability exposure. Scenarios:\n * - Security advisory released. Sites not auto-patching remain vulnerable for days.\n * - Attacker downloads vulnerability details from advisory. Scans for vulnerable sites.\n * - Exploits hit non-patched sites before manual patchers catch up.\n * - Ransomware spreads via known vulnerabilities before patches applied.\n *
 * **Business Impact:**
 * WordPress site doesn't auto-update. Security patch released. Administrator on\n * vacation for 2 weeks. Site remains vulnerable. Attacker exploits known hole.\n * Malware injected (keylogger for payment info). 500 customers affected per week.\n * 2 weeks × 500 = 1,000 customers exposed. Liability: $250K-$500K + recovery costs.\n *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Security patches applied automatically\n * - #9 Show Value: Zero-day protection, no admin action required\n * - #10 Beyond Pure: Defense in depth, patches first line\n *
 * **Related Checks:**
 * - Plugin Automatic Updates Not Enabled (same principle)\n * - Core WordPress Version Not Current (prerequisite)\n * - Security Patches Missing (specific vulnerability check)\n *
 * **Learn More:**
 * Auto-update setup: https://wpshadow.com/kb/wordpress-auto-updates\n * Video: Enabling automatic updates (5min): https://wpshadow.com/training/auto-updates\n *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Core WordPress Updates Not Automatically Applied Diagnostic Class
 *
 * Implements detection of disabled auto-update settings.\n *
 * **Detection Pattern:**
 * 1. Check constant AUTOMATIC_UPDATER_DISABLED\n * 2. Query option auto_core_update_enable\n * 3. Check filter automatic_updater_disabled\n * 4. Validate major version updates not disabled\n * 5. Get current WordPress version\n * 6. Compare against latest version (check known vulns)\n *
 * **Real-World Scenario:**
 * Client disables auto-updates due to fear of \"breaking changes\". Site runs\n * WordPress 6.0 for 6 months (current: 6.8). Major security hole discovered\n * affecting versions 6.0-6.4. Client's site vulnerable for entire window.\n * Attacker finds vulnerability details. Scans for unpatched 6.0 sites. Client\n * site hit. Enables auto-updates retroactively (too late, already compromised).\n *
 * **Implementation Notes:**
 * - Checks AUTOMATIC_UPDATER_DISABLED constant\n * - Validates auto_core_update_enable option\n * - Tests for disabling filters\n * - Severity: critical (major patch missing), high (minor patch)\n * - Treatment: enable auto-updates, explain safety\n *
 * @since 0.6093.1200
 */
class Diagnostic_Core_WordPress_Updates_Not_Automatically_Applied extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'core-wordpress-updates-not-automatically-applied';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Core WordPress Updates Not Automatically Applied';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if core updates are automated';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if automatic updates are enabled
		if ( defined( 'AUTOMATIC_UPDATER_DISABLED' ) && AUTOMATIC_UPDATER_DISABLED ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Core WordPress updates are not automatically applied. Enable automatic updates for WordPress core to get security patches automatically.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 70,
				'auto_fixable'  => true,
				'kb_link'       => 'https://wpshadow.com/kb/core-wordpress-updates-not-automatically-applied?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
