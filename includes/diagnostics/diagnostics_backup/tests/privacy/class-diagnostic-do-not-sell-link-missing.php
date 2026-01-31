<?php
/**
 * Do Not Sell Link Missing Diagnostic
 *
 * Verifies required CCPA opt-out link for California residents.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Privacy
 * @since      1.6028.1500
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CCPA Do Not Sell Link Diagnostic Class
 *
 * @since 1.6028.1500
 */
class Diagnostic_DoNotSellLinkMissing extends Diagnostic_Base {

	protected static $slug        = 'do-not-sell-link-missing';
	protected static $title       = '"Do Not Sell My Personal Information" Link';
	protected static $description = 'Verify required CCPA opt-out link for California residents';
	protected static $family      = 'privacy-ccpa';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6028.1500
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for Do Not Sell page.
		$pages = get_posts(
			array(
				'post_type'      => 'page',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'fields'         => 'ids',
			)
		);

		$has_dns_page = false;
		foreach ( $pages as $page_id ) {
			$title   = get_the_title( $page_id );
			$content = get_post_field( 'post_content', $page_id );
			
			if ( stripos( $title, 'do not sell' ) !== false ||
				 stripos( $content, 'do not sell' ) !== false ) {
				$has_dns_page = true;
				break;
			}
		}

		// Check menus for DNS link.
		$has_menu_link = false;
		$menu_locations = get_nav_menu_locations();
		
		foreach ( $menu_locations as $location => $menu_id ) {
			$menu_items = wp_get_nav_menu_items( $menu_id );
			if ( $menu_items ) {
				foreach ( $menu_items as $item ) {
					if ( stripos( $item->title, 'do not sell' ) !== false ) {
						$has_menu_link = true;
						break 2;
					}
				}
			}
		}

		if ( ! $has_dns_page && ! $has_menu_link ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'CCPA requires "Do Not Sell My Personal Information" link on homepage', 'wpshadow' ),
				'severity'     => 'critical',
				'threat_level' => 95,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/ccpa-do-not-sell',
				'details'      => array(
					'finding'        => __( 'Missing required CCPA "Do Not Sell" link', 'wpshadow' ),
					'impact'         => __( 'CCPA §1798.120 requires opt-out link. Fines $2,500-$7,500 per violation. Must be on homepage.', 'wpshadow' ),
					'recommendation' => __( 'Create Do Not Sell page and add prominent link', 'wpshadow' ),
					'solution_free'  => array(
						'label' => __( 'Manual Implementation', 'wpshadow' ),
						'steps' => array(
							__( '1. Create new page: "Do Not Sell My Personal Information"', 'wpshadow' ),
							__( '2. Add opt-out form or email address', 'wpshadow' ),
							__( '3. Link from footer menu', 'wpshadow' ),
							__( '4. Make link prominent and easy to find', 'wpshadow' ),
						),
					),
					'solution_premium' => array(
						'label' => __( 'CCPA Plugin Solution', 'wpshadow' ),
						'steps' => array(
							__( '1. Install CCPA compliance plugin', 'wpshadow' ),
							__( '2. Configure Do Not Sell mechanism', 'wpshadow' ),
							__( '3. Enable Global Privacy Control support', 'wpshadow' ),
							__( '4. Test opt-out functionality', 'wpshadow' ),
						),
					),
				),
			);
		}

		return null;
	}
}
