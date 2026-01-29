<?php
/**
 * Yoast Keyphrase Coverage Diagnostic
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

class Diagnostic_Yoast_Keyphrase_Coverage extends Diagnostic_Base {

	protected static $slug        = 'yoast-keyphrase-coverage';
	protected static $title       = 'Yoast Focus Keyphrase Coverage';
	protected static $description = 'Analyzes focus keyphrase coverage';
	protected static $family      = 'seo';

	public static function check() {
		$cache_key = 'wpshadow_yoast_keyphrase';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		if ( ! defined( 'WPSEO_VERSION' ) ) {
			set_transient( $cache_key, null, 24 * HOUR_IN_SECONDS );
			return null;
		}

		global $wpdb;

		$posts_with_keyword = $wpdb->get_var(
			"SELECT COUNT(DISTINCT p.ID) FROM {$wpdb->posts} p
			INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
			WHERE p.post_status = 'publish'
			AND p.post_type IN ('post', 'page')
			AND pm.meta_key = '_yoast_wpseo_focuskw'
			AND pm.meta_value != ''"
		);

		$total_posts = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts}
			WHERE post_status = 'publish'
			AND post_type IN ('post', 'page')"
		);

		if ( $total_posts == 0 ) {
			set_transient( $cache_key, null, 24 * HOUR_IN_SECONDS );
			return null;
		}

		$coverage = ( $posts_with_keyword / $total_posts ) * 100;

		if ( $coverage < 80 ) {
			$result = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: 1: percentage, 2: count */
					__( 'Only %1$d%% of posts (%2$d/%3$d) have focus keyphrases. Improve for better SEO.', 'wpshadow' ),
					round( $coverage ),
					$posts_with_keyword,
					$total_posts
				),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/seo-yoast-keyphrase',
				'data'         => array(
					'coverage' => round( $coverage, 1 ),
					'posts_with_keyword' => (int) $posts_with_keyword,
					'total_posts' => (int) $total_posts,
				),
			);

			set_transient( $cache_key, $result, 12 * HOUR_IN_SECONDS );
			return $result;
		}

		set_transient( $cache_key, null, 24 * HOUR_IN_SECONDS );
		return null;
	}
}
