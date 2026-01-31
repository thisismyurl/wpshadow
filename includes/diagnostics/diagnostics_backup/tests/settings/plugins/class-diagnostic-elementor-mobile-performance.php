<?php
/**
 * Elementor Mobile Performance
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5029.1800
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Diagnostic_Elementor_Mobile_Performance extends Diagnostic_Base {

	protected static $slug        = 'elementor-mobile-performance';
	protected static $title       = 'Elementor Mobile Performance';
	protected static $description = 'Checks Elementor mobile optimization';
	protected static $family      = 'performance';

	public static function check() {
		$cache_key = 'wpshadow_elementor_mobile';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		if ( ! did_action( 'elementor/loaded' ) ) {
			set_transient( $cache_key, null, 24 * HOUR_IN_SECONDS );
			return null;
		}

		// Check if mobile editing is enabled.
		$mobile_breakpoint = get_option( 'elementor_viewport_md', 768 );
		
		$issues = array();

		if ( $mobile_breakpoint < 768 ) {
			$issues[] = 'Mobile breakpoint set too low';
		}

		// Check for responsive preview.
		$posts = get_posts( array(
			'post_type'      => array( 'page', 'post' ),
			'posts_per_page' => 10,
			'meta_query'     => array(
				array(
					'key'     => '_elementor_edit_mode',
					'compare' => 'EXISTS',
				),
			),
		) );

		$unoptimized_count = 0;
		foreach ( $posts as $post ) {
			$mobile_data = get_post_meta( $post->ID, '_elementor_data', true );
			if ( empty( $mobile_data ) ) {
				$unoptimized_count++;
			}
		}

		if ( $unoptimized_count > 3 ) {
			$issues[] = sprintf( '%d pages lack mobile optimization', $unoptimized_count );
		}

		if ( ! empty( $issues ) ) {
			$result = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Elementor mobile performance needs improvement.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/elementor-mobile',
				'data'         => array(
					'issues' => $issues,
				),
			);

			set_transient( $cache_key, $result, 12 * HOUR_IN_SECONDS );
			return $result;
		}

		set_transient( $cache_key, null, 24 * HOUR_IN_SECONDS );
		return null;
	}
}
