<?php
/**
 * Advanced Custom Fields Performance Diagnostic
 *
 * Advanced Custom Fields Performance issue detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1052.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Advanced Custom Fields Performance Diagnostic Class
 *
 * @since 1.1052.0000
 */
class Diagnostic_AdvancedCustomFieldsPerformance extends Diagnostic_Base {

	protected static $slug = 'advanced-custom-fields-performance';
	protected static $title = 'Advanced Custom Fields Performance';
	protected static $description = 'Advanced Custom Fields Performance issue detected';
	protected static $family = 'performance';

	public static function check() {
		if ( ! class_exists( 'ACF' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Too many field groups.
		global $wpdb;
		$field_group_count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s AND post_status = %s",
				'acf-field-group',
				'publish'
			)
		);
		if ( $field_group_count > 50 ) {
			$issues[] = "{$field_group_count} field groups active (consider consolidation)";
		}
		
		// Check 2: Large repeater fields.
		$large_repeaters = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key LIKE %s AND CAST(meta_value AS UNSIGNED) > 100",
				'%_repeater%'
			)
		);
		if ( $large_repeaters > 0 ) {
			$issues[] = "{$large_repeaters} repeater fields with >100 rows (query performance impact)";
		}
		
		// Check 3: Relationship field limits.
		$unlimited_relationships = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s AND post_content LIKE %s AND post_content NOT LIKE %s",
				'acf-field',
				'%"type":"relationship"%',
				'%"max":%'
			)
		);
		if ( $unlimited_relationships > 0 ) {
			$issues[] = "{$unlimited_relationships} relationship fields without max limits (slow queries)";
		}
		
		// Check 4: JSON caching disabled.
		$json_cache = get_option( 'acf_json_cache', '1' );
		if ( '0' === $json_cache ) {
			$issues[] = 'ACF JSON caching disabled (repeated file reads)';
		}
		
		// Check 5: Local JSON not used.
		$json_path = get_option( 'acf_json_save_path', '' );
		if ( empty( $json_path ) && $field_group_count > 10 ) {
			$issues[] = 'local JSON not configured (field groups loaded from database)';
		}
		
		// Check 6: Flexible content layouts.
		$complex_flexible = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s AND post_content LIKE %s",
				'acf-field',
				'%"type":"flexible_content"%'
			)
		);
		if ( $complex_flexible > 10 ) {
			$issues[] = "{$complex_flexible} flexible content fields (memory and query intensive)";
		}
		
		if ( ! empty( $issues ) ) {
			$threat_level = min( 75, 45 + ( count( $issues ) * 6 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'ACF performance issues: ' . implode( ', ', $issues ),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/advanced-custom-fields-performance',
			);
		}
		
		return null;
	}
}
