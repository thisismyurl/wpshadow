<?php
/**
 * Customer Journey Mapped Diagnostic
 *
 * Tests if customer journey and touchpoints are documented.
 *
 * @since 0.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Customer Journey Mapped Diagnostic Class
 *
 * Verifies that customer journey mapping exists.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Maps_Customer_Journey extends Diagnostic_Base {

	protected static $slug = 'maps-customer-journey';
	protected static $title = 'Customer Journey Mapped';
	protected static $description = 'Tests if customer journey and touchpoints are documented';
	protected static $family = 'publisher';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$manual_flag = get_option( 'wpshadow_customer_journey_mapped' );
		if ( $manual_flag ) {
			return null;
		}

		$keywords = array(
			'customer journey',
			'user journey',
			'funnel map',
			'touchpoints',
		);

		if ( self::has_documented_item( $keywords ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'No customer journey mapping found. Document touchpoints to improve conversions and retention.', 'wpshadow' ),
			'severity'     => 'low',
			'threat_level' => 20,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/customer-journey-mapped?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'persona'      => 'publisher',
		);
	}

	/**
	 * Check for documentation evidence in posts.
	 *
	 * @since 0.6093.1200
	 * @param  array $keywords Search terms.
	 * @return bool True if found.
	 */
	private static function has_documented_item( array $keywords ) {
		if ( ! function_exists( 'get_posts' ) ) {
			return false;
		}

		foreach ( $keywords as $keyword ) {
			$posts = get_posts(
				array(
					's'              => $keyword,
					'post_type'      => array( 'page', 'post', 'documentation', 'kb' ),
					'post_status'    => array( 'publish', 'private' ),
					'posts_per_page' => 1,
					'fields'         => 'ids',
				)
			);

			if ( ! empty( $posts ) ) {
				return true;
			}
		}

		return false;
	}
}
