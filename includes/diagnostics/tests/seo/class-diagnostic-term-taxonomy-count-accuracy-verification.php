<?php
/**
 * Term Taxonomy Count Accuracy Verification Diagnostic
 *
 * Validates term counts match actual post assignments.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26029.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Term Taxonomy Count Accuracy Verification Class
 *
 * Tests term count accuracy.
 *
 * @since 1.26029.0000
 */
class Diagnostic_Term_Taxonomy_Count_Accuracy_Verification extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'term-taxonomy-count-accuracy-verification';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Term Taxonomy Count Accuracy Verification';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates term counts match actual post assignments';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26029.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$count_check = self::check_term_counts();
		
		if ( $count_check['incorrect_count'] > 0 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of terms with incorrect counts */
					__( '%d terms have incorrect post counts (breaks archive pages and widgets)', 'wpshadow' ),
					$count_check['incorrect_count']
				),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/term-taxonomy-count-accuracy-verification',
				'meta'         => array(
					'incorrect_count' => $count_check['incorrect_count'],
					'sample_terms'    => $count_check['sample_terms'],
				),
			);
		}

		return null;
	}

	/**
	 * Check term count accuracy.
	 *
	 * @since  1.26029.0000
	 * @return array Check results.
	 */
	private static function check_term_counts() {
		global $wpdb;

		$check = array(
			'incorrect_count' => 0,
			'sample_terms'    => array(),
		);

		// Check for terms with incorrect counts.
		$incorrect_terms = $wpdb->get_results(
			"SELECT 
				tt.term_taxonomy_id,
				tt.term_id,
				tt.taxonomy,
				tt.count as stored_count,
				COUNT(tr.object_id) as actual_count
			FROM {$wpdb->term_taxonomy} tt
			LEFT JOIN {$wpdb->term_relationships} tr ON tt.term_taxonomy_id = tr.term_taxonomy_id
			LEFT JOIN {$wpdb->posts} p ON tr.object_id = p.ID
			WHERE (
				p.post_status = 'publish' 
				OR p.ID IS NULL
			)
			GROUP BY tt.term_taxonomy_id
			HAVING stored_count != actual_count
			LIMIT 20",
			ARRAY_A
		);

		if ( ! empty( $incorrect_terms ) ) {
			$check['incorrect_count'] = count( $incorrect_terms );

			foreach ( $incorrect_terms as $term ) {
				$term_obj = get_term( (int) $term['term_id'], $term['taxonomy'] );
				
				if ( $term_obj && ! is_wp_error( $term_obj ) ) {
					$check['sample_terms'][] = array(
						'name'         => $term_obj->name,
						'taxonomy'     => $term['taxonomy'],
						'stored_count' => (int) $term['stored_count'],
						'actual_count' => (int) $term['actual_count'],
					);
				}
			}
		}

		return $check;
	}
}
