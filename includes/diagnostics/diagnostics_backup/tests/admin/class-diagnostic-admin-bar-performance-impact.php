<?php
/**
 * Admin Bar Performance Impact Diagnostic
 *
 * Checks if admin bar is optimized.
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
 * Admin Bar Performance Impact Diagnostic Class
 *
 * Detects performance issues with admin bar.
 *
 * @since 1.2601.2310
 */
class Diagnostic_Admin_Bar_Performance_Impact extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'admin-bar-performance-impact';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Admin Bar Performance Impact';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if admin bar is affecting performance';

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
		// Check if admin bar is showing to logged-out users
		if ( is_admin_bar_showing() && function_exists( 'is_user_logged_in' ) && ! is_user_logged_in() ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Admin bar is being shown to non-logged-in users. This increases page rendering time without benefit.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 15,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/admin-bar-performance-impact',
			);
		}

		// Check how many filters are attached to admin bar
		global $wp_filter;
		if ( isset( $wp_filter['admin_bar_menu'] ) ) {
			$menu_filters = count( $wp_filter['admin_bar_menu'] );
			if ( $menu_filters > 20 ) {
				return array(
					'id'            => self::$slug,
					'title'         => self::$title,
					'description'   => sprintf(
						__( 'Admin bar has %d filters attached. Too many admin bar modifications can slow down frontend rendering.', 'wpshadow' ),
						absint( $menu_filters )
					),
					'severity'      => 'medium',
					'threat_level'  => 35,
					'auto_fixable'  => false,
					'kb_link'       => 'https://wpshadow.com/kb/admin-bar-performance-impact',
				);
			}
		}

		return null;
	}
}
