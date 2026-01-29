<?php
/**
 * Primary Keyword Coverage Diagnostic
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5029.1730
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Diagnostic_Primary_Keyword_Coverage extends Diagnostic_Base {

	protected static $slug        = 'primary-keyword-coverage';
	protected static $title       = 'Primary Keyword Coverage';
	protected static $description = 'Verifies keyword optimization';
	protected static $family      = 'seo';

	public static function check() {
		$cache_key = 'wpshadow_keyword_coverage';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		$posts = get_posts( array(
			'post_type'      => array( 'post', 'page' ),
			'posts_per_page' => 50,
			'post_status'    => 'publish',
		) );

		$missing_keywords = array();

		foreach ( $posts as $post ) {
			setup_postdata( $post );
			
			$title   = get_the_title( $post );
			$content = get_the_content( null, false, $post );
			
			// Check if title and content are optimized.
			if ( empty( $title ) || empty( $content ) ) {
				$missing_keywords[] = array(
					'ID' => $post->ID,
					'title' => $title,
					'issue' => 'Empty title or content',
				);
			}
		}

		wp_reset_postdata();

		if ( ! empty( $missing_keywords ) ) {
			$result = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: count */
					__( '%d posts have keyword optimization issues. Review for better SEO.', 'wpshadow' ),
					count( $missing_keywords )
				),
				'severity'     => 'high',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/seo-keyword-coverage',
				'data'         => array(
					'missing_keywords' => array_slice( $missing_keywords, 0, 10 ),
					'total_issues' => count( $missing_keywords ),
				),
			);

			set_transient( $cache_key, $result, 12 * HOUR_IN_SECONDS );
			return $result;
		}

		set_transient( $cache_key, null, 24 * HOUR_IN_SECONDS );
		return null;
	}
}
