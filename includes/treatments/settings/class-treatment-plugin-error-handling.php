<?php
/**
 * Plugin Error Handling Treatment
 *
 * Checks for missing error handling around remote requests.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.5049.1345
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin Error Handling Treatment Class
 *
 * Detects active plugins that may call remote APIs without checking for WP_Error.
 *
 * @since 1.5049.1345
 */
class Treatment_Plugin_Error_Handling extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'plugin-error-handling';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Plugin Error Handling';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if plugins handle remote request errors';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'plugins';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.5049.1345
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
