<?php
/**
 * Diagnostic: Large Menu Overhead
 *
 * Detects admin menus with too many items causing slow rendering.
 *
 * Philosophy: Inspire Confidence (#8) - Clean navigation = usable
 * KB Link: https://wpshadow.com/kb/large-menu-overhead
 * Training: https://wpshadow.com/training/large-menu-overhead
 *
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Large Menu Overhead diagnostic
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Large_Menu_Overhead extends Diagnostic_Base {

	/**
	 * Run the diagnostic check
	 *
	 * @return array|null Diagnostic result or null if no issue
	 */
	public static function check(): ?array {
		global $menu, $submenu;

		if ( empty( $menu ) ) {
			return null;
		}

		// Count total menu items
		$top_level_count = count( $menu );
		$submenu_count = 0;
		$large_submenus = [];

		foreach ( $submenu as $parent => $items ) {
			$count = count( $items );
			$submenu_count += $count;

			// Flag submenus with more than 10 items
			if ( $count > 10 ) {
				$large_submenus[] = [
					'parent' => $parent,
					'count'  => $count,
					'title'  => self::get_menu_title( $parent ),
				];
			}
		}

		$total_items = $top_level_count + $submenu_count;

		// Only flag if excessive (more than 100 total items or any submenu > 15)
		$has_excessive_submenu = ! empty( array_filter( $large_submenus, function( $submenu ) {
			return $submenu['count'] > 15;
		} ) );

		if ( $total_items < 100 && ! $has_excessive_submenu ) {
			return null;
		}

		$severity = $total_items > 150 || $has_excessive_submenu ? 'medium' : 'low';

		$description = sprintf(
			__( 'Your admin menu has %d top-level items and %d submenu items. Large menus slow down every admin page load and make navigation difficult.', 'wpshadow' ),
			$top_level_count,
			$submenu_count
		);

		if ( ! empty( $large_submenus ) ) {
			usort( $large_submenus, function( $a, $b ) {
				return $b['count'] - $a['count'];
			} );
			
			$top_culprit = $large_submenus[0];
			$description .= sprintf(
				' ' . __( 'Largest submenu: "%s" has %d items.', 'wpshadow' ),
				$top_culprit['title'],
				$top_culprit['count']
			);
		}

		return [
			'id'                => 'large-menu-overhead',
			'title'             => __( 'Excessive Admin Menu Items', 'wpshadow' ),
			'description'       => $description,
			'severity'          => $severity,
			'category'          => 'performance',
			'impact'            => 'low',
			'effort'            => 'low',
			'kb_link'           => 'https://wpshadow.com/kb/large-menu-overhead',
			'training_link'     => 'https://wpshadow.com/training/large-menu-overhead',
			'affected_resource' => sprintf( '%d total items', $total_items ),
			'metadata'          => [
				'top_level_count'  => $top_level_count,
				'submenu_count'    => $submenu_count,
				'large_submenus'   => $large_submenus,
			],
		];
	}

	/**
	 * Get menu title from slug
	 *
	 * @param string $slug Menu slug
	 * @return string Menu title
	 */
	private static function get_menu_title( string $slug ): string {
		global $menu;

		foreach ( $menu as $item ) {
			if ( isset( $item[2] ) && $item[2] === $slug ) {
				return strip_tags( $item[0] );
			}
		}

		return $slug;
	}

}