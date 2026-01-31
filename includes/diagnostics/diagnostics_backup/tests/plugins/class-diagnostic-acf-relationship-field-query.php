<?php
/**
 * ACF Relationship Field Query Diagnostic
 *
 * ACF relationship queries inefficient.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.454.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ACF Relationship Field Query Diagnostic Class
 *
 * @since 1.454.0000
 */
class Diagnostic_AcfRelationshipFieldQuery extends Diagnostic_Base {

	protected static $slug = 'acf-relationship-field-query';
	protected static $title = 'ACF Relationship Field Query';
	protected static $description = 'ACF relationship queries inefficient';
	protected static $family = 'performance';

	public static function check() {
		if ( ! class_exists( 'ACF' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Relationship fields count.
		global $wpdb;
		$relationship_fields = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s AND post_excerpt = %s",
				'acf-field',
				'relationship'
			)
		);
		if ( $relationship_fields > 10 ) {
			$issues[] = "{$relationship_fields} relationship fields (each adds query overhead)";
		}

		// Check 2: Relationship fields without post type filters.
		$unfiltered_relationships = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s AND post_excerpt = %s AND post_content NOT LIKE %s",
				'acf-field',
				'relationship',
				'%post_type%'
			)
		);
		if ( $unfiltered_relationships > 0 ) {
			$issues[] = "{$unfiltered_relationships} relationship fields without post type filter (queries all post types)";
		}

		// Check 3: Relationship fields with high max values.
		$unlimited_relationships = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s AND post_excerpt = %s AND (post_content LIKE %s OR post_content NOT LIKE %s)",
				'acf-field',
				'relationship',
				'%max%:0%',
				'%max%'
			)
		);
		if ( $unlimited_relationships > 0 ) {
			$issues[] = "{$unlimited_relationships} relationship fields with no max limit (can create huge queries)";
		}

		// Check 4: Posts with many relationships.
		$posts_with_many = $wpdb->get_var(
			"SELECT COUNT(*) FROM (SELECT post_id, COUNT(*) as rel_count FROM {$wpdb->postmeta} WHERE meta_key LIKE '_relationship_%' GROUP BY post_id HAVING rel_count > 20) as counts"
		);
		if ( $posts_with_many > 0 ) {
			$issues[] = "{$posts_with_many} posts with over 20 relationship connections (slow to load/edit)";
		}

		// Check 5: Bidirectional relationships enabled.
		$bidirectional_fields = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s AND post_excerpt = %s AND post_content LIKE %s",
				'acf-field',
				'relationship',
				'%bidirectional%:1%'
			)
		);
		if ( $bidirectional_fields > 5 ) {
			$issues[] = "{$bidirectional_fields} bidirectional relationship fields (doubles database writes)";
		}

		// Check 6: Relationship query filters applied.
		$has_filters = has_filter( 'acf/fields/relationship/query' );
		if ( ! $has_filters && $relationship_fields > 5 ) {
			$issues[] = 'no custom relationship query filters (consider limiting query scope for performance)';
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 75, 45 + ( count( $issues ) * 6 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'ACF relationship field query issues: ' . implode( ', ', $issues ),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/acf-relationship-field-query',
			);
		}

		return null;
	}
}
