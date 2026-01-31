<?php
/**
 * ACF Field Group Optimization Diagnostic
 *
 * ACF field groups not optimized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.450.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ACF Field Group Optimization Diagnostic Class
 *
 * @since 1.450.0000
 */
class Diagnostic_AcfFieldGroupOptimization extends Diagnostic_Base {

	protected static $slug = 'acf-field-group-optimization';
	protected static $title = 'ACF Field Group Optimization';
	protected static $description = 'ACF field groups not optimized';
	protected static $family = 'performance';

	public static function check() {
		if ( ! class_exists( 'ACF' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Total field groups count.
		global $wpdb;
		$field_group_count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s AND post_status = %s",
				'acf-field-group',
				'publish'
			)
		);
		if ( $field_group_count > 50 ) {
			$issues[] = "{$field_group_count} field groups active (consider consolidation for better performance)";
		}

		// Check 2: Field groups without location rules.
		$groups_without_rules = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} p LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = %s WHERE p.post_type = %s AND p.post_status = %s AND pm.meta_value IS NULL",
				'rule',
				'acf-field-group',
				'publish'
			)
		);
		if ( $groups_without_rules > 0 ) {
			$issues[] = "{$groups_without_rules} field groups without location rules (loaded on all pages)";
		}

		// Check 3: Overly broad location rules.
		$broad_rules = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value LIKE %s",
				'rule',
				'%post_type%==%%all%%'
			)
		);
		if ( $broad_rules > 5 ) {
			$issues[] = "{$broad_rules} field groups with 'all post types' rule (impacts performance)";
		}

		// Check 4: Field groups with excessive fields.
		$groups_with_many_fields = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(DISTINCT post_parent) FROM {$wpdb->posts} WHERE post_type = %s GROUP BY post_parent HAVING COUNT(*) > 30",
				'acf-field'
			)
		);
		if ( $groups_with_many_fields > 0 ) {
			$issues[] = "{$groups_with_many_fields} field groups with over 30 fields (split into smaller groups)";
		}

		// Check 5: JSON sync not enabled.
		$json_save_path = apply_filters( 'acf/settings/save_json', false );
		if ( false === $json_save_path ) {
			$issues[] = 'JSON sync not enabled (field groups stored in database, slower loading)';
		}

		// Check 6: Local JSON files out of sync.
		if ( false !== $json_save_path && is_dir( $json_save_path ) ) {
			$json_files = glob( $json_save_path . '/group_*.json' );
			if ( count( $json_files ) !== $field_group_count ) {
				$issues[] = "JSON files ({count($json_files)}) don't match field groups ({$field_group_count}) - sync needed";
			}
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 75, 45 + ( count( $issues ) * 6 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'ACF field group optimization issues: ' . implode( ', ', $issues ),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/acf-field-group-optimization',
			);
		}

		return null;
	}
}
