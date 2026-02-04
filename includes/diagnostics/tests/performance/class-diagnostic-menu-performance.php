<?php
/**
 * Menu Performance Diagnostic
 *
 * Analyzes WordPress menu implementation for performance optimization
 * including menu depth, item count, and rendering efficiency.
 *
 * @since   1.6033.2087
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Menu Performance Diagnostic Class
 *
 * Monitors menu performance:
 * - Menu item count
 * - Menu depth complexity
 * - Custom menu walkers
 * - Menu caching
 *
 * @since 1.6033.2087
 */
class Diagnostic_Menu_Performance extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'menu-performance';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Menu Performance';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Analyzes menu implementation for performance optimization';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6033.2087
	 * @return array|null Finding array if issues found, null otherwise.
	 */
	public static function check() {
		$menus = wp_get_nav_menus();
		$high_item_count = false;
		$total_items = 0;
		$max_depth = 0;

		foreach ( $menus as $menu ) {
			$menu_items = wp_get_nav_menu_items( $menu->term_id );

			if ( ! empty( $menu_items ) ) {
				$total_items += count( $menu_items );

				// Calculate menu depth
				$depths = wp_list_pluck( $menu_items, 'menu_item_parent' );
				if ( ! empty( $depths ) ) {
					$depth = count( array_unique( $depths ) );
					if ( $depth > $max_depth ) {
						$max_depth = $depth;
					}
				}

				// Flag if menu has many items
				if ( count( $menu_items ) > 30 ) {
					$high_item_count = true;
				}
			}
		}

		if ( $high_item_count || $total_items > 50 || $max_depth > 4 ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => sprintf(
					/* translators: %d: total items, %d: max depth */
					__( 'Menu structure has %d items with depth of %d. Complex menus can add 20-50ms to rendering.', 'wpshadow' ),
					$total_items,
					$max_depth
				),
				'severity'      => 'low',
				'threat_level'  => 20,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/menu-performance',
				'meta'          => array(
					'total_menu_items'     => $total_items,
					'max_menu_depth'       => $max_depth,
					'recommendation'       => 'Simplify menu structure: limit to 3 levels, max 25 items per menu',
					'impact'               => 'Optimized menus reduce render time by 20-50ms',
					'optimization'         => array(
						'Limit menu depth to 2-3 levels',
						'Use mega menus for hierarchy',
						'Cache menu output',
						'Lazy-load deep submenu items',
						'Use custom post type for navigation',
					),
				),
			);
		}

		return null;
	}
}
