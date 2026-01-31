<?php
/**
 * Admin Bar Resource Bloat Detection Diagnostic
 *
 * Checks if admin bar is loading unnecessary resources.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2320
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin Bar Resource Bloat Detection Diagnostic Class
 *
 * Detects admin bar resource bloat.
 *
 * @since 1.2601.2320
 */
class Diagnostic_Admin_Bar_Resource_Bloat_Detection extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'admin-bar-resource-bloat-detection';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Admin Bar Resource Bloat Detection';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if admin bar is loading unnecessary resources';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2320
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if admin bar is enabled for frontend
		$show_admin_bar = get_option( 'show_admin_bar_front' );

		if ( ! is_user_logged_in() && $show_admin_bar ) {
			return null; // Not showing for visitors
		}

		if ( is_user_logged_in() && ! get_user_option( 'show_admin_bar_front' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Admin bar is hidden for logged-in users on frontend. This is good for performance but may impact admin workflow.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 10,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/admin-bar-resource-bloat-detection',
			);
		}

		return null;
	}
}
