<?php
/**
 * Menu Performance Not Optimized Diagnostic
 *
 * Checks if menus are optimized for performance.
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
 * Menu Performance Not Optimized Diagnostic Class
 *
 * Detects menu performance issues.
 *
 * @since 1.2601.2310
 */
class Diagnostic_Menu_Performance_Not_Optimized extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'menu-performance-not-optimized';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Menu Performance Not Optimized';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if menus are optimized';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2310
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		// Count menu items
		$menu_items = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'nav_menu_item'"
		);

		if ( $menu_items > 200 ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => sprintf(
					__( 'Site has %d menu items. Excessive menu items slow down page rendering and navigation queries.', 'wpshadow' ),
					absint( $menu_items )
				),
				'severity'      => 'low',
				'threat_level'  => 30,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/menu-performance-not-optimized',
			);
		}

		return null;
	}
}
