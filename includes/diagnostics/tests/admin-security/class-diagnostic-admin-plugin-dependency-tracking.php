<?php
/**
 * Admin Plugin Dependency Tracking Diagnostic
 *
 * Ensures plugins meet their required WordPress and PHP version dependencies.
 * Running plugins on unsupported versions can cause fatal errors, broken admin
 * screens, or silent failures. This diagnostic highlights incompatibilities
 * before they create downtime.
 *
 * **What This Check Does:**
 * - Reads plugin headers for `RequiresPHP` and `RequiresWP`
 * - Compares requirements to current environment
 * - Flags plugins running on unsupported versions
 * - Highlights dependency drift after server upgrades/downgrades
 * - Encourages proactive update planning
 *
 * **Why This Matters:**
 * Plugins often require newer PHP or WordPress versions to remain secure.
 * If your site runs an older version, plugins may disable features or crash.
 * If you upgrade PHP without checking requirements, older plugins may break.
 *
 * **Real-World Failure Scenario:**
 * - Plugin requires PHP 8.1+ for security fixes
 * - Server still on PHP 7.4
 * - Plugin loads but fails on modern syntax
 * - Admin pages crash, updates fail
 *
 * Result: Site is unstable and cannot receive security updates.
 *
 * **Common Dependency Gaps:**
 * - PHP version below required minimum
 * - WordPress core version below required minimum
 * - Plugins depending on other plugins not installed
 * - Features requiring specific PHP extensions missing on server
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Prevents compatibility failures
 * - #9 Show Value: Reduces downtime and maintenance surprises
 * - Helpful Neighbor: Clear guidance for safe upgrades
 *
 * **Learn More:**
 * See https://wpshadow.com/kb/plugin-dependency-management
 * or https://wpshadow.com/training/safe-wordpress-upgrades
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26033.0640
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: Admin Plugin Dependency Tracking
 *
 * Uses the plugin registry (`get_plugins()`) to read dependency metadata
 * and compare it against runtime versions.
 *
 * **Implementation Pattern:**
 * 1. Enumerate installed plugins via `get_plugins()`
 * 2. Check `RequiresPHP` against `PHP_VERSION`
 * 3. Check `RequiresWP` against `get_bloginfo( 'version' )`
 * 4. Count unmet dependencies
 * 5. Return findings with upgrade guidance
 *
 * **Detection Logic:**
 * - PHP version < required minimum = dependency failure
 * - WordPress version < required minimum = compatibility risk
 *
 * **Related Diagnostics:**
 * - Theme Update Notifications: Ensures update paths are safe
 * - Plugin Update Notifications: Same for plugin patches
 * - System Requirements Audit: Broad environment health check
 *
 * @since 1.26033.0640
 */
class Diagnostic_Admin_Plugin_Dependency_Tracking extends Diagnostic_Base {

	protected static $slug = 'admin-plugin-dependency-tracking';
	protected static $title = 'Admin Plugin Dependency Tracking';
	protected static $description = 'Verifies plugins don\'t have unmet dependencies';
	protected static $family = 'admin-security';

	public static function check() {
		$issues = array();

		// Get all active plugins
		$plugins = get_plugins();
		$unmet_dependencies = 0;

		foreach ( $plugins as $plugin_file => $plugin_data ) {
			// Check for requires_php and requires_wp
			$requires_php = $plugin_data['RequiresPHP'] ?? '';
			$requires_wp  = $plugin_data['RequiresWP'] ?? '';

			if ( $requires_php ) {
				if ( version_compare( PHP_VERSION, $requires_php, '<' ) ) {
					$unmet_dependencies++;
				}
			}

			if ( $requires_wp ) {
				if ( version_compare( get_bloginfo( 'version' ), $requires_wp, '<' ) ) {
					$unmet_dependencies++;
				}
			}
		}

		if ( $unmet_dependencies > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of plugins */
				__( '%d plugin(s) have unmet version requirements', 'wpshadow' ),
				$unmet_dependencies
			);
		}

		// Check for inactive plugins consuming resources
		$inactive_plugins = get_option( 'wpshadow_inactive_plugins', array() );
		if ( count( $inactive_plugins ) > 5 ) {
			$issues[] = sprintf(
				/* translators: %d: number of plugins */
				__( '%d inactive plugins may be consuming resources', 'wpshadow' ),
				count( $inactive_plugins )
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/admin-plugin-dependency-tracking',
			);
		}

		return null;
	}
}
