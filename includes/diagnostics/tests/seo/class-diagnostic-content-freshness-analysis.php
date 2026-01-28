<?php
/**
 * Content Freshness Analysis Diagnostic
 *
 * Identifies stale content not updated in 12+ months that needs refreshing.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26028.1905
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Content Freshness Analysis Class
 *
 * Tests content freshness.
 *
 * @since 1.26028.1905
 */
class Diagnostic_Content_Freshness_Analysis extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'content-freshness-analysis';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Content Freshness Analysis';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Identifies stale content not updated in 12+ months that needs refreshing';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26028.1905
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$freshness = self::analyze_content_freshness();
		
		if ( $freshness['stale_percentage'] > 50 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: 1: number of stale posts, 2: percentage */
					__( '%1$d posts (%2$d%%) not updated in 12+ months', 'wpshadow' ),
					$freshness['stale_count'],
					$freshness['stale_percentage']
				),
				'severity'     => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/content-freshness-analysis',
				'meta'         => array(
					'total_posts'       => $freshness['total_posts'],
					'stale_count'       => $freshness['stale_count'],
					'stale_percentage'  => $freshness['stale_percentage'],
					'oldest_post_age'   => $freshness['oldest_post_age'],
					'avg_age_months'    => $freshness['avg_age_months'],
				),
			);
		}

		return null;
	}

	/**
	 * Analyze content freshness.
	 *
	 * @since  1.26028.1905
	 * @return array Freshness analysis.
	 */
	private static function analyze_content_freshness() {
		global $wpdb;

		$analysis = array(
			'total_posts'      => 0,
			'stale_count'      => 0,
			'stale_percentage' => 0,
			'oldest_post_age'  => 0,
			'avg_age_months'   => 0,
		);

		// Get post counts.
		$twelve_months_ago = gmdate( 'Y-m-d H:i:s', strtotime( '-12 months' ) );

		$stale_count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*)
				FROM {$wpdb->posts}
				WHERE post_status = %s
				AND post_type IN ('post', 'page')
				AND post_modified < %s",
				'publish',
				$twelve_months_ago
			)
		);

		$total_count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*)
				FROM {$wpdb->posts}
				WHERE post_status = %s
				AND post_type IN ('post', 'page')",
				'publish'
			)
		);

		$analysis['total_posts'] = (int) $total_count;
		$analysis['stale_count'] = (int) $stale_count;

		if ( $analysis['total_posts'] > 0 ) {
			$analysis['stale_percentage'] = round( ( $analysis['stale_count'] / $analysis['total_posts'] ) * 100 );
		}

		// Get oldest post age.
		$oldest_post = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT post_modified
				FROM {$wpdb->posts}
				WHERE post_status = %s
				AND post_type IN ('post', 'page')
				ORDER BY post_modified ASC
				LIMIT 1",
				'publish'
			)
		);

		if ( $oldest_post ) {
			$oldest_timestamp = strtotime( $oldest_post );
			$analysis['oldest_post_age'] = floor( ( time() - $oldest_timestamp ) / ( 60 * 60 * 24 * 30 ) );
		}

		// Calculate average age.
		$avg_timestamp = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT AVG(UNIX_TIMESTAMP(post_modified))
				FROM {$wpdb->posts}
				WHERE post_status = %s
				AND post_type IN ('post', 'page')",
				'publish'
			)
		);

		if ( $avg_timestamp ) {
			$analysis['avg_age_months'] = floor( ( time() - $avg_timestamp ) / ( 60 * 60 * 24 * 30 ) );
		}

		return $analysis;
	}
}
