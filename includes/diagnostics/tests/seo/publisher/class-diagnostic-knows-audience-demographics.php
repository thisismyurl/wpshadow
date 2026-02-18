<?php
/**
 * Audience Demographics Known Diagnostic
 *
 * Tests if audience demographics are analyzed and understood.
 *
 * @since   1.6050.0000
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Audience Demographics Known Diagnostic Class
 *
 * Verifies that demographic insights are documented.
 *
 * @since 1.6050.0000
 */
class Diagnostic_Knows_Audience_Demographics extends Diagnostic_Base {

	protected static $slug = 'knows-audience-demographics';
	protected static $title = 'Audience Demographics Known';
	protected static $description = 'Tests if audience demographics are analyzed and understood';
	protected static $family = 'publisher';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6050.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$manual_flag = get_option( 'wpshadow_audience_demographics_documented' );
		if ( $manual_flag ) {
			return null;
		}

		$keywords = array(
			'audience demographics',
			'audience profile',
			'user personas',
			'customer demographics',
		);

		if ( self::has_documented_item( $keywords ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'No audience demographics documentation found. Understanding who visits helps target content and offers.', 'wpshadow' ),
			'severity'     => 'low',
			'threat_level' => 20,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/audience-demographics-known',
			'persona'      => 'publisher',
		);
	}

	/**
	 * Check for documentation evidence in posts.
	 *
	 * @since  1.6050.0000
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
