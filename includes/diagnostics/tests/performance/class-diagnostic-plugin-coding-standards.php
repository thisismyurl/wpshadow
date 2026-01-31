<?php
/**
 * Plugin Coding Standards Compliance Diagnostic
 *
 * Checks if plugins follow WordPress coding standards.
 *
 * @since   1.4031.1939
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Plugin_Coding_Standards Class
 *
 * Detects plugins that violate WordPress coding standards.
 */
class Diagnostic_Plugin_Coding_Standards extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'plugin-coding-standards';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Plugin Coding Standards';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks plugins for WordPress coding standards compliance';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.4031.1939
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$violations = array();

		// Get active plugins
		$active_plugins = get_option( 'active_plugins', array() );

		if ( empty( $active_plugins ) ) {
			return null;
		}

		$plugins_dir = WP_PLUGIN_DIR;

		foreach ( $active_plugins as $plugin ) {
			$plugin_file = $plugins_dir . '/' . $plugin;

			if ( ! file_exists( $plugin_file ) ) {
				continue;
			}

			$content = file_get_contents( $plugin_file );

			// Check for direct output (not using functions)
			if ( preg_match( '/<\?php\s*echo\s+["\']/', $content ) ) {
				$violations[] = sprintf(
					/* translators: %s: plugin file */
					__( '%s: Direct output without proper escaping detected.', 'wpshadow' ),
					basename( dirname( $plugin_file ) )
				);
			}

			// Check for empty sanitization/validation
			if ( preg_match( '/\$_(?:GET|POST|REQUEST)\[/', $content ) && ! preg_match( '/sanitize_/', $content ) ) {
				$violations[] = sprintf(
					/* translators: %s: plugin name */
					__( '%s: Accesses $_GET/POST without sanitization.', 'wpshadow' ),
					basename( dirname( $plugin_file ) )
				);
			}

			// Check for empty() on arrays (risky)
			if ( preg_match( '/empty\(\$[a-zA-Z_]+\[\]?\)/', $content ) && preg_match( '/\$\w+\s*=\s*array/', $content ) ) {
				$violations[] = sprintf(
					/* translators: %s: plugin name */
					__( '%s: Uses empty() on arrays which may have unexpected results.', 'wpshadow' ),
					basename( dirname( $plugin_file ) )
				);
			}
		}

		if ( ! empty( $violations ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: violation count, %s: details */
					__( '%d plugins have coding standard violations: %s', 'wpshadow' ),
					count( $violations ),
					implode( ' ', array_slice( $violations, 0, 5 ) )
				),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'details'      => array(
					'violations' => $violations,
				),
				'kb_link'      => 'https://wpshadow.com/kb/plugin-coding-standards',
			);
		}

		return null;
	}
}
