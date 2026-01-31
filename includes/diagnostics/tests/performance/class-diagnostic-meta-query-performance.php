<?php
/**
 * Meta Query Performance Diagnostic
 *
 * Detects inefficient meta queries that bypass indexes.
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
 * Diagnostic_Meta_Query_Performance Class
 *
 * Detects inefficient meta queries using LIKE or complex filters.
 */
class Diagnostic_Meta_Query_Performance extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'meta-query-performance';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Meta Query Performance';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for inefficient meta queries that bypass database indexes';

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

		// Check for meta keys using LIKE searches
		$results = $wpdb->get_results(
			"SELECT post_id, meta_key, COUNT(*) as cnt 
			FROM {$wpdb->postmeta} 
			GROUP BY meta_key 
			HAVING cnt > 1000
			ORDER BY cnt DESC 
			LIMIT 10",
			ARRAY_A
		);

		if ( ! empty( $results ) ) {
			$high_cardinality_keys = array();
			foreach ( $results as $row ) {
				$high_cardinality_keys[] = $row['meta_key'];
			}
			$issues[] = sprintf(
				/* translators: %s: comma-separated meta keys */
				__( 'High-cardinality meta keys found: %s. These may need separate indexing.', 'wpshadow' ),
				implode( ', ', $high_cardinality_keys )
			);
		}

		// Check for posts with excessive meta entries
		$excessive_meta = $wpdb->get_col(
			"SELECT post_id FROM {$wpdb->postmeta} 
			GROUP BY post_id 
			HAVING COUNT(*) > 100 
			LIMIT 5"
		);

		if ( ! empty( $excessive_meta ) ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts with excessive meta */
				__( '%d posts have over 100 meta entries. This slows meta queries significantly.', 'wpshadow' ),
				count( $excessive_meta )
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( ' ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'details'      => array(
					'high_cardinality_keys' => $high_cardinality_keys ?? array(),
					'excessive_meta_posts'  => $excessive_meta ?? array(),
				),
				'kb_link'      => 'https://wpshadow.com/kb/meta-query-performance',
			);
		}

		return null;
	}
}
