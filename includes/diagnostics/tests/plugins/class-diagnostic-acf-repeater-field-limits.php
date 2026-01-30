<?php
/**
 * ACF Repeater Field Limits Diagnostic
 *
 * ACF repeater fields no row limits.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.456.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ACF Repeater Field Limits Diagnostic Class
 *
 * @since 1.456.0000
 */
class Diagnostic_AcfRepeaterFieldLimits extends Diagnostic_Base {

	protected static $slug = 'acf-repeater-field-limits';
	protected static $title = 'ACF Repeater Field Limits';
	protected static $description = 'ACF repeater fields no row limits';
	protected static $family = 'performance';

	public static function check() {
		if ( ! class_exists( 'ACF' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Repeater fields without max rows.
		global $wpdb;
		$unlimited_repeaters = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s AND post_excerpt = %s AND (post_content NOT LIKE %s OR post_content LIKE %s)",
				'acf-field',
				'repeater',
				'%max%',
				'%max%:0%'
			)
		);
		if ( $unlimited_repeaters > 0 ) {
			$issues[] = "{$unlimited_repeaters} repeater fields with no max row limit (can create huge datasets)";
		}

		// Check 2: Posts with large repeater data.
		$large_repeaters = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key LIKE '_repeater_%' AND CAST(meta_value AS UNSIGNED) > 50"
		);
		if ( $large_repeaters > 0 ) {
			$issues[] = "{$large_repeaters} repeater instances with over 50 rows (slow to load and edit)";
		}

		// Check 3: Nested repeaters.
		$nested_repeaters = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} p1 INNER JOIN {$wpdb->posts} p2 ON p1.ID = p2.post_parent WHERE p1.post_excerpt = %s AND p2.post_excerpt = %s",
				'repeater',
				'repeater'
			)
		);
		if ( $nested_repeaters > 0 ) {
			$issues[] = "{$nested_repeaters} nested repeater fields (exponentially increases query complexity)";
		}

		// Check 4: Repeaters with many subfields.
		$complex_repeaters = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM (SELECT post_parent, COUNT(*) as field_count FROM {$wpdb->posts} WHERE post_type = %s GROUP BY post_parent HAVING field_count > 15) as counts WHERE post_parent IN (SELECT ID FROM {$wpdb->posts} WHERE post_excerpt = %s)",
				'acf-field',
				'repeater'
			)
		);
		if ( $complex_repeaters > 0 ) {
			$issues[] = "{$complex_repeaters} repeater fields with over 15 subfields (consider splitting)";
		}

		// Check 5: Repeaters with relationship/gallery fields.
		$heavy_repeaters = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(DISTINCT p1.ID) FROM {$wpdb->posts} p1 INNER JOIN {$wpdb->posts} p2 ON p1.ID = p2.post_parent WHERE p1.post_excerpt = %s AND p2.post_excerpt IN ('relationship', 'gallery', 'image')",
				'repeater'
			)
		);
		if ( $heavy_repeaters > 0 ) {
			$issues[] = "{$heavy_repeaters} repeaters with relationship/gallery fields (multiplies query load)";
		}

		// Check 6: Repeater button labels not set.
		$unlabeled_repeaters = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s AND post_excerpt = %s AND post_content NOT LIKE %s",
				'acf-field',
				'repeater',
				'%button_label%'
			)
		);
		if ( $unlabeled_repeaters > 5 ) {
			$issues[] = "{$unlabeled_repeaters} repeaters without custom button labels (poor UX)";
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 75, 45 + ( count( $issues ) * 6 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'ACF repeater field limit issues: ' . implode( ', ', $issues ),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/acf-repeater-field-limits',
			);
		}

		return null;
	}
}
