<?php
/**
 * Plugin Error Handling Diagnostic
 *
 * Checks for missing error handling around remote requests.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin Error Handling Diagnostic Class
 *
 * Detects active plugins that may call remote APIs without checking for WP_Error.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Plugin_Error_Handling extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'plugin-error-handling';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Plugin Error Handling';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if plugins handle remote request errors';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'plugins';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$active_plugins = get_option( 'active_plugins', array() );
		$all_plugins = get_plugins();
		$missing_error_checks = array();

		foreach ( $active_plugins as $plugin_file ) {
			$path = WP_PLUGIN_DIR . '/' . $plugin_file;
			if ( ! file_exists( $path ) ) {
				continue;
			}

			$content = file_get_contents( $path, false, null, 0, 60000 );
			if ( false === $content ) {
				continue;
			}

			if ( preg_match( '/wp_remote_(get|post|request)\s*\(/i', $content ) && ! preg_match( '/is_wp_error\s*\(/i', $content ) ) {
				$missing_error_checks[] = $all_plugins[ $plugin_file ]['Name'] ?? $plugin_file;
			}
		}

		if ( ! empty( $missing_error_checks ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Some plugins make remote requests without checking for errors. This can cause failures or missing fallback behavior.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 35,
				'auto_fixable' => false,
				'details'      => array(
					'plugins' => array_slice( $missing_error_checks, 0, 10 ),
				),
				'kb_link'      => 'https://wpshadow.com/kb/plugin-error-handling',
			);
		}

		return null;
	}
}
