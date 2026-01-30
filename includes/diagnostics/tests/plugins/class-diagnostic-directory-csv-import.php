<?php
/**
 * Directory CSV Import Diagnostic
 *
 * Directory CSV imports insecure.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.566.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Directory CSV Import Diagnostic Class
 *
 * @since 1.566.0000
 */
class Diagnostic_DirectoryCsvImport extends Diagnostic_Base {

	protected static $slug = 'directory-csv-import';
	protected static $title = 'Directory CSV Import';
	protected static $description = 'Directory CSV imports insecure';
	protected static $family = 'security';

	public static function check() {
		if ( ! function_exists( 'wpbdp' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: CSV validation enabled
		$csv_validation = get_option( 'wpbdp_csv_validation_enabled', 0 );
		if ( ! $csv_validation ) {
			$issues[] = 'CSV validation not enabled';
		}
		
		// Check 2: File size limit
		$file_limit = absint( get_option( 'wpbdp_csv_file_size_limit_mb', 0 ) );
		if ( $file_limit <= 0 ) {
			$issues[] = 'CSV file size limit not set';
		}
		
		// Check 3: Data sanitization
		$sanitize = get_option( 'wpbdp_csv_data_sanitization', 0 );
		if ( ! $sanitize ) {
			$issues[] = 'CSV data sanitization not enabled';
		}
		
		// Check 4: Duplicate detection
		$dup_detect = get_option( 'wpbdp_csv_duplicate_detection', 0 );
		if ( ! $dup_detect ) {
			$issues[] = 'Duplicate entry detection not enabled';
		}
		
		// Check 5: Import backup
		$backup = get_option( 'wpbdp_csv_import_backup', 0 );
		if ( ! $backup ) {
			$issues[] = 'Pre-import backup not enabled';
		}
		
		// Check 6: Error logging
		$logging = get_option( 'wpbdp_csv_error_logging', 0 );
		if ( ! $logging ) {
			$issues[] = 'Import error logging not enabled';
		}
		
		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 55;
			$threat_multiplier = 6;
			$max_threat = 85;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );
			
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d CSV import security issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/directory-csv-import',
			);
		}
		
		return null;
	}
}
