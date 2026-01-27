<?php
/**
 * Diagnostic: WP_DEBUG_DISPLAY Status
 *
 * Checks if WP_DEBUG_DISPLAY is showing errors to users on production.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Security
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Diagnostic_Wp_Debug_Display_Status
 *
 * Tests if debug output is being displayed to users.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Wp_Debug_Display_Status extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'wp-debug-display-status';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'WP_DEBUG_DISPLAY Status';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if debug messages are displayed to site visitors';

	/**
	 * Check WP_DEBUG_DISPLAY status.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$wp_debug         = defined( 'WP_DEBUG' ) ? WP_DEBUG : false;
		$wp_debug_display = defined( 'WP_DEBUG_DISPLAY' ) ? WP_DEBUG_DISPLAY : $wp_debug;

		if ( $wp_debug && $wp_debug_display ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'WP_DEBUG_DISPLAY is enabled and will show error messages to site visitors. This exposes sensitive information. Disable in wp-config.php or set to false on production.', 'wpshadow' ),
				'severity'    => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/wp_debug_display_status',
				'meta'        => array(
					'wp_debug'         => $wp_debug,
					'wp_debug_display' => $wp_debug_display,
				),
			);
		}

		return null;
	}
}
