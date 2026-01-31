<?php
/**
 * Content Expiration Monitor
 *
 * Flags outdated content that may need refresh, update, or removal
 * to maintain content quality and accuracy.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Content
 * @since      1.6029.1104
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Content Expiration Monitor Diagnostic Class
 *
 * Identifies old content that may be outdated and need updating.
 *
 * @since 1.6029.1104
 */
class Diagnostic_Content_Expiration extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'content-expiration';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Content Expiration Monitor';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Flags outdated content needing refresh or update';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'content';

	/**
	 * Age threshold in months
	 *
	 * @var int
	 */
	const AGE_THRESHOLD = 24;

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6029.1104
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$cache_key = 'wpshadow_content_expiration_check';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		$outdated = self::find_outdated_content();

		if ( $outdated['count'] === 0 ) {
			set_transient( $cache_key, null, 7 * DAY_IN_SECONDS );
			return null;
		}

		$severity = self::calculate_severity( $outdated['count'] );

		$finding = array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: 1: number of posts, 2: age threshold in months */
				__( 'Found %1$d posts not updated in over %2$d months that may need refresh.', 'wpshadow' ),
				$outdated['count'],
				self::AGE_THRESHOLD
			),
			'severity'     => $severity,
			'threat_level' => min( 60, 35 + ( $outdated['count'] / 5 ) ),
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/content-expiration',
			'meta'         => array(
				'outdated_count' => $outdated['count'],
				'oldest_date'    => $outdated['oldest_date'],
				'age_threshold'  => self::AGE_THRESHOLD,
				'sample_posts'   => $outdated['sample_posts'],
			),
			'details'      => array(
				sprintf(
					/* translators: %d: age in months */
					__( 'Content not updated in %d+ months may be outdated', 'wpshadow' ),
					self::AGE_THRESHOLD
				),
				__( 'Old content can contain inaccurate information', 'wpshadow' ),
				__( 'Search engines favor fresh, updated content', 'wpshadow' ),
			),
			'recommendation' => __( 'Review old content for accuracy and update or archive as needed.', 'wpshadow' ),
		);

		set_transient( $cache_key, $finding, 7 * DAY_IN_SECONDS );
		return $finding;
	}

	/**
	 * Find outdated content.
	 *
	 * @since  1.6029.1104
	 * @return array Outdated content data.
	 */
	private static function find_outdated_content() {
		global $wpdb;

		$threshold_date = date( 'Y-m-d H:i:s', strtotime( '-' . self::AGE_THRESHOLD . ' months' ) );

		$outdated_posts = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT ID, post_title, post_modified, post_type
				FROM {$wpdb->posts}
				WHERE post_status = 'publish'
				AND post_type IN ('post', 'page')
				AND post_modified < %s
				ORDER BY post_modified ASC
				LIMIT 20",
				$threshold_date
			),
			ARRAY_A
		);

		$count       = count( $outdated_posts );
		$oldest_date = ! empty( $outdated_posts ) ? $outdated_posts[0]['post_modified'] : null;

		$sample_posts = array_slice(
			array_map(
				function( $post ) {
					return array(
						'id'       => $post['ID'],
						'title'    => $post['post_title'],
						'modified' => $post['post_modified'],
						'type'     => $post['post_type'],
					);
				},
				$outdated_posts
			),
			0,
			10
		);

		return array(
			'count'        => $count,
			'oldest_date'  => $oldest_date,
			'sample_posts' => $sample_posts,
		);
	}

	/**
	 * Calculate severity based on count.
	 *
	 * @since  1.6029.1104
	 * @param  int $count Number of outdated posts.
	 * @return string Severity level.
	 */
	private static function calculate_severity( $count ) {
		if ( $count >= 50 ) {
			return 'medium';
		}
		return 'low';
	}
}
