<?php
/**
 * Diagnostic: WP_DEBUG Status
 *
 * Checks if WP_DEBUG is enabled and recommends disabling on production.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Configuration
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Diagnostic_Wp_Debug_Status
 *
 * Tests if WP_DEBUG is appropriately configured.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Wp_Debug_Status extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'wp-debug-status';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'WP_DEBUG Status';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if WP_DEBUG is appropriately configured for the environment';

	/**
	 * Check WP_DEBUG status.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$wp_debug          = defined( 'WP_DEBUG' ) ? WP_DEBUG : false;
		$wp_debug_display  = defined( 'WP_DEBUG_DISPLAY' ) ? WP_DEBUG_DISPLAY : $wp_debug;
		$wp_debug_log      = defined( 'WP_DEBUG_LOG' ) ? WP_DEBUG_LOG : false;

		// WP_DEBUG enabled on production is a security risk.
		if ( $wp_debug ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'WP_DEBUG is enabled. On production, this exposes error messages to visitors. Disable in wp-config.php or set WP_DEBUG_DISPLAY to false and WP_DEBUG_LOG to true.', 'wpshadow' ),
				'severity'    => 'low',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/wp_debug_status',
				'meta'        => array(
					'wp_debug'         => $wp_debug,
					'wp_debug_display' => $wp_debug_display,
					'wp_debug_log'     => $wp_debug_log,
				),
			);
		}

		return null;
	}
}
