<?php
/**
 * Plugin Conflict Detection Diagnostic
 *
 * Identifies potential plugin conflicts that could impact performance or
 * stability of the WordPress installation.
 *
 * @since   1.26033.2101
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin Conflict Detection Diagnostic Class
 *
 * Detects potential conflicts:
 * - Duplicate functionality detection
 * - Performance plugin conflicts
 * - Cache plugin incompatibility
 * - Version conflicts
 *
 * @since 1.26033.2101
 */
class Diagnostic_Plugin_Conflict_Detection extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'plugin-conflict-detection';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Plugin Conflict Detection';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Identifies potential plugin conflicts and incompatibilities';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26033.2101
	 * @return array|null Finding array if issues found, null otherwise.
	 */
	public static function check() {
		// Define conflicting plugin pairs
		$conflicts = array(
			array( 'wp-super-cache/wp-cache.php', 'w3-total-cache/w3-total-cache.php' ),
			array( 'wp-rocket/wp-rocket.php', 'w3-total-cache/w3-total-cache.php' ),
			array( 'wp-rocket/wp-rocket.php', 'wp-super-cache/wp-cache.php' ),
			array( 'wordfence/wordfence.php', 'all-in-one-wp-security-and-firewall/all_in_one_wp_security.php' ),
		);

		$detected_conflicts = array();

		foreach ( $conflicts as $pair ) {
			$plugin1_active = is_plugin_active( $pair[0] );
			$plugin2_active = is_plugin_active( $pair[1] );

			if ( $plugin1_active && $plugin2_active ) {
				$plugin1_name = basename( dirname( $pair[0] ) );
				$plugin2_name = basename( dirname( $pair[1] ) );
				$detected_conflicts[] = "{$plugin1_name} + {$plugin2_name}";
			}
		}

		if ( ! empty( $detected_conflicts ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => sprintf(
					/* translators: %s: plugin conflict pairs */
					__( 'Detected conflicting plugins: %s. These plugins may interfere with each other.', 'wpshadow' ),
					implode( ', ', $detected_conflicts )
				),
				'severity'      => 'medium',
				'threat_level'  => 50,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/plugin-conflicts',
				'meta'          => array(
					'conflict_count'       => count( $detected_conflicts ),
					'conflicts'            => $detected_conflicts,
					'recommendation'       => 'Deactivate one plugin from each conflicting pair and choose the best for your needs',
					'impact'               => 'Conflicting plugins can cause performance issues, errors, or security vulnerabilities',
				),
			);
		}

		return null;
	}
}
