<?php
/**
 * Traffic Source Understanding Diagnostic
 *
 * Tests if owner understands and analyzes traffic sources.
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
 * Traffic Source Understanding Diagnostic Class
 *
 * Verifies that traffic sources are analyzed.
 *
 * @since 1.6050.0000
 */
class Diagnostic_Understands_Traffic_Sources extends Diagnostic_Base {

	protected static $slug = 'understands-traffic-sources';
	protected static $title = 'Traffic Source Understanding';
	protected static $description = 'Tests if owner understands and analyzes traffic sources';
	protected static $family = 'publisher';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6050.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$manual_flag = get_option( 'wpshadow_traffic_sources_reviewed' );
		if ( $manual_flag ) {
			return null;
		}

		$keywords = array(
			'traffic sources',
			'utm tracking',
			'channel analysis',
			'google analytics sources',
		);

		if ( self::has_documented_item( $keywords ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'No traffic source analysis found. Track channels to focus on what drives results.', 'wpshadow' ),
			'severity'     => 'low',
			'threat_level' => 20,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/traffic-source-understanding',
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
