<?php
/**
 * Caldera Forms Database Entries Diagnostic
 *
 * Caldera Forms database entries growing.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.473.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Caldera Forms Database Entries Diagnostic Class
 *
 * @since 1.473.0000
 */
class Diagnostic_CalderaFormsDatabaseEntries extends Diagnostic_Base {

	protected static $slug = 'caldera-forms-database-entries';
	protected static $title = 'Caldera Forms Database Entries';
	protected static $description = 'Caldera Forms database entries growing';
	protected static $family = 'performance';

	public static function check() {
		if ( ! class_exists( 'Caldera_Forms' ) ) {
			return null;
		}

		global $wpdb;
		$issues = array();

		// Check 1: Entry count
		$entry_count = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->prefix}cf_form_entries"
		);

		if ( $entry_count === null ) {
			return null;
		}

		if ( $entry_count > 10000 ) {
			$issues[] = sprintf( __( '%s entries (database bloat)', 'wpshadow' ), number_format( $entry_count ) );
		}

		// Check 2: Entry meta count
		$meta_count = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->prefix}cf_form_entry_meta"
		);

		if ( $meta_count > 50000 ) {
			$issues[] = sprintf( __( '%s meta rows (slow queries)', 'wpshadow' ), number_format( $meta_count ) );
		}

		// Check 3: Auto-delete old entries
		$auto_delete = get_option( 'caldera_forms_auto_delete', 'no' );
		if ( 'no' === $auto_delete ) {
			$issues[] = __( 'Old entries never deleted (grows forever)', 'wpshadow' );
		}

		// Check 4: File uploads
		$file_count = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->prefix}cf_form_entry_meta
			 WHERE meta_key LIKE '%_file' OR meta_key LIKE '%_upload'"
		);

		if ( $file_count > 1000 ) {
			$issues[] = sprintf( __( '%d file uploads (disk space)', 'wpshadow' ), $file_count );
		}

		// Check 5: Database optimization
		$last_optimized = get_option( 'caldera_forms_last_db_optimize', 0 );
		if ( $last_optimized === 0 || ( time() - $last_optimized ) > ( 90 * DAY_IN_SECONDS ) ) {
			$issues[] = __( 'Database not optimized recently (fragmentation)', 'wpshadow' );
		}

		// Check 6: Entry export
		$export_enabled = get_option( 'caldera_forms_enable_export', 'no' );
		if ( 'no' === $export_enabled ) {
			$issues[] = __( 'Export disabled (data portability)', 'wpshadow' );
		}

		if ( empty( $issues ) ) {
			return null;
		}

		$threat_level = 50;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 62;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 56;
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				__( 'Caldera Forms database has %d issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/caldera-forms-database-entries',
		);
	}
}
