<?php
/**
 * ACF Flexible Content Performance Diagnostic
 *
 * ACF flexible content slowing queries.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.452.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ACF Flexible Content Performance Diagnostic Class
 *
 * @since 1.452.0000
 */
class Diagnostic_AcfFlexibleContentPerformance extends Diagnostic_Base {

	protected static $slug = 'acf-flexible-content-performance';
	protected static $title = 'ACF Flexible Content Performance';
	protected static $description = 'ACF flexible content slowing queries';
	protected static $family = 'performance';

	public static function check() {
		if ( ! class_exists( 'ACF' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Flexible content fields count.
		global $wpdb;
		$flexible_fields = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s AND post_excerpt = %s",
				'acf-field',
				'flexible_content'
			)
		);
		if ( $flexible_fields > 10 ) {
			$issues[] = "{$flexible_fields} flexible content fields (consider reducing for better query performance)";
		}

		// Check 2: Layouts per flexible field.
		$max_layouts = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT MAX(layout_count) FROM (SELECT post_parent, COUNT(*) as layout_count FROM {$wpdb->posts} WHERE post_type = %s GROUP BY post_parent) as counts",
				'acf-field'
			)
		);
		if ( $max_layouts > 20 ) {
			$issues[] = "flexible field with {$max_layouts} layouts (too many options slow editing)";
		}

		// Check 3: Posts with large flexible content data.
		$large_flex_data = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key LIKE '_flexible_%' AND LENGTH(meta_value) > 10000"
		);
		if ( $large_flex_data > 50 ) {
			$issues[] = "{$large_flex_data} posts with large flexible content data (slows queries)";
		}

		// Check 4: Nested flexible content.
		$nested_flexible = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} p1 INNER JOIN {$wpdb->posts} p2 ON p1.ID = p2.post_parent WHERE p1.post_excerpt = %s AND p2.post_excerpt = %s",
				'flexible_content',
				'flexible_content'
			)
		);
		if ( $nested_flexible > 0 ) {
			$issues[] = "{$nested_flexible} nested flexible content fields (significantly impacts performance)";
		}

		// Check 5: Flexible content with image fields.
		$flex_with_images = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(DISTINCT p1.ID) FROM {$wpdb->posts} p1 INNER JOIN {$wpdb->posts} p2 ON p1.ID = p2.post_parent WHERE p1.post_excerpt = %s AND p2.post_excerpt = %s",
				'flexible_content',
				'image'
			)
		);
		if ( $flex_with_images > 5 ) {
			$issues[] = "{$flex_with_images} flexible fields with image fields (use gallery or optimize loading)";
		}

		// Check 6: Clone fields in flexible content.
		$flex_with_clones = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(DISTINCT p1.ID) FROM {$wpdb->posts} p1 INNER JOIN {$wpdb->posts} p2 ON p1.ID = p2.post_parent WHERE p1.post_excerpt = %s AND p2.post_excerpt = %s",
				'flexible_content',
				'clone'
			)
		);
		if ( $flex_with_clones > 3 ) {
			$issues[] = "{$flex_with_clones} flexible fields using clone fields (adds query overhead)";
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 75, 45 + ( count( $issues ) * 6 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'ACF flexible content performance issues: ' . implode( ', ', $issues ),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/acf-flexible-content-performance',
			);
		}

		return null;
	}
}
