<?php
/**
 * Advanced Custom Fields Field Key Conflicts Diagnostic
 *
 * Advanced Custom Fields Field Key Conflicts issue detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1051.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Advanced Custom Fields Field Key Conflicts Diagnostic Class
 *
 * @since 1.1051.0000
 */
class Diagnostic_AdvancedCustomFieldsFieldKeyConflicts extends Diagnostic_Base {

	protected static $slug = 'advanced-custom-fields-field-key-conflicts';
	protected static $title = 'Advanced Custom Fields Field Key Conflicts';
	protected static $description = 'Advanced Custom Fields Field Key Conflicts issue detected';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! class_exists( 'ACF' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Duplicate field keys.
		global $wpdb;
		$duplicate_keys = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT post_excerpt, COUNT(*) as cnt FROM {$wpdb->posts}
				WHERE post_type = %s AND post_status = %s AND post_excerpt != ''
				GROUP BY post_excerpt HAVING cnt > 1",
				'acf-field',
				'publish'
			)
		);
		if ( ! empty( $duplicate_keys ) ) {
			$count = count( $duplicate_keys );
			$issues[] = "{$count} duplicate field keys detected (data corruption risk)";
		}

		// Check 2: Missing field keys.
		$missing_keys = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s AND post_status = %s AND (post_excerpt = '' OR post_excerpt IS NULL)",
				'acf-field',
				'publish'
			)
		);
		if ( $missing_keys > 0 ) {
			$issues[] = "{$missing_keys} fields without unique keys (import/export issues)";
		}

		// Check 3: Field name collisions.
		$duplicate_names = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT post_title, COUNT(*) as cnt FROM {$wpdb->posts}
				WHERE post_type = %s AND post_status = %s
				GROUP BY post_title HAVING cnt > 1",
				'acf-field',
				'publish'
			)
		);
		if ( ! empty( $duplicate_names ) ) {
			$count = count( $duplicate_names );
			$issues[] = "{$count} field name collisions (data retrieval conflicts)";
		}

		// Check 4: Orphaned field data.
		$orphaned_meta = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->postmeta} pm
			LEFT JOIN {$wpdb->posts} p ON pm.meta_key = CONCAT('_', p.post_name)
			WHERE pm.meta_key LIKE 'field_%' AND p.ID IS NULL"
		);
		if ( $orphaned_meta > 0 ) {
			$issues[] = "{$orphaned_meta} orphaned field references (cleanup needed)";
		}

		// Check 5: JSON sync conflicts.
		$json_path = get_option( 'acf_json_save_path', '' );
		if ( ! empty( $json_path ) && is_dir( $json_path ) ) {
			$json_files = glob( $json_path . '/*.json' );
			if ( is_array( $json_files ) && count( $json_files ) > 0 ) {
				// Check for sync conflicts.
				$needs_sync = array();
				foreach ( $json_files as $file ) {
					$json_data = json_decode( file_get_contents( $file ), true );
					if ( isset( $json_data['key'] ) ) {
						$db_version = $wpdb->get_var(
							$wpdb->prepare(
								"SELECT post_modified FROM {$wpdb->posts} WHERE post_excerpt = %s AND post_type = %s",
								$json_data['key'],
								'acf-field-group'
							)
						);
						if ( $db_version && filemtime( $file ) > strtotime( $db_version ) ) {
							$needs_sync[] = basename( $file );
						}
					}
				}
				if ( ! empty( $needs_sync ) ) {
					$count = count( $needs_sync );
					$issues[] = "{$count} field groups out of sync with JSON files";
				}
			}
		}

		// Check 6: Field group key conflicts.
		$duplicate_group_keys = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT post_excerpt, COUNT(*) as cnt FROM {$wpdb->posts}
				WHERE post_type = %s AND post_status = %s AND post_excerpt != ''
				GROUP BY post_excerpt HAVING cnt > 1",
				'acf-field-group',
				'publish'
			)
		);
		if ( ! empty( $duplicate_group_keys ) ) {
			$count = count( $duplicate_group_keys );
			$issues[] = "{$count} duplicate field group keys (import conflicts)";
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 70, 40 + ( count( $issues ) * 6 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'ACF field key conflict issues: ' . implode( ', ', $issues ),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/advanced-custom-fields-field-key-conflicts',
			);
		}

		return null;
	}
}
