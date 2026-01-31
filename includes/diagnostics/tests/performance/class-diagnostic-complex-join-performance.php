<?php
/**
 * Complex JOIN Query Performance Diagnostic
 *
 * Detects queries with complex or unnecessary JOINs.
 *
 * @since   1.4031.1939
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Complex_Join_Performance Class
 *
 * Identifies queries with complex JOINs that could be optimized.
 */
class Diagnostic_Complex_Join_Performance extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'complex-join-performance';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Complex JOIN Query Performance';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for complex or unnecessary JOIN operations that slow queries';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.4031.1939
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$issues = array();

		// Check relationship between posts and postmeta (common JOIN issue)
		$post_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts}" );
		$meta_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->postmeta}" );
		$meta_ratio  = ( $post_count > 0 ) ? ( $meta_count / $post_count ) : 0;

		if ( $meta_ratio > 50 ) {
			$issues[] = sprintf(
				/* translators: %d: average number of meta per post */
				__( 'Average of %d meta entries per post. JOINing posts and postmeta is expensive.', 'wpshadow' ),
				(int) $meta_ratio
			);
		}

		// Check for unused taxonomies (affects term JOIN queries)
		$used_taxonomies = $wpdb->get_var(
			"SELECT COUNT(DISTINCT taxonomy) FROM {$wpdb->term_taxonomy}"
		);

		$registered_taxonomies = count( get_taxonomies() );
		if ( $used_taxonomies < ( $registered_taxonomies * 0.5 ) ) {
			$issues[] = sprintf(
				/* translators: %d: count of unused taxonomies */
				__( '%d registered taxonomies but only %d in use. Unused JOINs slow queries.', 'wpshadow' ),
				$registered_taxonomies,
				$used_taxonomies
			);
		}

		// Check for multiple meta keys on same post (indicates need for denormalization)
		$high_meta_keys = $wpdb->get_var(
			"SELECT COUNT(DISTINCT meta_key) FROM {$wpdb->postmeta}
			WHERE post_id IN (
				SELECT post_id FROM {$wpdb->postmeta}
				GROUP BY post_id
				HAVING COUNT(*) > 50
			)"
		);

		if ( $high_meta_keys > 20 ) {
			$issues[] = sprintf(
				/* translators: %d: count of meta keys */
				__( '%d different meta keys on high-volume posts. Multiple meta JOINs are very expensive.', 'wpshadow' ),
				$high_meta_keys
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( ' ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'details'      => array(
					'meta_ratio_per_post'       => $meta_ratio,
					'used_taxonomies'           => $used_taxonomies ?? 0,
					'registered_taxonomies'     => $registered_taxonomies,
					'high_volume_meta_keys'     => $high_meta_keys ?? 0,
				),
				'kb_link'      => 'https://wpshadow.com/kb/complex-join-performance',
			);
		}

		return null;
	}
}
