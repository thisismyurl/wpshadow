<?php
/**
 * Smush Pro Backup Originals Diagnostic
 *
 * Smush Pro Backup Originals detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.761.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Smush Pro Backup Originals Diagnostic Class
 *
 * @since 1.761.0000
 */
class Diagnostic_SmushProBackupOriginals extends Diagnostic_Base {

	protected static $slug = 'smush-pro-backup-originals';
	protected static $title = 'Smush Pro Backup Originals';
	protected static $description = 'Smush Pro Backup Originals detected';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'WP_SMUSH_VERSION' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Backup originals enabled
		$backup = get_option( 'wp_smush_backup_originals_enabled', 0 );
		if ( ! $backup ) {
			$issues[] = 'Backup originals not enabled';
		}
		
		// Check 2: Storage quota
		$quota = absint( get_option( 'wp_smush_backup_quota_configured', 0 ) );
		if ( $quota <= 0 ) {
			$issues[] = 'Backup storage quota not configured';
		}
		
		// Check 3: Automatic cleanup
		$cleanup = get_option( 'wp_smush_backup_cleanup_schedule_enabled', 0 );
		if ( ! $cleanup ) {
			$issues[] = 'Backup automatic cleanup not enabled';
		}
		
		// Check 4: Local backup
		$local = get_option( 'wp_smush_local_backup_enabled', 0 );
		if ( ! $local ) {
			$issues[] = 'Local backup not enabled';
		}
		
		// Check 5: Cloud backup
		$cloud = get_option( 'wp_smush_cloud_backup_enabled', 0 );
		if ( ! $cloud ) {
			$issues[] = 'Cloud backup not enabled';
		}
		
		// Check 6: Restore functionality
		$restore = get_option( 'wp_smush_restore_functionality_enabled', 0 );
		if ( ! $restore ) {
			$issues[] = 'Restore functionality not enabled';
		}
		
		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 35;
			$threat_multiplier = 6;
			$max_threat = 65;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );
			
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d backup issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/smush-pro-backup-originals',
			);
		}
		
		return null;
	}
}
