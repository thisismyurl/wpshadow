<?php
/**
 * Conversion Optimization Diagnostic
 *
 * Tests if conversion paths and funnels are defined and tracked.
 *
 * @since 1.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Conversion Optimization Diagnostic Class
 *
 * Verifies that conversion tracking or funnel documentation exists.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Optimizes_For_Conversions extends Diagnostic_Base {

	protected static $slug = 'optimizes-for-conversions';
	protected static $title = 'Conversion Optimization';
	protected static $description = 'Tests if conversion paths and funnels are defined and tracked';
	protected static $family = 'publisher';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$manual_flag = get_option( 'wpshadow_conversion_funnel_documented' );
		if ( $manual_flag ) {
			return null;
		}

		$keywords = array(
			'conversion funnel',
			'conversion rate optimization',
			'cro',
			'sales funnel',
		);

		if ( self::has_documented_item( $keywords ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'No conversion optimization plan found. Define funnels and track conversions to improve results.', 'wpshadow' ),
			'severity'     => 'low',
			'threat_level' => 20,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/conversion-optimization',
			'persona'      => 'publisher',
		);
	}

	/**
	 * Check for documentation evidence in posts.
	 *
	 * @since 1.6093.1200
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
