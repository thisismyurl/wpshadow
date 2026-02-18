<?php
/**
 * Return and Refund Policy Diagnostic
 *
 * Checks whether a return or refund policy is easy to find.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Ecommerce
 * @since      1.6035.1400
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Return and Refund Policy Diagnostic Class
 *
 * Verifies that return policy pages and navigation links exist.
 *
 * @since 1.6035.1400
 */
class Diagnostic_Return_Refund_Policy extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'return-refund-policy';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Return or Refund Policy Not Easy to Find';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if a clear return policy is visible on key pages';

	/**
	 * The family this diagnostic belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'ecommerce';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6035.1400
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$issues  = array();
		$stats   = array();
		$matches = self::find_pages_by_keywords( array( 'return', 'refund', 'returns', 'refunds', 'exchange' ) );

		$stats['policy_pages'] = ! empty( $matches ) ? implode( ', ', $matches ) : 'none';

		$menu_links = self::find_menu_links_by_keywords( array( 'return', 'refund', 'returns', 'exchange' ) );
		$stats['menu_links'] = ! empty( $menu_links ) ? implode( ', ', $menu_links ) : 'none';

		if ( empty( $matches ) && empty( $menu_links ) ) {
			$issues[] = __( 'No return or refund policy page or menu link detected', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Clear return policies build purchase confidence. When shoppers can\'t find the policy quickly, they often pause or leave.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/return-refund-policy',
				'context'      => array(
					'stats'  => $stats,
					'issues' => $issues,
				),
			);
		}

		return null;
	}

	/**
	 * Find pages or posts by keyword search.
	 *
	 * @since  1.6035.1400
	 * @param  array $keywords Keywords to search for.
	 * @return array List of matching page titles.
	 */
	private static function find_pages_by_keywords( array $keywords ): array {
		$matches = array();

		foreach ( $keywords as $keyword ) {
			$results = get_posts(
				array(
					's'              => $keyword,
					'post_type'      => array( 'page', 'post' ),
					'post_status'    => 'publish',
					'posts_per_page' => 5,
				)
			);

			foreach ( $results as $post ) {
				$matches[ $post->ID ] = get_the_title( $post );
			}
		}

		return array_values( $matches );
	}

	/**
	 * Find navigation menu links by keyword.
	 *
	 * @since  1.6035.1400
	 * @param  array $keywords Keywords to search for.
	 * @return array List of matching menu item titles.
	 */
	private static function find_menu_links_by_keywords( array $keywords ): array {
		$matches = array();
		$menus   = wp_get_nav_menus();

		foreach ( $menus as $menu ) {
			$items = wp_get_nav_menu_items( $menu->term_id );
			if ( empty( $items ) ) {
				continue;
			}

			foreach ( $items as $item ) {
				$title = strtolower( $item->title );
				foreach ( $keywords as $keyword ) {
					if ( false !== strpos( $title, $keyword ) ) {
						$matches[] = $item->title;
						break;
					}
				}
			}
		}

		return array_values( array_unique( $matches ) );
	}
}
