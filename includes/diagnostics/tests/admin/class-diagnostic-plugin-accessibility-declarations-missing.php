<?php
/**
 * Plugin Accessibility Declarations Missing Diagnostic
 *
 * Checks if plugins declare accessibility support.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2310
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin Accessibility Declarations Missing Diagnostic Class
 *
 * Detects plugins lacking accessibility declarations.
 *
 * @since 1.2601.2310
 */
class Diagnostic_Plugin_Accessibility_Declarations_Missing extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'plugin-accessibility-declarations-missing';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Plugin Accessibility Declarations Missing';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if plugins support accessibility';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'admin';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2310
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$plugins = get_plugins();
		$non_accessible_plugins = 0;

		foreach ( $plugins as $plugin_file => $plugin_data ) {
			// Check if plugin is active
			if ( ! is_plugin_active( $plugin_file ) ) {
				continue;
			}

			// Check if plugin header includes accessibility-ready tag
			$plugin_headers = get_file_data( WP_PLUGIN_DIR . '/' . $plugin_file, array( 'Tags' => 'Tags' ) );
			
			if ( empty( $plugin_headers['Tags'] ) || stripos( $plugin_headers['Tags'], 'accessibility' ) === false ) {
				$non_accessible_plugins++;
			}
		}

		if ( $non_accessible_plugins > 5 ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => sprintf(
					__( '%d active plugins do not declare accessibility support. This may indicate poor accessibility implementation.', 'wpshadow' ),
					absint( $non_accessible_plugins )
				),
				'severity'      => 'low',
				'threat_level'  => 30,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/plugin-accessibility-declarations-missing',
			);
		}

		return null;
	}
}
