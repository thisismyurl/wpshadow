<?php
/**
 * Excessive Menu Depth Diagnostic
 *
 * Detects overly complex navigation menus with too many levels that
 * confuse users and make important pages hard to find.
 *
 * @since   1.2601.2148
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Excessive_Menu_Depth Class
 *
 * Detects overly complex navigation structures.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Excessive_Menu_Depth extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'excessive-menu-depth';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Excessive Menu Depth';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects overly complex navigation menus';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'user-experience';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if excessive depth found, null otherwise.
	 */
	public static function check() {
		$menu_analysis = self::analyze_menu_structure();

		if ( $menu_analysis['max_depth'] <= 2 ) {
			return null; // Menu depth acceptable
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: menu depth levels */
				__( 'Navigation menu has %d levels deep. Users abandon sites after 3 clicks (3-click rule). Each extra level = 25%% visitor loss.', 'wpshadow' ),
				$menu_analysis['max_depth']
			),
			'severity'     => 'medium',
			'threat_level' => min( 80, 40 + ( $menu_analysis['max_depth'] * 10 ) ),
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/navigation-structure',
			'family'       => self::$family,
			'meta'         => array(
				'max_depth'       => $menu_analysis['max_depth'],
				'recommended_depth' => 2,
				'total_items'     => $menu_analysis['total_items'],
				'three_click_rule' => __( 'Users expect any page in 3 clicks' ),
			),
			'details'      => array(
				'why_menu_depth_matters'  => array(
					__( '3-click rule: Users expect any page within 3 clicks' ),
					__( 'Cognitive load: Deep menus = information overwhelm' ),
					__( 'Mobile difficulty: Nested dropdowns hard on touch' ),
					__( 'SEO: Deep pages get less crawl frequency' ),
				),
				'menu_depth_guidelines'   => array(
					'1 level (Flat)' => 'Best for small sites (5-10 pages)',
					'2 levels (Standard)' => 'Ideal for most sites (10-50 pages)',
					'3 levels (Complex)' => 'Ok for large sites but test usability',
					'4+ levels (Too Deep)' => 'Confusing, restructure navigation',
				),
				'navigation_best_practices' => array(
					'Top-Level Items' => array(
						'Limit to 5-7 items (Miller\'s Law)',
						'Most important pages only',
						'Clear, descriptive labels',
					),
					'Sub-Items' => array(
						'Max 10 items per dropdown',
						'Group related pages',
						'Consider mega menu if 10+ items',
					),
					'Mobile Considerations' => array(
						'Hamburger menu ok for mobile',
						'Accordion-style sub-menus',
						'Large tap targets (48px)',
					),
				),
				'restructuring_deep_menus' => array(
					'Option 1: Flatten Hierarchy' => array(
						'Promote important sub-pages to top level',
						'Reduce from 3-4 levels to 2 levels',
						'Use breadcrumbs for context',
					),
					'Option 2: Use Mega Menu' => array(
						'Multi-column dropdown',
						'Shows all sub-pages at once',
						'Better than nested dropdowns',
						'Plugins: Max Mega Menu (free)',
					),
					'Option 3: Footer Menu' => array(
						'Move tertiary pages to footer',
						'Header: Primary navigation only',
						'Footer: Comprehensive sitemap',
					),
					'Option 4: Consolidate Pages' => array(
						'Merge similar thin pages',
						'Create comprehensive parent pages',
						'Use anchor links for sections',
					),
				),
				'navigation_testing'      => array(
					__( 'Test: Can you reach any page in 3 clicks?' ),
					__( 'Ask 3 people to find specific pages' ),
					__( 'Use heatmaps (Hotjar, Crazy Egg) to see click patterns' ),
					__( 'Google Analytics: Analyze navigation clicks' ),
					__( 'Mobile test: Can you navigate on phone easily?' ),
				),
			),
		);
	}

	/**
	 * Analyze menu structure depth.
	 *
	 * @since  1.2601.2148
	 * @return array Menu structure analysis.
	 */
	private static function analyze_menu_structure() {
		$locations = get_nav_menu_locations();
		$max_depth = 0;
		$total_items = 0;

		// Check primary menu location
		if ( isset( $locations['primary'] ) || isset( $locations['main'] ) || isset( $locations['header'] ) ) {
			$menu_id = $locations['primary'] ?? $locations['main'] ?? $locations['header'] ?? 0;

			if ( $menu_id ) {
				$menu_items = wp_get_nav_menu_items( $menu_id );

				if ( $menu_items ) {
					$total_items = count( $menu_items );

					// Calculate max depth by checking menu_item_parent chain
					foreach ( $menu_items as $item ) {
						$depth = self::calculate_item_depth( $item, $menu_items );
						$max_depth = max( $max_depth, $depth );
					}
				}
			}
		}

		// If no structured menu, estimate from pages
		if ( $max_depth === 0 ) {
			$pages = get_pages( array( 'number' => 100 ) );
			$total_items = count( $pages );

			foreach ( $pages as $page ) {
				$ancestors = get_post_ancestors( $page->ID );
				$max_depth = max( $max_depth, count( $ancestors ) + 1 );
			}
		}

		return array(
			'max_depth'   => $max_depth,
			'total_items' => $total_items,
		);
	}

	/**
	 * Calculate depth of a menu item.
	 *
	 * @since  1.2601.2148
	 * @param  object $item       Menu item to analyze.
	 * @param  array  $menu_items All menu items.
	 * @return int Item depth level.
	 */
	private static function calculate_item_depth( $item, $menu_items ) {
		$depth = 1;
		$parent_id = $item->menu_item_parent;

		while ( $parent_id ) {
			$depth++;
			$parent = wp_filter_object_list( $menu_items, array( 'ID' => $parent_id ) );
			$parent_id = $parent ? reset( $parent )->menu_item_parent : 0;
		}

		return $depth;
	}
}
