<?php
/**
 * WP All Import Duplicate Detection Diagnostic
 *
 * Duplicate detection not enabled.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.275.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WP All Import Duplicate Detection Diagnostic Class
 *
 * @since 1.275.0000
 */
class Diagnostic_WpAllImportDuplicateDetection extends Diagnostic_Base {

	protected static $slug = 'wp-all-import-duplicate-detection';
	protected static $title = 'WP All Import Duplicate Detection';
	protected static $description = 'Duplicate detection not enabled';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! class_exists( 'PMXI_Plugin' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Verify duplicate detection is enabled
		$duplicate_handling = get_option( 'pmxi_duplicate_handling', 'create' );
		if ( $duplicate_handling === 'create' ) {
			$issues[] = 'Duplicate detection not enabled (creating duplicates)';
		}
		
		// Check 2: Check unique identifier configuration
		$unique_key = get_option( 'pmxi_unique_key', '' );
		if ( empty( $unique_key ) ) {
			$issues[] = 'Unique identifier not configured';
		}
		
		// Check 3: Verify update vs. skip behavior
		$update_missing = get_option( 'pmxi_is_update_missing_cf', 0 );
		if ( ! $update_missing && $duplicate_handling === 'update' ) {
			$issues[] = 'Update behavior not configured for missing fields';
		}
		
		// Check 4: Check for deletion of missing records
		$delete_missing = get_option( 'pmxi_is_delete_missing', 0 );
		if ( $delete_missing ) {
			$issues[] = 'Auto-deletion enabled (could cause data loss)';
		}
		
		// Check 5: Verify import logging
		$logging = get_option( 'pmxi_enable_logs', 0 );
		if ( ! $logging ) {
			$issues[] = 'Import logging not enabled';
		}
		
		// Check 6: Check for scheduled imports without monitoring
		$cron_enabled = get_option( 'pmxi_cron_enabled', 0 );
		if ( $cron_enabled ) {
			$cron_notification = get_option( 'pmxi_cron_email', '' );
			if ( empty( $cron_notification ) ) {
				$issues[] = 'Scheduled imports without email notifications';
			}
		}
		
		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 40;
			$threat_multiplier = 6;
			$max_threat = 70;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );
			
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d WP All Import duplicate detection issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/wp-all-import-duplicate-detection',
			);
		}
		
		return null;
	}
}
