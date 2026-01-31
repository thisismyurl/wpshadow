<?php
/**
 * Admin Toolbar Customization Not Implemented Diagnostic
 *
 * Checks if admin toolbar is customized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2352
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin Toolbar Customization Not Implemented Diagnostic Class
 *
 * Detects unoptimized admin toolbar.
 *
 * @since 1.2601.2352
 */
class Diagnostic_Admin_Toolbar_Customization_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'admin-toolbar-customization-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Admin Toolbar Customization Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if admin toolbar is customized';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if toolbar customization filter exists
		if ( ! has_filter( 'wp_before_admin_bar_render' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Admin toolbar is not customized. Customize the toolbar to improve admin user experience and remove unnecessary items.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 15,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/admin-toolbar-customization-not-implemented',
			);
		}

		return null;
	}
}
