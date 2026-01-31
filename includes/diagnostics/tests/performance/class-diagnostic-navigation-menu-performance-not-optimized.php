<?php
/**
 * Navigation Menu Performance Not Optimized Diagnostic
 *
 * Checks if navigation menus are optimized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2340
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Navigation Menu Performance Not Optimized Diagnostic Class
 *
 * Detects unoptimized menus.
 *
 * @since 1.2601.2340
 */
class Diagnostic_Navigation_Menu_Performance_Not_Optimized extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'navigation-menu-performance-not-optimized';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Navigation Menu Performance Not Optimized';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if navigation menus are optimized';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2340
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Get registered menus
		$menus = get_registered_nav_menus();

		if ( ! empty( $menus ) ) {
			foreach ( $menus as $location => $label ) {
				$menu = wp_get_nav_menu_object( $location );
				if ( $menu && isset( $menu->count ) && $menu->count > 50 ) {
					return array(
						'id'            => self::$slug,
						'title'         => self::$title,
						'description'   => sprintf(
							__( 'Navigation menu "%s" has %d items. Large menus may impact performance. Consider using mega menus or dropdown optimization.', 'wpshadow' ),
							$label,
							$menu->count
						),
						'severity'      => 'low',
						'threat_level'  => 20,
						'auto_fixable'  => false,
						'kb_link'       => 'https://wpshadow.com/kb/navigation-menu-performance-not-optimized',
					);
				}
			}
		}

		return null;
	}
}
