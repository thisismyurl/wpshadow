<?php
/**
 * Plugin Conflict Detection Diagnostic
 *
 * Issue #4946: No Plugin Conflict Detection
 * Pillar: ⚙️ Murphy's Law / #8: Inspire Confidence
 *
 * Checks for common plugin conflicts.
 * Multiple plugins modifying same hooks cause failures.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6050.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Plugin_Conflict_Detection Class
 *
 * @since 1.6050.0000
 */
class Diagnostic_Plugin_Conflict_Detection extends Diagnostic_Base {

	protected static $slug = 'plugin-conflict-detection';
	protected static $title = 'No Plugin Conflict Detection';
	protected static $description = 'Checks for known plugin compatibility issues';
	protected static $family = 'reliability';

	public static function check() {
		$issues = array();

		// Check for multiple caching plugins
		$caching_plugins = array(
			'wp-super-cache/wp-cache.php',
			'w3-total-cache/w3-total-cache.php',
			'wp-fastest-cache/wpFastestCache.php',
			'cache-enabler/cache-enabler.php',
		);

		$active_caching = 0;
		foreach ( $caching_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_caching++;
			}
		}

		if ( $active_caching > 1 ) {
			$issues[] = sprintf(
				/* translators: %d: number of caching plugins */
				__( '%d caching plugins active (conflicts likely)', 'wpshadow' ),
				$active_caching
			);
		}

		// Check for multiple security plugins
		$security_plugins = array(
			'wordfence/wordfence.php',
			'all-in-one-wp-security-and-firewall/wp-security.php',
			'better-wp-security/better-wp-security.php',
		);

		$active_security = 0;
		foreach ( $security_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_security++;
			}
		}

		if ( $active_security > 1 ) {
			$issues[] = sprintf(
				/* translators: %d: number of security plugins */
				__( '%d security plugins active (conflicts likely)', 'wpshadow' ),
				$active_security
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Multiple plugins doing the same job (caching, security, SEO) often conflict. Use one plugin per function for stability.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/plugin-conflicts',
				'details'      => array(
					'conflicts'               => $issues,
					'recommendation'          => 'Deactivate all but one plugin in each category',
					'testing'                 => 'Test site after deactivating to confirm stability',
				),
			);
		}

		return null;
	}
}
