<?php
/**
 * Metabox Io Custom Tables Diagnostic
 *
 * Metabox Io Custom Tables issue detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1061.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Metabox Io Custom Tables Diagnostic Class
 *
 * @since 1.1061.0000
 */
class Diagnostic_MetaboxIoCustomTables extends Diagnostic_Base {

	protected static $slug = 'metabox-io-custom-tables';
	protected static $title = 'Metabox Io Custom Tables';
	protected static $description = 'Metabox Io Custom Tables issue detected';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! class_exists( 'MB_Custom_Table_API' ) && ! function_exists( 'mb_custom_table_load' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Custom tables defined
		$custom_tables = get_option( 'mb_custom_tables', array() );
		if ( ! empty( $custom_tables ) && is_array( $custom_tables ) ) {
			global $wpdb;
			foreach ( $custom_tables as $table ) {
				if ( isset( $table['name'] ) ) {
					$table_exists = $wpdb->get_var(
						$wpdb->prepare(
							"SHOW TABLES LIKE %s",
							$wpdb->prefix . $table['name']
						)
					);
					if ( ! $table_exists ) {
						$issues[] = "custom table '{$table['name']}' not created in database";
					}
				}
			}
		}

		// Check 2: Table indexes
		if ( ! empty( $custom_tables ) && is_array( $custom_tables ) ) {
			global $wpdb;
			foreach ( $custom_tables as $table ) {
				if ( isset( $table['name'] ) ) {
					$indexes = $wpdb->get_results(
						"SHOW INDEX FROM {$wpdb->prefix}{$table['name']}"
					);
					if ( empty( $indexes ) ) {
						$issues[] = "table '{$table['name']}' has no indexes (performance issue)";
					}
				}
			}
		}

		// Check 3: Table storage engine
		if ( ! empty( $custom_tables ) && is_array( $custom_tables ) ) {
			global $wpdb;
			foreach ( $custom_tables as $table ) {
				if ( isset( $table['name'] ) ) {
					$engine = $wpdb->get_var(
						$wpdb->prepare(
							"SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s",
							DB_NAME,
							$wpdb->prefix . $table['name']
						)
					);
					if ( $engine && 'InnoDB' !== $engine ) {
						$issues[] = "table '{$table['name']}' using {$engine} (recommend InnoDB)";
					}
				}
			}
		}

		// Check 4: Table row count vs postmeta
		if ( ! empty( $custom_tables ) && is_array( $custom_tables ) ) {
			global $wpdb;
			$postmeta_count = $wpdb->get_var(
				"SELECT COUNT(*) FROM {$wpdb->postmeta}"
			);
			foreach ( $custom_tables as $table ) {
				if ( isset( $table['name'] ) ) {
					$custom_count = $wpdb->get_var(
						"SELECT COUNT(*) FROM {$wpdb->prefix}{$table['name']}"
					);
					if ( $custom_count > $postmeta_count ) {
						$issues[] = "table '{$table['name']}' has more rows than postmeta (possible duplication)";
					}
				}
			}
		}

		// Check 5: Orphaned records
		if ( ! empty( $custom_tables ) && is_array( $custom_tables ) ) {
			global $wpdb;
			foreach ( $custom_tables as $table ) {
				if ( isset( $table['name'] ) ) {
					$orphaned = $wpdb->get_var(
						"SELECT COUNT(*) FROM {$wpdb->prefix}{$table['name']} ct
						 WHERE NOT EXISTS (SELECT 1 FROM {$wpdb->posts} p WHERE p.ID = ct.ID)"
					);
					if ( $orphaned > 0 ) {
						$issues[] = "{$orphaned} orphaned records in '{$table['name']}'";
					}
				}
			}
		}

		// Check 6: Backup strategy
		$backup_configured = get_option( 'mb_custom_tables_backup', '0' );
		if ( ! empty( $custom_tables ) && '0' === $backup_configured ) {
			$issues[] = 'custom tables not included in backup strategy';
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 70, 40 + ( count( $issues ) * 6 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'Metabox.io custom tables issues: ' . implode( ', ', $issues ),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/metabox-io-custom-tables',
			);
		}

		return null;
	}
}
