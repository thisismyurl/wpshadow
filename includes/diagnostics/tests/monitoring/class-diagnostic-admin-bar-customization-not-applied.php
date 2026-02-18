<?php
/**
 * Admin Bar Customization Not Applied Diagnostic
 *
 * Checks if admin bar is customized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6030.2352
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin Bar Customization Not Applied Diagnostic Class
 *
 * Detects uncustomized admin bar.
 *
 * @since 1.6030.2352
 */
class Diagnostic_Admin_Bar_Customization_Not_Applied extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'admin-bar-customization-not-applied';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Admin Bar Customization Not Applied';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if admin bar is customized';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'admin';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6030.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if admin bar has custom nodes
		if ( ! has_action( 'admin_bar_menu', 'wp_add_admin_bar_custom_items' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Admin bar customization is not applied. Add custom menu items to the admin bar for quick access to important features.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 10,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/admin-bar-customization-not-applied',
			);
		}

		return null;
	}
}
